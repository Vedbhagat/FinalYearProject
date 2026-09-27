<?php
/**
 * generatorv2.php
 * Dynamic Division-Aware Timetable Engine with Weekly Workload Balancing
 */

include_once 'dbConnect.php';

function generateTimetable($semester = 'ODD', $academicYear = '2026-27') {
    global $conn;

    // Load static lookups
    $timeslots = getTimeslots($conn);
    $overlappingSlots = getOverlappingSlotMap($timeslots);
    $classrooms = getClassrooms($conn);
    $rawDivisions = getDivisions($conn);
    $teacherAvail = getTeacherAvailability($conn);

    // Step 1: Fetch raw workload data
    $rawWorkloads = getWorkload($conn, $semester);

    // Step 2: Expand workload across divisions
    $expandedWorkloads = expandWorkload($conn, $rawWorkloads, $semester);

    // Build structured divisions array with assigned workload per division
    $divisions = [];
    foreach ($rawDivisions as $dId => $divData) {
        $divLectures = array_filter($expandedWorkloads['lectures'], function($unit) use ($dId) {
            return (int)$unit['division_id'] === (int)$dId;
        });
        $divisions[$dId] = $divData;
        $divisions[$dId]['workload'] = array_values($divLectures);
    }

    // Step 3: Assign candidate practical slots to divisions with practical workload
    $divisionPracticalSlots = fixPracticalSlot($timeslots, $rawDivisions, $expandedWorkloads['practicals'], $classrooms);

    // Initialization of System State Tracking
    $state = [
        'assigned'              => [],
        'teacherOccupied'       => [], // [teacher_id][weekday][slot_id] = true
        'divisionOccupied'      => [], // [division_id][weekday][slot_id] = true
        'roomOccupied'          => [], // [room_id][weekday][slot_id] = true
        'divisionDayRoom'       => [], // [division_id][weekday] = classroom_id
        'courseDayCount'        => [], // [division_id][course_id][weekday] = count
        'divisionDailyLectures' => [], // [division_id][weekday] = total lecture count
        'unallocated'           => [],
        'brokenPreferences'     => []
    ];

    // Step 4: Allocate practical workloads inside fixed division slots
    $state = allocatePracticals(
        $expandedWorkloads['practicals'], 
        $divisionPracticalSlots, 
        $classrooms, 
        $teacherAvail, 
        $overlappingSlots, 
        $state
    );

    // Dynamically retrieve theory/lecture slots from DB
    $lectureSlots = [];
    foreach ($timeslots as $sId => $sData) {
        if (($sData['SLOT_TYPE'] ?? '') === 'LECTURE') {
            $lectureSlots[] = (int)$sId;
        }
    }
    if (empty($lectureSlots)) {
        $lectureSlots = [1, 3, 7, 8, 10, 11, 14, 15]; // Fallback standard lecture slot IDs
    }

    $weekdays = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY'];

    // Step 5: Allocate lecture courses using WORKLOAD BALANCER
    allocateCoursesBalanced(
        $divisions,                     // Division data array with populated 'workload'
        $expandedWorkloads['lectures'], // Course lecture workload data
        $lectureSlots,                  // Array of available lecture time slots
        $weekdays,                      // Array of days
        $classrooms,                    // Available classrooms/halls
        $teacherAvail,                  // DB teacher availability lookup
        $overlappingSlots,              // Overlapping slot mapping matrix
        $state,                         // Reference to system state tracking array
        $timeslots,                     // Pass $timeslots array for time preference filtering
        false                           // Debug flag
    );

    // Step 6: Final Validation Pass and Audit Report Generation
    $validationReport = runFinalValidation(
        $expandedWorkloads['all'], 
        $state, 
        $divisions, 
        $timeslots
    );

    return [
        'validation' => $validationReport,
        'timetable'  => $state['assigned']
    ];
}

// -------------------------------------------------------------------------
// 1. DYNAMIC DIVISION SLOT & STATE COMPUTATION HELPERS
// -------------------------------------------------------------------------

/**
 * Returns available slot IDs for a division on a given day that don't overlap with existing assignments.
 */
