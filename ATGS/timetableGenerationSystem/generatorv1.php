
<?php

use LDAP\Result;

include_once 'dbConnect.php';
header('Content-Type: application/json');

$allocatedresourses = [
    "schedule" => [], // Final Output: Array of rows to insert
    "occupiedClassrooms" => [], // [weekday][slot_id][classroom_id] = true
    "occupiedTeachers"   => [], // [weekday][slot_id][teacher_id] = true
    "occupiedDivisions"  => [], // [weekday][slot_id][division_id] = true
    "divisionDaySlots"   => [], // [division_id][weekday][] = slot_id (Tracks gaps)
    "divisionClassroom"  => [], // [division_id] = classroom_id (Static classroom lock)
    "divisionPracticalSlot" => [] // [division_id] = slot_id (Fixed practical slot lock)"
];

$initialresourses = get_resourses();
global $initialresourses;

function get_resourses() {
    global $conn;
    $classrooms = [];
    $res = $conn->query("SELECT * FROM CLASSROOM ORDER BY CAPACITY ASC");
    while ($row = $res->fetch_assoc()) { $classrooms[$row['CLASSROOM_ID']] = $row; }

    $timeslots = [];
    $res = $conn->query("SELECT * FROM TIMESLOT ORDER BY START_TIME ASC");
    while ($row = $res->fetch_assoc()) { $timeslots[$row['SLOT_ID']] = $row; }

    $weekdays = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY'];

    $divisions = [];
    $res = $conn->query("SELECT * FROM DIVISION ORDER BY PROGRAMME_ID, YEAR_NUMBER, NAME");
    while ($row = $res->fetch_assoc()) { $divisions[$row['DIVISION_ID']] = $row; }

    $teachers = [];
    $res = $conn->query("SELECT * FROM TEACHER");
    while ($row = $res->fetch_assoc()) { $teachers[$row['TEACHER_ID']] = $row; }

    $availability = [];
    $res = $conn->query("SELECT * FROM AVAILABILITY WHERE STATUS = 'AVAILABLE'");
    while ($row = $res->fetch_assoc()) {
        $availability[$row['TEACHER_ID']][$row['WEEKDAY']][$row['SLOT_ID']] = true;
    }

    $courses = [];
    $res = $conn->query("SELECT * FROM COURSE");
    while ($row = $res->fetch_assoc()) { $courses[$row['COURSE_ID']] = $row; }

    // Load OPTED_BY mapping: maps course_id to division_ids
    $optedBy = [];
    $res = $conn->query("SELECT COURSE_ID, DIVISION_ID FROM OPTED_BY");
    while ($row = $res->fetch_assoc()) {
        $optedBy[(int)$row['COURSE_ID']][] = (int)$row['DIVISION_ID'];
    }

    // Load raw teaches workloads
    $workloads = [];
    $res = $conn->query("
        SELECT 
            t.*,
            c.ISPRACTICAL,
            c.TYPE,
            c.ISOPTIONAL,
            c.OPTIONAL_ID,
            c.PROGRAMME_ID,
            c.YEAR_NUMBER,
            c.SEMESTER,
            te.ISPARTTIME
        FROM TEACHES t
        JOIN COURSE c ON t.COURSE_ID = c.COURSE_ID
        JOIN TEACHER te ON t.TEACHER_ID = te.TEACHER_ID
    ");
    while ($row = $res->fetch_assoc()) {
        $workloads[] = $row;
    }

    return [
        "classroom"    => $classrooms,
        "timeslot"     => $timeslots,
        "weekday"      => $weekdays,
        "teacher"      => $teachers,
        "division"     => $divisions,
        "availability" => $availability,
        "course"       => $courses,
        "workload"     => $workloads,
        "opted_by"     => $optedBy
    ];
}

function get_allPracticalSlots($resourses) {
    $practicalSlots = [];
    foreach ($resourses['timeslot'] as $slot_id => $slot) {
        if ($slot['SLOT_TYPE'] === 'PRACTICAL') {
            $practicalSlots[$slot_id] = $slot;
        }
    }
    return $practicalSlots;
}

function get_divisionsWithPracticalWorkload($resourses) {
    $practicalDivisions = [];
    foreach ($resourses['workload'] as $workload) {
        if (!$workload['ISPRACTICAL']) {continue;}
        $divisionId = $workload['DIVISION_ID'];
        if (!isset($practicalDivisions[$divisionId])) {
            $practicalDivisions[$divisionId] = $resourses['division'][$divisionId];
            $practicalDivisions[$divisionId]['HOURS_PRACTICAL_WORKLOAD'] = 0;
            $practicalDivisions[$divisionId]['PRACTICAL_WORKLOADS'] = [];
        }
        $lectureCount = (int)$workload['LECTURE_COUNT'];
        $practicalDivisions[$divisionId]['HOURS_PRACTICAL_WORKLOAD'] += $lectureCount;
        $practicalDivisions[$divisionId]['PRACTICAL_WORKLOADS'][] = $workload;
    }
    return $practicalDivisions;
}

function get_practicalWorkloads($resources){
    $practicalWorkloads = [];
    foreach ($resources['workload'] as $workload) {
        if ((int)$workload['ISPRACTICAL'] !== 1) {continue;}
        $practicalWorkloads[] = $workload;
    }
    return $practicalWorkloads;
}

function expand_workloads_to_all_divisions($resources) {
    $expandedWorkloads = [];

    // Group divisions by PROGRAMME_ID and YEAR_NUMBER
    $divisionsByProgYear = [];
    foreach ($resources['division'] as $divId => $div) {
        $key = $div['PROGRAMME_ID'] . '_' . $div['YEAR_NUMBER'];
        $divisionsByProgYear[$key][] = (int)$divId;
    }

    foreach ($resources['workload'] as $w) {
        $courseId   = (int)$w['COURSE_ID'];
        $course     = $resources['course'][$courseId];
        $progId     = $course['PROGRAMME_ID'];
        $yearNum    = $course['YEAR_NUMBER'];
        $isOptional = (int)($course['ISOPTIONAL'] ?? 0) === 1;
        $divKey     = $progId . '_' . $yearNum;

        $targetDivisions = $divisionsByProgYear[$divKey] ?? [$w['DIVISION_ID']];

        if ($isOptional) {
            // If optional, respect OPTED_BY mapping if available
            if (!empty($resources['opted_by'][$courseId])) {
                $targetDivisions = $resources['opted_by'][$courseId];
            } else {
                $targetDivisions = [(int)$w['DIVISION_ID']];
            }
        }

        foreach ($targetDivisions as $dId) {
            $cloned = $w;
            $cloned['DIVISION_ID'] = $dId;
            $expandedWorkloads[] = $cloned;
        }
    }

    // Deduplicate by TEACHER_ID, COURSE_ID, DIVISION_ID
    $unique = [];
    foreach ($expandedWorkloads as $ew) {
        $k = $ew['TEACHER_ID'] . '_' . $ew['COURSE_ID'] . '_' . $ew['DIVISION_ID'];
        $unique[$k] = $ew;
    }

    return array_values($unique);
}

function get_practicalTeachersByDivision($resourses) {
    $teachersByDivision = [];
    foreach ($resourses['workload'] as $workload) {
        if (!$workload['ISPRACTICAL']) {continue;}
        $divisionId = $workload['DIVISION_ID'];
        $teacherId  = $workload['TEACHER_ID'];
        if (!isset($teachersByDivision[$divisionId])) {
            $teachersByDivision[$divisionId] = [];
        }
        if (!isset($teachersByDivision[$divisionId][$teacherId])) {
            $teachersByDivision[$divisionId][$teacherId] =
                $resourses['teacher'][$teacherId];
        }
    }
    return $teachersByDivision;
}

function sortTeachersByPartTime($teachers) {
    uasort($teachers, function ($a, $b) {

        $aPartTime = (int)$a['ISPARTTIME'];
        $bPartTime = (int)$b['ISPARTTIME'];

        // Part-time teachers first
        if ($aPartTime !== $bPartTime) {
            return $bPartTime <=> $aPartTime;
        }

        // Then sort by teacher ID
        return (int)$a['TEACHER_ID'] <=> (int)$b['TEACHER_ID'];
    });

    return $teachers;
}

function get_allParttimeTeachers($resources) {
    $partTimeTeachers = [];
    foreach ($resources['teacher'] as $teacher_id => $teacher) {
        if ($teacher['ISPARTTIME']) {
            $partTimeTeachers[$teacher_id] = $teacher;
        }
    }
    return $partTimeTeachers;
}

function get_allTeachers($resources) {
    return $resources['teacher'];
}

function get_availability($currentResources, $teacher_id, $slot_id, $weekday) {
    return isset(
        $currentResources["availability"][$teacher_id][$weekday][$slot_id]
    );
}

function get_partTimeTeacherAvailability($resourses) {
    $availability = [];
    $partTimeTeachers = get_allParttimeTeachers($resourses);
    foreach ($partTimeTeachers as $teacherId => $teacher) {
        $availability[$teacherId] = [];
        foreach ($resourses['weekday'] as $weekday) {
            $availability[$teacherId][$weekday] = [];
            foreach ($resourses['timeslot'] as $slotId => $slot) {
                if (get_availability(
                    $resourses,
                    $teacherId,
                    $slotId,
                    $weekday
                )) {
                    $availability[$teacherId][$weekday][] = $slotId;
                }
            }
        }
    }
    return $availability;
}

function get_divisionTimePreference($resourses, $divisionId) {
    $division = $resourses['division'][$divisionId];
    $startSlotId = $division['START_TIME_ID'];
    $endSlotId   = $division['END_TIME_ID'];
    $startTime = null;
    $endTime   = null;
    if ($startSlotId !== null &&
        isset($resourses['timeslot'][$startSlotId])) {

        $startTime =
            $resourses['timeslot'][$startSlotId]['START_TIME'];
    }
    if ($endSlotId !== null &&
        isset($resourses['timeslot'][$endSlotId])) {

        $endTime =
            $resourses['timeslot'][$endSlotId]['END_TIME'];
    }
    return [
        'start_slot_id' => $startSlotId,
        'end_slot_id'   => $endSlotId,
        'start_time'    => $startTime,
        'end_time'      => $endTime
    ];
}

function get_divisionClassroomPreference($resourses, $divisionId) {
    $division = $resourses['division'][$divisionId];
    $classroomId = $division['CLASSROOM_ID'] ?? null;
    return $classroomId;
}

function get_classroomAvailability($resourses, $classroomId){
    return [
        'start_time'  => $resourses['classroom'][$classroomId]["START_TIME"],
        'end_time'  => $resourses['classroom'][$classroomId]["END_TIME"]
    ];
}

function get_TeacherTotalAvailabilityHours($resources, $teacherId) {
    $totalSeconds = 0;
    // Teacher has no availability record
    if (!isset($resources['availability'][$teacherId])) {return 0;}
    foreach ($resources['availability'][$teacherId] as $weekday => $slots) {
        foreach ($slots as $slotId => $available) {
            if (!$available) {continue;}
            // Make sure the slot exists
            if (!isset($resources['timeslot'][$slotId])) {continue;}
            $slot = $resources['timeslot'][$slotId];
            $start = strtotime($slot['START_TIME']);
            $end   = strtotime($slot['END_TIME']);
            if ($start === false || $end === false) {continue;}
            $totalSeconds += ($end - $start);
        }
    }
    return floor($totalSeconds / 3600);
}


function get_commonAvailableSlot($resourses, $availability, $divisionId){
    $result = [];
    if (!isset($resourses['division'][$divisionId])) {
        return $result;
    }
    $division = $resourses['division'][$divisionId];
    $startSlotId = $division['START_TIME_ID'];
    $endSlotId   = $division['END_TIME_ID'];

    // Get teachers teaching this division's practical courses
    $teacherIds = [];

    foreach ($resourses['workload'] as $workload) {
        if (
            (int)$workload['DIVISION_ID'] === (int)$divisionId &&
            (int)$workload['ISPRACTICAL'] === 1
        ) {
            $teacherIds[(int)$workload['TEACHER_ID']] = true;
        }
    }

    $teacherIds = array_keys($teacherIds);

    if (empty($teacherIds)) {
        return $result;
    }

    foreach ($resourses['weekday'] as $weekday) {

        foreach ($resourses['timeslot'] as $slotId => $slot) {

            // Only practical slots
            if ($slot['SLOT_TYPE'] !== 'PRACTICAL') {
                continue;
            }

            // Respect division's preferred time range
            if ($startSlotId !== null && $slotId < $startSlotId) {
                continue;
            }

            if ($endSlotId !== null && $slotId > $endSlotId) {
                continue;
            }

            $allAvailable = true;

            foreach ($teacherIds as $teacherId) {

                if (
                    !isset($availability[$teacherId][$weekday][$slotId]) ||
                    !$availability[$teacherId][$weekday][$slotId]
                ) {
                    $allAvailable = false;
                    break;
                }
            }

            if ($allAvailable) {
                $result[$weekday][] = $slotId;
            }
        }
    }
    return $result;
}

function sortDivisionsByTimePreference($divisions) {
    uasort($divisions, function ($a, $b) {
        // Check whether each division has a time preference
        $aHasPreference =
            $a['START_TIME_ID'] !== null ||
            $a['END_TIME_ID'] !== null;

        $bHasPreference =
            $b['START_TIME_ID'] !== null ||
            $b['END_TIME_ID'] !== null;

        // 1. Divisions WITH preference come first
        if ($aHasPreference !== $bHasPreference) {
            return $bHasPreference <=> $aHasPreference;
        }
        // Both have no preference
        if (!$aHasPreference) {
            return 0;
        }
        // 2. Earlier START_TIME comes first
        $aStart = $a['START_TIME_ID'];
        $bStart = $b['START_TIME_ID'];
        if ($aStart !== null && $bStart === null) {return -1;}
        if ($aStart === null && $bStart !== null) {return 1;}

        if ($aStart !== $bStart) {return $aStart <=> $bStart;}

        // 3. If START_TIME is same, earlier END_TIME comes first
        $aEnd = $a['END_TIME_ID'];
        $bEnd = $b['END_TIME_ID'];
        if ($aEnd !== null && $bEnd === null) {return -1;}

        if ($aEnd === null && $bEnd !== null) {return 1;}

        if ($aEnd !== $bEnd) {return $aEnd <=> $bEnd;}

        // 4. Final deterministic ordering
        return $a['DIVISION_ID'] <=> $b['DIVISION_ID'];
    });

    return $divisions;
}

function sortTeachersByPriority($resources, $teachers) {
    uasort($teachers, function ($a, $b) use ($resources) {

        // 1. Part-time teachers first
        $aPartTime = (int)$a['ISPARTTIME'];
        $bPartTime = (int)$b['ISPARTTIME'];

        if ($aPartTime !== $bPartTime) {return $bPartTime <=> $aPartTime;}
        // 2. Fewer availability hours first
        $aHours = get_TeacherTotalAvailabilityHours($resources,$a['TEACHER_ID']);
        $bHours = get_TeacherTotalAvailabilityHours($resources,$b['TEACHER_ID']);

        if ($aHours !== $bHours) {return $aHours <=> $bHours;}

        // 3. Deterministic tie-breaker
        return (int)$a['TEACHER_ID'] <=> (int)$b['TEACHER_ID'];
    });

    return $teachers;
}

function sortWorkloadsByTeacherAvailability($resources, $workloads){
    uasort($workloads, function ($a, $b) use ($resources) {
        $aHours = get_TeacherTotalAvailabilityHours($resources,$a['TEACHER_ID']);
        $bHours = get_TeacherTotalAvailabilityHours($resources,$b['TEACHER_ID']);
        // Teachers with fewer available hours come first
        if ($aHours !== $bHours) {return $aHours <=> $bHours;}
        // Tie-breaker: teacher ID
        return (int)$a['TEACHER_ID'] <=> (int)$b['TEACHER_ID'];
    });
    return $workloads;
}


function fixingPracticalSlots_Depereciated(){
    global $initialresourses;
    global $allocatedresourses;

    $practicalSlots = get_allPracticalSlots($initialresourses);
    $practicalDivisions =
        get_divisionsWithPracticalWorkload($initialresourses);

    $divisionByTimePreference =
        sortDivisionsByTimePreference($practicalDivisions);

    foreach ($divisionByTimePreference as $division) {

        $divisionId = (int)$division['DIVISION_ID'];

        // Already assigned
        if (
            isset(
                $allocatedresourses['divisionPracticalSlot'][$divisionId]
            )
        ) {
            continue;
        }

        $pref = get_divisionTimePreference(
            $initialresourses,
            $divisionId
        );

        $selectedSlotId = null;

        /*
         * -------------------------------------------------
         * START TIME preference
         * -------------------------------------------------
         */
        if ($pref['start_time'] !== null) {

            foreach ($practicalSlots as $slotId => $slot) {

                // Slot already assigned to another division
                if (
                    in_array(
                        $slotId,
                        $allocatedresourses['divisionPracticalSlot'],
                        true
                    )
                ) {
                    continue;
                }

                if ($slot['START_TIME'] >= $pref['start_time']) {
                    $selectedSlotId = $slotId;
                    break;
                }
            }
        }

        /*
         * -------------------------------------------------
         * END TIME preference
         * -------------------------------------------------
         */
        elseif ($pref['end_time'] !== null) {

            foreach (array_reverse($practicalSlots, true) as $slotId => $slot) {

                if (
                    in_array(
                        $slotId,
                        $allocatedresourses['divisionPracticalSlot'],
                        true
                    )
                ) {
                    continue;
                }

                if ($slot['END_TIME'] <= $pref['end_time']) {
                    $selectedSlotId = $slotId;
                    break;
                }
            }
        }

        /*
         * -------------------------------------------------
         * NO preference
         * -------------------------------------------------
         */
        else {

            foreach ($practicalSlots as $slotId => $slot) {

                if (
                    in_array(
                        $slotId,
                        $allocatedresourses['divisionPracticalSlot'],
                        true
                    )
                ) {
                    continue;
                }

                $selectedSlotId = $slotId;
                break;
            }
        }

        /*
         * -------------------------------------------------
         * Save exactly ONE slot for this division
         * -------------------------------------------------
         */
        if ($selectedSlotId !== null) {

            $allocatedresourses['divisionPracticalSlot'][$divisionId]
                = $selectedSlotId;
        }
    }
}

/**
 * Helper: Find suitable and available lab classrooms for a division's practicals in a candidate slot.
 */
function get_available_labs_count($resources, $divisionId, $slotId) {
    $slot = $resources['timeslot'][$slotId] ?? null;
    if (!$slot) return 0;

    $studentCount = (int)($resources['division'][$divisionId]['STUDENT_COUNT'] ?? 0);

    // Identify required lab categories for this division's practicals
    $requiredCategories = [];
    $practicalLabCategory = [
        'IT PRACTICAL'        => 'IT LAB',
        'PHYSICS PRACTICAL'   => 'PHYSICS LAB',
        'BIOLOGY PRACTICAL'   => 'BIOLOGY LAB',
        'CHEMISTRY PRACTICAL' => 'CHEMISTRY LAB'
    ];

    foreach ($resources['workload'] as $w) {
        if ((int)$w['DIVISION_ID'] === (int)$divisionId && (int)($w['ISPRACTICAL'] ?? 0) === 1) {
            $cType = $w['TYPE'] ?? $resources['course'][$w['COURSE_ID']]['TYPE'] ?? null;
            if (isset($practicalLabCategory[$cType])) {
                $requiredCategories[$practicalLabCategory[$cType]] = true;
            }
        }
    }

    if (empty($requiredCategories)) {
        // Fallback: check any lab
        $requiredCategories['IT LAB'] = true;
    }

    // Count physical labs in the college matching these categories & operating hours
    $matchingLabsCount = 0;
    foreach ($resources['classroom'] as $classroom) {
        $category = strtoupper(trim($classroom['CATEGORY'] ?? ''));
        if (!isset($requiredCategories[$category])) {
            continue;
        }

        $rStart = $classroom['START_TIME'] ?? null;
        $rEnd   = $classroom['END_TIME'] ?? null;
        if ($rStart !== null && $slot['START_TIME'] < $rStart) continue;
        if ($rEnd !== null && $slot['END_TIME'] > $rEnd) continue;

        $cap = (int)($classroom['CAPACITY'] ?? 0);
        if ($studentCount > 0 && $cap > 0 && $cap < $studentCount) continue;

        $matchingLabsCount++;
    }

    return $matchingLabsCount;
}

/**
 * Fix practical slots allowing MULTIPLE divisions to share the same timeslot
 * as long as separate physical laboratory rooms exist.
 */
function fixingPracticalSlots() {
    global $initialresourses;
    global $allocatedresourses;

    $practicalSlots = get_allPracticalSlots($initialresourses);
    $practicalDivisions = get_divisionsWithPracticalWorkload($initialresourses);
    $divisionByTimePreference = sortDivisionsByTimePreference($practicalDivisions);

    // Track how many divisions are currently booked in each practical slot
    $slotDivisionUsage = [];
    foreach ($practicalSlots as $sId => $slot) {
        $slotDivisionUsage[$sId] = 0;
    }

    foreach ($divisionByTimePreference as $division) {
        $divisionId = (int)$division['DIVISION_ID'];

        if (isset($allocatedresourses['divisionPracticalSlot'][$divisionId])) {
            $existingSlot = $allocatedresourses['divisionPracticalSlot'][$divisionId];
            $slotDivisionUsage[$existingSlot] = ($slotDivisionUsage[$existingSlot] ?? 0) + 1;
            continue;
        }

        $pref = get_divisionTimePreference($initialresourses, $divisionId);
        $selectedSlotId = null;

        // Order candidate slots based on division time preference
        $candidateSlots = $practicalSlots;
        if ($pref['start_time'] !== null) {
            $candidateSlots = array_filter($practicalSlots, function($slot) use ($pref) {
                return $slot['START_TIME'] >= $pref['start_time'];
            });
        } elseif ($pref['end_time'] !== null) {
            $candidateSlots = array_reverse($practicalSlots, true);
            $candidateSlots = array_filter($candidateSlots, function($slot) use ($pref) {
                return $slot['END_TIME'] <= $pref['end_time'];
            });
        }

        if (empty($candidateSlots)) {
            $candidateSlots = $practicalSlots;
        }

        foreach ($candidateSlots as $slotId => $slot) {
            // Check how many physical labs exist for this division's practicals
            $totalLabsAvailable = get_available_labs_count($initialresourses, $divisionId, $slotId);

            // ALLOW multiple divisions in the same slot if more labs exist!
            $currentUsage = $slotDivisionUsage[$slotId] ?? 0;
            if ($currentUsage >= $totalLabsAvailable) {
                // All labs for this slot are occupied by other divisions
                continue;
            }

            // Verify teacher availability across weekdays for this division's practicals
            $hasTeacherWindow = false;
            foreach ($initialresourses['workload'] as $w) {
                if ((int)$w['DIVISION_ID'] === $divisionId && (int)($w['ISPRACTICAL'] ?? 0) === 1) {
                    $tId = (int)$w['TEACHER_ID'];
                    foreach ($initialresourses['weekday'] as $wkd) {
                        if (get_availability($initialresourses, $tId, $slotId, $wkd)) {
                            $hasTeacherWindow = true;
                            break 2;
                        }
                    }
                }
            }

            if ($hasTeacherWindow || empty($division['PRACTICAL_WORKLOADS'])) {
                $selectedSlotId = $slotId;
                break;
            }
        }

        // Fallback: If preferences are too strict, pick the slot with the lowest division count
        if ($selectedSlotId === null) {
            asort($slotDivisionUsage);
            $selectedSlotId = array_key_first($slotDivisionUsage);
        }

        if ($selectedSlotId !== null) {
            $allocatedresourses['divisionPracticalSlot'][$divisionId] = $selectedSlotId;
            $slotDivisionUsage[$selectedSlotId] = ($slotDivisionUsage[$selectedSlotId] ?? 0) + 1;
        }
    }
}

function allocatePracticalCourse_Depereciated($resources){
    global $allocatedresourses;
    $workloads = get_practicalWorkloads($resources);
    $workloads = sortWorkloadsByTeacherAvailability($resources, $workloads);
    $practicalLabCategory = [
        'IT PRACTICAL'         => 'IT LAB',
        'PHYSICS PRACTICAL'    => 'PHYSICS LAB',
        'BIOLOGY PRACTICAL'    => 'BIOLOGY LAB',
        'CHEMISTRY PRACTICAL'  => 'CHEMISTRY LAB'
    ];

    foreach ($workloads as $workload) {
        $teacherId  = (int)$workload['TEACHER_ID'];
        $courseId   = (int)$workload['COURSE_ID'];
        $divisionId = (int)$workload['DIVISION_ID'];

        $lectureCount = (int)$workload['LECTURE_COUNT'];
        if ($lectureCount <= 0) {continue;}

        if (!isset($resources['course'][$courseId])) {
            error_log( "Course {$courseId} does not exist in resources.");
            continue;
        }

        $course = $resources['course'][$courseId];

        $courseType = $course['TYPE'] ?? $workload['TYPE'] ?? null;
        if (!isset($practicalLabCategory[$courseType])) {
            error_log(
                "No laboratory category mapped for course {$courseId}. " .
                "Course type: {$courseType}"
            );
            continue;
        }

        $requiredLabCategory = $practicalLabCategory[$courseType];

        if (!isset($allocatedresourses['divisionPracticalSlot'][$divisionId])) {
            error_log("No practical slot assigned for Division {$divisionId}.");
            continue;
        }

        $slotId = $allocatedresourses['divisionPracticalSlot'][$divisionId];

        if (!isset($resources['timeslot'][$slotId]))
        {continue;}

        $slot = $resources['timeslot'][$slotId];

        if ($slot['SLOT_TYPE'] !== 'PRACTICAL') {
            error_log("Slot {$slotId} is not a practical slot.");
            continue;
        }

        $studentCount = 0;

        if (isset($resources['division'][$divisionId])) {
            $studentCount = (int)($resources['division'][$divisionId]['STUDENT_COUNT'] ?? 0);
        }

        $allocatedCount = 0;
        foreach ($resources['weekday'] as $weekday) {
            if ($allocatedCount >= $lectureCount) {break;}

            if (!get_availability($resources, $teacherId, $slotId, $weekday)) 
            {continue;}

            if (isset( $allocatedresourses['occupiedTeachers'] [$weekday] [$slotId] [$teacherId])) 
            {continue;}

            if (isset($allocatedresourses['occupiedDivisions'][$weekday][$slotId][$divisionId]))
            {continue;}
    
            $classroomId = null;
            foreach ($resources['classroom'] as $candidateId => $classroom) {

                if (strtoupper(trim($classroom['CATEGORY'])) !== $requiredLabCategory) 
                {continue;}

                if (isset($allocatedresourses['occupiedClassrooms'][$weekday][$slotId][$candidateId]))
                {continue;}
                $labStart = $classroom['START_TIME'];
                $labEnd   = $classroom['END_TIME'];

                if ($labStart !== null && $slot['START_TIME'] < $labStart) {continue;}
                if ($labEnd !== null && $slot['END_TIME'] > $labEnd) {continue;}
                $labCapacity = (int)($classroom['CAPACITY'] ?? 0);

                if ($studentCount > 0 && $labCapacity > 0 && $labCapacity < $studentCount) 
                {continue;}

                $classroomId = $candidateId;
                break;
            }

            if ($classroomId === null) {
                error_log(
                    "No {$requiredLabCategory} available for " .
                    "Course {$courseId}, " .
                    "Division {$divisionId}, " .
                    "Teacher {$teacherId}, " .
                    "{$weekday}, Slot {$slotId}"
                );
                continue;
            }

            $allocatedresourses['schedule'][] = [
                'WEEKDAY'      => $weekday,
                'SLOT_ID'       => $slotId,
                'TEACHER_ID'    => $teacherId,
                'COURSE_ID'     => $courseId,
                'DIVISION_ID'   => $divisionId,
                'CLASSROOM_ID'  => $classroomId,
                'TYPE'          => 'PRACTICAL'
            ];

            $allocatedresourses['occupiedTeachers'][$weekday][$slotId][$teacherId] = true;
            $allocatedresourses['occupiedDivisions'][$weekday][$slotId][$divisionId] = true;
            $allocatedresourses['occupiedClassrooms'][$weekday][$slotId][$classroomId] = true;

            if (!isset($allocatedresourses['divisionDaySlots'][$divisionId][$weekday])) 
            {$allocatedresourses['divisionDaySlots'][$divisionId][$weekday] = [];}

            $allocatedresourses['divisionDaySlots'][$divisionId][$weekday][] = $slotId;

            $allocatedCount++;
        }

        if ($allocatedCount < $lectureCount) {

            error_log(
                "Unable to allocate all practical sessions: " .
                "Course {$courseId}, " .
                "Division {$divisionId}, " .
                "Teacher {$teacherId}. " .
                "Lab required: {$requiredLabCategory}. " .
                "Required: {$lectureCount}, " .
                "Allocated: {$allocatedCount}"
            );
        }
    }

    return $allocatedresourses['schedule'];
}

function allocateOptionalCourses(&$workloads, $resources) {
    global $allocatedresourses;

    // Collect optional pairs
    $pairedCourses = [];
    $visited = [];

    foreach ($workloads as $idx => $w) {
        $courseId = (int)$w['COURSE_ID'];
        $course = $resources['course'][$courseId] ?? null;

        if (!$course || (int)$course['ISOPTIONAL'] !== 1 || empty($course['OPTIONAL_ID'])) {
            continue;
        }

        $partnerId = (int)$course['OPTIONAL_ID'];
        if (isset($visited[$courseId])) continue;

        // Find partner workload
        $partnerWorkload = null;
        foreach ($workloads as $pW) {
            if ((int)$pW['COURSE_ID'] === $partnerId) {
                $partnerWorkload = $pW;
                break;
            }
        }

        if ($partnerWorkload) {
            $visited[$courseId] = true;
            $visited[$partnerId] = true;
            $pairedCourses[] = [
                'w1' => $w,
                'w2' => $partnerWorkload
            ];
        }
    }

    // Remove paired optional workloads from regular pool
    $workloads = array_filter($workloads, function($w) use ($visited) {
        return !isset($visited[(int)$w['COURSE_ID']]);
    });

    // Available lecture slots sorted chronologically
    $lectureSlots = [];
    foreach ($resources['timeslot'] as $slotId => $slot) {
        if (strtoupper($slot['SLOT_TYPE']) === 'LECTURE') {
            $lectureSlots[$slotId] = $slot;
        }
    }
    uasort($lectureSlots, function($a, $b) { return strcmp($a['START_TIME'], $b['START_TIME']); });
    $slotIds = array_keys($lectureSlots);

    // Lecture classrooms sorted by capacity ASC
    $classrooms = [];
    foreach ($resources['classroom'] as $cid => $c) {
        if (!isset($c['CATEGORY']) || strtoupper(trim($c['CATEGORY'])) === 'LECTURE HALL' || strtoupper(trim($c['CATEGORY'])) === 'LECTURE_HALL' || stripos($c['CATEGORY'], 'LAB') === false) {
            $classrooms[$cid] = $c;
        }
    }
    uasort($classrooms, function($a, $b) { return ((int)($a['CAPACITY'] ?? 0)) <=> ((int)($b['CAPACITY'] ?? 0)); });

    foreach ($pairedCourses as $pair) {
        $w1 = $pair['w1'];
        $w2 = $pair['w2'];

        $lecturesNeeded = max((int)$w1['LECTURE_COUNT'], (int)$w2['LECTURE_COUNT']);
        $allocatedCount = 0;

        foreach ($resources['weekday'] as $weekday) {
            if ($allocatedCount >= $lecturesNeeded) break;

            foreach ($slotIds as $slotId) {
                // 1. Check teacher availability for both teachers
                if (!get_availability($resources, (int)$w1['TEACHER_ID'], $slotId, $weekday) ||
                    !get_availability($resources, (int)$w2['TEACHER_ID'], $slotId, $weekday)) {
                    continue;
                }

                // 2. Check collisions for both divisions and both teachers
                if (has_schedule_conflict($allocatedresourses['schedule'], $resources, $weekday, $slotId, (int)$w1['TEACHER_ID'], (int)$w1['DIVISION_ID'], null) ||
                    has_schedule_conflict($allocatedresourses['schedule'], $resources, $weekday, $slotId, (int)$w2['TEACHER_ID'], (int)$w2['DIVISION_ID'], null)) {
                    continue;
                }

                // 3. Find TWO distinct classrooms available at this time
                $room1 = null;
                $room2 = null;

                foreach ($classrooms as $cid => $rm) {
                    $cap = (int)($rm['CAPACITY'] ?? 0);
                    $s1 = (int)($resources['division'][$w1['DIVISION_ID']]['STUDENT_COUNT'] ?? 0);
                    if ($cap < $s1) continue;
                    if (has_schedule_conflict($allocatedresourses['schedule'], $resources, $weekday, $slotId, (int)$w1['TEACHER_ID'], (int)$w1['DIVISION_ID'], $cid)) {
                        continue;
                    }
                    $room1 = $cid;
                    break;
                }

                foreach ($classrooms as $cid => $rm) {
                    if ($cid === $room1) continue; // Must be separate rooms
                    $cap = (int)($rm['CAPACITY'] ?? 0);
                    $s2 = (int)($resources['division'][$w2['DIVISION_ID']]['STUDENT_COUNT'] ?? 0);
                    if ($cap < $s2) continue;
                    if (has_schedule_conflict($allocatedresourses['schedule'], $resources, $weekday, $slotId, (int)$w2['TEACHER_ID'], (int)$w2['DIVISION_ID'], $cid)) {
                        continue;
                    }
                    $room2 = $cid;
                    break;
                }

                if ($room1 === null || $room2 === null) {
                    continue;
                }

                // Commit both allocations at the EXACT SAME (WEEKDAY, SLOT_ID)
                $allocatedresourses['schedule'][] = [
                    'WEEKDAY'      => $weekday,
                    'SLOT_ID'      => $slotId,
                    'TEACHER_ID'   => (int)$w1['TEACHER_ID'],
                    'COURSE_ID'    => (int)$w1['COURSE_ID'],
                    'DIVISION_ID'  => (int)$w1['DIVISION_ID'],
                    'CLASSROOM_ID' => $room1,
                    'TYPE'         => 'ELECTIVE'
                ];

                $allocatedresourses['schedule'][] = [
                    'WEEKDAY'      => $weekday,
                    'SLOT_ID'      => $slotId,
                    'TEACHER_ID'   => (int)$w2['TEACHER_ID'],
                    'COURSE_ID'    => (int)$w2['COURSE_ID'],
                    'DIVISION_ID'  => (int)$w2['DIVISION_ID'],
                    'CLASSROOM_ID' => $room2,
                    'TYPE'         => 'ELECTIVE'
                ];

                $allocatedCount++;
                break; // One slot per weekday to maintain balance
            }
        }
    }
}

function allocateCourses_Depereciated($resources) {
    global $allocatedresourses;

    // 1. Separate and prioritize workloads: Part-time teachers first (sorted by availability), then full-time
    $partTimeWorkloads = [];
    $fullTimeWorkloads = [];

    foreach ($resources['workload'] as $workload) {
        // Skip practical courses already handled in allocatePracticalCourse
        if ((int)($workload['ISPRACTICAL'] ?? 0) === 1) {
            continue;
        }

        $teacherId = (int)$workload['TEACHER_ID'];
        $isPartTime = (int)($workload['ISPARTTIME'] ?? $resources['teacher'][$teacherId]['ISPARTTIME'] ?? 0);

        if ($isPartTime === 1) {
            $partTimeWorkloads[] = $workload;
        } else {
            $fullTimeWorkloads[] = $workload;
        }
    }

    // Sort part-time workloads by ascending total availability hours using get_TeacherTotalAvailabilityHours
    uasort($partTimeWorkloads, function ($a, $b) use ($resources) {
        $aHours = get_TeacherTotalAvailabilityHours($resources, $a['TEACHER_ID']);
        $bHours = get_TeacherTotalAvailabilityHours($resources, $b['TEACHER_ID']);
        if ($aHours !== $bHours) {
            return $aHours <=> $bHours;
        }
        return (int)$a['TEACHER_ID'] <=> (int)$b['TEACHER_ID'];
    });

    // Combine sorted part-time workloads followed by full-time workloads
    $workloadsToAllocate = array_merge(array_values($partTimeWorkloads), array_values($fullTimeWorkloads));

    // 2. Pre-filter and sort lecture classrooms by CAPACITY ASC
    $lectureClassrooms = [];
    foreach ($resources['classroom'] as $cid => $c) {
        if (!isset($c['CATEGORY']) || strtoupper(trim($c['CATEGORY'])) === 'LECTURE_HALL' || stripos($c['CATEGORY'], 'LAB') === false) {
            $lectureClassrooms[$cid] = $c;
        }
    }
    uasort($lectureClassrooms, function ($a, $b) {
        return ((int)($a['CAPACITY'] ?? 0)) <=> ((int)($b['CAPACITY'] ?? 0));
    });

    // 3. Sort base lecture timeslots by start time
    $lectureSlots = [];
    foreach ($resources['timeslot'] as $slotId => $slot) {
        if (strtoupper($slot['SLOT_TYPE']) === 'LECTURE') {
            $lectureSlots[$slotId] = $slot;
        }
    }
    uasort($lectureSlots, function ($a, $b) {
        return strcmp($a['START_TIME'], $b['START_TIME']);
    });
    $orderedLectureSlotIds = array_keys($lectureSlots);

    // Track division hours allocated per weekday to maintain balance
    $divisionDayHours = [];
    foreach ($resources['division'] as $divId => $div) {
        foreach ($resources['weekday'] as $w) {
            $divisionDayHours[$divId][$w] = 0;
        }
    }

    // Account for practical sessions already scheduled in divisionDayHours
    if (!empty($allocatedresourses['schedule'])) {
        foreach ($allocatedresourses['schedule'] as $sched) {
            $dId = $sched['DIVISION_ID'];
            $wkd = $sched['WEEKDAY'];
            $sId = $sched['SLOT_ID'];
            if (isset($resources['timeslot'][$sId])) {
                $duration = (strtotime($resources['timeslot'][$sId]['END_TIME']) - strtotime($resources['timeslot'][$sId]['START_TIME'])) / 3600;
                $divisionDayHours[$dId][$wkd] = ($divisionDayHours[$dId][$wkd] ?? 0) + $duration;
            }
        }
    }

    // 4. Allocate each course workload
    foreach ($workloadsToAllocate as $workload) {
        $teacherId     = (int)$workload['TEACHER_ID'];
        $courseId      = (int)$workload['COURSE_ID'];
        $divisionId    = (int)$workload['DIVISION_ID'];
        $totalLectures = (int)$workload['LECTURE_COUNT'];
        $courseType    = $workload['TYPE'] ?? $resources['course'][$courseId]['TYPE'] ?? 'THEORY';
        $studentCount  = (int)($resources['division'][$divisionId]['STUDENT_COUNT'] ?? 0);

        if ($totalLectures <= 0) {
            continue;
        }

        // Determine slot ordering based on division preference
        $pref = get_divisionTimePreference($resources, $divisionId);
        $candidateSlots = $orderedLectureSlotIds;

        if ($pref['start_slot_id'] !== null) {
            // Clockwise from preferred start slot
            $startIdx = array_search($pref['start_slot_id'], $candidateSlots);
            if ($startIdx !== false) {
                $candidateSlots = array_merge(
                    array_slice($candidateSlots, $startIdx),
                    array_slice($candidateSlots, 0, $startIdx)
                );
            }
        } elseif ($pref['end_slot_id'] !== null) {
            // Counter-clockwise from preferred end slot backwards to beginning
            $endIdx = array_search($pref['end_slot_id'], $candidateSlots);
            if ($endIdx !== false) {
                $revBefore = array_reverse(array_slice($candidateSlots, 0, $endIdx + 1));
                $revAfter  = array_reverse(array_slice($candidateSlots, $endIdx + 1));
                $candidateSlots = array_merge($revBefore, $revAfter);
            } else {
                $candidateSlots = array_reverse($candidateSlots);
            }
        }

        $allocatedCount = 0;

        // Balance hours across the week: repeat rounds through weekdays
        $maxAttempts = 30; // Guard against infinite loop if constraints are impossible
        $attempt = 0;

        while ($allocatedCount < $totalLectures && $attempt < $maxAttempts) {
            $attempt++;

            // Sort weekdays dynamically by least hours allocated to this division
            $weekdays = $resources['weekday'];
            usort($weekdays, function ($a, $b) use ($divisionDayHours, $divisionId) {
                return $divisionDayHours[$divisionId][$a] <=> $divisionDayHours[$divisionId][$b];
            });

            $madeProgressThisRound = false;

            foreach ($weekdays as $weekday) {
                if ($allocatedCount >= $totalLectures) {
                    break;
                }

                foreach ($candidateSlots as $slotId) {
                    // Strict teacher availability check
                    if (!get_availability($resources, $teacherId, $slotId, $weekday)) {
                        continue;
                    }

                    // Check teacher and division conflicts (respects already locked practical slots)
                    if (isset($allocatedresourses['occupiedTeachers'][$weekday][$slotId][$teacherId])) {
                        continue;
                    }
                    if (isset($allocatedresourses['occupiedDivisions'][$weekday][$slotId][$divisionId])) {
                        continue;
                    }

                    // Select smallest capacity classroom that fits the division
                    $selectedClassroomId = null;
                    foreach ($lectureClassrooms as $cid => $room) {
                        $cap = (int)($room['CAPACITY'] ?? 0);
                        if ($cap < $studentCount) {
                            continue;
                        }

                        // Check classroom availability
                        if (isset($allocatedresourses['occupiedClassrooms'][$weekday][$slotId][$cid])) {
                            continue;
                        }

                        // Check room operating hours if specified
                        $roomStart = $room['START_TIME'] ?? null;
                        $roomEnd   = $room['END_TIME'] ?? null;
                        $slotStart = $resources['timeslot'][$slotId]['START_TIME'];
                        $slotEnd   = $resources['timeslot'][$slotId]['END_TIME'];

                        if ($roomStart !== null && $slotStart < $roomStart) continue;
                        if ($roomEnd !== null && $slotEnd > $roomEnd) continue;

                        $selectedClassroomId = $cid;
                        break; // First match is smallest due to prior ASC sort
                    }

                    if ($selectedClassroomId === null) {
                        continue;
                    }

                    // Allocate slot
                    $allocatedresourses['schedule'][] = [
                        'WEEKDAY'      => $weekday,
                        'SLOT_ID'      => $slotId,
                        'TEACHER_ID'   => $teacherId,
                        'COURSE_ID'    => $courseId,
                        'DIVISION_ID'  => $divisionId,
                        'CLASSROOM_ID' => $selectedClassroomId,
                        'TYPE'         => $courseType
                    ];

                    // Mark occupancy
                    $allocatedresourses['occupiedTeachers'][$weekday][$slotId][$teacherId] = true;
                    $allocatedresourses['occupiedDivisions'][$weekday][$slotId][$divisionId] = true;
                    $allocatedresourses['occupiedClassrooms'][$weekday][$slotId][$selectedClassroomId] = true;

                    // Update weekly workload tracking
                    $slotHours = (strtotime($resources['timeslot'][$slotId]['END_TIME']) - strtotime($resources['timeslot'][$slotId]['START_TIME'])) / 3600;
                    $divisionDayHours[$divisionId][$weekday] += $slotHours;

                    if (!isset($allocatedresourses['divisionDaySlots'][$divisionId][$weekday])) {
                        $allocatedresourses['divisionDaySlots'][$divisionId][$weekday] = [];
                    }
                    $allocatedresourses['divisionDaySlots'][$divisionId][$weekday][] = $slotId;

                    $allocatedCount++;
                    $madeProgressThisRound = true;
                    break; // Move to next day to balance slots across the week
                }
            }

            // Stop if no available slots can fit remaining lectures to avoid infinite looping
            if (!$madeProgressThisRound) {
                error_log("Unable to allocate complete workload for Course {$courseId}, Division {$divisionId}, Teacher {$teacherId}. Remaining: " . ($totalLectures - $allocatedCount));
                break;
            }
        }
    }

    return $allocatedresourses['schedule'];
}
/**
 * Helper: Checks if a proposed time slot clashes with an existing event's clock times.
 */
function is_time_clashing($startA, $endA, $startB, $endB) {
    $sA = strtotime($startA);
    $eA = strtotime($endA);
    $sB = strtotime($startB);
    $eB = strtotime($endB);

    return ($sA < $eB && $eA > $sB);
}

/**
 * Helper: Checks if teacher, division, or classroom has any physical time collision on that weekday.
 */
function has_schedule_conflict($schedule, $resources, $weekday, $candidateSlotId, $teacherId, $divisionId, $classroomId) {
    if (!isset($resources['timeslot'][$candidateSlotId])) {
        return true;
    }

    $candidateSlot = $resources['timeslot'][$candidateSlotId];
    $cStart = $candidateSlot['START_TIME'];
    $cEnd   = $candidateSlot['END_TIME'];

    foreach ($schedule as $entry) {
        if ($entry['WEEKDAY'] !== $weekday) {
            continue;
        }

        $existingSlot = $resources['timeslot'][$entry['SLOT_ID']];
        $eStart = $existingSlot['START_TIME'];
        $eEnd   = $existingSlot['END_TIME'];

        if (is_time_clashing($cStart, $cEnd, $eStart, $eEnd)) {
            // Division collision
            if ((int)$entry['DIVISION_ID'] === (int)$divisionId) {
                return true;
            }
            // Teacher collision
            if ((int)$entry['TEACHER_ID'] === (int)$teacherId) {
                return true;
            }
            // Classroom collision
            if ($classroomId !== null && (int)$entry['CLASSROOM_ID'] === (int)$classroomId) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Allocate practical courses into dedicated laboratory rooms.
 */
function allocatePracticalCourse($resources) {
    global $allocatedresourses;

    $workloads = get_practicalWorkloads($resources);
    $workloads = sortWorkloadsByTeacherAvailability($resources, $workloads);

    $practicalLabCategory = [
        'IT PRACTICAL'        => 'IT LAB',
        'PHYSICS PRACTICAL'   => 'PHYSICS LAB',
        'BIOLOGY PRACTICAL'   => 'BIOLOGY LAB',
        'CHEMISTRY PRACTICAL' => 'CHEMISTRY LAB'
    ];

    foreach ($workloads as $workload) {
        $teacherId     = (int)$workload['TEACHER_ID'];
        $courseId      = (int)$workload['COURSE_ID'];
        $divisionId    = (int)$workload['DIVISION_ID'];
        $lectureCount  = (int)$workload['LECTURE_COUNT'];

        if ($lectureCount <= 0) {
            continue;
        }

        if (!isset($resources['course'][$courseId])) {
            error_log("Course {$courseId} does not exist in resources.");
            continue;
        }

        $course = $resources['course'][$courseId];
        $courseType = $course['TYPE'] ?? $workload['TYPE'] ?? null;

        if (!isset($practicalLabCategory[$courseType])) {
            error_log("No laboratory category mapped for course {$courseId}. Type: {$courseType}");
            continue;
        }

        $requiredLabCategory = $practicalLabCategory[$courseType];

        if (!isset($allocatedresourses['divisionPracticalSlot'][$divisionId])) {
            error_log("No practical slot assigned for Division {$divisionId}.");
            continue;
        }

        $slotId = (int)$allocatedresourses['divisionPracticalSlot'][$divisionId];

        if (!isset($resources['timeslot'][$slotId])) {
            continue;
        }

        $slot = $resources['timeslot'][$slotId];
        if ($slot['SLOT_TYPE'] !== 'PRACTICAL') {
            error_log("Slot {$slotId} is not a practical slot.");
            continue;
        }

        $studentCount = 0;
        if (isset($resources['division'][$divisionId])) {
            $studentCount = (int)($resources['division'][$divisionId]['STUDENT_COUNT'] ?? 0);
        }

        $allocatedCount = 0;
        foreach ($resources['weekday'] as $weekday) {
            if ($allocatedCount >= $lectureCount) {
                break;
            }

            // Verify teacher availability
            if (!get_availability($resources, $teacherId, $slotId, $weekday)) {
                continue;
            }

            // Check conflict for teacher and division against current schedule
            if (has_schedule_conflict($allocatedresourses['schedule'], $resources, $weekday, $slotId, $teacherId, $divisionId, null)) {
                continue;
            }

            // Find an available lab that meets category and capacity
            $classroomId = null;
            foreach ($resources['classroom'] as $candidateId => $classroom) {
                if (strtoupper(trim($classroom['CATEGORY'])) !== $requiredLabCategory) {
                    continue;
                }

                $labStart = $classroom['START_TIME'];
                $labEnd   = $classroom['END_TIME'];
                if ($labStart !== null && $slot['START_TIME'] < $labStart) continue;
                if ($labEnd !== null && $slot['END_TIME'] > $labEnd) continue;

                $labCapacity = (int)($classroom['CAPACITY'] ?? 0);
                if ($studentCount > 0 && $labCapacity > 0 && $labCapacity < $studentCount) {
                    continue;
                }

                // Verify classroom has no time collision
                if (has_schedule_conflict($allocatedresourses['schedule'], $resources, $weekday, $slotId, $teacherId, $divisionId, $candidateId)) {
                    continue;
                }

                $classroomId = $candidateId;
                break;
            }

            if ($classroomId === null) {
                error_log("No {$requiredLabCategory} available for Course {$courseId}, Division {$divisionId}, Weekday {$weekday}, Slot {$slotId}");
                continue;
            }

            // Commit practical allocation
            $allocatedresourses['schedule'][] = [
                'WEEKDAY'      => $weekday,
                'SLOT_ID'      => $slotId,
                'TEACHER_ID'   => $teacherId,
                'COURSE_ID'    => $courseId,
                'DIVISION_ID'  => $divisionId,
                'CLASSROOM_ID' => $classroomId,
                'TYPE'         => 'PRACTICAL'
            ];

            if (!isset($allocatedresourses['divisionDaySlots'][$divisionId][$weekday])) {
                $allocatedresourses['divisionDaySlots'][$divisionId][$weekday] = [];
            }
            $allocatedresourses['divisionDaySlots'][$divisionId][$weekday][] = $slotId;

            $allocatedCount++;
        }

        if ($allocatedCount < $lectureCount) {
            error_log("Unable to allocate all practical sessions for Course {$courseId}, Division {$divisionId}. Required: {$lectureCount}, Allocated: {$allocatedCount}");
        }
    }

    return $allocatedresourses['schedule'];
}

/**
 * Allocate theory lecture courses.
 * Enforces room capacity minimization, weekday workload balance, 
 * division time preferences, and interval-based conflict checks.
 */
function allocateCourses_Depereciated2($resources) {
    global $allocatedresourses;

    // 1. Separate workloads: part-time first (sorted by availability hours ASC), then full-time[cite: 6]
    $partTimeWorkloads = [];
    $fullTimeWorkloads = [];

    foreach ($resources['workload'] as $workload) {
        if ((int)($workload['ISPRACTICAL'] ?? 0) === 1) {
            continue;
        }

        $teacherId = (int)$workload['TEACHER_ID'];
        $isPartTime = (int)($workload['ISPARTTIME'] ?? $resources['teacher'][$teacherId]['ISPARTTIME'] ?? 0);

        if ($isPartTime === 1) {
            $partTimeWorkloads[] = $workload;
        } else {
            $fullTimeWorkloads[] = $workload;
        }
    }

    uasort($partTimeWorkloads, function ($a, $b) use ($resources) {
        $aHours = get_TeacherTotalAvailabilityHours($resources, $a['TEACHER_ID']);
        $bHours = get_TeacherTotalAvailabilityHours($resources, $b['TEACHER_ID']);
        if ($aHours !== $bHours) {
            return $aHours <=> $bHours;
        }
        return (int)$a['TEACHER_ID'] <=> (int)$b['TEACHER_ID'];
    });

    $workloadsToAllocate = array_merge(array_values($partTimeWorkloads), array_values($fullTimeWorkloads));

    // 2. Sort lecture classrooms by capacity ascending (assign smallest fitting classroom)
    $lectureClassrooms = [];
    foreach ($resources['classroom'] as $cid => $c) {
        if (!isset($c['CATEGORY']) || strtoupper(trim($c['CATEGORY'])) === 'LECTURE HALL' || strtoupper(trim($c['CATEGORY'])) === 'LECTURE_HALL' || stripos($c['CATEGORY'], 'LAB') === false) {
            $lectureClassrooms[$cid] = $c;
        }
    }
    uasort($lectureClassrooms, function ($a, $b) {
        return ((int)($a['CAPACITY'] ?? 0)) <=> ((int)($b['CAPACITY'] ?? 0));
    });

    // 3. Filter strictly LECTURE timeslots sorted chronologically
    $lectureSlots = [];
    foreach ($resources['timeslot'] as $slotId => $slot) {
        if (strtoupper($slot['SLOT_TYPE']) === 'LECTURE') {
            $lectureSlots[$slotId] = $slot;
        }
    }
    uasort($lectureSlots, function ($a, $b) {
        return strcmp($a['START_TIME'], $b['START_TIME']);
    });
    $orderedLectureSlotIds = array_keys($lectureSlots);

    // Track division hours allocated per day to balance loads
    $divisionDayHours = [];
    foreach ($resources['division'] as $divId => $div) {
        foreach ($resources['weekday'] as $w) {
            $divisionDayHours[$divId][$w] = 0;
        }
    }

    // Account for already scheduled practical hours in divisionDayHours
    if (!empty($allocatedresourses['schedule'])) {
        foreach ($allocatedresourses['schedule'] as $sched) {
            $dId = $sched['DIVISION_ID'];
            $wkd = $sched['WEEKDAY'];
            $sId = $sched['SLOT_ID'];
            if (isset($resources['timeslot'][$sId])) {
                $duration = (strtotime($resources['timeslot'][$sId]['END_TIME']) - strtotime($resources['timeslot'][$sId]['START_TIME'])) / 3600;
                $divisionDayHours[$dId][$wkd] = ($divisionDayHours[$dId][$wkd] ?? 0) + $duration;
            }
        }
    }

    // 4. Allocate each course workload
    foreach ($workloadsToAllocate as $workload) {
        $teacherId     = (int)$workload['TEACHER_ID'];
        $courseId      = (int)$workload['COURSE_ID'];
        $divisionId    = (int)$workload['DIVISION_ID'];
        $totalLectures = (int)$workload['LECTURE_COUNT'];
        $courseType    = $workload['TYPE'] ?? $resources['course'][$courseId]['TYPE'] ?? 'THEORY';
        $studentCount  = (int)($resources['division'][$divisionId]['STUDENT_COUNT'] ?? 0);

        if ($totalLectures <= 0) {
            continue;
        }

        // Apply division start or end preference order
        $pref = get_divisionTimePreference($resources, $divisionId);
        $candidateSlots = $orderedLectureSlotIds;

        if ($pref['start_slot_id'] !== null) {
            $startIdx = array_search($pref['start_slot_id'], $candidateSlots);
            if ($startIdx !== false) {
                $candidateSlots = array_merge(
                    array_slice($candidateSlots, $startIdx),
                    array_slice($candidateSlots, 0, $startIdx)
                );
            }
        } elseif ($pref['end_slot_id'] !== null) {
            $endIdx = array_search($pref['end_slot_id'], $candidateSlots);
            if ($endIdx !== false) {
                $revBefore = array_reverse(array_slice($candidateSlots, 0, $endIdx + 1));
                $revAfter  = array_reverse(array_slice($candidateSlots, $endIdx + 1));
                $candidateSlots = array_merge($revBefore, $revAfter);
            } else {
                $candidateSlots = array_reverse($candidateSlots);
            }
        }

        $allocatedCount = 0;
        $maxAttempts = 30;
        $attempt = 0;

        while ($allocatedCount < $totalLectures && $attempt < $maxAttempts) {
            $attempt++;

            // Pick weekdays with fewest allocated hours first to distribute load evenly
            $weekdays = $resources['weekday'];
            usort($weekdays, function ($a, $b) use ($divisionDayHours, $divisionId) {
                return $divisionDayHours[$divisionId][$a] <=> $divisionDayHours[$divisionId][$b];
            });

            $madeProgressThisRound = false;

            foreach ($weekdays as $weekday) {
                if ($allocatedCount >= $totalLectures) {
                    break;
                }

                foreach ($candidateSlots as $slotId) {
                    // Strict teacher availability check
                    if (!get_availability($resources, $teacherId, $slotId, $weekday)) {
                        continue;
                    }

                    // Check if teacher or division is already busy at this time
                    if (has_schedule_conflict($allocatedresourses['schedule'], $resources, $weekday, $slotId, $teacherId, $divisionId, null)) {
                        continue;
                    }

                    // Select smallest capacity classroom that fits the division
                    $selectedClassroomId = null;
                    foreach ($lectureClassrooms as $cid => $room) {
                        $cap = (int)($room['CAPACITY'] ?? 0);
                        if ($cap < $studentCount) {
                            continue;
                        }

                        // Room operating hour bounds
                        $roomStart = $room['START_TIME'] ?? null;
                        $roomEnd   = $room['END_TIME'] ?? null;
                        $slotStart = $resources['timeslot'][$slotId]['START_TIME'];
                        $slotEnd   = $resources['timeslot'][$slotId]['END_TIME'];

                        if ($roomStart !== null && $slotStart < $roomStart) continue;
                        if ($roomEnd !== null && $slotEnd > $roomEnd) continue;

                        // Check if classroom is busy at this exact time
                        if (has_schedule_conflict($allocatedresourses['schedule'], $resources, $weekday, $slotId, $teacherId, $divisionId, $cid)) {
                            continue;
                        }

                        $selectedClassroomId = $cid;
                        break; // First match is smallest due to ASC sort
                    }

                    if ($selectedClassroomId === null) {
                        continue;
                    }

                    // Commit lecture allocation
                    $allocatedresourses['schedule'][] = [
                        'WEEKDAY'      => $weekday,
                        'SLOT_ID'      => $slotId,
                        'TEACHER_ID'   => $teacherId,
                        'COURSE_ID'    => $courseId,
                        'DIVISION_ID'  => $divisionId,
                        'CLASSROOM_ID' => $selectedClassroomId,
                        'TYPE'         => $courseType
                    ];

                    // Track weekly hours
                    $slotHours = (strtotime($resources['timeslot'][$slotId]['END_TIME']) - strtotime($resources['timeslot'][$slotId]['START_TIME'])) / 3600;
                    $divisionDayHours[$divisionId][$weekday] += $slotHours;

                    if (!isset($allocatedresourses['divisionDaySlots'][$divisionId][$weekday])) {
                        $allocatedresourses['divisionDaySlots'][$divisionId][$weekday] = [];
                    }
                    $allocatedresourses['divisionDaySlots'][$divisionId][$weekday][] = $slotId;

                    $allocatedCount++;
                    $madeProgressThisRound = true;
                    break; // Move to the next day
                }
            }

            if (!$madeProgressThisRound) {
                error_log("Unable to allocate complete workload for Course {$courseId}, Division {$divisionId}, Teacher {$teacherId}. Remaining: " . ($totalLectures - $allocatedCount));
                break;
            }
        }
    }

    return $allocatedresourses['schedule'];
}



/**
 * Allocate theory lecture courses with strict priority order:
 * 1. 100% Workload Allocation (Hard Requirement via Multi-phase Fallbacks)
 * 2. Minimum Gaps Between Lectures (Consecutive slot preference)
 * 3. Division Time Preference (Start/End time slot ordering)
 * 4. Same Classroom Throughout the Day (Soft preference, falls back if occupied)
 */
function allocateCourses($resources) {
    global $allocatedresourses;

    // 1. Separate & prioritize workloads: Part-time teachers first (by availability ASC), then full-time
    $partTimeWorkloads = [];
    $fullTimeWorkloads = [];

    foreach ($resources['workload'] as $workload) {
        if ((int)($workload['ISPRACTICAL'] ?? 0) === 1) {
            continue;
        }

        $teacherId = (int)$workload['TEACHER_ID'];
        $isPartTime = (int)($workload['ISPARTTIME'] ?? $resources['teacher'][$teacherId]['ISPARTTIME'] ?? 0);

        if ($isPartTime === 1) {
            $partTimeWorkloads[] = $workload;
        } else {
            $fullTimeWorkloads[] = $workload;
        }
    }

    uasort($partTimeWorkloads, function ($a, $b) use ($resources) {
        $aHours = get_TeacherTotalAvailabilityHours($resources, $a['TEACHER_ID']);
        $bHours = get_TeacherTotalAvailabilityHours($resources, $b['TEACHER_ID']);
        if ($aHours !== $bHours) {
            return $aHours <=> $bHours;
        }
        return (int)$a['TEACHER_ID'] <=> (int)$b['TEACHER_ID'];
    });

    $workloadsToAllocate = array_merge(array_values($partTimeWorkloads), array_values($fullTimeWorkloads));

    // 2. Pre-sort lecture classrooms by CAPACITY ASC (Smallest capacity that fits student count first)
    $lectureClassrooms = [];
    foreach ($resources['classroom'] as $cid => $c) {
        if (!isset($c['CATEGORY']) || strtoupper(trim($c['CATEGORY'])) === 'LECTURE HALL' || strtoupper(trim($c['CATEGORY'])) === 'LECTURE_HALL' || stripos($c['CATEGORY'], 'LAB') === false) {
            $lectureClassrooms[$cid] = $c;
        }
    }
    uasort($lectureClassrooms, function ($a, $b) {
        return ((int)($a['CAPACITY'] ?? 0)) <=> ((int)($b['CAPACITY'] ?? 0));
    });

    // 3. Collect chronologically ordered lecture slots
    $lectureSlots = [];
    foreach ($resources['timeslot'] as $slotId => $slot) {
        if (strtoupper($slot['SLOT_TYPE']) === 'LECTURE') {
            $lectureSlots[$slotId] = $slot;
        }
    }
    uasort($lectureSlots, function ($a, $b) {
        return strcmp($a['START_TIME'], $b['START_TIME']);
    });
    $orderedLectureSlotIds = array_keys($lectureSlots);

    // Track division hours & locked daily classrooms
    $divisionDayHours = [];
    $divisionDayClassroom = []; // [division_id][weekday] = classroom_id

    foreach ($resources['division'] as $divId => $div) {
        foreach ($resources['weekday'] as $w) {
            $divisionDayHours[$divId][$w] = 0;
            $divisionDayClassroom[$divId][$w] = null;
        }
    }

    // Account for practical sessions already scheduled in divisionDayHours
    if (!empty($allocatedresourses['schedule'])) {
        foreach ($allocatedresourses['schedule'] as $sched) {
            $dId = $sched['DIVISION_ID'];
            $wkd = $sched['WEEKDAY'];
            $sId = $sched['SLOT_ID'];
            if (isset($resources['timeslot'][$sId])) {
                $duration = (strtotime($resources['timeslot'][$sId]['END_TIME']) - strtotime($resources['timeslot'][$sId]['START_TIME'])) / 3600;
                $divisionDayHours[$dId][$wkd] = ($divisionDayHours[$dId][$wkd] ?? 0) + $duration;
            }
        }
    }

    // Helper closure to rank slots to MINIMIZE GAPS (Priority 2)
    $getGapOptimizedSlots = function($divId, $weekday, $baseSlotIds) use (&$allocatedresourses) {
        // Collect currently allocated slots for this division on this weekday
        $assignedSlots = [];
        foreach ($allocatedresourses['schedule'] as $entry) {
            if ((int)$entry['DIVISION_ID'] === (int)$divId && $entry['WEEKDAY'] === $weekday) {
                $assignedSlots[] = (int)$entry['SLOT_ID'];
            }
        }

        if (empty($assignedSlots)) {
            return $baseSlotIds; // No anchor yet, return default order
        }

        // Rank base slots by minimum distance to an already assigned slot (minimizes gaps)
        $ranked = $baseSlotIds;
        usort($ranked, function($slotA, $slotB) use ($assignedSlots) {
            $minDistA = min(array_map(fn($s) => abs($s - $slotA), $assignedSlots));
            $minDistB = min(array_map(fn($s) => abs($s - $slotB), $assignedSlots));
            return $minDistA <=> $minDistB;
        });

        return $ranked;
    };

    // 4. Allocate each workload completely
    foreach ($workloadsToAllocate as $workload) {
        $teacherId     = (int)$workload['TEACHER_ID'];
        $courseId      = (int)$workload['COURSE_ID'];
        $divisionId    = (int)$workload['DIVISION_ID'];
        $totalLectures = (int)$workload['LECTURE_COUNT'];
        $courseType    = $workload['TYPE'] ?? $resources['course'][$courseId]['TYPE'] ?? 'THEORY';
        $studentCount  = (int)($resources['division'][$divisionId]['STUDENT_COUNT'] ?? 0);

        if ($totalLectures <= 0) {
            continue;
        }

        // Determine base slots according to Division Time Preference (Priority 3)
        $pref = get_divisionTimePreference($resources, $divisionId);
        $prefCandidateSlots = $orderedLectureSlotIds;

        if ($pref['start_slot_id'] !== null) {
            $startIdx = array_search($pref['start_slot_id'], $prefCandidateSlots);
            if ($startIdx !== false) {
                $prefCandidateSlots = array_merge(
                    array_slice($prefCandidateSlots, $startIdx),
                    array_slice($prefCandidateSlots, 0, $startIdx)
                );
            }
        } elseif ($pref['end_slot_id'] !== null) {
            $endIdx = array_search($pref['end_slot_id'], $prefCandidateSlots);
            if ($endIdx !== false) {
                $revBefore = array_reverse(array_slice($prefCandidateSlots, 0, $endIdx + 1));
                $revAfter  = array_reverse(array_slice($prefCandidateSlots, $endIdx + 1));
                $prefCandidateSlots = array_merge($revBefore, $revAfter);
            } else {
                $prefCandidateSlots = array_reverse($prefCandidateSlots);
            }
        }

        $allocatedCount = 0;

        // MULTI-PHASE FALLBACK SYSTEM TO GUARANTEE 100% ALLOCATION (Priority 1)
        // Phase 1: Same Room + Pref Slots
        // Phase 2: Any Room + Pref Slots
        // Phase 3: Any Room + Any Slot (Absolute Fallback)
        for ($phase = 1; $phase <= 3 && $allocatedCount < $totalLectures; $phase++) {
            
            $maxPasses = 10;
            $pass = 0;

            while ($allocatedCount < $totalLectures && $pass < $maxPasses) {
                $pass++;

                // Sort weekdays by least hours allocated to distribute load evenly
                $weekdays = $resources['weekday'];
                usort($weekdays, function ($a, $b) use ($divisionDayHours, $divisionId) {
                    return $divisionDayHours[$divisionId][$a] <=> $divisionDayHours[$divisionId][$b];
                });

                $madeProgressInPass = false;

                foreach ($weekdays as $weekday) {
                    if ($allocatedCount >= $totalLectures) break;

                    // Choose slot pool based on phase
                    $basePool = ($phase === 3) ? $orderedLectureSlotIds : $prefCandidateSlots;
                    
                    // Order slots to minimize gaps (Priority 2)
                    $candidateSlots = $getGapOptimizedSlots($divisionId, $weekday, $basePool);

                    foreach ($candidateSlots as $slotId) {
                        // 1. Teacher availability check
                        if (!get_availability($resources, $teacherId, $slotId, $weekday)) {
                            continue;
                        }

                        // 2. Collision check for teacher and division
                        if (has_schedule_conflict($allocatedresourses['schedule'], $resources, $weekday, $slotId, $teacherId, $divisionId, null)) {
                            continue;
                        }

                        // 3. Classrooms pool setup
                        $preferredRoomId = $divisionDayClassroom[$divisionId][$weekday] ?? null;
                        $orderedRooms = [];

                        if ($phase === 1 && $preferredRoomId !== null && isset($lectureClassrooms[$preferredRoomId])) {
                            // Phase 1: STRICTLY try preferred classroom first
                            $orderedRooms = [$preferredRoomId => $lectureClassrooms[$preferredRoomId]];
                        } else {
                            // Phase 2 & 3: Try preferred room first, then fall back to all fitting rooms (Priority 4)
                            if ($preferredRoomId !== null && isset($lectureClassrooms[$preferredRoomId])) {
                                $orderedRooms[$preferredRoomId] = $lectureClassrooms[$preferredRoomId];
                            }
                            foreach ($lectureClassrooms as $cid => $room) {
                                if ($cid !== $preferredRoomId) {
                                    $orderedRooms[$cid] = $room;
                                }
                            }
                        }

                        // 4. Room capacity & collision check
                        $selectedClassroomId = null;
                        foreach ($orderedRooms as $cid => $room) {
                            $cap = (int)($room['CAPACITY'] ?? 0);
                            if ($cap < $studentCount) {
                                continue; // Smallest capacity fitting rule
                            }

                            $roomStart = $room['START_TIME'] ?? null;
                            $roomEnd   = $room['END_TIME'] ?? null;
                            $slotStart = $resources['timeslot'][$slotId]['START_TIME'];
                            $slotEnd   = $resources['timeslot'][$slotId]['END_TIME'];

                            if ($roomStart !== null && $slotStart < $roomStart) continue;
                            if ($roomEnd !== null && $slotEnd > $roomEnd) continue;

                            if (has_schedule_conflict($allocatedresourses['schedule'], $resources, $weekday, $slotId, $teacherId, $divisionId, $cid)) {
                                continue;
                            }

                            $selectedClassroomId = $cid;
                            break;
                        }

                        if ($selectedClassroomId === null) {
                            continue;
                        }

                        // Commit allocation
                        $allocatedresourses['schedule'][] = [
                            'WEEKDAY'      => $weekday,
                            'SLOT_ID'      => $slotId,
                            'TEACHER_ID'   => $teacherId,
                            'COURSE_ID'    => $courseId,
                            'DIVISION_ID'  => $divisionId,
                            'CLASSROOM_ID' => $selectedClassroomId,
                            'TYPE'         => $courseType
                        ];

                        // Lock classroom for division on this day if not set
                        if ($divisionDayClassroom[$divisionId][$weekday] === null) {
                            $divisionDayClassroom[$divisionId][$weekday] = $selectedClassroomId;
                        }

                        // Update tracking metrics
                        $slotHours = (strtotime($resources['timeslot'][$slotId]['END_TIME']) - strtotime($resources['timeslot'][$slotId]['START_TIME'])) / 3600;
                        $divisionDayHours[$divisionId][$weekday] += $slotHours;

                        if (!isset($allocatedresourses['divisionDaySlots'][$divisionId][$weekday])) {
                            $allocatedresourses['divisionDaySlots'][$divisionId][$weekday] = [];
                        }
                        $allocatedresourses['divisionDaySlots'][$divisionId][$weekday][] = $slotId;

                        $allocatedCount++;
                        $madeProgressInPass = true;
                        
                        // Continue checking remaining needed lectures in this pass
                        if ($allocatedCount >= $totalLectures) break 2;
                    }
                }

                if (!$madeProgressInPass) {
                    break; // Move to next fallback phase
                }
            }
        }

        if ($allocatedCount < $totalLectures) {
            error_log("CRITICAL ERROR: Unable to allocate complete workload for Course {$courseId}, Division {$divisionId}, Teacher {$teacherId}. Allocated {$allocatedCount}/{$totalLectures}");
        }
    }

    return $allocatedresourses['schedule'];
}

function prettify(){
    global $allocatedresourses;
    global $initialresourses;

    $schedule = $allocatedresourses['schedule'];

    /*
     * ---------------------------------------------------------
     * Helper: sort timeslots by START_TIME
     * ---------------------------------------------------------
     */
    $sortedSlots = $initialresourses['timeslot'];

    uasort($sortedSlots, function ($a, $b) {
        return strcmp($a['START_TIME'], $b['START_TIME']);
    });


    /*
     * ---------------------------------------------------------
     * 1. DIVISION-WISE TIMETABLE
     * ---------------------------------------------------------
     */

    $divisionWise = [];

    foreach ($initialresourses['division'] as $divisionId => $division) {

        $divisionWise[$divisionId] = [
            'DIVISION_ID' => $divisionId,
            'DIVISION_NAME' => $division['DIVISION_NAME'] ?? $divisionId,
            'timetable' => []
        ];

        foreach ($initialresourses['weekday'] as $weekday) {

            $divisionWise[$divisionId]['timetable'][$weekday] = [];

            foreach ($sortedSlots as $slotId => $slot) {

                $divisionWise[$divisionId]['timetable'][$weekday][$slotId] = [
                    'SLOT_ID' => $slotId,
                    'START_TIME' => $slot['START_TIME'],
                    'END_TIME' => $slot['END_TIME'],
                    'SLOT_TYPE' => $slot['SLOT_TYPE'],
                    'COURSE_ID' => null,
                    'TEACHER_ID' => null,
                    'CLASSROOM_ID' => null,
                    'TYPE' => null
                ];
            }
        }
    }


    /*
     * ---------------------------------------------------------
     * Put allocated classes into division timetable
     * ---------------------------------------------------------
     */

    foreach ($schedule as $row) {

        $divisionId = $row['DIVISION_ID'];
        $weekday    = $row['WEEKDAY'];
        $slotId     = $row['SLOT_ID'];

        if (
            !isset(
                $divisionWise[$divisionId],
                $divisionWise[$divisionId]['timetable'][$weekday][$slotId]
            )
        ) {
            continue;
        }

        $divisionWise[$divisionId]['timetable'][$weekday][$slotId] = [
            'SLOT_ID'       => $slotId,
            'START_TIME'    => $initialresourses['timeslot'][$slotId]['START_TIME'],
            'END_TIME'      => $initialresourses['timeslot'][$slotId]['END_TIME'],
            'SLOT_TYPE'     => $initialresourses['timeslot'][$slotId]['SLOT_TYPE'],

            'COURSE_ID'     => $row['COURSE_ID'],
            'TEACHER_ID'    => $row['TEACHER_ID'],
            'CLASSROOM_ID'  => $row['CLASSROOM_ID'],
            'TYPE'          => $row['TYPE']
        ];
    }


    /*
     * ---------------------------------------------------------
     * 2. TEACHER-WISE TIMETABLE
     * ---------------------------------------------------------
     */

    $teacherWise = [];

    foreach ($initialresourses['teacher'] as $teacherId => $teacher) {

        $teacherWise[$teacherId] = [
            'TEACHER_ID' => $teacherId,
            'TEACHER_NAME' => $teacher['TEACHER_NAME'] ?? $teacherId,
            'timetable' => []
        ];

        foreach ($initialresourses['weekday'] as $weekday) {

            $teacherWise[$teacherId]['timetable'][$weekday] = [];

            foreach ($sortedSlots as $slotId => $slot) {

                $teacherWise[$teacherId]['timetable'][$weekday][$slotId] = [
                    'SLOT_ID'       => $slotId,
                    'START_TIME'    => $slot['START_TIME'],
                    'END_TIME'      => $slot['END_TIME'],
                    'SLOT_TYPE'     => $slot['SLOT_TYPE'],
                    'COURSE_ID'     => null,
                    'DIVISION_ID'   => null,
                    'CLASSROOM_ID'  => null,
                    'TYPE'          => null
                ];
            }
        }
    }


    /*
     * ---------------------------------------------------------
     * Put allocated classes into teacher timetable
     * ---------------------------------------------------------
     */

    foreach ($schedule as $row) {

        $teacherId = $row['TEACHER_ID'];
        $weekday   = $row['WEEKDAY'];
        $slotId    = $row['SLOT_ID'];

        if (
            !isset(
                $teacherWise[$teacherId],
                $teacherWise[$teacherId]['timetable'][$weekday][$slotId]
            )
        ) {
            continue;
        }

        $teacherWise[$teacherId]['timetable'][$weekday][$slotId] = [
            'SLOT_ID'       => $slotId,
            'START_TIME'    => $initialresourses['timeslot'][$slotId]['START_TIME'],
            'END_TIME'      => $initialresourses['timeslot'][$slotId]['END_TIME'],
            'SLOT_TYPE'     => $initialresourses['timeslot'][$slotId]['SLOT_TYPE'],

            'COURSE_ID'     => $row['COURSE_ID'],
            'DIVISION_ID'   => $row['DIVISION_ID'],
            'CLASSROOM_ID'  => $row['CLASSROOM_ID'],
            'TYPE'          => $row['TYPE']
        ];
    }


    /*
     * ---------------------------------------------------------
     * 3. BUILD A SIMPLE DISPLAY VERSION
     * ---------------------------------------------------------
     *
     * This is easier to read than the complete timetable
     * containing every empty slot.
     */

    $divisionDisplay = [];

    foreach ($divisionWise as $divisionId => $division) {

        $divisionDisplay[$divisionId] = [
            'DIVISION_ID'   => $divisionId,
            'DIVISION_NAME' => $division['DIVISION_NAME'],
            'timetable'     => []
        ];

        foreach ($division['timetable'] as $weekday => $slots) {

            foreach ($slots as $slot) {

                if ($slot['COURSE_ID'] === null) {
                    continue;
                }

                $courseId = $slot['COURSE_ID'];
                $teacherId = $slot['TEACHER_ID'];
                $classroomId = $slot['CLASSROOM_ID'];

                $courseName =
                    $initialresourses['course'][$courseId]['COURSE_NAME']
                    ?? $courseId;

                $teacherName =
                    $initialresourses['teacher'][$teacherId]['TEACHER_NAME']
                    ?? $teacherId;

                $classroomName =
                    $initialresourses['classroom'][$classroomId]['CLASSROOM_NAME']
                    ?? $classroomId;

                $divisionDisplay[$divisionId]['timetable'][$weekday][] = [
                    'TIME' =>
                        $slot['START_TIME'] .
                        ' - ' .
                        $slot['END_TIME'],

                    'COURSE' => $courseName,
                    'TEACHER' => $teacherName,
                    'CLASSROOM' => $classroomName,
                    'TYPE' => $slot['TYPE']
                ];
            }
        }
    }


    /*
     * ---------------------------------------------------------
     * 4. TEACHER DISPLAY VERSION
     * ---------------------------------------------------------
     */

    $teacherDisplay = [];

    foreach ($teacherWise as $teacherId => $teacher) {

        $teacherDisplay[$teacherId] = [
            'TEACHER_ID'   => $teacherId,
            'TEACHER_NAME' => $teacher['TEACHER_NAME'],
            'timetable'    => []
        ];

        foreach ($teacher['timetable'] as $weekday => $slots) {

            foreach ($slots as $slot) {

                if ($slot['COURSE_ID'] === null) {
                    continue;
                }

                $courseId = $slot['COURSE_ID'];
                $divisionId = $slot['DIVISION_ID'];
                $classroomId = $slot['CLASSROOM_ID'];

                $courseName =
                    $initialresourses['course'][$courseId]['COURSE_NAME']
                    ?? $courseId;

                $divisionName =
                    $initialresourses['division'][$divisionId]['DIVISION_NAME']
                    ?? $divisionId;

                $classroomName =
                    $initialresourses['classroom'][$classroomId]['CLASSROOM_NAME']
                    ?? $classroomId;

                $teacherDisplay[$teacherId]['timetable'][$weekday][] = [
                    'TIME' =>
                        $slot['START_TIME'] .
                        ' - ' .
                        $slot['END_TIME'],

                    'COURSE' => $courseName,
                    'DIVISION' => $divisionName,
                    'CLASSROOM' => $classroomName,
                    'TYPE' => $slot['TYPE']
                ];
            }
        }
    }


    /*
     * ---------------------------------------------------------
     * 5. RETURN BOTH VIEWS
     * ---------------------------------------------------------
     */

    return [
        'divisionWise' => $divisionDisplay,
        'teacherWise'  => $teacherDisplay
    ];
}

function generate() {
    global $initialresourses;
    global $allocatedresourses;

    // Reset runtime containers
    $allocatedresourses['schedule'] = [];
    $allocatedresourses['divisionDaySlots'] = [];
    $allocatedresourses['divisionPracticalSlot'] = [];

    // 1. Expand workloads so all divisions of the year get all year courses
    $initialresourses['workload'] = expand_workloads_to_all_divisions($initialresourses);

    // 2. Lock and allocate laboratory practicals first
    fixingPracticalSlots();
    allocatePracticalCourse($initialresourses);

    // 3. Allocate paired optional courses simultaneously in the same slot
    allocateOptionalCourses($initialresourses['workload'], $initialresourses);

    // 4. Allocate remaining regular theory courses
    allocateCourses($initialresourses);

    return $allocatedresourses;
}

// complete the function allocateCourses($resources) such that if the division preffered time is given then for start time begnning the allotment clock wise and for preffered end time begin the allotment counter clock wise from the end time to the first slot the number of hours must be ballanced accross the week
// this function shall also consider the classroom allotment such that a division is alloted the smallest possible classroom capacity wise.
// the function first must dothe allotment for the parttimeteachers sorted using get_TeacherTotalAvailabilityHours function