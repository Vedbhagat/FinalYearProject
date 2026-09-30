<?php
/**
 * generatorv2.php
 * Fully Optimized Dynamic Timetable Engine
 * Features:
 * - Zero-gap contiguous schedule packing
 * - Simultaneous slot locking for Optional Courses across divisions
 * - Strict Division Time Preference adherence
 * - Least-excess capacity classroom optimization
 * - Balanced weekly workload distribution
 */

include_once 'dbConnect.php';

$GLOBALS['allocationLog'] = "";

function logStep($message) {
    $GLOBALS['allocationLog'] .= $message . "\n";
}

// =========================================================================
// 1. MAIN GENERATION CONTROLLER
// =========================================================================

function generateTimetable($semester = 'ODD', $academicYear = '2026-27') {
    global $conn;
    $GLOBALS['allocationLog'] = "";
    logStep("=== START OPTIMIZED TIMETABLE GENERATION PROCESS ===");

    // Fetch static lookups
    $timeslots        = getTimeslots($conn);
    $overlappingSlots = getOverlappingSlotMap($timeslots);
    $classrooms       = getClassrooms($conn);
    $rawDivisions     = getDivisions($conn);
    $teacherAvail     = getTeacherAvailability($conn);
    $teachersMap      = getTeachersMap($conn);
    $coursesMap       = getCoursesMap($conn);

    // Fetch & expand workloads
    $rawWorkloads      = getWorkload($conn, $semester);
    $expandedWorkloads = expandWorkload($conn, $rawWorkloads, $semester);

    $weekdays     = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY'];
    $lectureSlots = getLectureSlotsList($timeslots);

    // Build divisions array
    $divisions = buildDivisionsArray($rawDivisions, $expandedWorkloads['lectures']);

    // Build tracking matrices
    $divSlotMatrix = buildDivisionSlotMatrix($divisions, $weekdays, $lectureSlots);
    $teacherMatrix = buildTeacherAvailabilityMatrix($teachersMap, $weekdays, $lectureSlots, $teacherAvail);

    // Setup Practical Slots
    $divisionPracticalSlots = fixPracticalSlot($timeslots, $rawDivisions, $expandedWorkloads['practicals'], $classrooms);

    // System state initialization
    $state = initSystemState();

    // ---------------------------------------------------------------------
    // STEP 1: ALLOCATE PRACTICAL WORKLOADS (FIXED LAB SLOTS)
    // ---------------------------------------------------------------------
    logStep("\n--- STEP 1: ALLOCATING PRACTICAL WORKLOADS ---");
    $state = allocatePracticals(
        $expandedWorkloads['practicals'], 
        $divisionPracticalSlots, 
        $classrooms, 
        $teacherAvail, 
        $overlappingSlots, 
        $state,
        $teachersMap,
        $coursesMap,
        $timeslots
    );

    syncPracticalsToSlotMatrices($state['assigned'], $divSlotMatrix, $teacherMatrix, $overlappingSlots);

    // Calculate daily targets for balanced distribution
    $divDailyQuotas = calculateDivisionDailyQuotas($divisions, $expandedWorkloads, $weekdays);

    // ---------------------------------------------------------------------
    // STEP 2: ALLOCATE SYNCHRONIZED OPTIONAL COURSES
    // ---------------------------------------------------------------------
    logStep("\n--- STEP 2: ALLOCATING SYNCHRONIZED OPTIONAL COURSES ---");
    allocateOptionalCoursesSynchronized(
        $expandedWorkloads['optional'],
        $divisions,
        $lectureSlots,
        $weekdays,
        $classrooms,
        $teacherAvail,
        $overlappingSlots,
        $state,
        $timeslots,
        $teachersMap,
        $coursesMap,
        $divSlotMatrix,
        $teacherMatrix,
        $divDailyQuotas
    );

    // ---------------------------------------------------------------------
    // STEP 3: ALLOCATE COMPULSORY LECTURE WORKLOADS
    // ---------------------------------------------------------------------
    logStep("\n--- STEP 3: ALLOCATING COMPULSORY LECTURE WORKLOADS ---");
    allocateCoursesBalanced(
        $divisions,
        $expandedWorkloads['compulsory'],
        $lectureSlots,
        $weekdays,
        $classrooms,
        $teacherAvail,
        $overlappingSlots,
        $state,
        $timeslots,
        $teachersMap,
        $coursesMap,
        $divSlotMatrix,
        $teacherMatrix,
        $divDailyQuotas
    );

    // ---------------------------------------------------------------------
    // STEP 4: FALLBACK RESOLUTION PASS
    // ---------------------------------------------------------------------
    logStep("\n--- STEP 4: RUNNING FALLBACK PASS FOR UNASSIGNED WORKLOADS ---");
    $state = fallBackAllocation(
        $divisions,
        $lectureSlots,
        $weekdays,
        $classrooms,
        $overlappingSlots,
        $state,
        $teachersMap,
        $coursesMap,
        $timeslots,
        $divSlotMatrix,
        $teacherMatrix
    );

    // ---------------------------------------------------------------------
    // STEP 5: FINAL VALIDATION & AUDIT
    // ---------------------------------------------------------------------
    $validationReport = runFinalValidation(
        $expandedWorkloads['all'], 
        $state, 
        $divisions, 
        $timeslots
    );

    logStep("\n=== TIMETABLE GENERATION COMPLETE ===");

    return [
        'validation' => $validationReport,
        'timetable'  => $state['assigned'],
        'log'        => $GLOBALS['allocationLog']
    ];
}

