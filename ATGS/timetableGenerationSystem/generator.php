
<?php

use LDAP\Result;

include_once 'dbConnect.php';
header('Content-Type: application/json');

$allocatedResources = [
    "schedule" => [], // Final Output: Array of rows to insert
    "occupiedClassrooms" => [], // [weekday][slot_id][classroom_id] = true
    "occupiedTeachers"   => [], // [weekday][slot_id][teacher_id] = true
    "occupiedDivisions"  => [], // [weekday][slot_id][division_id] = true
    "divisionDaySlots"   => [], // [division_id][weekday][] = slot_id (Tracks gaps)
    "divisionClassroom"  => [], // [division_id] = classroom_id (Static classroom lock)
    "divisionPracticalSlot" => [] // [division_id] = slot_id (Fixed practical slot lock)"
];

function get_resourses(){
    global $conn;
    $classrooms = [];
    $res = $conn->query("SELECT * FROM CLASSROOM ORDER BY CAPACITY ASC");
    while ($row = $res->fetch_assoc()) {
        $classrooms[$row['CLASSROOM_ID']] = $row;
    }

    $timeslots = [];
    $res = $conn->query("SELECT * FROM TIMESLOT ORDER BY START_TIME ASC");
    while ($row = $res->fetch_assoc()) {
        $timeslots[$row['SLOT_ID']] = $row;
    }

    $weekdays = [
        'MONDAY',
        'TUESDAY',
        'WEDNESDAY',
        'THURSDAY',
        'FRIDAY',
        'SATURDAY'
    ];

    $divisions = [];
    $res = $conn->query("SELECT * FROM DIVISION");
    while ($row = $res->fetch_assoc()) {
        $divisions[$row['DIVISION_ID']] = $row;
    }

    $teachers = [];
    $res = $conn->query("SELECT * FROM TEACHER");
    while ($row = $res->fetch_assoc()) {
        $teachers[$row['TEACHER_ID']] = $row;
    }

    $availability = [];
    $res = $conn->query("
        SELECT *
        FROM AVAILABILITY
        WHERE STATUS = 'AVAILABLE'
    ");
    while ($row = $res->fetch_assoc()) {
        $availability
            [$row['TEACHER_ID']]
            [$row['WEEKDAY']]
            [$row['SLOT_ID']] = true;
    }

    $courses = [];
    $res = $conn->query("SELECT * FROM COURSE");
    while ($row = $res->fetch_assoc()) {
        $courses[$row['COURSE_ID']] = $row;
    }

    $workloads = [];
    $res = $conn->query("
        SELECT 
            t.*,
            c.ISPRACTICAL,
            c.TYPE,
            c.ISOPTIONAL,
            c.OPTIONAL_ID,
            te.ISPARTTIME
        FROM TEACHES t
        JOIN COURSE c 
            ON t.COURSE_ID = c.COURSE_ID
        JOIN TEACHER te 
            ON t.TEACHER_ID = te.TEACHER_ID
    ");
    while ($row = $res->fetch_assoc()) {
        $workloads[] = $row;
    }

    $resources = [
        "classroom"    => $classrooms,
        "timeslot"     => $timeslots,
        "weekday"      => $weekdays,
        "teacher"      => $teachers,
        "division"     => $divisions,
        "availability" => $availability,
        "course"       => $courses,
        "workload"     => $workloads
    ];
    // var_dump($resources);
    return $resources;
}

function get_availability($currentResources, $teacher_id, $slot_id, $weekday) {
    return isset(
        $currentResources["availability"][$teacher_id][$weekday][$slot_id]
    );
}

function is_parttime($resources, $teacher_id) {
    return $resources['teacher'][$teacher_id]['ISPARTTIME'];
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

function get_allLabs($resources) {
    $labs = [
        'IT' => [],
        'PHYSICS' => [],
        'CHEMISTRY' => [],
        'BIOLOGY' => []
    ];

    foreach ($resources['classroom'] as $classroom_id => $classroom) {
        switch ($classroom['CATEGORY']) {
            case 'IT LAB':
                $labs['IT'][$classroom_id] = $classroom;
                break;

            case 'PHYSICS LAB':
                $labs['PHYSICS'][$classroom_id] = $classroom;
                break;

            case 'CHEMISTRY LAB':
                $labs['CHEMISTRY'][$classroom_id] = $classroom;
                break;

            case 'BIOLOGY LAB':
                $labs['BIOLOGY'][$classroom_id] = $classroom;
                break;
        }
    }

    return $labs;
}

function get_allLectureRooms($resources) {
    $lectureRooms = [];

    foreach ($resources['classroom'] as $classroom_id => $classroom) {
        if ($classroom['CATEGORY'] === 'LECTURE HALL') {
            $lectureRooms[$classroom_id] = $classroom;
        }
    }

    return $lectureRooms;
}
 
// Generation Steps
// 1. Fix Practical slots(if a division has a practical course)
// -- Check workload to know wheather the practical course is assigned to some get_allParttimeTeachers
// -- if partime teacher is there then get the slots of their availablity.
// -- if not parttime teacher is there then allocate the first possible slot for practical
// -- get the labs and its availability and intersect the availability with the parttime teacher availability.
// -- then intersect the resulted output with the startime(the 3hr after this can be considered) or endtime(3hr before this can be considered making sure that the slot is not getting beyond the first timeslot) of the division preference.
// -- this will result in the fixed Practical timeslots for a division accross the week
// Fixes one practical slot for every division that has a practical workload.
function fixPracticalSlots($resources)
{
    global $allocatedResources;

    $labs = get_allLabs($resources);

    // Step 1: Get only practical timeslots.
    $practicalSlots = [];

    foreach ($resources['timeslot'] as $slot_id => $slot) {
        if (($slot['SLOT_TYPE'] ?? '') === 'PRACTICAL') {
            $practicalSlots[$slot_id] = $slot;
        }
    }

    // Step 2: Check every division's practical workload.
    foreach ($resources['workload'] as $workload) {

        // FIX: Skip non-practical courses.
        if (!$workload['ISPRACTICAL']) {
            continue;
        }

        $division_id = $workload['DIVISION_ID'];
        $teacher_id  = $workload['TEACHER_ID'];
        $course_type = $workload['TYPE'];

        // Step 3: Check whether teacher is part-time.
        $isPartTime = is_parttime($resources, $teacher_id);

        // Step 4: Select required lab category.
        $labType = null;

        switch ($course_type) {

            case 'IT PRACTICAL':
                $labType = 'IT';
                break;

            case 'PHYSICS PRACTICAL':
                $labType = 'PHYSICS';
                break;

            case 'CHEMISTRY PRACTICAL':
                $labType = 'CHEMISTRY';
                break;

            case 'BIOLOGY PRACTICAL':
                $labType = 'BIOLOGY';
                break;
        }

        if ($labType === null || empty($labs[$labType])) {
            continue;
        }

        // Division information.
        $division = $resources['division'][$division_id];

        $studentCount = (int)$division['STUDENT_COUNT'];

        /*
         * -----------------------------------------------------
         * Step 5: Find possible slots.
         * -----------------------------------------------------
         */
        $possibleSlots = [];

        foreach ($resources['weekday'] as $weekday) {

            foreach ($practicalSlots as $slot_id => $slot) {

                /*
                 * If teacher is part-time:
                 * teacher MUST be available.
                 *
                 * If teacher is full-time:
                 * don't restrict using AVAILABILITY.
                 */
                if (
                    $isPartTime &&
                    !get_availability(
                        $resources,
                        $teacher_id,
                        $slot_id,
                        $weekday
                    )
                ) {
                    continue;
                }

                /*
                 * Find suitable laboratory.
                 */
                foreach ($labs[$labType] as $classroom_id => $lab) {

                    if (
                        $lab['CAPACITY'] !== null &&
                        $lab['CAPACITY'] < $studentCount
                    ) {
                        continue;
                    }

                    /*
                     * Check classroom is not already occupied.
                     */
                    if (
                        isset(
                            $allocatedResources['occupiedClassrooms']
                            [$weekday]
                            [$slot_id]
                            [$classroom_id]
                        )
                    ) {
                        continue;
                    }

                    /*
                     * Check teacher is not already occupied.
                     */
                    if (
                        isset(
                            $allocatedResources['occupiedTeachers']
                            [$weekday]
                            [$slot_id]
                            [$teacher_id]
                        )
                    ) {
                        continue;
                    }

                    /*
                     * Check division is not already occupied.
                     */
                    if (
                        isset(
                            $allocatedResources['occupiedDivisions']
                            [$weekday]
                            [$slot_id]
                            [$division_id]
                        )
                    ) {
                        continue;
                    }

                    $possibleSlots[$weekday][$slot_id] = $classroom_id;

                    break;
                }
            }
        }

        if (empty($possibleSlots)) {
            continue;
        }


        /*
         * -----------------------------------------------------
         * Step 6: Apply division time preference.
         * -----------------------------------------------------
         */

        $divisionStartTime = null;
        $divisionEndTime   = null;

        if ($division['START_TIME_ID'] !== null) {

            if (isset(
                $resources['timeslot']
                [$division['START_TIME_ID']]
            )) {
                $divisionStartTime =
                    $resources['timeslot']
                    [$division['START_TIME_ID']]
                    ['START_TIME'];
            }
        }

        if ($division['END_TIME_ID'] !== null) {

            if (isset(
                $resources['timeslot']
                [$division['END_TIME_ID']]
            )) {
                $divisionEndTime =
                    $resources['timeslot']
                    [$division['END_TIME_ID']]
                    ['END_TIME'];
            }
        }


        /*
         * -----------------------------------------------------
         * Step 7: Order practical slots by start time.
         * -----------------------------------------------------
         */

        uasort(
            $practicalSlots,
            function ($a, $b) {
                return strcmp(
                    $a['START_TIME'],
                    $b['START_TIME']
                );
            }
        );


        /*
         * -----------------------------------------------------
         * Step 8: Find first valid practical slot.
         * -----------------------------------------------------
         */

        foreach ($practicalSlots as $slot_id => $slot) {

            /*
             * Start-time preference:
             *
             * Practical cannot START before division start.
             */
            if (
                $divisionStartTime !== null &&
                $slot['START_TIME'] < $divisionStartTime
            ) {
                continue;
            }

            /*
             * End-time preference:
             *
             * Practical cannot END after division end.
             */
            if (
                $divisionEndTime !== null &&
                $slot['END_TIME'] > $divisionEndTime
            ) {
                continue;
            }


            foreach ($resources['weekday'] as $weekday) {

                if (
                    !isset(
                        $possibleSlots
                        [$weekday]
                        [$slot_id]
                    )
                ) {
                    continue;
                }

                $classroom_id =
                    $possibleSlots
                    [$weekday]
                    [$slot_id];


                /*
                 * -------------------------------------------------
                 * Step 9: Fix the practical slot.
                 * -------------------------------------------------
                 */

                $allocatedResources[
                    'divisionPracticalSlot'
                ][$division_id] = [

                    'slot_id'       => $slot_id,
                    'weekday'       => $weekday,
                    'classroom_id'  => $classroom_id,
                    'teacher_id'    => $teacher_id
                ];


                /*
                 * -------------------------------------------------
                 * Step 10: Mark resources occupied.
                 * -------------------------------------------------
                 */

                $allocatedResources[
                    'occupiedClassrooms'
                ][$weekday][$slot_id][$classroom_id] = true;


                $allocatedResources[
                    'occupiedTeachers'
                ][$weekday][$slot_id][$teacher_id] = true;


                $allocatedResources[
                    'occupiedDivisions'
                ][$weekday][$slot_id][$division_id] = true;


                break 2;
            }
        }
    }
    // var_dump($allocatedResources);
    return $allocatedResources['divisionPracticalSlot'];
}



// Generation Steps
// 2 Allocate practical course to slots
// -- get the workload and teacher availability and the fixedPractical slot for the division
// -- now teacher wise (priority/first allocation of/to parttime teacher) allocate the first possible practical course to practical slot keeping in mind the number of practicals to be conducted as per the Minimum lecture crieteria to a division
// -- if a practical course is optional to a specific divison(s) then while allocating the slot, try to allocate the other optional practical in a different lab at the same timeslot if this is not possible then give the other divisions an empty slot.
function allocatePracticalCourses($resources, $semester) {
    global $allocatedResources;

    // Step 1: Get all practical workloads and arrange part-time teachers first.
    $practicalWorkloads = [];

    foreach ($resources['workload'] as $workload) {
        if (
            $workload['ISPRACTICAL'] &&
            isset($resources['course'][$workload['COURSE_ID']]['SEMESTER']) &&
            $resources['course'][$workload['COURSE_ID']]['SEMESTER'] === $semester
        ){
            $practicalWorkloads[] = $workload;
        }
    }

    usort($practicalWorkloads, function ($a, $b) {
        return (int)$b['ISPARTTIME'] <=> (int)$a['ISPARTTIME'];
    });

    // Step 2: Allocate each practical workload to its division's fixed practical slot.
    foreach ($practicalWorkloads as $workload) {

        $division_id = $workload['DIVISION_ID'];
        $teacher_id  = $workload['TEACHER_ID'];
        $course_id   = $workload['COURSE_ID'];

        if (!isset($allocatedResources['divisionPracticalSlot'][$division_id])) {
            continue;
        }

        $fixedSlot = $allocatedResources['divisionPracticalSlot'][$division_id];

        $slot_id = $fixedSlot['slot_id'];
        $weekday = $fixedSlot['weekday'];

        // Step 3: Check that the teacher is available at the fixed practical slot.
        if (!get_availability(
            $resources,
            $teacher_id,
            $slot_id,
            $weekday
        )) {
            continue;
        }

        // Step 4: Get the minimum number of practical sessions required for the course.
        $requiredPracticals = (int)$resources['course'][$course_id]['WEEKLY_LECTURES'];

        if ($requiredPracticals <= 0) {
            $requiredPracticals = 1;
        }

        if (!isset($allocatedResources['schedule'][$division_id])) {
            $allocatedResources['schedule'][$division_id] = [];
        }

        // Step 5: Count practical sessions already allocated to this course and division.
        $allocatedCount = 0;

        foreach ($allocatedResources['schedule'][$division_id] as $allocation) {
            if (
                $allocation['course_id'] == $course_id &&
                $allocation['is_practical']
            ) {
                $allocatedCount++;
            }
        }

        // Step 6: Allocate the practical course until its required count is reached.
        while ($allocatedCount < $requiredPracticals) {

            $slotAlreadyUsed = false;

            foreach ($allocatedResources['schedule'][$division_id] as $allocation) {
                if (
                    $allocation['slot_id'] == $slot_id &&
                    $allocation['weekday'] == $weekday
                ) {
                    $slotAlreadyUsed = true;
                    break;
                }
            }

            if ($slotAlreadyUsed) {
                break;
            }

            $allocatedResources['schedule'][$division_id][] = [
                'course_id'    => $course_id,
                'division_id'  => $division_id,
                'teacher_id'   => $teacher_id,
                'classroom_id' => $fixedSlot['classroom_id'],
                'slot_id'      => $slot_id,
                'weekday'      => $weekday,
                'is_practical' => true
            ];

            $allocatedCount++;
        }

        //  If this is an optional practical course, give every division that has NOT opted for this course an empty slot at the same practical time.

        if ($resources['course'][$course_id]['ISOPTIONAL']) {

            $res = $GLOBALS['conn']->query("
                SELECT DIVISION_ID
                FROM DIVISION
                WHERE DIVISION_ID NOT IN (
                    SELECT DIVISION_ID
                    FROM OPTED_BY
                    WHERE COURSE_ID = {$course_id}
                )
            ");

            while ($row = $res->fetch_assoc()) {

                $otherDivisionId = $row['DIVISION_ID'];

                if (!isset($allocatedResources['schedule'][$otherDivisionId])) {
                    $allocatedResources['schedule'][$otherDivisionId] = [];
                }

                // Don't overwrite an existing allocation.
                $slotAlreadyUsed = false;
                foreach ($allocatedResources['schedule'][$otherDivisionId] as $allocation) {
                    if (
                        $allocation['slot_id'] == $slot_id &&
                        $allocation['weekday'] == $weekday
                    ) {
                        $slotAlreadyUsed = true;
                        break;
                    }
                }

                if (!$slotAlreadyUsed) {
                    $allocatedResources['schedule'][$otherDivisionId][] = [
                        'course_id'    => null,
                        'division_id'  => $otherDivisionId,
                        'teacher_id'   => null,
                        'classroom_id' => null,
                        'slot_id'      => $slot_id,
                        'weekday'      => $weekday,
                        'is_practical' => false,
                        'is_empty'     => true
                    ];
                }
            }
        }
    }
    return $allocatedResources['schedule'];
}



// Generation Step 3:
// 1. Calculate total weekly hours for each division.
//    - Theory = WEEKLY_LECTURES * 1
//    - Practical = WEEKLY_LECTURES * 2
// 2. Divide weekly hours across 6 working days.
//    - If the result is fractional, Saturday gets the lower hour count.
//    - The remaining hours are distributed across Monday-Friday.
// 3. For every division/day, allocate classrooms according to:
//    - Division's preferred classroom first.
//    - Otherwise, smallest suitable classroom.
//    - Classroom must have enough capacity and must not already be occupied.
// 4. Existing practical classroom allocations are preserved.
function allocateClassrooms($resources, $semester) {
    global $allocatedResources;

    $classrooms = $resources['classroom'];
    $timeslots  = $resources['timeslot'];
    $divisions  = $resources['division'];
    $courses    = $resources['course'];

    // Sort classrooms by capacity so the smallest suitable room is selected.
    uasort($classrooms, function ($a, $b) {
        return (int)$a['CAPACITY'] <=> (int)$b['CAPACITY'];
    });

    // Mark classrooms already occupied by the practical allocations.
    foreach ($allocatedResources['schedule'] as $divisionSchedule) {
        foreach ($divisionSchedule as $allocation) {
            if (
                !empty($allocation['classroom_id']) &&
                isset($allocation['slot_id']) &&
                isset($allocation['weekday'])
            ) {
                $allocatedResources['occupiedClassrooms']
                    [$allocation['weekday']]
                    [$allocation['slot_id']]
                    [$allocation['classroom_id']] = true;
            }
        }
    }

    // Calculate the required weekly hours for every division.
    $divisionHours = [];

    foreach ($divisions as $divisionId => $division) {
        $divisionHours[$divisionId] = 0;

        foreach ($courses as $course) {
            if (
                $course['SEMESTER'] !== $semester ||
                (int)$course['YEAR_NUMBER'] !== (int)$division['YEAR_NUMBER'] ||
                (int)$course['PROGRAMME_ID'] !== (int)$division['PROGRAMME_ID']
            ) {
                continue;
            }

            $lectures = (int)$course['WEEKLY_LECTURES'];

            if ($course['ISPRACTICAL']) {
                $divisionHours[$divisionId] += $lectures * 2;
            } else {
                $divisionHours[$divisionId] += $lectures;
            }
        }
    }

    // Calculate how many hours each division needs on every weekday.
    $dailyHours = [];

    foreach ($divisionHours as $divisionId => $weeklyHours) {
        $baseHours = intdiv($weeklyHours, 6);
        $remainder = $weeklyHours % 6;

        $dailyHours[$divisionId] = [];

        foreach ($resources['weekday'] as $index => $weekday) {
            if ($weekday === 'SATURDAY') {
                // Saturday gets the lower number of hours when the weekly
                // total cannot be divided equally across six days.
                $dailyHours[$divisionId][$weekday] = $baseHours;
            } else {
                // The remaining hours are distributed over Monday-Friday.
                $dailyHours[$divisionId][$weekday] =
                    $baseHours + ($index < $remainder ? 1 : 0);
            }
        }
    }

    // Convert a timeslot into its duration in hours.
    function slotHours($slot) {
        $start = strtotime($slot['START_TIME']);
        $end   = strtotime($slot['END_TIME']);

        return ($end - $start) / 3600;
    }

    // Allocate classrooms for each division and weekday.
    foreach ($dailyHours as $divisionId => $weekHours) {

        $division = $divisions[$divisionId];
        $requiredClassroom = $division['CLASSROOM_ID'];
        $studentCount = (int)$division['STUDENT_COUNT'];

        foreach ($weekHours as $weekday => $requiredHours) {

            if ($requiredHours <= 0) {
                continue;
            }

            $allocatedHours = 0;

            // Use the division's preferred start time when provided.
            $orderedSlots = $timeslots;

            if ($division['START_TIME_ID'] !== null) {
                $startIndex = array_search(
                    $division['START_TIME_ID'],
                    array_keys($timeslots)
                );

                if ($startIndex !== false) {
                    $orderedSlots = array_slice(
                        $timeslots,
                        $startIndex,
                        null,
                        true
                    );
                }
            }
            foreach ($orderedSlots as $slotId => $slot) {
                if ($allocatedHours >= $requiredHours) {
                    break;
                }
                // Break slots cannot be used for classroom allocation.
                if ($slot['SLOT_TYPE'] === 'BREAK') {
                    continue;
                }

                $hours = slotHours($slot);

                if ($allocatedHours + $hours > $requiredHours) {
                    continue;
                }

                // Try the user's preferred classroom first.
                $selectedClassroom = null;

                if (
                    $requiredClassroom !== null &&
                    isset($classrooms[$requiredClassroom]) &&
                    $classrooms[$requiredClassroom]['CAPACITY'] >= $studentCount &&
                    !isset(
                        $allocatedResources['occupiedClassrooms']
                        [$weekday][$slotId][$requiredClassroom]
                    )
                ) {
                    $selectedClassroom = $requiredClassroom;
                }

                // If preferred classroom is unavailable, choose the
                // smallest classroom that can accommodate the division.
                if ($selectedClassroom === null) {
                    foreach ($classrooms as $classroomId => $classroom) {

                        if (
                            $classroom['CAPACITY'] === null ||
                            $classroom['CAPACITY'] < $studentCount
                        ) {
                            continue;
                        }

                        if (
                            isset(
                                $allocatedResources['occupiedClassrooms']
                                [$weekday][$slotId][$classroomId]
                            )
                        ) {
                            continue;
                        }

                        $selectedClassroom = $classroomId;
                        break;
                    }
                }

                if ($selectedClassroom === null) {
                    continue;
                }

                // Add classroom allocation to the division schedule.
                $allocatedResources['schedule'][$divisionId][] = [
                    'course_id'    => null,
                    'division_id'  => $divisionId,
                    'teacher_id'   => null,
                    'classroom_id' => $selectedClassroom,
                    'slot_id'      => $slotId,
                    'weekday'      => $weekday,
                    'is_practical' => false
                ];

                // Mark the classroom as occupied.
                $allocatedResources['occupiedClassrooms']
                    [$weekday]
                    [$slotId]
                    [$selectedClassroom] = true;

                $allocatedHours += $hours;
            }
        }
    }

    return $allocatedResources['schedule'];
}



/*
 * Generation Step 4: Allocate theory courses.
 *
 * Working:
 * 1. Get theory workloads for the selected semester.
 * 2. Process them in this priority:
 *      - Part-time + Optional
 *      - Part-time + Compulsory
 *      - Full-time + Optional
 *      - Full-time + Compulsory
 * 3. Use only empty classroom allocations already present in schedule.
 * 4. Check:
 *      - division is free
 *      - teacher is available
 *      - classroom is free
 *      - slot belongs to the division timetable
 * 5. Optional courses with the same OPTIONAL_ID are allocated at the
 *    same weekday and timeslot.
 * 6. Store the final allocation in $allocatedResources['schedule'].
 */
function allocateCourses($resources, $semester) {
    global $allocatedResources;

    $workloads = [];

    // Get only theory workloads for the selected semester.
    foreach ($resources['workload'] as $workload) {

        $course = $resources['course'][$workload['COURSE_ID']];

        if (
            $course['SEMESTER'] == $semester &&
            !$course['ISPRACTICAL']
        ) {
            $workloads[] = $workload;
        }
    }

    // Process workloads according to the four required priorities.
    usort($workloads, function ($a, $b) {

        $aPriority =
            $a['ISPARTTIME']
                ? ($a['ISOPTIONAL'] ? 1 : 2)
                : ($a['ISOPTIONAL'] ? 3 : 4);

        $bPriority =
            $b['ISPARTTIME']
                ? ($b['ISOPTIONAL'] ? 1 : 2)
                : ($b['ISOPTIONAL'] ? 3 : 4);

        return $aPriority <=> $bPriority;
    });

    /*
     * Allocate every theory workload.
     *
     * Only the empty classroom rows created by allocateClassrooms()
     * are used here.
     */
    foreach ($workloads as $workload) {

        $courseId   = $workload['COURSE_ID'];
        $divisionId = $workload['DIVISION_ID'];
        $teacherId  = $workload['TEACHER_ID'];

        $required = (int)$resources['course'][$courseId]['WEEKLY_LECTURES'];

        if (!isset($allocatedResources['schedule'][$divisionId])) {
            continue;
        }
        // Find empty slots belonging to this division's timetable.
        foreach (
            $allocatedResources['schedule'][$divisionId]
            as $index => $allocation
        ) {
            // Stop when all required lectures are allocated.
            $allocated = 0;
            foreach (
                $allocatedResources['schedule'][$divisionId]
                as $row
            ) {
                if ($row['course_id'] == $courseId) {
                    $allocated++;
                }
            }
            if ($allocated >= $required) {
                break;
            }
            // Use only empty classroom allocations.
            if (
                $allocation['course_id'] !== null ||
                $allocation['classroom_id'] === null
            ) {
                continue;
            }

            $weekday = $allocation['weekday'];
            $slotId  = $allocation['slot_id'];
            $roomId  = $allocation['classroom_id'];

            // Teacher must be available.
            if (
                !isset(
                    $resources['availability']
                    [$teacherId]
                    [$weekday]
                    [$slotId]
                )
            ) {
                continue;
            }
            // Teacher must not already be occupied.
            if (
                isset(
                    $allocatedResources['occupiedTeachers']
                    [$weekday]
                    [$slotId]
                    [$teacherId]
                )
            ) {
                continue;
            }
            // Classroom must not already be occupied.
            if (
                isset(
                    $allocatedResources['occupiedClassrooms']
                    [$weekday]
                    [$slotId]
                    [$roomId]
                )
            ) {
                continue;
            }
            // Allocate the course to this classroom and timeslot.
            $allocatedResources['schedule']
                [$divisionId]
                [$index]['course_id'] = $courseId;

            $allocatedResources['schedule']
                [$divisionId]
                [$index]['teacher_id'] = $teacherId;

            $allocatedResources['schedule']
                [$divisionId]
                [$index]['is_practical'] = false;

            // Mark teacher as occupied.
            $allocatedResources['occupiedTeachers']
                [$weekday]
                [$slotId]
                [$teacherId] = true;

            // Mark classroom as occupied.
            $allocatedResources['occupiedClassrooms']
                [$weekday]
                [$slotId]
                [$roomId] = true;
        }
    }

    return $allocatedResources['schedule'];
}

function generateTimetable(){
    global $allocatedResources;

    $resources = get_resourses();

    fixPracticalSlots($resources);
    allocatePracticalCourses($resources, "EVEN");
    // allocateClassrooms($resources, "EVEN");
    // allocateCourses($resources, "EVEN");

    return $allocatedResources;
}

echo json_encode(generateTimetable());

?>