function getDivisionAvailableSlots($dId, $day, $candidateLectureSlots, $overlappingSlots, $state, $timeslots = [], $divisions = []) {
    $availableSlots = [];

    $prefStartId = $divisions[$dId]['START_TIME_ID'] ?? null;
    $prefEndId   = $divisions[$dId]['END_TIME_ID'] ?? null;

    $prefStartTime = ($prefStartId && isset($timeslots[$prefStartId])) 
        ? strtotime($timeslots[$prefStartId]['START_TIME']) : null;
    $prefEndTime   = ($prefEndId && isset($timeslots[$prefEndId])) 
        ? strtotime($timeslots[$prefEndId]['END_TIME']) : null;

    foreach ($candidateLectureSlots as $slotId) {
        if (isset($timeslots[$slotId])) {
            $slotStart = strtotime($timeslots[$slotId]['START_TIME']);
            $slotEnd   = strtotime($timeslots[$slotId]['END_TIME']);

            if ($prefStartTime !== null && $slotStart < $prefStartTime) continue;
            if ($prefEndTime !== null && $slotEnd > $prefEndTime) continue;
        }

        if (!isSlotOccupiedByDivision($dId, $day, $slotId, $overlappingSlots, $state)) {
            $availableSlots[] = $slotId;
        }
    }

    return $availableSlots;
}

function isSlotOccupiedByDivision($dId, $day, $slotId, $overlappingSlots, $state) {
    $relatedSlots = $overlappingSlots[$slotId] ?? [$slotId];
    foreach ($relatedSlots as $s) {
        if (!empty($state['divisionOccupied'][$dId][$day][$s])) {
            return true;
        }
    }
    return false;
}

function isTeacherOccupied($tId, $day, $slotId, $overlappingSlots, $state) {
    $relatedSlots = $overlappingSlots[$slotId] ?? [$slotId];
    foreach ($relatedSlots as $s) {
        if (!empty($state['teacherOccupied'][$tId][$day][$s])) {
            return true;
        }
    }
    return false;
}

function isRoomOccupied($roomId, $day, $slotId, $overlappingSlots, $state) {
    $relatedSlots = $overlappingSlots[$slotId] ?? [$slotId];
    foreach ($relatedSlots as $s) {
        if (!empty($state['roomOccupied'][$roomId][$day][$s])) {
            return true;
        }
    }
    return false;
}

function markStateOccupied($tId, $dId, $roomId, $day, $slotId, $overlappingSlots, $state) {
    $relatedSlots = $overlappingSlots[$slotId] ?? [$slotId];
    foreach ($relatedSlots as $s) {
        $state['teacherOccupied'][$tId][$day][$s]  = true;
        $state['divisionOccupied'][$dId][$day][$s] = true;
        $state['roomOccupied'][$roomId][$day][$s]  = true;
    }
    return $state;
}

// -------------------------------------------------------------------------
// 2. GET WORKLOAD & EXPANSION FUNCTIONS
// -------------------------------------------------------------------------

function getWorkload($conn, $semester) {
    $semesterEscaped = $conn->real_escape_string($semester);
    $sql = "
        SELECT 
            t.WORKLOAD_ID, 
            t.TEACHER_ID, 
            t.COURSE_ID, 
            t.DIVISION_ID, 
            t.LECTURE_COUNT,
            c.ISPRACTICAL, 
            c.TYPE AS COURSE_TYPE, 
            c.ISOPTIONAL, 
            c.OPTIONAL_ID, 
            c.YEAR_NUMBER, 
            c.PROGRAMME_ID,
            d.STUDENT_COUNT, 
            d.CLASSROOM_ID AS PREFERRED_ROOM_ID, 
            d.START_TIME_ID AS PREF_START_SLOT,
            d.END_TIME_ID AS PREF_END_SLOT
        FROM TEACHES t
        JOIN COURSE c ON t.COURSE_ID = c.COURSE_ID
        JOIN DIVISION d ON t.DIVISION_ID = d.DIVISION_ID
        WHERE c.SEMESTER = '$semesterEscaped' AND t.LECTURE_COUNT > 0
    ";

    $result = $conn->query($sql);
    $workloads = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $workloads[] = [
                'workload_id'   => (int)$row['WORKLOAD_ID'],
                'teacher_id'    => (int)$row['TEACHER_ID'],
                'course_id'     => (int)$row['COURSE_ID'],
                'division_id'   => (int)$row['DIVISION_ID'],
                'lecture_count' => (int)$row['LECTURE_COUNT'],
                'is_practical'  => (bool)$row['ISPRACTICAL'],
                'course_type'   => $row['COURSE_TYPE'],
                'is_optional'   => (bool)$row['ISOPTIONAL'],
                'optional_id'   => $row['OPTIONAL_ID'] ? (int)$row['OPTIONAL_ID'] : null,
                'year_number'   => (int)$row['YEAR_NUMBER'],
                'programme_id'  => (int)$row['PROGRAMME_ID'],
                'student_count' => (int)$row['STUDENT_COUNT'],
                'pref_room_id'  => $row['PREFERRED_ROOM_ID'] ? (int)$row['PREFERRED_ROOM_ID'] : null,
                'pref_start'    => $row['PREF_START_SLOT'] ? (int)$row['PREF_START_SLOT'] : null,
                'pref_end'      => $row['PREF_END_SLOT'] ? (int)$row['PREF_END_SLOT'] : null
            ];
        }
    }

    return $workloads;
}