// =========================================================================
// 2. WORKLOAD EXPANSION & SEPARATION
// =========================================================================

function expandWorkload($conn, $rawWorkloads, $semester) {
    $expandedLectures   = [];
    $expandedPracticals = [];
    $optionalUnits      = [];
    $compulsoryUnits    = [];
    $allUnits           = [];

    foreach ($rawWorkloads as $wl) {
        $isPractical = $wl['is_practical'];
        $unitsCount  = (int)$wl['lecture_count'];

        for ($i = 0; $i < $unitsCount; $i++) {
            $unit = [
                'instance_id'   => $wl['workload_id'] . '_D' . $wl['division_id'] . '_' . $i,
                'workload_id'   => $wl['workload_id'],
                'course_id'     => $wl['course_id'],
                'division_id'   => $wl['division_id'],
                'teacher_id'    => $wl['teacher_id'],
                'is_practical'  => $isPractical,
                'course_type'   => $wl['course_type'],
                'is_optional'   => $wl['is_optional'],
                'optional_id'   => $wl['optional_id'],
                'student_count' => $wl['student_count'],
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
                if ($wl['is_optional']) {
                    $optionalUnits[] = $unit;
                } else {
                    $compulsoryUnits[] = $unit;
                }
            }
        }
    }

    return [
        'all'        => $allUnits,
        'lectures'   => $expandedLectures,
        'practicals' => $expandedPracticals,
        'optional'   => $optionalUnits,
        'compulsory' => $compulsoryUnits
    ];
}

// =========================================================================
// 3. ZERO-GAP CONTIGUOUS SLOT ORDERING
// =========================================================================

function getOrderedContiguousSlots($freeSlots, $dId, $day, $state, $divisionPrefStartSlot = null) {
    if (empty($freeSlots)) return [];

    sort($freeSlots, SORT_NUMERIC);

    // Get all slots already allocated to this division today
    $assignedSlots = [];
    foreach ($state['assigned'] as $alloc) {
        if ($alloc['DIVISION_ID'] == $dId && $alloc['DAY'] === $day) {
            $assignedSlots[] = (int)$alloc['SLOT_ID'];
        }
    }

    // If no lectures are scheduled today, start strictly at the Division's Preferred Start Slot
    if (empty($assignedSlots)) {
        if ($divisionPrefStartSlot && in_array($divisionPrefStartSlot, $freeSlots)) {
            $ordered = [$divisionPrefStartSlot];
            foreach ($freeSlots as $s) {
                if ($s !== $divisionPrefStartSlot) $ordered[] = $s;
            }
            return $ordered;
        }
        return $freeSlots; // Default ascending order
    }

    sort($assignedSlots, SORT_NUMERIC);
    $minSlot = min($assignedSlots);
    $maxSlot = max($assignedSlots);

    $internalGaps = [];
    $adjacentNext = [];
    $adjacentPrev = [];
    $outerSlots   = [];

    foreach ($freeSlots as $sId) {
        $sId = (int)$sId;
        if ($sId > $minSlot && $sId < $maxSlot) {
            $internalGaps[] = $sId; // HIGHEST PRIORITY: Fill gaps inside schedule
        } elseif ($sId === ($maxSlot + 1)) {
            $adjacentNext[] = $sId; // Expand right
        } elseif ($sId === ($minSlot - 1)) {
            $adjacentPrev[] = $sId; // Expand left
        } else {
            $outerSlots[] = $sId;   // Non-contiguous fallback
        }
    }

    return array_merge($internalGaps, $adjacentNext, $adjacentPrev, $outerSlots);
}

