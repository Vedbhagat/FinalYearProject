<?php

use LDAP\Result;

include_once 'dbConnect.php';
header('Content-Type: application/json');

$formCategory = $_REQUEST['formCategory'] ?? '';
$formtype = $_REQUEST['formType'] ?? '';
$user_id = $_SESSION['user_id'] ?? null;

// Response Jsonify
function sendJsonResponse($httpStatusCode, $httpStatusDescription, $jsonResponseBody = '', $actionToPerform = '') {
    http_response_code($httpStatusCode);
    echo json_encode([
        'statusCode' => $httpStatusCode,
        'statusDescription' => $httpStatusDescription,
        'responseBody' => $jsonResponseBody,
        'perform' => $actionToPerform
    ]);
    exit; // Stops execution right after flushing the output
}

// Check login
function verifyLogin($uid) {
    if (!$uid) {
        sendJsonResponse(401,'Unauthorized access','The action requires login');
        header("Location: login.php");
        exit;
    }
}
function validateTimeslot($startTime, $endTime, $slotType, $slotId = null) {
    /**
     * RETURNS:
     * 1 if success
     * 2 if outofbound
     * 3 if time not matching with the slotType
     * 4 if inverted timeslot
     * 5 if overlapping timeslot
     */
    global $conn;
    $parsedStartTime = (new DateTime($startTime))->getTimestamp();
    $parsedEndTime = (new DateTime($endTime))->getTimestamp();

    $minTime = (new DateTime('07:00'))->getTimestamp();
    $maxTime = (new DateTime('21:00'))->getTimestamp();
    if($parsedStartTime<$minTime || $parsedEndTime>$maxTime){
        return 2;
    }
    switch($slotType){
        case 'lecture':
            if($parsedEndTime - $parsedStartTime != 60*60){// 1 hour lecture
                echo ($parsedEndTime - $parsedStartTime)." ";
                echo ($parsedStartTime - $parsedEndTime)." ";
                return 3;
            }
            break;
        case 'practical':
            if($parsedEndTime - $parsedStartTime != 60*120){// 2 hour practical
                return 3;
            }
            break;
        case 'break':
            if($parsedEndTime - $parsedStartTime != 60*15){// 15 minute break
                return 3;
            }
            break;
        default:
            break;
    }
    if ($parsedStartTime > $parsedEndTime) {
        return 4; // Inverted timeslot
    }else {
        $query = "SELECT slot_Id, start_time, end_time, slot_type FROM TIMESLOT WHERE ? < end_Time AND ? > start_Time AND SLOT_ID != ? AND SLOT_TYPE = ? LIMIT 1";
        $result = $conn->execute_query($query, [$startTime, $endTime, $slotId ?? 0, $slotType]);
        if ($result->num_rows > 0) {
            return 5; // Overlapping Timeslot
        }
    }
    return 1;
}
function isValidName(string $str){
    $pattern = preg_match("/^[A-Za-z\s.]+$/",trim($str)) === 1;
    return ($pattern);
}
function validateClassroom($floor,$room,$capacity,$startTime,$endTime){
    global $conn;
    if($capacity<20 && $capacity>200) return 2; //too small or too large classroom

    $query = 'SELECT CLASSROOM_ID FROM CLASSROOM WHERE FLOOR_NUMBER = ?';
    $result = $conn->execute_query($query,[$floor]);
    if($result->num_rows>20) return 3; //Too many rooms at a floor
    if ( ((new DateTime($startTime))->getTimestamp()) > ((new DateTime($endTime))->getTimestamp()) ) return 4; //Inverted Availability


    // $query = 'SELECT * FROM CLASSROOM WHERE ROOM_NUMBER = ?';
    // $result = $conn->execute_query($query,[$room]);
    // if($result->num_rows>0) return 4; //room number already exists

    return 1;
}
function validateDepartment($fullname,$shortname){
    /**
     * Returns
     * 1 for Success
     * 2 for contains invalid characters
     */
    global $conn;
    if(isValidName($fullname) && isValidName($shortname)) return 1;
    else return 2; //Numbers not allowed
}
function validateProgramme($fullname, $shortname){
    /**
     * Returns
     * 1 for Success
     * 2 for contains invalid characters
     */
    global $conn;
    if(isValidName($fullname) && isValidName($shortname)) return 1;
    else return 2; //Numbers not allowed
}
function validateCourse($fullname, $shortname){
    /**
     * Returns
     * 1 for Success
     * 2 for contains invalid characters
     */
    global $conn;
    if(isValidName($fullname) && isValidName($shortname)) return 1;
    else return 2; //Numbers not allowed
}