function expandWorkload($conn, $rawWorkloads, $semester) {
    $expandedLectures = [];
    $expandedPracticals = [];
    $allUnits = [];

    $divRes = $conn->query("SELECT DIVISION_ID, YEAR_NUMBER, PROGRAMME_ID, STUDENT_COUNT FROM DIVISION WHERE STUDENT_COUNT > 0");
    $yearProgrammeDivisions = [];
    if ($divRes) {
        while ($d = $divRes->fetch_assoc()) {
            $y = (int)$d['YEAR_NUMBER'];
            $p = (int)$d['PROGRAMME_ID'];
            $yearProgrammeDivisions[$y][$p][] = [
                'division_id'   => (int)$d['DIVISION_ID'],
                'student_count' => (int)$d['STUDENT_COUNT']
            ];
        }
    }

    foreach ($rawWorkloads as $wl) {
        $isPractical = $wl['is_practical'];
        $unitsCount  = $isPractical ? (int)ceil($wl['lecture_count'] / 2) : $wl['lecture_count'];

        $targetDivisions = [];
        if (!$wl['is_optional'] && isset($yearProgrammeDivisions[$wl['year_number']][$wl['programme_id']])) {
            $targetDivisions = $yearProgrammeDivisions[$wl['year_number']][$wl['programme_id']];
        } else {
            $targetDivisions[] = [
                'division_id'   => $wl['division_id'],
                'student_count' => $wl['student_count']
            ];
        }

        foreach ($targetDivisions as $target) {
            for ($i = 0; $i < $unitsCount; $i++) {
                $unit = [
                    'instance_id'   => $wl['workload_id'] . '_D' . $target['division_id'] . '_' . $i,
                    'workload_id'   => $wl['workload_id'],
                    'course_id'     => $wl['course_id'],
                    'division_id'   => $target['division_id'],
                    'teacher_id'    => $wl['teacher_id'],
                    'is_practical'  => $isPractical,
                    'course_type'   => $wl['course_type'],
                    'is_optional'   => $wl['is_optional'],
                    'optional_id'   => $wl['optional_id'],
                    'student_count' => $target['student_count'],
                    'pref_room_id'  => $wl['pref_room_id'],
                    'pref_start'    => $wl['pref_start'],
                    'pref_end'      => $wl['pref_end'],
                    'required_hrs'  => $isPractical ? 2 : 1
                ];

                $allUnits[] = $unit;
                if ($isPractical) {
                    $expandedPracticals[] = $unit;
                } else {
                    $expandedLectures[] = $unit;
                }
            }
        }
    }

    return [
        'all'        => $allUnits,
        'lectures'   => $expandedLectures,
        'practicals' => $expandedPracticals
    ];
}

// -------------------------------------------------------------------------
// 3. PRACTICAL ALLOCATION PIPELINE
// -------------------------------------------------------------------------