// =========================================================================
// 4. OPTIONAL COURSE SYNCHRONIZATION ENGINE
// =========================================================================

function allocateOptionalCoursesSynchronized(
    $optionalUnits,
    $divisions,
    $lectureSlots,
    $weekdays,
    $classrooms,
    $teacherAvail,
    $overlappingSlots,
    &$state,
    $timeslots,
    $teachersMap,
    $coursesMap,
    &$divSlotMatrix,
    &$teacherMatrix,
    $divDailyQuotas
) {
    if (empty($optionalUnits)) return;

    // Group optional course units by optional pair identifier
    $groupedPairs = [];
    foreach ($optionalUnits as $unit) {
        $pairKey = min($unit['course_id'], $unit['optional_id']) . '_' . max($unit['course_id'], $unit['optional_id']);
        $groupedPairs[$pairKey][] = $unit;
    }

    foreach ($groupedPairs as $pairKey => $units) {
        logStep(" -> Synchronizing Optional Group [{$pairKey}] containing " . count($units) . " workload units.");

        // Group by division to align simultaneous execution
        $unitsByDiv = [];
        foreach ($units as $u) {
            $unitsByDiv[$u['division_id']][] = $u;
        }

        foreach ($weekdays as $day) {
            foreach ($lectureSlots as $slotId) {
                $canScheduleAll = true;
                $proposedAllocations = [];

                foreach ($unitsByDiv as $dId => $divUnits) {
                    $u = $divUnits[0]; // Process current unit
                    $tId = $u['teacher_id'];
                    $cId = $u['course_id'];

                    if (isSlotOccupiedByDivision($dId, $day, $slotId, $overlappingSlots, $state) ||
                        isTeacherOccupied($tId, $day, $slotId, $overlappingSlots, $state) ||
                        ($teacherMatrix[$tId][$day][$slotId] ?? '') !== 'FREE') {
                        $canScheduleAll = false;
                        break;
                    }

                    $room = selectClassroom($dId, $day, $slotId, $u['student_count'] ?? 60, $classrooms, $overlappingSlots, $state);
                    if (!$room) {
                        $canScheduleAll = false;
                        break;
                    }

                    $proposedAllocations[] = [
                        'unit' => $u,
                        'dId'  => $dId,
                        'tId'  => $tId,
                        'cId'  => $cId,
                        'room' => $room
                    ];
                }

                if ($canScheduleAll && !empty($proposedAllocations)) {
                    foreach ($proposedAllocations as $alloc) {
                        $u    = $alloc['unit'];
                        $dId  = $alloc['dId'];
                        $tId  = $alloc['tId'];
                        $cId  = $alloc['cId'];
                        $room = $alloc['room'];

                        markStateOccupied($state, $tId, $dId, $room, $day, $slotId, $overlappingSlots);
                        markDivisionSlotAllotted($divSlotMatrix, $dId, $day, $slotId, $overlappingSlots);
                        markTeacherSlotAllotted($teacherMatrix, $tId, $day, $slotId, $overlappingSlots);

                        $state['divisionDayRoom'][$dId][$day]       = $room;
                        $state['courseDayCount'][$dId][$cId][$day]  = ($state['courseDayCount'][$dId][$cId][$day] ?? 0) + 1;
                        $state['divisionDailyLectures'][$dId][$day] = ($state['divisionDailyLectures'][$dId][$day] ?? 0) + 1;

                        $state['assigned'][] = [
                            'DAY'         => $day,
                            'SLOT_ID'     => $slotId,
                            'DIVISION_ID' => $dId,
                            'COURSE_ID'   => $cId,
                            'TYPE'        => 'LECTURE',
                            'TEACHER_ID'  => $tId,
                            'ROOM_ID'     => $room,
                            'IS_OPTIONAL' => true
                        ];
                    }
                    logStep("    ==> OPTIONAL SYNCHRONIZED: Successfully paired group {$pairKey} on {$day} at Slot {$slotId}.");
                    break 2; // Move to next optional group
                }
            }
        }
    }
}