try {
    if($formCategory == 'getMinimalData'){
        global $conn;
        $response = [];
        if($formtype == 'department'){
            $sql = "SELECT DEPARTMENT_ID, LONG_NAME, SHORT_NAME FROM DEPARTMENT ORDER BY LONG_NAME";
            $result = mysqli_query($conn, $sql);
            $response['departments'] = [];
            while($row = mysqli_fetch_assoc($result)) {$response['departments'][] = $row;}
        }
        elseif ($formtype == 'programme') {
            $sql = "
                SELECT p.DEPARTMENT_ID, p.PROGRAMME_ID, p.LONG_NAME, p.SHORT_NAME, COUNT(c.PROGRAMME_ID) AS DURATION
                FROM PROGRAMME p
                LEFT JOIN CONSISTS c ON p.PROGRAMME_ID = c.PROGRAMME_ID
                GROUP BY p.DEPARTMENT_ID, p.PROGRAMME_ID, p.LONG_NAME, p.SHORT_NAME
                ORDER BY p.LONG_NAME
            ";
            $result = mysqli_query($conn, $sql);
            $response['programmes'] = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $response['programmes'][] = $row;
            }
        }
        elseif($formtype == 'classroom'){
            $sql = "SELECT CLASSROOM_ID, ROOM_NUMBER, CAPACITY FROM CLASSROOM ORDER BY ROOM_NUMBER";
            $result = mysqli_query($conn, $sql);
            $response['classrooms'] = [];
            while($row = mysqli_fetch_assoc($result)) {$response['classrooms'][] = $row;}
        }
        elseif($formtype == 'timeslot'){
            $sql = "SELECT SLOT_ID, START_TIME, END_TIME, SLOT_TYPE FROM TIMESLOT WHERE SLOT_TYPE = 'lecture' ORDER BY SLOT_TYPE, START_TIME";
            $result = mysqli_query($conn, $sql);
            $response['timeslots'] = [];
            while($row = mysqli_fetch_assoc($result)) {$response['timeslots'][] = $row;}
        }
        elseif($formtype == 'year'){
            $sql = "SELECT YEAR_NUMBER, YEAR_NAME FROM YEAR";
            $result = mysqli_query($conn, $sql);
            $response['years'] = [];
            while($row = mysqli_fetch_assoc($result)) {$response['years'][] = $row;}
        }
        elseif($formtype == 'consists'){
            $sql = "SELECT YEAR_NUMBER, PROGRAMME_ID FROM CONSISTS";
            $result = mysqli_query($conn, $sql);
            $response['consists'] = [];
            while($row = mysqli_fetch_assoc($result)) {$response['consists'][] = $row;}
        }
        elseif($formtype == 'course'){
            $sql = 
            "
                SELECT
                    c.COURSE_ID,
                    c.PROGRAMME_ID,
                    p.DEPARTMENT_ID,
                    p.SHORT_NAME AS PROGRAMME_NAME,
                    d.SHORT_NAME AS DEPARTMENT_NAME,
                    c.LONG_NAME,
                    c.SHORT_NAME,
                    c.WEEKLY_LECTURES,
                    c.ISPRACTICAL,
                    c.ISOPTIONAL,
                    c.YEAR_NUMBER,
                    c.SEMESTER,
                    c.OPTIONAL_ID
                
                FROM COURSE c
                LEFT JOIN PROGRAMME p
                    ON c.PROGRAMME_ID = p.PROGRAMME_ID
                LEFT JOIN DEPARTMENT d
                    ON p.DEPARTMENT_ID = d.DEPARTMENT_ID
                ORDER BY p.DEPARTMENT_ID, PROGRAMME_NAME, c.YEAR_NUMBER, c.SEMESTER
            ";
            $result = mysqli_query($conn, $sql);
            $response['courses'] = [];
            while($row = mysqli_fetch_assoc($result)) {$response['courses'][] = $row;}
        }
        elseif($formtype == 'optionalcourse'){
            $sql = "SELECT
                d.DEPARTMENT_ID,
                d.SHORT_NAME AS DEPARTMENT_SHORT_NAME,
                p.PROGRAMME_ID,
                p.SHORT_NAME AS PROGRAMME_SHORT_NAME,
                y.YEAR_NUMBER,
                y.YEAR_NAME,
                c.COURSE_ID,
                c.SEMESTER,
                c.LONG_NAME AS COURSE_FULL_NAME,
                c.SHORT_NAME AS COURSE_SHORT_NAME,
                c.ISOPTIONAL,
                c.OPTIONAL_ID,
                op.SHORT_NAME AS OPTIONAL_COURSE_NAME,
                c.ISPRACTICAL,
                c.WEEKLY_LECTURES

            FROM COURSE c

            LEFT JOIN COURSE op
                ON op.COURSE_ID = c.OPTIONAL_ID

            JOIN PROGRAMME p
                ON c.PROGRAMME_ID = p.PROGRAMME_ID

            JOIN DEPARTMENT d
                ON p.DEPARTMENT_ID = d.DEPARTMENT_ID

            JOIN YEAR y
                ON c.YEAR_NUMBER = y.YEAR_NUMBER

            WHERE c.ISOPTIONAL = TRUE
            AND (
                c.OPTIONAL_ID IS NULL
                OR c.COURSE_ID < c.OPTIONAL_ID
            )

            ORDER BY
                d.DEPARTMENT_ID,
                p.PROGRAMME_ID,
                y.YEAR_NUMBER,
                c.SEMESTER;";
            $result = mysqli_query($conn, $sql);
            $response['optionalcourses'] = [];
            while($row = mysqli_fetch_assoc($result)) {$response['optionalcourses'][] = $row;}
        }
        elseif($formtype == 'division'){
            $sql = '
                SELECT 
                    d.DEPARTMENT_ID,
                    d.SHORT_NAME AS DEPARTMENT_NAME,

                    p.PROGRAMME_ID,
                    p.SHORT_NAME AS PROGRAMME_NAME,

                    y.YEAR_NUMBER,
                    y.YEAR_NAME,

                    dv.DIVISION_ID,
                    dv.NAME AS DIVISION_NAME,
                    dv.STUDENT_COUNT,
                    dv.CLASSROOM_ID,
                    dv.START_TIME_ID,
                    dv.END_TIME_ID

                FROM DEPARTMENT AS d

                JOIN PROGRAMME AS p
                    ON p.DEPARTMENT_ID = d.DEPARTMENT_ID

                JOIN CONSISTS AS c
                    ON c.PROGRAMME_ID = p.PROGRAMME_ID

                JOIN YEAR AS y
                    ON y.YEAR_NUMBER = c.YEAR_NUMBER

                LEFT JOIN DIVISION AS dv
                    ON dv.PROGRAMME_ID = c.PROGRAMME_ID
                    AND dv.YEAR_NUMBER = c.YEAR_NUMBER

                ORDER BY
                    dv.STUDENT_COUNT,
                    d.SHORT_NAME,
                    p.SHORT_NAME,
                    y.YEAR_NUMBER,
                    dv.NAME;
            ';
            $result = mysqli_query($conn, $sql);
            $response['divisions'] = [];
            while($row = mysqli_fetch_assoc($result)) {$response['divisions'][] = $row;}
        }
        elseif($formtype == 'optionalcoursemapper') {

            $sql = "SELECT
                d.DEPARTMENT_ID,
                d.SHORT_NAME AS DEPARTMENT_SHORT_NAME,

                p.PROGRAMME_ID,
                p.SHORT_NAME AS PROGRAMME_SHORT_NAME,

                y.YEAR_NUMBER,
                y.YEAR_NAME,

                c.COURSE_ID,
                c.SEMESTER,
                c.LONG_NAME AS COURSE_FULL_NAME,
                c.SHORT_NAME AS COURSE_SHORT_NAME,
                c.ISOPTIONAL,
                c.OPTIONAL_ID,

                op.SHORT_NAME AS OPTIONAL_COURSE_NAME,

                c.ISPRACTICAL,
                c.WEEKLY_LECTURES,

                ob.DIVISION_ID AS MAPPED_DIVISION_ID,
                dv.NAME AS MAPPED_DIVISION_NAME

            FROM COURSE c

            LEFT JOIN COURSE op
                ON op.COURSE_ID = c.OPTIONAL_ID

            LEFT JOIN OPTED_BY ob
                ON ob.COURSE_ID = c.COURSE_ID

            LEFT JOIN DIVISION dv
                ON dv.DIVISION_ID = ob.DIVISION_ID

            JOIN PROGRAMME p
                ON c.PROGRAMME_ID = p.PROGRAMME_ID

            JOIN DEPARTMENT d
                ON p.DEPARTMENT_ID = d.DEPARTMENT_ID

            JOIN YEAR y
                ON c.YEAR_NUMBER = y.YEAR_NUMBER

            WHERE c.ISOPTIONAL = TRUE
              AND c.OPTIONAL_ID IS NOT NULL

            ORDER BY
                d.DEPARTMENT_ID,
                p.PROGRAMME_ID,
                y.YEAR_NUMBER,
                c.SEMESTER,
                c.COURSE_ID;";

            $result = mysqli_query($conn, $sql);

            $response['optionalcourses'] = [];

            while($row = mysqli_fetch_assoc($result)) {
                $response['optionalcourses'][] = $row;
            }
        }
        elseif($formtype == 'workload') {
            $sql = "SELECT
                t.WORKLOAD_ID,
                t.TEACHER_ID,
                t.COURSE_ID,
                t.DIVISION_ID,

                d.DEPARTMENT_ID,
                d.SHORT_NAME AS DEPARTMENT_NAME,

                p.PROGRAMME_ID,
                p.SHORT_NAME AS PROGRAMME_NAME,

                y.YEAR_NUMBER,
                y.YEAR_NAME,

                dv.NAME AS DIVISION_NAME,

                c.SEMESTER,
                c.LONG_NAME AS COURSE_NAME,
                c.SHORT_NAME AS COURSE_SHORT_NAME,

                t.LECTURE_COUNT,

                CONCAT(tr.FIRST_NAME, ' ', tr.LAST_NAME) AS TEACHER_NAME

            FROM TEACHES t

            INNER JOIN TEACHER tr
                ON t.TEACHER_ID = tr.TEACHER_ID

            INNER JOIN COURSE c
                ON t.COURSE_ID = c.COURSE_ID

            INNER JOIN DIVISION dv
                ON t.DIVISION_ID = dv.DIVISION_ID

            INNER JOIN PROGRAMME p
                ON c.PROGRAMME_ID = p.PROGRAMME_ID

            INNER JOIN DEPARTMENT d
                ON p.DEPARTMENT_ID = d.DEPARTMENT_ID

            INNER JOIN YEAR y
                ON c.YEAR_NUMBER = y.YEAR_NUMBER

            ORDER BY
                d.LONG_NAME,
                p.LONG_NAME,
                y.YEAR_NUMBER,
                dv.NAME,
                c.SEMESTER,
                c.LONG_NAME,
                TEACHER_NAME;
            ";

            $result = mysqli_query($conn, $sql);
            $response['teaches'] = [];
            while($row = mysqli_fetch_assoc($result)) {
                $response['teaches'][] = $row;
            }
        }
        
        header('Content-Type: application/json');
        sendJsonResponse(200, "The Minimal JSON generated", $response);
    }
    elseif($formCategory == 'load_existing'){
        $tablename = $_POST['tablename'];
        $query = "SELECT * FROM ". $tablename;
        $result = $conn->execute_query($query);
        $parsedResult = $result->fetch_all();
        sendJsonResponse(200,'Existing Data of '.$tablename.' table',$parsedResult);
    }
    elseif($formCategory == 'timeslot'){
        if($formtype == 'add_timeslot'){
            $startTime = $_POST['startTime'] ?? '';
            $endTime = $_POST['endTime'] ?? '';
            $slotType = $_POST['slotType'] ?? "lecture";

            $isValid = validateTimeslot($startTime, $endTime, $slotType);

            if ($isValid === 1) {
                $query = "INSERT INTO TIMESLOT(START_TIME, END_TIME, SLOT_TYPE) VALUES(?,?,?)";
                $result = $conn->execute_query($query, [$startTime, $endTime, $slotType]);
                if (mysqli_affected_rows($conn) == 1) {
                    sendJsonResponse(201, 'Created', 'Timeslot created successfully');
                } else {
                    sendJsonResponse(400, 'Try again', "Couldn't create timeslot");
                }
            } elseif ($isValid === 2) {
                sendJsonResponse(422, 'Timeslot OutOfBound', 'The timeslot must be between 07:00 AM to 08:00 PM.');
            } elseif ($isValid === 3) {
                sendJsonResponse(422, 'Mismatched SlotType', 'The timeslot does not match the slot type.');
            } elseif ($isValid === 4) {
                sendJsonResponse(422, 'Inverted Timeslot', 'The Start Time is greater than the End Time.');
            } elseif ($isValid === 5) {
                sendJsonResponse(409, 'Overlapping Timeslot', 'The timeslot overlaps with an existing record.');
            } else {
                sendJsonResponse(400, 'Invalid Input', 'Start Time or End Time is invalid.');
            }
        }
        elseif($formtype == 'get_one_timeslot'){ 
            $slotId = $_POST['slotId'];
            $query = "SELECT * FROM timeslot WHERE slot_id = ?;";
            $result = $conn->execute_query($query,[$slotId]);
            if($result->num_rows>0){
                sendJsonResponse(200,"Timeslot found.",$result->fetch_assoc());
                exit;
            }
            sendJsonResponse(400,"Timeslot NOT found.");
        }
        elseif($formtype == 'update_timeslot'){
            $slotId = $_POST['slotId'] ?? null;
            $startTime = $_POST['startTime'] ?? '';
            $endTime = $_POST['endTime'] ?? '';
            $slotType = $_POST['slotType'] ?? "No";
            $isValid = validateTimeslot($startTime, $endTime, $slotType, $slotId);
            if ($isValid === 1) {
                $query = "UPDATE TIMESLOT SET START_TIME = ?, END_TIME = ?, SLOT_TYPE = ? WHERE SLOT_ID = ?";
                $result = $conn->execute_query($query, [$startTime, $endTime, $slotType, $slotId]);
                if (mysqli_affected_rows($conn) > 0) {
                    sendJsonResponse(200, 'Updated', 'Timeslot updated successfully');
                } else {
                    sendJsonResponse(400, 'Try again', "Couldn't update timeslot");
                }
            } elseif ($isValid === 2) {
                sendJsonResponse(422, 'Inverted Timeslot', 'The Start Time is greater than the End Time.');
            } elseif ($isValid === 3) {
                sendJsonResponse(409, 'Overlapping Timeslot', 'The timeslot overlaps with an existing record.');
            } elseif ($isValid === 4) {
                sendJsonResponse(422, 'Too Small Slot', 'Timeslot must be at least 15 minutes.');
            } else {
                sendJsonResponse(400, 'Invalid Input', 'Entered values are not valid.');
            }
        }
        elseif($formtype == 'delete_timeslot'){
            $slotId = $_POST['slotId'];
            
            $query = "SELECT * FROM TIMESLOT WHERE SLOT_ID = ?";
            $result = $conn->execute_query($query,[$slotId]);
            if($result->num_rows==0){
                sendJsonResponse(200,'Deleted','Timeslot deleted succesfully');
            }
            $query = "DELETE FROM TIMESLOT WHERE SLOT_ID = ?";
            $result = $conn->execute_query($query,[$slotId]);
            if(mysqli_affected_rows($conn)>0 ){
                sendJsonResponse(200,'Deleted','Timeslot deleted succesfully');
            }else{
                sendJsonResponse(400,'Try again',"Couldn't update timeslot");
            }
        }
    }
    elseif($formCategory == 'classroom'){
        if($formtype == 'add_classroom'){
            $floor = $_POST['floor'] ?? '';
            $room = $_POST['room'] ?? '';
            $capacity = $_POST['capacity'] ?? '';
            $startTime = $_POST['startTime'] ?? '';
            $endTime = $_POST['endTime'] ?? '';
            $roomType = ($_POST['roomType'])? 'LAB':'LECTURE_HALL';

            $isValid = validateClassroom($floor, $room, $capacity, $startTime, $endTime);
            if ($isValid === 1) {
                $query = "INSERT INTO CLASSROOM(FLOOR_NUMBER, ROOM_NUMBER, CAPACITY, START_TIME, END_TIME) VALUES(?, ?, ?, ?, ?)";
                $result = $conn->execute_query($query, [$floor, $room, $capacity, $startTime, $endTime]);
                if (mysqli_affected_rows($conn) > 0) {
                    sendJsonResponse(201, 'Created', 'Classroom created successfully');
                } else {
                    sendJsonResponse(500, 'Try again', "Couldn't create Classroom");
                }
            }elseif($isValid === 2){
                sendJsonResponse(422,'Capacity out of range','The classroom capacity out of range');
            }elseif($isValid === 3){
                sendJsonResponse(422,'Too many rooms','There are too many rooms at a single floor');        
            }elseif($isValid === 4){
                sendJsonResponse(422,'Inverted Availability','The entered Availability is Invalid');        
            }else {
                sendJsonResponse(400, 'Invalid Classroom', 'Entered values are not valid');
            }
        }
        elseif($formtype == 'get_one_classroom'){ 
                $classroomId = $_POST['classroomId'];
                $query = "SELECT * FROM CLASSROOM WHERE classroom_id = ?;";
                $result = $conn->execute_query($query,[$classroomId]);
                if($result->num_rows>0){
                    sendJsonResponse(200,"Classroom found.",$result->fetch_assoc());
                    exit;
                }
                sendJsonResponse(404,"Classroom not found.");
        }
        elseif($formtype ==  'update_classroom'){
            $classroomId = $_POST['classroomId'] ?? '';
            $floor = $_POST['floor'] ?? '';
            $room = $_POST['room'] ?? '';
            $capacity = $_POST['capacity'] ?? '';
            $startTime = $_POST['startTime'] ?? '';
            $endTime = $_POST['endTime'] ?? '';
            
            $isValid = validateClassroom($floor, $room, $capacity, $startTime, $endTime);
            if ($isValid === 1) {
                $query = "UPDATE CLASSROOM SET FLOOR_NUMBER = ?, ROOM_NUMBER = ?, CAPACITY = ?, START_TIME = ?, END_TIME = ? WHERE CLASSROOM_ID = ?";
                $result = $conn->execute_query($query, [$floor, $room, $capacity, $startTime, $endTime, $classroomId]);
                if (mysqli_affected_rows($conn) > 0) {
                    sendJsonResponse(200, 'Updated', 'Classroom updated successfully');
                } else {
                    sendJsonResponse(400, 'Try again', "Couldn't update classroom");
                }
            } elseif ($isValid === 2) {
                sendJsonResponse(422, 'Too small classroom', 'The classroom capacity is too low');
            } elseif ($isValid === 3) {
                sendJsonResponse(422, 'Too many rooms', 'There are too many rooms at a single floor');
            } elseif ($isValid === 4) {
                sendJsonResponse(422, 'Inverted Availability','The entered Availability is Invalid');
            } else {
                sendJsonResponse(429, 'Invalid Classroom', 'Entered values are not valid');
            }
        }
        elseif($formtype == 'delete_classroom'){
                $classroomId = $_POST['classroomId'];
                $query = "DELETE FROM CLASSROOM WHERE CLASSROOM_ID = ?";
                $result = $conn->execute_query($query,[$classroomId]);
                if (mysqli_affected_rows($conn)>0 ){
                    sendJsonResponse(200,'Deleted','Classroom deleted succesfully');
                }else{
                    sendJsonResponse(400,'Try again',"Couldn't delete classroom");
                }
        }
    }
    elseif($formCategory == 'department'){
        if($formtype == 'add_department'){
            $fullname = strtoupper($_POST['fullname'] ?? '');
            $shortname = strtoupper($_POST['shortname'] ?? '');

            $isValid = validateDepartment($fullname, $shortname);
            if ($isValid === 1) {
                $query = "INSERT INTO DEPARTMENT(LONG_NAME, SHORT_NAME) VALUES(?, ?)";
                $result = $conn->execute_query($query, [$fullname, $shortname]);
                if (mysqli_affected_rows($conn) > 0) {
                    sendJsonResponse(201, 'Created', 'Department created successfully');
                } else {
                    sendJsonResponse(400, 'Try again', "Couldn't create Department");
                }
            }elseif($isValid === 2){
                sendJsonResponse(422,'Contains Special Characters','The department name contains invalid character.');
            }else {
                sendJsonResponse(429, 'Invalid Department', 'Entered values are not valid');
            }
        }
        elseif($formtype ==  'get_one_department'){ 
                $departmentId = $_POST['departmentId'];
                $query = "SELECT * FROM DEPARTMENT WHERE DEPARTMENT_ID = ?;";
                $result = $conn->execute_query($query,[$departmentId]);
                if($result->num_rows>0){
                    sendJsonResponse(200,"Department found.",$result->fetch_assoc());
                    exit;
                }
                sendJsonResponse(404,"Department not found.");
        }
        elseif($formtype == 'update_department'){
                $departmentId = $_POST['departmentId'] ?? '';
                $fullname = strtoupper($_POST['fullname'] ?? '');
                $shortname = strtoupper($_POST['shortname'] ?? '');

                $isValid = validateDepartment($fullname, $shortname);
                if ($isValid === 1) {
                    $query = "UPDATE DEPARTMENT SET LONG_NAME = ?, SHORT_NAME = ? WHERE DEPARTMENT_ID = ?";
                    $result = $conn->execute_query($query, [$fullname, $shortname, $departmentId]);
                    if (mysqli_affected_rows($conn) > 0) {
                        sendJsonResponse(201, 'Created', 'Department updated successfully');
                    } else {
                        sendJsonResponse(400, 'Try again', "Couldn't update Department");
                    }
                }elseif($isValid === 2){
                    sendJsonResponse(422,'Contains Special Characters','The department name contains invalid character.');
                }else {
                    sendJsonResponse(429, 'Invalid Department', 'Entered values are not valid');
                }
        }
        elseif($formtype == 'delete_department'){
            $departmentId = $_POST['departmentId'];
            $query = "DELETE FROM DEPARTMENT WHERE DEPARTMENT_ID = ?";
            $result = $conn->execute_query($query,[$departmentId]);
            if (mysqli_affected_rows($conn)>0 ){
                sendJsonResponse(200,'Deleted','Department deleted succesfully');
            }else{
                sendJsonResponse(400,'Try again',"Couldn't update timeslot");
            }
        }        
    }
    elseif($formCategory == 'teacher'){
        if ($formtype == 'add_teacher') {
            $deptId     = $_POST['departmentDropdown'] ?? '';
            $fname      = trim($_POST['fname'] ?? '');
            $lname      = trim($_POST['lname'] ?? '');
            $isPartTime = isset($_POST['isPartTime']) ? 1 : 0;
            if (empty($deptId) || empty($fname) || empty($lname)) {
                sendJsonResponse(400, "Bad Request", "All required fields are mandatory.");
            }
            if (!isValidName($fname) || !isValidName($lname)) {
                sendJsonResponse(400, "Bad Request", "Invalid teacher name.");
            }
            $days = [
                'monday'    => 'MONDAY', 'tuesday'   => 'TUESDAY', 'wednesday' => 'WEDNESDAY',
                'thursday'  => 'THURSDAY', 'friday'    => 'FRIDAY', 'saturday'  => 'SATURDAY'
            ];
            mysqli_begin_transaction($conn);
            try {
                // Insert Teacher
                $query = "INSERT INTO TEACHER (DEPARTMENT_ID, FIRST_NAME, LAST_NAME, ISPARTTIME) VALUES (?, ?, ?, ?)";
                $conn->execute_query($query, [$deptId, strtoupper($fname), strtoupper($lname), $isPartTime]);
                $teacherId = mysqli_insert_id($conn);
                if (!$teacherId) { sendJsonResponse(500, 'Try again', "Couldn't create Teacher"); }
                // PART TIME TEACHER
                if ($isPartTime) {

                    foreach ($days as $dayKey => $weekday) {
                        if (!isset($_POST[$dayKey])) { continue; }

                        $startSlot = (int)$_POST["punchIn_$dayKey"];
                        $endSlot   = (int)$_POST["punchOut_$dayKey"];

                        if ($startSlot > $endSlot) { sendJsonResponse(422,"$weekday : Punch In must be before Punch Out."); }

                        // Fetch actual slot ids
                        $slotResult = $conn->execute_query(
                            "SELECT DISTINCT SLOT_ID
                            FROM TIMESLOT
                            WHERE SLOT_ID BETWEEN ? AND ?
                            ORDER BY SLOT_ID",
                            [$startSlot, $endSlot]
                        );

                        while ($slot = $slotResult->fetch_assoc()) {
                            $conn->execute_query(
                                "INSERT INTO AVAILABILITY (TEACHER_ID,SLOT_ID,WEEKDAY,STATUS) VALUES (?,?,?,'AVAILABLE')",
                                [$teacherId, $slot['SLOT_ID'], $weekday]
                            );
                        }
                    }
                }
                // FULL TIME TEACHER
                else {
                    $slotResult = $conn->execute_query("SELECT SLOT_ID FROM TIMESLOT ORDER BY SLOT_ID");
                    $slots = [];
                    while ($row = $slotResult->fetch_assoc()) {$slots[] = $row['SLOT_ID'];}
                    foreach ($days as $weekday) {
                        foreach ($slots as $slotId) {
                            $conn->execute_query(
                                "INSERT INTO AVAILABILITY (TEACHER_ID,SLOT_ID,WEEKDAY,STATUS) VALUES (?,?,?,'AVAILABLE')",
                                [$teacherId, $slotId, $weekday]
                            );
                        }
                    }
                }
                mysqli_commit($conn);
                sendJsonResponse(201,"Created","Teacher created successfully.");

            } catch (Exception $e) {

                mysqli_rollback($conn);
                sendJsonResponse(
                    500,
                    "Server Error"
                );

            }

        }
        elseif($formtype == 'get_one_teacher'){ 
            $teacherId = $_POST['teacherId'];
            $query = "SELECT * FROM TEACHER WHERE TEACHER_ID = ?;";
            $result = $conn->execute_query($query,[$teacherId]);
            if($result->num_rows>0){
                sendJsonResponse(200,"Teacher found.",$result->fetch_assoc());
                exit;
            }
            sendJsonResponse(404,"Teacher not found.");
        }
        elseif ($formtype == 'update_teacher') {

            $teacherId  = $_POST['teacherId'] ?? '';
            $deptId     = $_POST['departmentDropdown'] ?? '';
            $fname      = trim($_POST['fname'] ?? '');
            $lname      = trim($_POST['lname'] ?? '');
            $isPartTime = isset($_POST['isPartTime']) ? 1 : 0;
            if (empty($teacherId) || empty($deptId) || empty($fname) || empty($lname)) {
                sendJsonResponse(400, "Bad Request", "All required fields are mandatory.");
                exit;
            }
            if (!isValidName($fname) || !isValidName($lname)) {
                sendJsonResponse(400, "Bad Request", "Invalid teacher name.");
                exit;
            }
            $days = [
                'monday'=>'MONDAY','tuesday'=>'TUESDAY','wednesday'=>'WEDNESDAY',
                'thursday'=>'THURSDAY','friday'=>'FRIDAY','saturday'=>'SATURDAY'
            ];
            mysqli_begin_transaction($conn);
            try {
                $result = $conn->execute_query(
                    "SELECT TEACHER_ID FROM TEACHER WHERE TEACHER_ID = ?",
                    [$teacherId]
                );
                if ($result->num_rows == 0) {
                    throw new Exception("Teacher not found.");
                }
                $conn->execute_query(
                    "UPDATE TEACHER SET DEPARTMENT_ID=?, FIRST_NAME=?, LAST_NAME=?, ISPARTTIME=? WHERE TEACHER_ID=?",
                    [$deptId, strtoupper($fname), strtoupper($lname), $isPartTime, $teacherId]
                );
                $conn->execute_query(
                    "DELETE FROM AVAILABILITY WHERE TEACHER_ID=?",
                    [$teacherId]
                );
                if ($isPartTime) {
                    foreach ($days as $key => $weekday) {
                        if (!isset($_POST[$key])) continue;

                        $start = (int)($_POST["punchIn_$key"] ?? 0);
                        $end   = (int)($_POST["punchOut_$key"] ?? 0);
                        if ($start > $end) throw new Exception("$weekday: Punch In must be before Punch Out.");

                        $slots = $conn->execute_query(
                            "SELECT SLOT_ID FROM TIMESLOT WHERE SLOT_ID BETWEEN ? AND ? ORDER BY SLOT_ID",
                            [$start, $end]
                        );

                        while ($row = $slots->fetch_assoc()) {
                            $conn->execute_query(
                                "INSERT INTO AVAILABILITY (TEACHER_ID,SLOT_ID,WEEKDAY,STATUS) VALUES (?,?,?,'AVAILABLE')",
                                [$teacherId, $row['SLOT_ID'], $weekday]
                            );
                        }
                    }
                } else {
                    $slots = $conn->execute_query("SELECT SLOT_ID FROM TIMESLOT ORDER BY SLOT_ID");
                    while ($row = $slots->fetch_assoc()) {
                        foreach ($days as $weekday) {
                            $conn->execute_query(
                                "INSERT INTO AVAILABILITY (TEACHER_ID,SLOT_ID,WEEKDAY,STATUS) VALUES (?,?,?,'AVAILABLE')",
                                [$teacherId, $row['SLOT_ID'], $weekday]
                            );
                        }
                    }
                }
                mysqli_commit($conn);
                sendJsonResponse(200, "Updated", "Teacher updated successfully.");
                exit;
                
            } catch (Exception $e) {
                mysqli_rollback($conn);
                sendJsonResponse(500, "Server Error", $e->getMessage());
                exit;
            }
        }
        elseif($formtype == 'delete_teacher'){
            $teacherId = $_POST['teacherId'];
            $query = "DELETE FROM TEACHER WHERE TEACHER_ID = ?";
            $result = $conn->execute_query($query,[$teacherId]);
            if (mysqli_affected_rows($conn)>0 ){
                sendJsonResponse(200,'Deleted','Teacher deleted succesfully');
            }else{
                sendJsonResponse(400,'Try again',"Couldn't update timeslot");
            }
        }
    }      
    elseif ($formCategory == 'programme') {
        if ($formtype == 'add_programme') {
            $departmentId = $_POST['departmentId'] ?? '';
            $fullname     = strtoupper(trim($_POST['fullname'] ?? ''));
            $shortname    = strtoupper(trim($_POST['shortname'] ?? ''));
            $duration     = (int)($_POST['duration'] ?? 0);
            $divisionCounts = [
                1 => (int)($_POST['division1'] ?? 0),
                2 => (int)($_POST['division2'] ?? 0),
                3 => (int)($_POST['division3'] ?? 0),
                4 => (int)($_POST['division4'] ?? 0),
                5 => (int)($_POST['division5'] ?? 0)
            ];
            $divisionCodes = ['A', 'B', 'C', 'D', 'E'];
            $isValid = validateProgramme($fullname, $shortname);
            if ($isValid === 2) {
                sendJsonResponse( 422, 'Contains Special Characters', 'The programme name contains invalid character.' );
            }
            if ($isValid !== 1) {
                sendJsonResponse( 429, 'Invalid Programme', 'Entered values are not valid' );
            }
            if ($duration < 1 || $duration > 5) {
                sendJsonResponse( 422, 'Invalid Duration', 'Programme duration must be between 1 and 5 years.'  );
            }
            for ($i = 1; $i <= $duration; $i++) {
                if ($divisionCounts[$i] < 1 || $divisionCounts[$i] > 5) {
                    sendJsonResponse( 422, 'Invalid Division Count', "Invalid division count for year $i." );
                }
            }
            $totalDivisionCount = array_sum(
                array_slice($divisionCounts, 0, $duration, true)
            );
            mysqli_begin_transaction($conn);
            $query = "INSERT INTO PROGRAMME (DEPARTMENT_ID, LONG_NAME, SHORT_NAME, DIVISION_COUNT) VALUES (?, ?, ?, ?)";
            $result = $conn->execute_query(
                $query,[ $departmentId, $fullname, $shortname, $totalDivisionCount ]
            );
            if (!$result || mysqli_affected_rows($conn) != 1) {
                mysqli_rollback($conn);
                sendJsonResponse( 400, 'Try again', "Couldn't create Programme" );
            }
            $programmeId = mysqli_insert_id($conn);
            for ($year = 1; $year <= $duration; $year++) {
                $result = $conn->execute_query(
                    "INSERT INTO CONSISTS (YEAR_NUMBER, PROGRAMME_ID) VALUES (?, ?)",
                    [ $year, $programmeId ]
                );
                if (!$result || mysqli_affected_rows($conn) != 1) {
                    mysqli_rollback($conn);
                    sendJsonResponse( 400, 'Try again', "Couldn't create Programme" );
                }
                for ($j = 0; $j < $divisionCounts[$year]; $j++) {
                    $result = $conn->execute_query(
                        "INSERT INTO DIVISION (YEAR_NUMBER, NAME, PROGRAMME_ID) VALUES (?, ?, ?)",
                        [ $year, $divisionCodes[$j], $programmeId ]
                    );
                    if (!$result || mysqli_affected_rows($conn) != 1) {
                        mysqli_rollback($conn);
                        sendJsonResponse( 400, 'Try again', "Couldn't create Programme" );
                    }
                }
            }
            mysqli_commit($conn);
            sendJsonResponse( 201, 'Created', 'Programme created successfully');
        }
        elseif ($formtype == 'get_one_programme') {
            $programmeId = $_POST['programmeId'] ?? '';
            $result = $conn->execute_query(
                "SELECT PROGRAMME_ID, DEPARTMENT_ID, LONG_NAME, SHORT_NAME FROM PROGRAMME WHERE PROGRAMME_ID = ?",
                [$programmeId]
            );
            if ($result->num_rows == 0) {
                sendJsonResponse( 404, 'Programme not found.' );
            }
            $data = $result->fetch_assoc();
            $result = $conn->execute_query(
                "SELECT COUNT(*) AS DURATION FROM CONSISTS WHERE PROGRAMME_ID = ?",
                [$programmeId]
            );
            $data['DURATION'] = (int)$result->fetch_assoc()['DURATION'];
            $result = $conn->execute_query(
                "SELECT YEAR_NUMBER, COUNT(*) AS DIVISION_COUNT FROM DIVISION WHERE PROGRAMME_ID = ? GROUP BY YEAR_NUMBER ORDER BY YEAR_NUMBER",
                [$programmeId]
            );
            $data['DIVISIONS'] = [];
            while ($row = $result->fetch_assoc()) {
                $data['DIVISIONS'][(int)$row['YEAR_NUMBER']] = (int)$row['DIVISION_COUNT'];
            }
            sendJsonResponse(200,'Programme found.',$data);
        }
        elseif ($formtype == 'update_programme') {
            $programmeId = $_POST['programmeId'] ?? '';
            $departmentId = $_POST['departmentId'] ?? '';
            $fullname = strtoupper( trim($_POST['fullname'] ?? '') );
            $shortname = strtoupper( trim($_POST['shortname'] ?? '') );
            $duration = (int)( $_POST['duration'] ?? 0 );
            $divisionCounts = [
                1 => (int)($_POST['division1'] ?? 0),
                2 => (int)($_POST['division2'] ?? 0),
                3 => (int)($_POST['division3'] ?? 0),
                4 => (int)($_POST['division4'] ?? 0),
                5 => (int)($_POST['division5'] ?? 0)
            ];
            $divisionCodes = [ 'A', 'B', 'C', 'D', 'E' ];
            $isValid = validateProgramme( $fullname, $shortname );
            if ($isValid === 2) {
                sendJsonResponse( 422, 'Contains Special Characters', 'The programme name contains invalid character.');
            }
            if ($isValid !== 1) {
                sendJsonResponse( 429, 'Invalid Programme', 'Entered values are not valid');
            }
            if ($duration < 1 || $duration > 5) {
                sendJsonResponse( 422, 'Invalid Duration', 'Programme duration must be between 1 and 5 years.');
            }
            for ($i = 1; $i <= $duration; $i++) {
                if (
                    $divisionCounts[$i] < 1 ||
                    $divisionCounts[$i] > 5
                ) {
                    sendJsonResponse(422,'Invalid Division Count',"Invalid division count for year $i."
                    );
                }
            }
            $result = $conn->execute_query(
                "SELECT PROGRAMME_ID FROM PROGRAMME WHERE PROGRAMME_ID = ?",
                [$programmeId]
            );
            if ($result->num_rows == 0) {
                sendJsonResponse(404,'Programme not found.');
            }
            $totalDivisionCount = array_sum(
                array_slice($divisionCounts, 0, $duration, true)
            );
            mysqli_begin_transaction($conn);
            $result = $conn->execute_query(
                "UPDATE PROGRAMME SET DEPARTMENT_ID = ?, LONG_NAME = ?, SHORT_NAME = ?, DIVISION_COUNT = ? WHERE PROGRAMME_ID = ?", 
                [ $departmentId, $fullname, $shortname, $totalDivisionCount, $programmeId ]
            );
            if (!$result) {
                mysqli_rollback($conn);
                sendJsonResponse( 400, 'Try again', "Couldn't update Programme" );
            }
            for ($year = 1; $year <= $duration; $year++) {
                $result = $conn->execute_query( "SELECT YEAR_NUMBER FROM CONSISTS WHERE YEAR_NUMBER = ? AND PROGRAMME_ID = ?", [ $year, $programmeId ] );
                if ($result->num_rows == 0) {
                    $result = $conn->execute_query( "INSERT INTO CONSISTS (YEAR_NUMBER, PROGRAMME_ID) VALUES (?, ?)", [ $year, $programmeId ] );
                    if (!$result) {
                        mysqli_rollback($conn);
                        sendJsonResponse( 400, 'Try again', "Couldn't update Programme" );
                    }
                }
                for ( $j = 0; $j < $divisionCounts[$year]; $j++ ) {
                    $code = $divisionCodes[$j];
                    $result = $conn->execute_query("SELECT DIVISION_ID FROM DIVISION WHERE YEAR_NUMBER = ? AND PROGRAMME_ID = ? AND NAME = ?", [ $year, $programmeId, $code ]);
                    if ($result->num_rows == 0) {
                        $result = $conn->execute_query( "INSERT INTO DIVISION (YEAR_NUMBER, NAME, PROGRAMME_ID) VALUES (?, ?, ?)", [ $year, $code, $programmeId ] );
                        if (!$result) {
                            mysqli_rollback($conn);
                            sendJsonResponse( 400, 'Try again', "Couldn't update Programme" );
                        }
                    }
                }
                if ($divisionCounts[$year] < 5) {
                    $deleteFrom = $divisionCodes[$divisionCounts[$year]];
                    $result = $conn->execute_query("DELETE FROM DIVISION WHERE PROGRAMME_ID = ? AND YEAR_NUMBER = ? AND NAME >= ?", [ $programmeId, $year, $deleteFrom ]);
                    if (!$result) {
                        mysqli_rollback($conn);
                        sendJsonResponse( 400, 'Try again', "Couldn't update Programme" );
                    }
                }
            }
            $result = $conn->execute_query( "DELETE FROM DIVISION WHERE PROGRAMME_ID = ? AND YEAR_NUMBER > ?", [ $programmeId, $duration ] );
            if (!$result) {
                mysqli_rollback($conn);
                sendJsonResponse( 400, 'Try again', "Couldn't update Programme" );
            }
            $result = $conn->execute_query(
                "DELETE FROM CONSISTS WHERE PROGRAMME_ID = ? AND YEAR_NUMBER > ?",
                [ $programmeId, $duration ]
            );
            if (!$result) {
                mysqli_rollback($conn);
                sendJsonResponse(400,'Try again',"Couldn't update Programme");
            }
            mysqli_commit($conn);
            sendJsonResponse( 200, 'Updated', 'Programme updated successfully');
        }
        elseif ($formtype == 'delete_programme') {
            $programmeId = $_POST['programmeId'] ?? '';
            $result = $conn->execute_query( "DELETE FROM PROGRAMME WHERE PROGRAMME_ID = ?", [$programmeId]);
            if ($result && mysqli_affected_rows($conn) > 0) {
                sendJsonResponse( 200, 'Deleted', 'Programme deleted successfully');
            } else {
                sendJsonResponse( 400, 'Try again', "Couldn't delete Programme");
            }
        }
    }
    elseif($formCategory == 'course'){
        if($formtype == 'add_course'){
            $departmentId = $_POST['departmentId'] ?? '';
            $programmeId = $_POST['programmeId'] ?? '';
            $yearId = $_POST['yearId'] ?? '';
            $fullname = strtoupper($_POST['fullname'] ?? '');
            $shortname = strtoupper($_POST['shortname'] ?? '');
            $weeklyLectures = strtoupper($_POST['lectureCount'] ?? 0);
            $semester = ($_POST['isEven'] =='true')? "EVEN" : "ODD";
            $isPractical = ($_POST['courseType'] =='false')? true : false;
            $isOptional = ($_POST['isOptional'] ?? '' =='true')? true : false;
            $optionalCourseId = $_POST['optionalCourseId'] ?? null;
            $isValid = validateCourse($fullname, $shortname);
            if ($isValid === 1) {
                $query = "INSERT INTO COURSE(PROGRAMME_ID, YEAR_NUMBER, LONG_NAME, SHORT_NAME, SEMESTER, WEEKLY_LECTURES, ISPRACTICAL, ISOPTIONAL) VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
                $result = $conn->execute_query($query, [$programmeId, $yearId, $fullname, $shortname, $semester, $weeklyLectures, $isPractical, $isOptional]);
                if (mysqli_affected_rows($conn) > 0) {
                    sendJsonResponse(201, 'Created', 'Course created successfully');
                } else {
                    sendJsonResponse(400, 'Try again', "Couldn't create course");
                }
            }elseif($isValid === 2){
                sendJsonResponse(422,'Contains Special Characters','The course name contains invalid character.');
            }else {
                sendJsonResponse(429, 'Invalid course', 'Entered values are not valid');
            }
        }
        elseif($formtype == 'get_one_course') {
            $courseId = $_POST['courseId'] ?? null;
            if(!$courseId || !is_numeric($courseId)){
                sendJsonResponse(400, "INVALID COURSE ID.");
                exit;
            }
            $query = "
                SELECT
                    C.COURSE_ID,
                    P.DEPARTMENT_ID,
                    C.PROGRAMME_ID,
                    C.YEAR_NUMBER,
                    C.SEMESTER,
                    C.LONG_NAME,
                    C.SHORT_NAME,
                    C.WEEKLY_LECTURES,
                    C.ISPRACTICAL,
                    C.ISOPTIONAL,
                    C.OPTIONAL_ID
                FROM COURSE C
                INNER JOIN PROGRAMME P ON P.PROGRAMME_ID = C.PROGRAMME_ID
                WHERE C.COURSE_ID = ?
                LIMIT 1
            ";
            $result = $conn->execute_query($query, [(int)$courseId]);
            if($result->num_rows > 0){sendJsonResponse(200, "COURSE FOUND.", $result->fetch_assoc());}
            sendJsonResponse(404, "COURSE NOT FOUND.");
            exit;
        }
        elseif($formtype == 'update_course') {
            $courseId = $_POST['courseId'] ?? '';
            $departmentId = $_POST['departmentId'] ?? '';
            $programmeId = $_POST['programmeId'] ?? '';
            $yearId = $_POST['yearId'] ?? '';
            $fullname = strtoupper($_POST['fullname'] ?? '');
            $shortname = strtoupper($_POST['shortname'] ?? '');
            $semester = ($_POST['isEven'] =='true')? "EVEN" : "ODD";
            $weeklyLectures = strtoupper($_POST['lectureCount'] ?? 0);
            $isPractical = ($_POST['courseType'] =='false')? true : false;
            $isOptional = ($_POST['isOptional'] =='true')? true : false;
            $optionalCourseId = $_POST['optionalCourseId'] ?? null;
            $isValid = validateCourse($fullname, $shortname);
            
            if ($isValid === 1) {
                $query = "UPDATE COURSE SET PROGRAMME_ID = ?, YEAR_NUMBER = ?, LONG_NAME = ?, SHORT_NAME = ?, SEMESTER = ?, WEEKLY_LECTURES = ?, ISPRACTICAL = ?, ISOPTIONAL = ? WHERE COURSE_ID = ?";
                $result = $conn->execute_query($query, [ $programmeId, $yearId, $fullname, $shortname, $semester, $weeklyLectures, $isPractical, $isOptional, $courseId]);
                if ($result) {sendJsonResponse(200, 'Updated', 'Course updated successfully');
                }else {sendJsonResponse(400, 'Try again', "Couldn't update course");}

            }elseif ($isValid === 2) {
                sendJsonResponse(422, 'Contains Special Characters', 'The course name contains invalid character.');
            } else {
                sendJsonResponse(429, 'Invalid course', 'Entered values are not valid');
            }
        }
        elseif($formtype == 'delete_course'){
            $courseId = $_POST['courseId'];
            $query = "DELETE FROM COURSE WHERE COURSE_ID = ?";
            $result = $conn->execute_query($query,[$courseId]);
            if (mysqli_affected_rows($conn)>0 ){
                sendJsonResponse(200,'Deleted','Course deleted succesfully');
            }else{
                sendJsonResponse(400,'Try again',"Couldn't update timeslot");
            }
        }        
    }
    elseif($formCategory == 'optionalcourse') {
        if ($formtype == 'map_course') {
            $course1 = (int) $_POST['course1'];
            $course2 = (int) $_POST['course2'];
            $query = "
                UPDATE COURSE
                SET OPTIONAL_ID = ?
                WHERE COURSE_ID = ?
            ";
            $conn->execute_query($query, [$course2, $course1]);
            $query = "
                UPDATE COURSE
                SET OPTIONAL_ID = ?
                WHERE COURSE_ID = ?
            ";
            $conn->execute_query($query, [$course1, $course2]);
            sendJsonResponse(200, 'Mapped', 'Courses mapped successfully');
        } elseif($formtype == 'unmap_course') {
            $mainCourse = (int) $_POST['mainCourse'];
            // Find the course that is mapped to this course
            $query = "SELECT COURSE_ID FROM COURSE WHERE OPTIONAL_ID = ?";
            $result = $conn->execute_query($query, [$mainCourse]);

            $row = $result->fetch_assoc();
            if ($row) {
                $sideCourse = (int) $row['COURSE_ID'];
                $query = "UPDATE COURSE SET OPTIONAL_ID = NULL WHERE COURSE_ID = ?";
                $conn->execute_query($query, [$sideCourse]);
                $conn->execute_query($query, [$mainCourse]);
                sendJsonResponse(200,'Unmapped','Course unmapped succesfully');
            }
        }
    }
    elseif($formCategory == 'division'){
        if($formtype == 'add_divisionDetails'){
            $divisionId = $_POST['divisionId'] ?? '';
            $studCount = $_POST['studentCount'] ?? '';
            $classroomId = $_POST['classroomId'] ?? null;
            $hasPrefTime = $_POST['prefTime'] ?? false;
            $isStartPref = $_POST['isStartPref'] ?? false;
            $timeBound = $_POST['timeBound'] ?? null;
            $query = "";
            if($hasPrefTime){
                if($isStartPref){
                    $query = "UPDATE DIVISION SET STUDENT_COUNT = ?, CLASSROOM_ID = ?, START_TIME_ID = ? WHERE DIVISION_ID = ?";
                    $result = $conn->execute_query($query,[$studCount,$classroomId,$timeBound,$divisionId]);
                }
                else{
                    $query = "UPDATE DIVISION SET STUDENT_COUNT = ?, CLASSROOM_ID = ?, END_TIME_ID = ? WHERE DIVISION_ID = ?";
                    $result = $conn->execute_query($query,[$studCount,$classroomId,$timeBound,$divisionId]);
                }
            }
            else{
                $query = "UPDATE DIVISION SET STUDENT_COUNT = ?, CLASSROOM_ID = ? WHERE DIVISION_ID = ?";
                $result = $conn->execute_query($query,[$studCount,$classroomId,$divisionId]);
            }
            if($result){
                sendJsonResponse(200,'Details saved',"The division details were saved successfully");
            }
            else{
                sendJsonResponse(500,'Details were not saved',"Error in saving division details");
            }
        }
        elseif($formtype == 'delete_divisionDetails'){
            $divisionId = $_POST['divisionId'];
            $query = "UPDATE DIVISION SET STUDENT_COUNT = NULL, CLASSROOM_ID = NULL, START_TIME_ID = NULL, END_TIME_ID = NULL WHERE DIVISION_ID = ?";
            $result = $conn->execute_query($query,[$divisionId]);
            if($result){
                sendJsonResponse(200,"Details Deleted","The Details were deleted successfully.","");
            }
            else{
                sendJsonResponse(400,'Delete failed', "The delete details operation was unsuccesfull.");
            }
        }
            
    }
    elseif($formCategory == 'optionalcoursemapper') {
        if ($formtype == 'map_course2division') {
            $course1 = (int) $_POST['course1'];
            $course2 = (int) $_POST['course2'];
            $division1 = (int) $_POST['division1'];
            $division2 = (int) $_POST['division2'];

            $query = "
                INSERT INTO OPTED_BY (COURSE_ID,DIVISION_ID) VALUES(?,?);
            ";
            $conn->begin_transaction();
            $result = $conn->execute_query($query, [$course1, $division1]);
            if($result){
                $result = $conn->execute_query($query, [$course2, $division2]);
                if($result){
                    $conn->commit();
                    sendJsonResponse(200,"Course Mapped to Division","Courses were succesfully mapped to divisions");
                }else{
                    $conn->rollback();
                    sendJsonResponse(400,"Error in mapping", "Mapping was unsuccesfull");
                }
            }else{
                $conn->rollback();
                sendJsonResponse(400,"Error in mapping", "Mapping was unsuccesfull");
            }
            
        } elseif($formtype == 'unmap_course') {
            $courseId1 = $_POST['courseId1'];
            $query = "SELECT OPTIONAL_ID FROM COURSE WHERE COURSE_ID = ?";
            $result = $conn->execute_query($query, [$courseId1]);
            $courseId2 = ($result->fetch_assoc())['OPTIONAL_ID'];

            $query = "DELETE FROM OPTED_BY WHERE COURSE_ID = ? OR COURSE_ID = ?";
            $result = $conn->execute_query($query, [$courseId1, $courseId2]);
            if ($result) {
                sendJsonResponse(200,'Unmapped','Course unmapped succesfully');
            }else{
                sendJsonResponse(400,'Failed','Course unmapped Failed');
            }
        }
    }
    elseif($formCategory == 'workload'){

        if($formtype == "create_workload"){
            $teacherId = (int) ($_POST['teacherId'] ?? 0);
            $courseId = (int) ($_POST['courseId'] ?? 0);
            $divisionId = $_POST['divisionId'] ?? '';
            $lectureCount = (int) ($_POST['lectureCount'] ?? 0);
            if($lectureCount < 0){
                sendJsonResponse(
                    400,
                    'Invalid Lecture Count',
                    'Lecture count cannot be negative.'
                );
                exit;
            }
            $divisionIds = json_decode($divisionId, true);
            if(!is_array($divisionIds)){
                $divisionIds = [(int)$divisionId];
            }
            $query = "
                INSERT INTO TEACHES
                    (TEACHER_ID, COURSE_ID, DIVISION_ID, LECTURE_COUNT)
                VALUES (?, ?, ?, ?)
            ";
            $conn->begin_transaction();
            try {
                foreach($divisionIds as $divisionId){
                    $divisionId = (int)$divisionId;
                    $conn->execute_query(
                        $query,
                        [$teacherId, $courseId, $divisionId, $lectureCount]
                    );
                }
                $conn->commit();
                sendJsonResponse(
                    200,
                    'Workload Created',
                    'Workload created successfully.'
                );
            } catch(mysqli_sql_exception $e) {

                $conn->rollback();

                sendJsonResponse(
                    500,
                    'Creation Failed',
                    'Unable to create workload.'
                );
            }
        }

        elseif($formtype == 'delete_workload'){
            $workloadId = (int) ($_POST['workloadId'] ?? 0);
            if($workloadId <= 0){
                sendJsonResponse(400,'Invalid Data','Invalid workload ID.');
                exit;
            }
            $query = "DELETE FROM TEACHES WHERE WORKLOAD_ID = ?";
            $result = $conn->execute_query($query, [$workloadId]);

            if($result && $conn->affected_rows == 1){
                sendJsonResponse(200,'Deleted','Workload deleted successfully.');
            }else{
                sendJsonResponse(500,'Delete Failed','Unable to delete workload.');
            }
        }

}

    
    

} catch (Throwable $e) {
    // Catch ALL exceptions/errors and return them as valid JSON
    sendJsonResponse(500, 'Server Error', $e->getMessage());
}

?>