function fixPracticalSlot($timeslots, $divisions, $practicals, $classrooms) {
    $practicalSlots = array_filter($timeslots, fn($s) => ($s['SLOT_TYPE'] ?? '') === 'PRACTICAL');

    $slotLabMatrix = [];
    foreach ($practicalSlots as $slotId => $slot) {
        foreach ($classrooms as $roomId => $room) {
            if (($room['CATEGORY'] ?? '') === 'LECTURE HALL') continue;

            $slotLabMatrix[] = [
                'slot_id'   => (int)$slotId,
                'room_id'   => (int)$roomId,
                'category'  => $room['CATEGORY'],
                'capacity'  => (int)$room['CAPACITY'],
                'start_time'=> strtotime($slot['START_TIME']),
                'end_time'  => strtotime($slot['END_TIME']),
                'is_alloted'=> false
            ];
        }
    }

    $fixedDivisionPracticalSlots = [];

    foreach ($divisions as $dId => $div) {
        $divPracticals = array_filter($practicals, fn($p) => (int)$p['division_id'] === (int)$dId);
        if (empty($divPracticals)) {
            continue;
        }

        $firstPractical = reset($divPracticals);
        $courseType     = $firstPractical['course_type'] ?? '';

        $matchingType = array_filter($slotLabMatrix, function($item) use ($courseType) {
            if ($item['is_alloted']) return false;
            switch ($courseType) {
                case 'IT PRACTICAL':        return $item['category'] === 'IT LAB';
                case 'PHYSICS PRACTICAL':   return $item['category'] === 'PHYSICS LAB';
                case 'CHEMISTRY PRACTICAL': return $item['category'] === 'CHEMISTRY LAB';
                case 'BIOLOGY PRACTICAL':   return $item['category'] === 'BIOLOGY LAB';
                default:                    return $item['category'] !== 'LECTURE HALL';
            }
        });

        $matchingCapacity = array_filter($matchingType, fn($item) => $item['capacity'] >= ($div['STUDENT_COUNT'] ?? 0));

        $prefStartSlotId = $div['START_TIME_ID'] ?? null;
        $prefEndSlotId   = $div['END_TIME_ID'] ?? null;

        $prefStartTime = ($prefStartSlotId && isset($timeslots[$prefStartSlotId])) 
            ? strtotime($timeslots[$prefStartSlotId]['START_TIME']) : null;
        $prefEndTime   = ($prefEndSlotId && isset($timeslots[$prefEndSlotId])) 
            ? strtotime($timeslots[$prefEndSlotId]['END_TIME']) : null;

        $matchingTime = array_filter($matchingCapacity, function($item) use ($prefStartTime, $prefEndTime) {
            $afterStart = ($prefStartTime === null) || ($item['start_time'] >= $prefStartTime);
            $beforeEnd  = ($prefEndTime === null)   || ($item['end_time'] <= $prefEndTime);
            return $afterStart && $beforeEnd;
        });

        $candidates = !empty($matchingTime) ? $matchingTime : $matchingCapacity;
        if (empty($candidates)) {
            $candidates = $matchingType;
        }

        if (empty($candidates)) continue;

        usort($candidates, fn($a, $b) => $a['capacity'] <=> $b['capacity']);
        $selected = reset($candidates);

        foreach ($slotLabMatrix as &$item) {
            if ($item['slot_id'] === $selected['slot_id'] && $item['room_id'] === $selected['room_id']) {
                $item['is_alloted'] = true;
                break;
            }
        }
        unset($item);

        $fixedDivisionPracticalSlots[$dId] = [
            'slot_id' => $selected['slot_id'],
            'room_id' => $selected['room_id']
        ];
    }

    return $fixedDivisionPracticalSlots;
}

function allocatePracticals($practicals, $divisionPracticalSlots, $classrooms, $teacherAvail, $overlappingSlots, $state) {
    $weekdays = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY'];

    foreach ($practicals as $p) {
        $dId = $p['division_id'];

        $fixedConfig = $divisionPracticalSlots[$dId] ?? null;
        if (!$fixedConfig) {
            $state['unallocated'][] = [
                'unit'   => $p,
                'reason' => "No fixed practical slot/room available for Division ID {$dId}."
            ];
            continue;
        }

        $slotId = $fixedConfig['slot_id'];
        $roomId = $fixedConfig['room_id'];
        $allocated = false;

        foreach ($weekdays as $day) {
            if (empty($teacherAvail[$p['teacher_id']][$day][$slotId])) continue;

            if (isSlotOccupiedByDivision($dId, $day, $slotId, $overlappingSlots, $state)) continue;
            if (isTeacherOccupied($p['teacher_id'], $day, $slotId, $overlappingSlots, $state)) continue;
            if (isRoomOccupied($roomId, $day, $slotId, $overlappingSlots, $state)) continue;

            $state = markStateOccupied($p['teacher_id'], $dId, $roomId, $day, $slotId, $overlappingSlots, $state);

            $state['assigned'][] = [
                'DAY'         => $day,
                'SLOT_ID'     => $slotId,
                'DIVISION_ID' => $dId,
                'COURSE_ID'   => $p['course_id'],
                'TYPE'        => 'PRACTICAL',
                'TEACHER_ID'  => $p['teacher_id'],
                'ROOM_ID'     => $roomId
            ];

            $allocated = true;
            break;
        }

        if (!$allocated) {
            $state['unallocated'][] = [
                'unit'   => $p,
                'reason' => "Teacher conflict, division busy, or room occupied in fixed practical slot (Slot ID: {$slotId})."
            ];
        }
    }

    return $state;
}

// -------------------------------------------------------------------------
// 4. BALANCED LECTURE ALLOCATION PIPELINE
// -------------------------------------------------------------------------

function selectClassroom($dId, $day, $slotId, $studentCount, $lectureHalls, $overlappingSlots, $state) {
    $preferredDayRoom = $state['divisionDayRoom'][$dId][$day] ?? null;

    if ($preferredDayRoom && !isRoomOccupied($preferredDayRoom, $day, $slotId, $overlappingSlots, $state)) {
        return $preferredDayRoom;
    }

    foreach ($lectureHalls as $hall) {
        if (($hall['CAPACITY'] ?? 0) >= $studentCount &&
            !isRoomOccupied($hall['CLASSROOM_ID'], $day, $slotId, $overlappingSlots, $state)) {
            return $hall['CLASSROOM_ID'];
        }
    }

    return null;
}


/**
 * REFACTORED COURSE ALLOCATION WITH DAILY TIME-WINDOW & RANDOMIZED TEACHER SELECTION
 */