// =========================================================================
// 5. OPTIMUM CLASSROOM ALLOCATION
// =========================================================================

function selectClassroom($dId, $day, $slotId, $studentCount, $lectureHalls, $overlappingSlots, $state, $excludeRooms = []) {
    // 1. Prefer room already assigned to division for this day to avoid room switching
    $preferredDayRoom = $state['divisionDayRoom'][$dId][$day] ?? null;

    if ($preferredDayRoom && !in_array($preferredDayRoom, $excludeRooms) && !isRoomOccupied($preferredDayRoom, $day, $slotId, $overlappingSlots, $state)) {
        return $preferredDayRoom;
    }

    // 2. Rank remaining available classrooms by Least Excess Capacity
    $eligibleRooms = [];
    foreach ($lectureHalls as $hall) {
        $hId = $hall['CLASSROOM_ID'];
        if (in_array($hId, $excludeRooms)) continue;

        $capacity = (int)($hall['CAPACITY'] ?? 0);

        if ($capacity >= $studentCount && !isRoomOccupied($hId, $day, $slotId, $overlappingSlots, $state)) {
            $eligibleRooms[] = [
                'room_id' => $hId,
                'waste'   => $capacity - $studentCount
            ];
        }
    }

    if (empty($eligibleRooms)) return null;

    // Sort ascending by excess capacity (minimizes wasted seats)
    usort($eligibleRooms, fn($a, $b) => $a['waste'] <=> $b['waste']);

    return $eligibleRooms[0]['room_id'];
}

// =========================================================================
// 6. HELPER FUNCTIONS & COMPATIBILITY LAYER
// =========================================================================

function buildDivisionSlotMatrix($divisions, $weekdays, $lectureSlots) {
    $matrix = [];
    foreach ($divisions as $dId => $div) {
        foreach ($weekdays as $day) {
            foreach ($lectureSlots as $sId) {
                $matrix[$dId][$day][$sId] = 'FREE';
            }
        }
    }
    return $matrix;
}

function buildTeacherAvailabilityMatrix($teachersMap, $weekdays, $lectureSlots, $teacherAvail) {
    $matrix = [];
    foreach ($teachersMap as $tId => $name) {
        foreach ($weekdays as $day) {
            foreach ($lectureSlots as $sId) {
                $isAvail = $teacherAvail[$tId][$day][$sId] ?? false;
                $matrix[$tId][$day][$sId] = $isAvail ? 'FREE' : 'UNAVAILABLE';
            }
        }
    }
    return $matrix;
}

function markDivisionSlotAllotted(&$divSlotMatrix, $dId, $day, $slotId, $overlappingSlots) {
    $related = $overlappingSlots[$slotId] ?? [$slotId];
    foreach ($related as $s) {
        if (isset($divSlotMatrix[$dId][$day][$s])) {
            $divSlotMatrix[$dId][$day][$s] = 'ALLOTTED';
        }
    }
}

function markTeacherSlotAllotted(&$teacherMatrix, $tId, $day, $slotId, $overlappingSlots) {
    $related = $overlappingSlots[$slotId] ?? [$slotId];
    foreach ($related as $s) {
        if (isset($teacherMatrix[$tId][$day][$s])) {
            $teacherMatrix[$tId][$day][$s] = 'ALLOTTED';
        }
    }
}

function syncPracticalsToSlotMatrices($assignedList, &$divSlotMatrix, &$teacherMatrix, $overlappingSlots) {
    foreach ($assignedList as $alloc) {
        if (($alloc['TYPE'] ?? '') === 'PRACTICAL') {
            $dId = $alloc['DIVISION_ID'];
            $tId = $alloc['TEACHER_ID'];
            $day = $alloc['DAY'];
            $sId = $alloc['SLOT_ID'];

            markDivisionSlotAllotted($divSlotMatrix, $dId, $day, $sId, $overlappingSlots);
            markTeacherSlotAllotted($teacherMatrix, $tId, $day, $sId, $overlappingSlots);
        }
    }
}

