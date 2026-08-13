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
            $sql = "SELECT CLASSROOM_ID, ROOM_NUMBER FROM CLASSROOM ORDER BY ROOM_NUMBER";
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
                    c.OPTIONAL_ID,
                
                FROM COURSE c
                LEFT JOIN PROGRAMME p
                    ON c.PROGRAMME_ID = p.PROGRAMME_ID
                LEFT JOIN DEPARTMENT d
                    ON p.DEPARTMENT_ID = d.DEPARTMENT_ID
                ORDER BY c.LONG_NAME
            ";
            $result = mysqli_query($conn, $sql);
            $response['courses'] = [];
            while($row = mysqli_fetch_assoc($result)) {$response['courses'][] = $row;}
        }
        elseif($formtype == 'optionalcourse'){
            $sql = 
            "
                SELECT
                    d.DEPARTMENT_ID,
                    d.SHORT_NAME AS DEPARTMENT_SHORT_NAME,
                    p.PROGRAMME_ID,
                    p.SHORT_NAME AS PROGRAMME_SHORT_NAME,
                    y.YEAR_NUMBER,
                    y.YEAR_NAME,
                    c.COURSE_ID,
                    c.LONG_NAME AS COURSE_NAME,
                    c.OPTIONAL_ID,
                    c.ISOPTIONAL,
                    oc.LONG_NAME AS OPTIONAL_COURSE_NAME
                FROM COURSE c
                JOIN PROGRAMME p ON c.PROGRAMME_ID = p.PROGRAMME_ID
                JOIN DEPARTMENT d ON p.DEPARTMENT_ID = d.DEPARTMENT_ID
                JOIN YEAR y ON c.YEAR_NUMBER = y.YEAR_NUMBER
                LEFT JOIN COURSE oc ON c.OPTIONAL_ID = oc.COURSE_ID
                WHERE c.ISOPTIONAL = TRUE
                ORDER BY c.COURSE_ID;
            ";
            $result = mysqli_query($conn, $sql);
            $response['optionalcourses'] = [];
            while($row = mysqli_fetch_assoc($result)) {$response['optionalcourses'][] = $row;}
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
    elseif($formCategory == 'programme'){
        if($formtype == 'add_programme'){
            $departmentId = $_POST['departmentId'] ?? '';
            $fullname = strtoupper($_POST['fullname'] ?? '');
            $shortname = strtoupper($_POST['shortname'] ?? '');
            $duration = $_POST['duration'] ?? '';
            $division1 = $_POST['division1'] ?? 0;
            $division2 = $_POST['division2'] ?? 0;
            $division3 = $_POST['division3'] ?? 0;

            $divisionCounts = [1=>$division1, 2=>$division2, 3=>$division3];
            $divisionCodes = ['A','B','C','D','E'];

            $isValid = validateProgramme($fullname, $shortname);
            if ($isValid === 1) {
                mysqli_begin_transaction($conn);
                $query = "INSERT INTO PROGRAMME(DEPARTMENT_ID, LONG_NAME, SHORT_NAME) VALUES(?, ?, ?)";
                $result = $conn->execute_query($query, [$departmentId, $fullname, $shortname]);
                $programmeId = mysqli_insert_id($conn);
                if (mysqli_affected_rows($conn) == 1) {
                    $programmeId = mysqli_insert_id($conn);
                    for ($i=1; $i<=$duration; $i++){
                        $query = "INSERT INTO CONSISTS(YEAR_NUMBER, PROGRAMME_ID) VALUES(?, ?)";
                        $result = $conn->execute_query($query, [$i, $programmeId]);
                        if(mysqli_affected_rows($conn) == 1){
                            for($j=0; $j<($divisionCounts[$i]); $j++){
                                $query = "INSERT INTO DIVISION(YEAR_NUMBER, NAME, PROGRAMME_ID) VALUES(?, ?, ?)";
                                $result = $conn->execute_query($query, [$i, $divisionCodes[$j], $programmeId]);
                                if(mysqli_affected_rows($conn) != 1){
                                    mysqli_rollback($conn);
                                    sendJsonResponse(400, 'Try again', "Couldn't create Programme");
                                }
                            }
                        }else{
                            mysqli_rollback($conn);
                            sendJsonResponse(400, 'Try again', "Couldn't create Programme");
                        }
                    }
                }else{
                    mysqli_rollback($conn);
                    sendJsonResponse(400, 'Try again', "Couldn't create Programme");
                }
                if (mysqli_affected_rows($conn) > 0) {
                    mysqli_commit($conn);
                    sendJsonResponse(201, 'Created', 'Programme created successfully');
                } else {
                    sendJsonResponse(400, 'Try again', "Couldn't create Programme");
                }
            }elseif($isValid === 2){
                sendJsonResponse(422,'Contains Special Characters','The programme name contains invalid character.');
            }else {
                sendJsonResponse(429, 'Invalid Programme', 'Entered values are not valid');
            }

        }
        elseif ($formtype == 'get_one_programme') {
            $programmeId = $_POST['programmeId'];

            $query = "SELECT * FROM PROGRAMME WHERE PROGRAMME_ID = ?";
            $result = $conn->execute_query($query, [$programmeId]);

            if ($result->num_rows > 0) {
                $data = $result->fetch_assoc();

                $query = "SELECT COUNT(*) AS DURATION FROM CONSISTS WHERE PROGRAMME_ID = ?";
                $result = $conn->execute_query($query, [$programmeId]);
                $data['DURATION'] = (int)$result->fetch_assoc()['DURATION'];

                $query = "SELECT YEAR_NUMBER, COUNT(*) AS DIVISION_COUNT FROM DIVISION WHERE PROGRAMME_ID = ? GROUP BY YEAR_NUMBER";
                $result = $conn->execute_query($query, [$programmeId]);
                $data['DIVISIONS'] = [];
                while ($row = $result->fetch_assoc()) {
                    $data['DIVISIONS'][$row['YEAR_NUMBER']] = (int)$row['DIVISION_COUNT'];
                }

                $query = "SELECT YEAR_NUMBER, COUNT(*) AS DIVISION_COUNT FROM DIVISION WHERE PROGRAMME_ID = ? GROUP BY YEAR_NUMBER";
                $result = $conn->execute_query($query, [$programmeId]);

                $data['DIVISIONS'] = [];
                while ($row = $result->fetch_assoc()) {
                    $data['DIVISIONS'][$row['YEAR_NUMBER']] = (int)$row['DIVISION_COUNT'];
                }
                sendJsonResponse(200, "Programme found.", $data);
            }

            sendJsonResponse(404, "Programme not found.");
        }
        elseif($formtype=='update_programme'){
            $programmeId=$_POST['programmeId']??'';
            $departmentId=$_POST['departmentId']??'';
            $fullname=strtoupper(trim($_POST['fullname']??''));
            $shortname=strtoupper(trim($_POST['shortname']??''));
            $duration=(int)($_POST['duration']??0);

            $divisionCounts=[
                1=>(int)($_POST['division1']??0),
                2=>(int)($_POST['division2']??0),
                3=>(int)($_POST['division3']??0)
            ];

            $divisionCodes=['A','B','C','D','E'];

            $isValid=validateProgramme($fullname,$shortname);

            if($isValid===1){
                for($i=1;$i<=$duration;$i++)
                    if($divisionCounts[$i]<1||$divisionCounts[$i]>5)
                        sendJsonResponse(422,'Invalid Division Count',"Invalid division count for year $i.");

                $totalDivisionCount=array_sum(array_slice($divisionCounts,0,$duration,true));

                mysqli_begin_transaction($conn);
                $conn->execute_query(
                    "UPDATE PROGRAMME SET DEPARTMENT_ID=?,LONG_NAME=?,SHORT_NAME=?,DIVISION_COUNT=? WHERE PROGRAMME_ID=?",
                    [$departmentId,$fullname,$shortname,$totalDivisionCount,$programmeId]
                );
                for($i=1;$i<=$duration;$i++){
                    $conn->execute_query(
                        "INSERT IGNORE INTO CONSISTS(YEAR_NUMBER,PROGRAMME_ID) VALUES(?,?)",
                        [$i,$programmeId]
                    );
                    for($j=0;$j<$divisionCounts[$i];$j++){
                        $code=$divisionCodes[$j];
                        $result=$conn->execute_query(
                            "SELECT DIVISION_ID FROM DIVISION WHERE YEAR_NUMBER=? AND PROGRAMME_ID=? AND NAME=?",
                            [$i,$programmeId,$code]
                        );
                        if($result->num_rows===0)
                            $conn->execute_query(
                                "INSERT INTO DIVISION(YEAR_NUMBER,NAME,PROGRAMME_ID) VALUES(?,?,?)",
                                [$i,$code,$programmeId]
                            );
                    }
                    if($divisionCounts[$i]<5)
                        $conn->execute_query(
                            "DELETE FROM DIVISION WHERE YEAR_NUMBER=? AND PROGRAMME_ID=? AND NAME>=?",
                            [$i,$programmeId,$divisionCodes[$divisionCounts[$i]]]
                        );
                }
                $conn->execute_query(
                    "DELETE FROM DIVISION WHERE PROGRAMME_ID=? AND YEAR_NUMBER>?",
                    [$programmeId,$duration]
                );
                $conn->execute_query(
                    "DELETE FROM CONSISTS WHERE PROGRAMME_ID=? AND YEAR_NUMBER>?",
                    [$programmeId,$duration]
                );

                mysqli_commit($conn);
                sendJsonResponse(200,'Updated','Programme updated successfully');
            }elseif($isValid === 2){
                sendJsonResponse(422,'Contains Special Characters','The programme name contains invalid character.');
            }else {
                sendJsonResponse(429, 'Invalid Programme', 'Entered values are not valid');
            }
        }
        elseif($formtype == 'delete_programme'){
            $programmeId = $_POST['programmeId'];
            $query = "DELETE FROM PROGRAMME WHERE PROGRAMME_ID = ?";
            $result = $conn->execute_query($query,[$programmeId]);
            if (mysqli_affected_rows($conn)>0 ){
                sendJsonResponse(200,'Deleted','Programme deleted succesfully');
            }else{
                sendJsonResponse(400,'Try again',"Couldn't update timeslot");
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
            $isPractical = ($_POST['courseType'] =='false')? true : false;
            $isOptional = ($_POST['isOptional'] ?? '' =='true')? true : false;
            $optionalCourseId = $_POST['optionalCourseId'] ?? null;
            $isValid = validateCourse($fullname, $shortname);
            if ($isValid === 1) {
                $query = "INSERT INTO COURSE(PROGRAMME_ID, YEAR_NUMBER, LONG_NAME, SHORT_NAME, WEEKLY_LECTURES, ISPRACTICAL, ISOPTIONAL) VALUES(?, ?, ?, ?, ?, ?, ?)";
                $result = $conn->execute_query($query, [$programmeId, $yearId, $fullname, $shortname, $weeklyLectures, $isPractical, $isOptional]);
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
            $weeklyLectures = strtoupper($_POST['lectureCount'] ?? 0);
            $isPractical = ($_POST['courseType'] =='false')? true : false;
            $isOptional = ($_POST['isOptional'] =='true')? true : false;
            $optionalCourseId = $_POST['optionalCourseId'] ?? null;
            $isValid = validateCourse($fullname, $shortname);
            
            if ($isValid === 1) {
                $query = "UPDATE COURSE SET PROGRAMME_ID = ?, YEAR_NUMBER = ?, LONG_NAME = ?, SHORT_NAME = ?, WEEKLY_LECTURES = ?, ISPRACTICAL = ?, ISOPTIONAL = ? WHERE COURSE_ID = ?";
                $result = $conn->execute_query($query, [ $programmeId, $yearId, $fullname, $shortname, $weeklyLectures, $isPractical, $isOptional, $courseId]);
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
        if($formtype == 'map_course') {

            $course1 = (int) $_POST['course1'];
            $course2 = (int) $_POST['course2'];

            $query = "UPDATE COURSE SET OPTIONAL_ID = ? WHERE COURSE_ID = ?";
            $result = $conn->execute_query($query, [$course2, $course1]);

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
        if($formtype == 'add_division'){
            $departmentId = $_POST['departmentId'] ?? '';
            $programmeId = $_POST['programmeId'] ?? '';
            $yearId = $_POST['yearId'] ?? '';
            $fullname = strtoupper($_POST['fullname'] ?? '');
            $shortname = strtoupper($_POST['shortname'] ?? '');
            $weeklyLectures = $_POST['lectureCount'] ?? '';
            $isPractical = $_POST['isPractical'] ?? false;
            $isOptional = $_POST['isOptional'] ?? '';
            $optionalCourseId = $_POST['optionalCourseId'] ?? null;
            var_dump($_POST);
            $isValid = validateCourse($fullname, $shortname);
            if ($isValid === 1) {
                $query = "INSERT INTO COURSE(OPTIONAL_ID, LONG_NAME, SHORT_NAME, WEEKLY_LECTURES, ISPRACTICAL, ISOPTIONAL) VALUES(?, ?, ?, ?, ?, ?)";
                $result = $conn->execute_query($query, [$optionalCourseId, $fullname,  $shortname,  $weeklyLectures, $isPractical,  $isOptional]);
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
        elseif($formtype ==  'get_one_programme'){ 
                $programmeId = $_POST['programmeId'];
                $query = "SELECT * FROM PROGRAMME WHERE PROGRAMME_ID = ?;";
                $result = $conn->execute_query($query,[$programmeId]);
                // var_dump($result);
                if($result->num_rows>0){
                    $parsedResult = $result->fetch_assoc();
                    $newquery = "SELECT * FROM CONSISTS WHERE PROGRAMME_ID = ?;";
                    $newresult = $conn->execute_query($newquery,[$programmeId]);
                    $parsedResult['DURATION'] = $newresult->num_rows;
                    sendJsonResponse(200,"Programme found.",$parsedResult);
                    exit;
                }
                sendJsonResponse(404,"Programme not found.");
        }
        elseif ($formtype == 'update_programme') {
            $programmeId = $_POST['programmeId'] ?? '';
            $departmentId = $_POST['departmentId'] ?? '';
            $fullname = strtoupper($_POST['fullname'] ?? '');
            $shortname = strtoupper($_POST['shortname'] ?? '');
            $duration = (int)($_POST['duration'] ?? 0);

            $isValid = validateProgramme($fullname, $shortname);
            if ($isValid === 1) {
                mysqli_begin_transaction($conn);
                $query = " UPDATE PROGRAMME SET DEPARTMENT_ID = ?, LONG_NAME = ?, SHORT_NAME = ? WHERE PROGRAMME_ID = ?";

                $conn->execute_query($query, [$departmentId, $fullname, $shortname, $programmeId]);

                // Check/update CONSISTS according to duration
                $query = "SELECT COUNT(*) AS total FROM CONSISTS WHERE PROGRAMME_ID = ?";
                $result = $conn->execute_query($query, [$programmeId]);
                $currentDuration = (int)$result->fetch_assoc()['total'];

                if ($duration > $currentDuration) {
                    // Add missing years
                    for ($i = $currentDuration + 1; $i <= $duration; $i++) {
                        $query = "INSERT INTO CONSISTS(YEAR_NUMBER, PROGRAMME_ID) VALUES(?, ?)";
                        $conn->execute_query($query, [$i, $programmeId]);
                    }
                } elseif ($duration < $currentDuration) {
                    // Remove extra years
                    $query = "
                        DELETE FROM CONSISTS WHERE PROGRAMME_ID = ? AND YEAR_NUMBER > ?
                    ";
                    $conn->execute_query($query, [
                        $programmeId,
                        $duration
                    ]);
                }
                mysqli_commit($conn);
                sendJsonResponse(200, 'Updated', 'Programme updated successfully');

            } elseif ($isValid === 2) {
                sendJsonResponse(422, 'Contains Special Characters', 'The programme name contains invalid character.');

            } else {
                sendJsonResponse(400,'Try again', "Couldn't update Programme");
            }
        }
        elseif($formtype == 'delete_programme'){
            $programmeId = $_POST['programmeId'];
            $query = "DELETE FROM PROGRAMME WHERE PROGRAMME_ID = ?";
            $result = $conn->execute_query($query,[$programmeId]);
            if (mysqli_affected_rows($conn)>0 ){
                sendJsonResponse(200,'Deleted','Programme deleted succesfully');
            }else{
                sendJsonResponse(400,'Try again',"Couldn't update timeslot");
            }
        }        
    }
    

} catch (Throwable $e) {
    // Catch ALL exceptions/errors and return them as valid JSON
    sendJsonResponse(500, 'Server Error', $e->getMessage());
}

?>