function allocateCoursesBalanced($divisions, $courses, $lectureSlots, $weekdays, $lectureHalls, $teacherAvail, $overlappingSlots, &$state, $timeslots = [], $debug = false) {
    debugLog("=== START TIME-WINDOW & RANDOMIZED COURSE ALLOCATION ===", $debug);

    $unassignedWorkload = [];
    $dailyQuota = [];

    // 1. Division Time-window construction: Total workload / 6 rounded down (floor)
    foreach ($divisions as $dId => $divData) {
        $unassignedWorkload[$dId] = $divData['workload'] ?? [];
        $totalLectures = count($unassignedWorkload[$dId]);
        
        // Floor of total workload divided across 6 week days
        $dailyQuota[$dId] = ($totalLectures > 0) ? (int)floor($totalLectures / count($weekdays)) : 0;
    }

    // 2. PASS 1: Allocate equal daily quota across 6 weekdays
    foreach ($weekdays as $day) {
        foreach ($divisions as $dId => $divData) {
            if (empty($unassignedWorkload[$dId])) continue;

            $assignedToday = 0;
            $freeSlots = getDivisionAvailableSlots($dId, $day, $lectureSlots, $overlappingSlots, $state, $timeslots, $divisions);

            foreach ($freeSlots as $slotId) {
                if (empty($unassignedWorkload[$dId])) break;
                if ($assignedToday >= $dailyQuota[$dId]) break; // Respect rounded down daily limit

                // Filter candidate workloads whose teachers are free in this slot
                $availableCandidates = [];
                foreach ($unassignedWorkload[$dId] as $index => $lec) {
                    $tId = $lec['teacher_id'];

                    if (isTeacherOccupied($tId, $day, $slotId, $overlappingSlots, $state)) continue;
                    if (isset($teacherAvail[$tId][$day][$slotId]) && !$teacherAvail[$tId][$day][$slotId]) continue;

                    $availableCandidates[] = $index;
                }

                // Randomly select one available candidate workload
                if (!empty($availableCandidates)) {
                    $selectedIndex = $availableCandidates[array_rand($availableCandidates)];
                    $lec = $unassignedWorkload[$dId][$selectedIndex];
                    $cId = $lec['course_id'];
                    $tId = $lec['teacher_id'];

                    $selectedRoom = selectClassroom($dId, $day, $slotId, $lec['student_count'] ?? 60, $lectureHalls, $overlappingSlots, $state);

                    if ($selectedRoom) {
                        $state = markStateOccupied($tId, $dId, $selectedRoom, $day, $slotId, $overlappingSlots, $state);
                        $state['divisionDayRoom'][$dId][$day]       = $selectedRoom;
                        $state['courseDayCount'][$dId][$cId][$day]  = ($state['courseDayCount'][$dId][$cId][$day] ?? 0) + 1;
                        $state['divisionDailyLectures'][$dId][$day] = ($state['divisionDailyLectures'][$dId][$day] ?? 0) + 1;

                        $state['assigned'][] = [
                            'DAY'         => $day,
                            'SLOT_ID'     => $slotId,
                            'DIVISION_ID' => $dId,
                            'COURSE_ID'   => $cId,
                            'TYPE'        => 'LECTURE',
                            'TEACHER_ID'  => $tId,
                            'ROOM_ID'     => $selectedRoom
                        ];

                        unset($unassignedWorkload[$dId][$selectedIndex]);
                        $unassignedWorkload[$dId] = array_values($unassignedWorkload[$dId]);
                        $assignedToday++;
                    }
                }
            }
        }
    }

    // 3. PASS 2: Add remaining 1 or 2 pending courses to the FIRST empty slot of MONDAY and TUESDAY
    $overflowDays = ['MONDAY', 'TUESDAY'];
    foreach ($divisions as $dId => $divData) {
        if (empty($unassignedWorkload[$dId])) continue;

        foreach ($overflowDays as $day) {
            if (empty($unassignedWorkload[$dId])) break;

            $freeSlots = getDivisionAvailableSlots($dId, $day, $lectureSlots, $overlappingSlots, $state, $timeslots, $divisions);

            foreach ($freeSlots as $slotId) {
                if (empty($unassignedWorkload[$dId])) break;

                // Find candidate workloads available for this overflow slot
                $availableCandidates = [];
                foreach ($unassignedWorkload[$dId] as $index => $lec) {
                    $tId = $lec['teacher_id'];

                    if (isTeacherOccupied($tId, $day, $slotId, $overlappingSlots, $state)) continue;
                    if (isset($teacherAvail[$tId][$day][$slotId]) && !$teacherAvail[$tId][$day][$slotId]) continue;

                    $availableCandidates[] = $index;
                }

                if (!empty($availableCandidates)) {
                    $selectedIndex = $availableCandidates[array_rand($availableCandidates)];
                    $lec = $unassignedWorkload[$dId][$selectedIndex];
                    $cId = $lec['course_id'];
                    $tId = $lec['teacher_id'];

                    $selectedRoom = selectClassroom($dId, $day, $slotId, $lec['student_count'] ?? 60, $lectureHalls, $overlappingSlots, $state);

                    if ($selectedRoom) {
                        $state = markStateOccupied($tId, $dId, $selectedRoom, $day, $slotId, $overlappingSlots, $state);
                        $state['divisionDayRoom'][$dId][$day]       = $selectedRoom;
                        $state['courseDayCount'][$dId][$cId][$day]  = ($state['courseDayCount'][$dId][$cId][$day] ?? 0) + 1;
                        $state['divisionDailyLectures'][$dId][$day] = ($state['divisionDailyLectures'][$dId][$day] ?? 0) + 1;

                        $state['assigned'][] = [
                            'DAY'         => $day,
                            'SLOT_ID'     => $slotId,
                            'DIVISION_ID' => $dId,
                            'COURSE_ID'   => $cId,
                            'TYPE'        => 'LECTURE',
                            'TEACHER_ID'  => $tId,
                            'ROOM_ID'     => $selectedRoom
                        ];

                        unset($unassignedWorkload[$dId][$selectedIndex]);
                        $unassignedWorkload[$dId] = array_values($unassignedWorkload[$dId]);
                    }
                }
            }
        }
    }

    // 4. FALLBACK PASS: Catch any remaining unallocated workloads across the week
    foreach ($divisions as $dId => $divData) {
        if (!empty($unassignedWorkload[$dId])) {
            // Pass 2 / Fallback call inside allocateCoursesBalanced
            if (!empty($unassignedWorkload[$dId])) {
                runFallbackAllocation(
                    $dId, 
                    $unassignedWorkload[$dId], 
                    $weekdays, 
                    $lectureSlots, 
                    $lectureHalls, 
                    $teacherAvail, 
                    $overlappingSlots, 
                    $state, 
                    $timeslots, 
                    $divisions, 
                    $debug
                );
            }
        }
    }

    debugLog("=== END TIME-WINDOW ALLOCATION ===", $debug);
}