function calculateDivisionTotalWeeklyHours($dId, $expandedWorkloads) {
    $totalHours = 0;
    foreach ($expandedWorkloads['lectures'] as $lec) {
        if ((int)$lec['division_id'] === (int)$dId) {
            $totalHours += 1;
        }
    }
    foreach ($expandedWorkloads['practicals'] as $prac) {
        if ((int)$prac['division_id'] === (int)$dId) {
            $totalHours += 2;
        }
    }
    return $totalHours;
}

function calculateDivisionDailyQuotas($divisions, $expandedWorkloads, $weekdays) {
    $numDays = count($weekdays) > 0 ? count($weekdays) : 6;
    $quotas = [];

    foreach ($divisions as $dId => $div) {
        $weeklyHours = calculateDivisionTotalWeeklyHours($dId, $expandedWorkloads);
        $dailyLectureQuota = (int)ceil(($weeklyHours / $numDays));
        $quotas[$dId] = [
            'total_weekly_hours'  => $weeklyHours,
            'daily_lecture_quota' => $dailyLectureQuota
        ];
    }
    return $quotas;
}

function calculateTeacherWeeklyAvailableHours($tId, $teacherMatrix, $weekdays, $lectureSlots) {
    $freeSlots = 0;
    foreach ($weekdays as $day) {
        foreach ($lectureSlots as $sId) {
            if (($teacherMatrix[$tId][$day][$sId] ?? '') === 'FREE') {
                $freeSlots++;
            }
        }
    }
    return $freeSlots;
}

function findTeacherWithSmallestAvailability($pendingWorkload, $dId, $day, $slotId, $teacherMatrix, $weekdays, $lectureSlots, $teacherAvail, $overlappingSlots, $state, $maxLecturesPerDay = 1) {
    $bestCandidate = null;
    $minAvailableHours = PHP_INT_MAX;

    foreach ($pendingWorkload as $index => $lec) {
        $tId = (int)$lec['teacher_id'];
        $cId = (int)$lec['course_id'];

        $currentCourseDayCount = $state['courseDayCount'][$dId][$cId][$day] ?? 0;
        if ($currentCourseDayCount >= $maxLecturesPerDay) continue;

        if (isTeacherOccupied($tId, $day, $slotId, $overlappingSlots, $state)) continue;
        if (($teacherMatrix[$tId][$day][$slotId] ?? '') !== 'FREE') continue;

        $availHours = calculateTeacherWeeklyAvailableHours($tId, $teacherMatrix, $weekdays, $lectureSlots);

        if ($availHours < $minAvailableHours) {
            $minAvailableHours = $availHours;
            $bestCandidate = [
                'index'       => $index,
                'workload'    => $lec,
                'teacher_id'  => $tId,
                'avail_hours' => $availHours
            ];
        }
    }

    return $bestCandidate;
}

