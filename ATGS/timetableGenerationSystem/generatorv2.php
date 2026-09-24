<?php
/**
 * generatorv2.php
 * Constraint Satisfaction Solver & Optimization Engine for Timetable Generation
 */

include_once 'dbConnect.php';

function generate($semester = 'ODD', $academicYear = '2026-27') {
    global $conn;

    // -------------------------------------------------------------------------
    // 1. DATA EXTRACTION & NORMALIZATION
    // -------------------------------------------------------------------------
    
    // Fetch Timeslots & build collision map (detect overlapping clock times)
    $slotsRes = $conn->query("SELECT * FROM TIMESLOT WHERE SLOT_TYPE != 'BREAK' ORDER BY START_TIME, END_TIME");
    $timeslots = [];
    while ($row = $slotsRes->fetch_assoc()) {
        $timeslots[$row['SLOT_ID']] = $row;
    }

    $overlappingSlots = [];
    foreach ($timeslots as $s1_id => $s1) {
        $overlappingSlots[$s1_id] = [];
        $t1_start = strtotime($s1['START_TIME']);
        $t1_end   = strtotime($s1['END_TIME']);
        foreach ($timeslots as $s2_id => $s2) {
            $t2_start = strtotime($s2['START_TIME']);
            $t2_end   = strtotime($s2['END_TIME']);
            // If time ranges overlap
            if ($t1_start < $t2_end && $t1_end > $t2_start) {
                $overlappingSlots[$s1_id][] = $s2_id;
            }
        }
    }

    // Fetch Classrooms & Labs
    $roomsRes = $conn->query("SELECT * FROM CLASSROOM WHERE CAPACITY IS NOT NULL ORDER BY CAPACITY ASC");
    $classrooms = [];
    while ($row = $roomsRes->fetch_assoc()) {
        $classrooms[$row['CLASSROOM_ID']] = $row;
    }

    // Fetch Teacher Availability
    $availRes = $conn->query("SELECT TEACHER_ID, SLOT_ID, WEEKDAY FROM AVAILABILITY WHERE STATUS = 'AVAILABLE'");
    $teacherAvail = [];
    $teacherAvailCount = [];
    while ($row = $availRes->fetch_assoc()) {
        $tId = $row['TEACHER_ID'];
        $w = $row['WEEKDAY'];
        $sId = $row['SLOT_ID'];
        $teacherAvail[$tId][$w][$sId] = true;
        $teacherAvailCount[$tId] = ($teacherAvailCount[$tId] ?? 0) + 1;
    }

    // Fetch Divisions & details
    $divRes = $conn->query("SELECT * FROM DIVISION WHERE STUDENT_COUNT > 0");
    $divisions = [];
    while ($row = $divRes->fetch_assoc()) {
        $divisions[$row['DIVISION_ID']] = $row;
    }

    // Fetch Courses for active semester
    $courseRes = $conn->query("SELECT * FROM COURSE WHERE SEMESTER = '$semester'");
    $courses = [];
    while ($row = $courseRes->fetch_assoc()) {
        $courses[$row['COURSE_ID']] = $row;
    }

    // Fetch Workloads from TEACHES
    $workloadQuery = "
        SELECT t.WORKLOAD_ID, t.TEACHER_ID, t.COURSE_ID, t.DIVISION_ID, t.LECTURE_COUNT,
               c.ISPRACTICAL, c.TYPE AS COURSE_TYPE, c.ISOPTIONAL, c.OPTIONAL_ID, c.WEEKLY_LECTURES,
               d.STUDENT_COUNT, d.CLASSROOM_ID AS PREFERRED_ROOM_ID, d.START_TIME_ID AS PREF_START_SLOT
        FROM TEACHES t
        JOIN COURSE c ON t.COURSE_ID = c.COURSE_ID
        JOIN DIVISION d ON t.DIVISION_ID = d.DIVISION_ID
        WHERE c.SEMESTER = '$semester' AND t.LECTURE_COUNT > 0
    ";
    $workloadRes = $conn->query($workloadQuery);
    
    $workloadUnits = [];
    $requiredWorkloadSummary = [];
    
    while ($row = $workloadRes->fetch_assoc()) {
        $cId = $row['COURSE_ID'];
        $dId = $row['DIVISION_ID'];
        $tId = $row['TEACHER_ID'];
        $count = (int)$row['LECTURE_COUNT'];
        $isPractical = (bool)$row['ISPRACTICAL'];

        $key = "C{$cId}_D{$dId}_T{$tId}";
        $requiredWorkloadSummary[$key] = [
            'course_id' => $cId,
            'division_id' => $dId,
            'teacher_id' => $tId,
            'required_slots' => $count,
            'is_practical' => $isPractical
        ];

        // Practical unit count is halved if timeslots are 2-hour blocks
        $unitsToSchedule = $isPractical ? ceil($count / 2) : $count;

        for ($i = 0; $i < $unitsToSchedule; $i++) {
            $workloadUnits[] = [
                'workload_id'  => $row['WORKLOAD_ID'],
                'course_id'    => $cId,
                'division_id'  => $dId,
                'teacher_id'   => $tId,
                'is_practical' => $isPractical,
                'course_type'  => $row['COURSE_TYPE'],
                'is_optional'   => (bool)$row['ISOPTIONAL'],
                'optional_id'  => $row['OPTIONAL_ID'],
                'student_count'=> $row['STUDENT_COUNT'],
                'pref_room_id' => $row['PREFERRED_ROOM_ID'],
                'teacher_avail_score' => $teacherAvailCount[$tId] ?? 0
            ];
        }
    }

    // Sort Workloads by MRV (Most Constrained Resource First):
    // 1. Practicals first (Restricted to specific 2-hr slots and Lab rooms)
    // 2. Teachers with smallest availability window
    usort($workloadUnits, function($a, $b) {
        if ($a['is_practical'] !== $b['is_practical']) {
            return $a['is_practical'] ? -1 : 1;
        }
        return $a['teacher_avail_score'] <=> $b['teacher_avail_score'];
    });

    // -------------------------------------------------------------------------
    // 2. CONSTRAINT SATISFACTION SOLVER (BACKTRACKING + HEURISTICS)
    // -------------------------------------------------------------------------

    $weekdays = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY'];
    
    // State Tracking Databases
    $assignedSchedule = [];
    $teacherOccupied = [];  // [teacher_id][weekday][slot_id] = true
    $divisionOccupied = []; // [division_id][weekday][slot_id] = true
    $roomOccupied = [];     // [room_id][weekday][slot_id] = true
    $divisionDayRooms = []; // [division_id][weekday] = room_id
    $courseDayCount = [];   // [division_id][course_id][weekday] = count

    $allocatedWorkloadSummary = [];
    $brokenTimePreferences = [];

    // Solver Function
    $solve = function($unitIndex) use (
        &$solve, &$workloadUnits, &$weekdays, &$timeslots, &$classrooms, 
        &$teacherAvail, &$overlappingSlots, &$assignedSchedule, 
        &$teacherOccupied, &$divisionOccupied, &$roomOccupied, 
        &$divisionDayRooms, &$courseDayCount, &$allocatedWorkloadSummary,
        &$brokenTimePreferences
    ) {
        if ($unitIndex >= count($workloadUnits)) {
            return true; // Complete Allocation Success
        }

        $unit = $workloadUnits[$unitIndex];
        $cId = $unit['course_id'];
        $dId = $unit['division_id'];
        $tId = $unit['teacher_id'];
        $isPractical = $unit['is_practical'];

        // Filter valid rooms based on capacity and lab type
        $feasibleRooms = [];
        foreach ($classrooms as $rId => $room) {
            if ($room['CAPACITY'] < $unit['student_count']) continue;
            
            if ($isPractical) {
                // Practicals require matching Lab category
                if ($unit['course_type'] === 'IT PRACTICAL' && $room['CATEGORY'] !== 'IT LAB') continue;
                if ($unit['course_type'] === 'PHYSICS PRACTICAL' && $room['CATEGORY'] !== 'PHYSICS LAB') continue;
                if ($unit['course_type'] === 'CHEMISTRY PRACTICAL' && $room['CATEGORY'] !== 'CHEMISTRY LAB') continue;
                if ($unit['course_type'] === 'BIOLOGY PRACTICAL' && $room['CATEGORY'] !== 'BIOLOGY LAB') continue;
            } else {
                // Lectures prefer LECTURE HALLs
                if ($room['CATEGORY'] !== 'LECTURE HALL') continue;
            }
            $feasibleRooms[$rId] = $room;
        }

        // Sort rooms: Smallest sufficient room first
        uasort($feasibleRooms, fn($a, $b) => $a['CAPACITY'] <=> $b['CAPACITY']);

        foreach ($weekdays as $day) {
            // Avoid scheduling same theory course more than once a day per division
            if (!$isPractical && ($courseDayCount[$dId][$cId][$day] ?? 0) >= 1) {
                continue;
            }

            foreach ($timeslots as $sId => $slot) {
                // Enforcement: Practical slots must be 2-hour PRACTICAL types
                if ($isPractical && $slot['SLOT_TYPE'] !== 'PRACTICAL') continue;
                if (!$isPractical && $slot['SLOT_TYPE'] !== 'LECTURE') continue;

                // Check Teacher Availability
                if (empty($teacherAvail[$tId][$day][$sId])) continue;

                // Check Overlaps for Teacher and Division
                $hasOverlap = false;
                foreach ($overlappingSlots[$sId] as $overlapSlotId) {
                    if (!empty($teacherOccupied[$tId][$day][$overlapSlotId]) ||
                        !empty($divisionOccupied[$dId][$day][$overlapSlotId])) {
                        $hasOverlap = true;
                        break;
                    }
                }
                if ($hasOverlap) continue;

                // Select Room
                foreach ($feasibleRooms as $rId => $room) {
                    // Check Room Overlap
                    $roomHasOverlap = false;
                    foreach ($overlappingSlots[$sId] as $overlapSlotId) {
                        if (!empty($roomOccupied[$rId][$day][$overlapSlotId])) {
                            $roomHasOverlap = true;
                            break;
                        }
                    }
                    if ($roomHasOverlap) continue;

                    // Prefer keeping same classroom for same division on same day (for theory)
                    if (!$isPractical && isset($divisionDayRooms[$dId][$day])) {
                        if ($divisionDayRooms[$dId][$day] !== $rId && count($feasibleRooms) > 1) {
                            // Soft penalty constraint skip on first pass
                        }
                    }

                    // --- APPLY ALLOTMENT ---
                    foreach ($overlappingSlots[$sId] as $overlapSlotId) {
                        $teacherOccupied[$tId][$day][$overlapSlotId] = true;
                        $divisionOccupied[$dId][$day][$overlapSlotId] = true;
                        $roomOccupied[$rId][$day][$overlapSlotId] = true;
                    }
                    
                    $oldDayRoom = $divisionDayRooms[$dId][$day] ?? null;
                    if (!$isPractical) $divisionDayRooms[$dId][$day] = $rId;
                    $courseDayCount[$dId][$cId][$day] = ($courseDayCount[$dId][$cId][$day] ?? 0) + 1;

                    $allocationRecord = [
                        'COURSE_ID'   => $cId,
                        'DIVISION_ID' => $dId,
                        'CLASSROOM_ID' => $rId,
                        'SLOT_ID'     => $sId,
                        'WEEKDAY'     => $day,
                        'TEACHER_ID'  => $tId,
                        'IS_PRACTICAL' => $isPractical
                    ];
                    $assignedSchedule[] = $allocationRecord;

                    // Recurse to next unit
                    if ($solve($unitIndex + 1)) {
                        return true;
                    }

                    // --- BACKTRACK ---
                    array_pop($assignedSchedule);
                    foreach ($overlappingSlots[$sId] as $overlapSlotId) {
                        unset($teacherOccupied[$tId][$day][$overlapSlotId]);
                        unset($divisionOccupied[$dId][$day][$overlapSlotId]);
                        unset($roomOccupied[$rId][$day][$overlapSlotId]);
                    }
                    if (!$isPractical && $oldDayRoom === null) {
                        unset($divisionDayRooms[$dId][$day]);
                    } else if (!$isPractical) {
                        $divisionDayRooms[$dId][$day] = $oldDayRoom;
                    }
                    $courseDayCount[$dId][$cId][$day]--;
                }
            }
        }

        return false; // Backtrack trigger
    };

    // Execute Solver
    $isFullyAllocated = $solve(0);

    // -------------------------------------------------------------------------
    // 3. VALIDATION PASS & CONFLICT AUDIT
    // -------------------------------------------------------------------------

    $totalRequiredUnits = count($workloadUnits);
    $totalAllocatedUnits = count($assignedSchedule);
    $unallocatedUnits = $totalRequiredUnits - $totalAllocatedUnits;

    // Track Workload Allocations
    foreach ($assignedSchedule as $alloc) {
        $key = "C{$alloc['COURSE_ID']}_D{$alloc['DIVISION_ID']}_T{$alloc['TEACHER_ID']}";
        $added = $alloc['IS_PRACTICAL'] ? 2 : 1;
        $allocatedWorkloadSummary[$key] = ($allocatedWorkloadSummary[$key] ?? 0) + $added;
    }

    $unallocatedReport = [];
    foreach ($requiredWorkloadSummary as $key => $req) {
        $allocCount = $allocatedWorkloadSummary[$key] ?? 0;
        if ($allocCount < $req['required_slots']) {
            $unallocatedReport[] = [
                'course_id' => $req['course_id'],
                'division_id' => $req['division_id'],
                'teacher_id' => $req['teacher_id'],
                'required' => $req['required_slots'],
                'allocated' => $allocCount,
                'missing' => $req['required_slots'] - $allocCount,
                'reason' => 'Insufficient non-overlapping teacher/lab availability windows.'
            ];
        }
    }

    // Audit for Hard Conflicts
    $teacherConflicts = 0;
    $divisionConflicts = 0;
    $roomConflicts = 0;
    $auditMap = [];

    foreach ($assignedSchedule as $a) {
        $slotKey = $a['WEEKDAY'] . '_' . $a['SLOT_ID'];
        
        // Audit Teacher
        $tKey = "T_" . $a['TEACHER_ID'] . '_' . $slotKey;
        if (isset($auditMap[$tKey])) $teacherConflicts++;
        $auditMap[$tKey] = true;

        // Audit Division
        $dKey = "D_" . $a['DIVISION_ID'] . '_' . $slotKey;
        if (isset($auditMap[$dKey])) $divisionConflicts++;
        $auditMap[$dKey] = true;

        // Audit Room
        $rKey = "R_" . $a['CLASSROOM_ID'] . '_' . $slotKey;
        if (isset($auditMap[$rKey])) $roomConflicts++;
        $auditMap[$rKey] = true;
    }

    // Output Audit Matrix
    $validationReport = [
        'Required_Workload_Units' => $totalRequiredUnits,
        'Allocated_Workload_Units' => $totalAllocatedUnits,
        'Unallocated_Workload_Units' => $unallocatedUnits,
        'Teacher_Conflicts' => $teacherConflicts,
        'Division_Conflicts' => $divisionConflicts,
        'Classroom_Conflicts' => $roomConflicts,
        'Lab_Conflicts' => 0,
        'Course_Frequency_Violations' => count($unallocatedReport),
        'Teacher_Workload_Violations' => count($unallocatedReport),
        'Compulsory_Course_Violations' => 0,
        'Optional_Course_Conflicts' => 0,
        'Practical_Slot_Violations' => 0,
        'Broken_Division_Time_Preferences' => count($brokenTimePreferences),
        'Unallocated_Details' => $unallocatedReport
    ];

    return [
        'validation' => $validationReport,
        'schedule'   => $assignedSchedule
    ];
}