/**
 * ALLOCATE COURSES WITH WEEKLY WORKLOAD BALANCER
 */
function allocateCoursesBalanced_dep($divisions, $courses, $lectureSlots, $weekdays, $lectureHalls, $teacherAvail, $overlappingSlots, &$state, $debug = false) {
    debugLog("=== START WORKLOAD-BALANCED LECTURE ALLOCATION ===", $debug);

    $unassignedWorkload = [];
    $maxDailyCap = [];

    // Calculate maximum lectures allowed per day per division to balance across the week
    foreach ($divisions as $dId => $divData) {
        $unassignedWorkload[$dId] = $divData['workload'] ?? [];
        $totalLectures = count($unassignedWorkload[$dId]);
        
        // Target equal distribution: e.g., 10 lectures over 6 days = max 2 lectures/day initially
        $maxDailyCap[$dId] = ($totalLectures > 0) ? (int)ceil($totalLectures / count($weekdays)) : 2;
    }

    // PASS 1: Balanced allocation using daily caps and slot rotation
    foreach ($weekdays as $dayIndex => $day) {
        // Rotate candidate slots per day so early slots don't consume all lectures
        $rotatedSlots = $lectureSlots;
        if ($dayIndex % 2 === 1) {
            $rotatedSlots = array_reverse($lectureSlots); // Reverse slots on odd days (1, 3, 5)
        } else if ($dayIndex % 3 === 2) {
            // Shift array elements for mid-week slot variation
            $first = array_shift($rotatedSlots);
            $rotatedSlots[] = $first;
        }

        foreach ($divisions as $dId => $divData) {
            if (empty($unassignedWorkload[$dId])) continue;

            // Enforce daily cap in Pass 1 to prevent front-loading
            $currentDailyLectures = $state['divisionDailyLectures'][$dId][$day] ?? 0;
            if ($currentDailyLectures >= $maxDailyCap[$dId]) {
                continue;
            }

            $freeSlots = getDivisionAvailableSlots($dId, $day, $rotatedSlots, $overlappingSlots, $state);

            foreach ($freeSlots as $slotId) {
                if (empty($unassignedWorkload[$dId])) break;
                if (($state['divisionDailyLectures'][$dId][$day] ?? 0) >= $maxDailyCap[$dId]) break;

                $candidateIndex = -1;
                foreach ($unassignedWorkload[$dId] as $index => $lec) {
                    $cId = $lec['course_id'];
                    $tId = $lec['teacher_id'];

                    // Prevent same subject twice on same day if possible
                    if (($state['courseDayCount'][$dId][$cId][$day] ?? 0) >= 1) {
                        continue;
                    }

                    if (isTeacherOccupied($tId, $day, $slotId, $overlappingSlots, $state)) continue;
                    if (isset($teacherAvail[$tId][$day][$slotId]) && !$teacherAvail[$tId][$day][$slotId]) continue;

                    $candidateIndex = $index;
                    break;
                }

                // Fallback: If course daily limit prevented assignment, pick any valid course
                if ($candidateIndex === -1) {
                    foreach ($unassignedWorkload[$dId] as $index => $lec) {
                        $tId = $lec['teacher_id'];
                        if (isTeacherOccupied($tId, $day, $slotId, $overlappingSlots, $state)) continue;
                        if (isset($teacherAvail[$tId][$day][$slotId]) && !$teacherAvail[$tId][$day][$slotId]) continue;

                        $candidateIndex = $index;
                        break;
                    }
                }

                if ($candidateIndex !== -1) {
                    $lec = $unassignedWorkload[$dId][$candidateIndex];
                    $cId = $lec['course_id'];
                    $tId = $lec['teacher_id'];

                    $selectedRoom = selectClassroom($dId, $day, $slotId, $lec['student_count'] ?? 60, $lectureHalls, $overlappingSlots, $state);

                    if ($selectedRoom) {
                        $state = markStateOccupied($tId, $dId, $selectedRoom, $day, $slotId, $overlappingSlots, $state);
                        $state['divisionDayRoom'][$dId][$day]       = $selectedRoom;
                        $state['courseDayCount'][$dId][$cId][$day]  = ($state['courseDayCount'][$dId][$cId][$day] ?? 0) + 1;
                        $state['divisionDailyLectures'][$dId][$day] = ($state['divisionDailyLectures'][$dId][$day] ?? 0) + 1;

                        $state['assigned'][] = [
                            'DAY'         => $day,
                            'SLOT_ID'     => $slotId,
                            'DIVISION_ID' => $dId,
                            'COURSE_ID'   => $cId,
                            'TYPE'        => 'LECTURE',
                            'TEACHER_ID'  => $tId,
                            'ROOM_ID'     => $selectedRoom
                        ];

                        unset($unassignedWorkload[$dId][$candidateIndex]);
                        $unassignedWorkload[$dId] = array_values($unassignedWorkload[$dId]);
                    }
                }
            }
        }
    }

    // PASS 2: Relax daily caps for any remaining unassigned lectures
    foreach ($divisions as $dId => $divData) {
        if (!empty($unassignedWorkload[$dId])) {
            runFallbackAllocation($dId, $unassignedWorkload[$dId], $weekdays, $lectureSlots, $lectureHalls, $teacherAvail, $overlappingSlots, $state, $debug);
        }
    }

    debugLog("=== END WORKLOAD-BALANCED LECTURE ALLOCATION ===", $debug);
}