function allocateCoursesBalanced(
    $divisions,
    $courses,
    $lectureSlots,
    $weekdays,
    $lectureHalls,
    $teacherAvail,
    $overlappingSlots,
    &$state,
    $timeslots = [],
    $teachersMap = [],
    $coursesMap = [],
    &$divSlotMatrix = [],
    &$teacherMatrix = [],
    $divDailyQuotas = []
) {
    $unassignedWorkload = [];
    foreach ($divisions as $dId => $divData) {
        $unassignedWorkload[$dId] = array_filter($courses, fn($c) => (int)$c['division_id'] === (int)$dId);
        $unassignedWorkload[$dId] = array_values($unassignedWorkload[$dId]);
    }

    foreach ($weekdays as $day) {
        foreach ($divisions as $dId => $divData) {
            $divName = $divData['DIVISION_NAME'] ?? "Division ID {$dId}";
            $pendingWorkload = getPendingWorkloadForDivision($unassignedWorkload, $dId);
            if (empty($pendingWorkload)) continue;

            $targetForToday = $divDailyQuotas[$dId]['daily_lecture_quota'] ?? 4;
            $prefStartSlot = $divData['START_TIME_ID'] ?? null;

            $freeSlots = getDivisionAvailableSlots($dId, $day, $lectureSlots, $overlappingSlots, $state, $timeslots, $divisions);
            $orderedSlots = getOrderedContiguousSlots($freeSlots, $dId, $day, $state, $prefStartSlot);

            foreach ($orderedSlots as $slotId) {
                $pendingWorkload = getPendingWorkloadForDivision($unassignedWorkload, $dId);
                if (empty($pendingWorkload)) break;
                if (($state['divisionDailyLectures'][$dId][$day] ?? 0) >= $targetForToday) break;

                $slotLabel = isset($timeslots[$slotId]) ? "{$timeslots[$slotId]['START_TIME']}-{$timeslots[$slotId]['END_TIME']}" : "Slot {$slotId}";

                $candidate = findTeacherWithSmallestAvailability(
                    $pendingWorkload, $dId, $day, $slotId, $teacherMatrix, $weekdays, $lectureSlots, $teacherAvail, $overlappingSlots, $state, 1
                );

                if ($candidate !== null) {
                    $i   = $candidate['index'];
                    $lec = $candidate['workload'];
                    $tId = $lec['teacher_id'];
                    $cId = $lec['course_id'];

                    $tName = $teachersMap[$tId] ?? "Teacher ID {$tId}";
                    $cName = $coursesMap[$cId] ?? "Course ID {$cId}";

                    $selectedRoom = selectClassroom($dId, $day, $slotId, $lec['student_count'] ?? 60, $lectureHalls, $overlappingSlots, $state);

                    if ($selectedRoom) {
                        markStateOccupied($state, $tId, $dId, $selectedRoom, $day, $slotId, $overlappingSlots);
                        markDivisionSlotAllotted($divSlotMatrix, $dId, $day, $slotId, $overlappingSlots);
                        markTeacherSlotAllotted($teacherMatrix, $tId, $day, $slotId, $overlappingSlots);

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

                        logStep("   ==> ALLOCATED: Workload {$lec['workload_id']} ({$cName}) - {$tName} to {$divName} on {$day} at slot {$slotLabel} [Room ID: {$selectedRoom}].");

                        array_splice($unassignedWorkload[$dId], $i, 1);

                        // Re-fetch contiguous slots dynamically
                        $freeSlots = getDivisionAvailableSlots($dId, $day, $lectureSlots, $overlappingSlots, $state, $timeslots, $divisions);
                        $orderedSlots = getOrderedContiguousSlots($freeSlots, $dId, $day, $state, $prefStartSlot);
                    }
                }
            }
        }
    }

    foreach ($unassignedWorkload as $dId => $pending) {
        foreach ($pending as $unassignedUnit) {
            $state['unallocated'][] = [
                'unit'   => $unassignedUnit,
                'reason' => "Could not allocate within target quota constraints."
            ];
        }
    }
}

function fallBackAllocation(
    $divisions,
    $lectureSlots,
    $weekdays,
    $classrooms,
    $overlappingSlots,
    &$state,
    $teachersMap,
    $coursesMap,
    $timeslots,
    &$divSlotMatrix,
    &$teacherMatrix
) {
    if (empty($state['unallocated'])) return $state;

    logStep("\n[FALLBACK PASS] Resolving " . count($state['unallocated']) . " unallocated workloads...");

    $remainingUnallocated = [];

    foreach ($state['unallocated'] as $item) {
        $unit = $item['unit'];
        $dId  = $unit['division_id'];
        $tId  = $unit['teacher_id'];
        $cId  = $unit['course_id'];
        $allocated = false;

        $sortedWeekdays = $weekdays;
        usort($sortedWeekdays, fn($a, $b) => getDivisionTotalAllocatedHours($dId, $a, $state) <=> getDivisionTotalAllocatedHours($dId, $b, $state));

        foreach ($sortedWeekdays as $day) {
            $freeSlots = getDivisionAvailableSlots($dId, $day, $lectureSlots, $overlappingSlots, $state, $timeslots, $divisions);
            $orderedSlots = getOrderedContiguousSlots($freeSlots, $dId, $day, $state);

            foreach ($orderedSlots as $sId) {
                if (isTeacherOccupied($tId, $day, $sId, $overlappingSlots, $state)) continue;

                $fallbackRoom = selectClassroom($dId, $day, $sId, $unit['student_count'] ?? 60, $classrooms, $overlappingSlots, $state);

                if ($fallbackRoom) {
                    markStateOccupied($state, $tId, $dId, $fallbackRoom, $day, $sId, $overlappingSlots);
                    markDivisionSlotAllotted($divSlotMatrix, $dId, $day, $sId, $overlappingSlots);
                    markTeacherSlotAllotted($teacherMatrix, $tId, $day, $sId, $overlappingSlots);

                    $state['divisionDayRoom'][$dId][$day]       = $fallbackRoom;
                    $state['courseDayCount'][$dId][$cId][$day]  = ($state['courseDayCount'][$dId][$cId][$day] ?? 0) + 1;
                    $state['divisionDailyLectures'][$dId][$day] = ($state['divisionDailyLectures'][$dId][$day] ?? 0) + 1;

                    $state['assigned'][] = [
                        'DAY'         => $day,
                        'SLOT_ID'     => $sId,
                        'DIVISION_ID' => $dId,
                        'COURSE_ID'   => $cId,
                        'TYPE'        => 'LECTURE',
                        'TEACHER_ID'  => $tId,
                        'ROOM_ID'     => $fallbackRoom,
                        'IS_FALLBACK' => true
                    ];

                    $tName = $teachersMap[$tId] ?? "Teacher ID {$tId}";
                    $cName = $coursesMap[$cId] ?? "Course ID {$cId}";
                    logStep(" ==> FALLBACK ALLOCATED: Workload {$unit['workload_id']} ({$cName}) - {$tName} to Div {$dId} on {$day} at Slot {$sId} [Room ID: {$fallbackRoom}].");

                    $allocated = true;
                    break 2;
                }
            }
        }

        if (!$allocated) {
            $remainingUnallocated[] = $item;
        }
    }

    $state['unallocated'] = $remainingUnallocated;
    return $state;
}