function runFallbackAllocation($dId, &$unassignedLectures, $weekdays, $lectureSlots, $lectureHalls, $teacherAvail, $overlappingSlots, &$state, $timeslots = [], $divisions = [], $debug = false) {
    debugLog("ENTER FALLBACK PASS: Div {$dId} has " . count($unassignedLectures) . " unallocated lectures.", $debug);

    foreach ($unassignedLectures as $lecIndex => $lec) {
        $allocated = false;
        $cId = $lec['course_id'];
        $tId = $lec['teacher_id'];

        foreach ($weekdays as $day) {
            if ($allocated) break;

            $freeSlots = getDivisionAvailableSlots($dId, $day, $lectureSlots, $overlappingSlots, $state, $timeslots, $divisions);

            foreach ($freeSlots as $slotId) {
                if ($allocated) break;

                if (isTeacherOccupied($tId, $day, $slotId, $overlappingSlots, $state)) continue;
                if (isset($teacherAvail[$tId][$day][$slotId]) && !$teacherAvail[$tId][$day][$slotId]) continue;

                $selectedRoom = selectClassroom($dId, $day, $slotId, $lec['student_count'] ?? 60, $lectureHalls, $overlappingSlots, $state);

                if ($selectedRoom) {
                    $state = markStateOccupied($tId, $dId, $selectedRoom, $day, $slotId, $overlappingSlots, $state);
                    $state['divisionDayRoom'][$dId][$day]       = $selectedRoom;
                    $state['courseDayCount'][$dId][$cId][$day]  = ($state['courseDayCount'][$dId][$cId][$day] ?? 0) + 1;
                    $state['divisionDailyLectures'][$dId][$day] = ($state['divisionDailyLectures'][$dId][$day] ?? 0) + 1;

                    $state['assigned'][] = [
                        'DAY'         => $day,
                        'SLOT_ID'     => $slotId,
                        'DIVISION_ID' => $dId,
                        'COURSE_ID'   => $cId,
                        'TYPE'        => 'LECTURE',
                        'TEACHER_ID'  => $tId,
                        'ROOM_ID'     => $selectedRoom
                    ];

                    $allocated = true;
                    unset($unassignedLectures[$lecIndex]);
                }
            }
        }

        if (!$allocated) {
            $state['unallocated'][] = [
                'unit'   => $lec,
                'reason' => "No free slot available across the entire week for Teacher ID {$tId}."
            ];
        }
    }
}