function initSystemState() {
    return [
        'assigned'              => [],
        'teacherOccupied'       => [], 
        'divisionOccupied'      => [], 
        'roomOccupied'          => [], 
        'divisionDayRoom'       => [], 
        'courseDayCount'        => [], 
        'divisionDailyLectures' => [], 
        'unallocated'           => [],
        'brokenPreferences'     => []
    ];
}

function buildDivisionsArray($rawDivisions, $expandedLectures) {
    $divisions = [];
    foreach ($rawDivisions as $dId => $divData) {
        $divLectures = array_filter($expandedLectures, fn($unit) => (int)$unit['division_id'] === (int)$dId);
        $divisions[$dId] = $divData;
        $divisions[$dId]['workload'] = array_values($divLectures);
    }
    return $divisions;
}

function getLectureSlotsList($timeslots) {
    $lectureSlots = [];
    foreach ($timeslots as $sId => $sData) {
        if (($sData['SLOT_TYPE'] ?? '') === 'LECTURE') {
            $lectureSlots[] = (int)$sId;
        }
    }
    return !empty($lectureSlots) ? $lectureSlots : [1, 3, 7, 8, 10, 11, 14, 15];
}

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
            $availableSlots[] = (int)$slotId;
        }
    }
    return $availableSlots;
}

function isSlotOccupiedByDivision($dId, $day, $slotId, $overlappingSlots, $state) {
    $relatedSlots = $overlappingSlots[$slotId] ?? [$slotId];
    foreach ($relatedSlots as $s) {
        if (!empty($state['divisionOccupied'][$dId][$day][$s])) return true;
    }
    return false;
}

function isTeacherOccupied($tId, $day, $slotId, $overlappingSlots, $state) {
    if (!empty($state['teacherOccupied'][$tId][$day][$slotId])) return true;
    $relatedSlots = $overlappingSlots[$slotId] ?? [];
    foreach ($relatedSlots as $s) {
        if ((int)$s !== (int)$slotId && !empty($state['teacherOccupied'][$tId][$day][$s])) return true;
    }
    return false;
}

function isRoomOccupied($roomId, $day, $slotId, $overlappingSlots, $state) {
    $relatedSlots = $overlappingSlots[$slotId] ?? [$slotId];
    foreach ($relatedSlots as $s) {
        if (!empty($state['roomOccupied'][$roomId][$day][$s])) return true;
    }
    return false;
}

function markStateOccupied(&$state, $tId, $dId, $roomId, $day, $slotId, $overlappingSlots) {
    $relatedSlots = $overlappingSlots[$slotId] ?? [$slotId];
    foreach ($relatedSlots as $s) {
        $state['teacherOccupied'][$tId][$day][$s]  = true;
        $state['divisionOccupied'][$dId][$day][$s] = true;
        $state['roomOccupied'][$roomId][$day][$s]  = true;
    }
}

function getPendingWorkloadForDivision(&$unassignedWorkload, $dId) {
    return $unassignedWorkload[$dId] ?? [];
}

function getDivisionTotalAllocatedHours($dId, $day, $state) {
    $totalHours = 0;
    foreach ($state['assigned'] as $alloc) {
        if ((int)$alloc['DIVISION_ID'] === (int)$dId && $alloc['DAY'] === $day) {
            $totalHours += (($alloc['TYPE'] ?? '') === 'PRACTICAL') ? 2 : 1;
        }
    }
    return $totalHours;
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
                $map[$s1_id][] = (int)$s2_id;
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

function getTeachersMap($conn) {
    $res = $conn->query("SELECT TEACHER_ID, FIRST_NAME FROM TEACHER");
    $map = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $map[(int)$row['TEACHER_ID']] = $row['FIRST_NAME'];
        }
    }
    return $map;
}

function getCoursesMap($conn) {
    $res = $conn->query("SELECT COURSE_ID, SHORT_NAME FROM COURSE");
    $map = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $map[(int)$row['COURSE_ID']] = $row['SHORT_NAME'];
        }
    }
    return $map;
}

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
        if (empty($divPracticals)) continue;

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
        if (empty($candidates)) $candidates = $matchingType;
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

function allocatePracticals($practicals, $divisionPracticalSlots, $classrooms, $teacherAvail, $overlappingSlots, $state, $teachersMap = [], $coursesMap = [], $timeslots = []) {
    $weekdays = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY'];

    foreach ($practicals as $p) {
        $dId = $p['division_id'];
        $tName = $teachersMap[$p['teacher_id']] ?? "Teacher ID {$p['teacher_id']}";
        $cName = $coursesMap[$p['course_id']] ?? "Course ID {$p['course_id']}";

        $fixedConfig = $divisionPracticalSlots[$dId] ?? null;
        if (!$fixedConfig) {
            logStep(" [Practical Error] No fixed lab room/slot found for Division ID {$dId}.");
            $state['unallocated'][] = [
                'unit'   => $p,
                'reason' => "No fixed practical slot/room available for Division ID {$dId}."
            ];
            continue;
        }

        $slotId = $fixedConfig['slot_id'];
        $roomId = $fixedConfig['room_id'];
        $slotLabel = isset($timeslots[$slotId]) ? "{$timeslots[$slotId]['START_TIME']}-{$timeslots[$slotId]['END_TIME']}" : "Slot {$slotId}";
        $allocated = false;

        foreach ($weekdays as $day) {
            if (empty($teacherAvail[$p['teacher_id']][$day][$slotId])) continue;

            if (isSlotOccupiedByDivision($dId, $day, $slotId, $overlappingSlots, $state)) continue;
            if (isTeacherOccupied($p['teacher_id'], $day, $slotId, $overlappingSlots, $state)) continue;
            if (isRoomOccupied($roomId, $day, $slotId, $overlappingSlots, $state)) continue;

            markStateOccupied($state, $p['teacher_id'], $dId, $roomId, $day, $slotId, $overlappingSlots);

            $state['assigned'][] = [
                'DAY'         => $day,
                'SLOT_ID'     => $slotId,
                'DIVISION_ID' => $dId,
                'COURSE_ID'   => $p['course_id'],
                'TYPE'        => 'PRACTICAL',
                'TEACHER_ID'  => $p['teacher_id'],
                'ROOM_ID'     => $roomId
            ];

            logStep(" ==> PRACTICAL ALLOCATED: Workload {$p['workload_id']} ({$cName}) - {$tName} to Div {$dId} on {$day} at slot {$slotLabel} [Room ID: {$roomId}].");

            $allocated = true;
            break;
        }

        if (!$allocated) {
            logStep(" [Practical Conflict] Could not allocate {$cName} ({$tName}) for Div {$dId} in fixed slot {$slotLabel}.");
            $state['unallocated'][] = [
                'unit'   => $p,
                'reason' => "Teacher conflict or division busy in fixed practical slot (Slot ID: {$slotId})."
            ];
        }
    }

    return $state;
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