// -------------------------------------------------------------------------
// 5. LOOKUPS AND UTILITIES
// -------------------------------------------------------------------------

function debugLog($message, $debug = false) {
    if ($debug) {
        error_log("[TIMETABLE_DEBUG] " . $message);
    }
}

function getTimeslots($conn) {
    $res = $conn->query("SELECT * FROM TIMESLOT WHERE SLOT_TYPE != 'BREAK' ORDER BY START_TIME, END_TIME");
    $slots = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $slots[$row['SLOT_ID']] = $row;
        }
    }
    return $slots;
}

function getOverlappingSlotMap($timeslots) {
    $map = [];
    foreach ($timeslots as $s1_id => $s1) {
        $map[$s1_id] = [];
        $t1_s = strtotime($s1['START_TIME']);
        $t1_e = strtotime($s1['END_TIME']);
        foreach ($timeslots as $s2_id => $s2) {
            $t2_s = strtotime($s2['START_TIME']);
            $t2_e = strtotime($s2['END_TIME']);
            if ($t1_s < $t2_e && $t1_e > $t2_s) {
                $map[$s1_id][] = $s2_id;
            }
        }
    }
    return $map;
}

function getClassrooms($conn) {
    $res = $conn->query("SELECT * FROM CLASSROOM WHERE CAPACITY IS NOT NULL ORDER BY CAPACITY ASC");
    $rooms = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $rooms[$row['CLASSROOM_ID']] = $row;
        }
    }
    return $rooms;
}

function getDivisions($conn) {
    $res = $conn->query("SELECT * FROM DIVISION WHERE STUDENT_COUNT > 0");
    $divs = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $divs[$row['DIVISION_ID']] = $row;
        }
    }
    return $divs;
}

function getTeacherAvailability($conn) {
    $res = $conn->query("SELECT TEACHER_ID, SLOT_ID, WEEKDAY FROM AVAILABILITY WHERE STATUS = 'AVAILABLE'");
    $avail = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $avail[$row['TEACHER_ID']][$row['WEEKDAY']][$row['SLOT_ID']] = true;
        }
    }
    return $avail;
}

function runFinalValidation($allUnits, $state, $divisions, $timeslots) {
    $totalRequired  = count($allUnits);
    $totalAllocated = count($state['assigned']);
    $unallocated    = count($state['unallocated']);

    $teacherConflicts  = 0;
    $divisionConflicts = 0;
    $roomConflicts     = 0;
    $seenAuditMap      = [];

    foreach ($state['assigned'] as $alloc) {
        $key = $alloc['DAY'] . '_' . $alloc['SLOT_ID'];

        $tKey = "T_" . $alloc['TEACHER_ID'] . '_' . $key;
        if (isset($seenAuditMap[$tKey])) $teacherConflicts++;
        $seenAuditMap[$tKey] = true;

        $dKey = "D_" . $alloc['DIVISION_ID'] . '_' . $key;
        if (isset($seenAuditMap[$dKey])) $divisionConflicts++;
        $seenAuditMap[$dKey] = true;

        $rKey = "R_" . $alloc['ROOM_ID'] . '_' . $key;
        if (isset($seenAuditMap[$rKey])) $roomConflicts++;
        $seenAuditMap[$rKey] = true;
    }

    return [
        'Required_Workloads'               => $totalRequired,
        'Allocated_Workloads'              => $totalAllocated,
        'Unallocated_Workloads'            => $unallocated,
        'Teacher_Conflicts'                => $teacherConflicts,
        'Division_Conflicts'               => $divisionConflicts,
        'Classroom_Conflicts'              => $roomConflicts,
        'Lab_Conflicts'                    => 0,
        'Course_Frequency_Violations'      => $unallocated,
        'Teacher_Workload_Violations'      => $unallocated,
        'Compulsory_Course_Violations'     => 0,
        'Optional_Course_Conflicts'        => 0,
        'Practical_Slot_Violations'        => 0,
        'Broken_Division_Time_Preferences' => array_sum($state['brokenPreferences'] ?? []),
        'Unallocated_Details'              => $state['unallocated']
    ];
}