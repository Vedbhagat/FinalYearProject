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
    $containsNumber = preg_match('/[0-9]/',trim($str)) === 1;
    $containsSpecChar = preg_match('/[\'^£$%&*()!}{@#~?><>,|=_+¬-]/',trim($str)) === 1;
    return !($containsNumber || $containsSpecChar);
}
function validateClassroom($floor,$room,$capacity){
    global $conn;
    if($capacity<60) return 2; //too small classroom

    $query = 'SELECT CLASSROOM_ID FROM CLASSROOM WHERE FLOOR_NUMBER = ?';
    $result = $conn->execute_query($query,[$floor]);
    if($result->num_rows>2) return 3; //Too many rooms at a floor

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
        elseif($formtype == 'programme'){
            $sql = "SELECT PROGRAMME_ID, LONG_NAME, SHORT_NAME FROM PROGRAMME ORDER BY LONG_NAME";
            $result = mysqli_query($conn, $sql);
            $response['programmes'] = [];
            while($row = mysqli_fetch_assoc($result)) {$response['programmes'][] = $row;}
        }
        elseif($formtype == 'classroom'){
            $sql = "SELECT CLASSROOM_ID, ROOM_NUMBER FROM CLASSROOM ORDER BY ROOM_NUMBER";
            $result = mysqli_query($conn, $sql);
            $response['classrooms'] = [];
            while($row = mysqli_fetch_assoc($result)) {$response['classrooms'][] = $row;}
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

            $isValid = validateClassroom($floor, $room, $capacity);
            if ($isValid === 1) {
                $query = "INSERT INTO CLASSROOM(FLOOR_NUMBER, ROOM_NUMBER, CAPACITY) VALUES(?, ?, ?)";
                $result = $conn->execute_query($query, [$floor, $room, $capacity]);
                if (mysqli_affected_rows($conn) > 0) {
                    sendJsonResponse(201, 'Created', 'Classroom created successfully');
                } else {
                    sendJsonResponse(400, 'Try again', "Couldn't create Classroom");
                }
            }elseif($isValid === 2){
                sendJsonResponse(422,'Too small classroom','The classroom capacity is too low');
            }elseif($isValid === 3){
                sendJsonResponse(422,'Too many rooms','There are too many rooms at a single floor');        
            }elseif($isValid === 4){
                sendJsonResponse(422,'Room number exists','The entered room number already exists');        
            }else {
                sendJsonResponse(429, 'Invalid Classroom', 'Entered values are not valid');
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

            $isValid = validateClassroom($floor, $room, $capacity, $classroomId);

            if ($isValid === 1) {
                $query = "UPDATE CLASSROOM SET FLOOR_NUMBER = ?, ROOM_NUMBER = ?, CAPACITY = ? WHERE CLASSROOM_ID = ?";
                $result = $conn->execute_query($query, [$floor, $room, $capacity, $classroomId]);
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
                sendJsonResponse(422, 'Room number exists', 'The entered room number already exists');
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
        if ($formtype == 'add_teacher_old') {
            $deptId = $_POST['departmentDropdown'] ?? '';
            $fname = trim($_POST['fname'] ?? '');
            $lname = trim($_POST['lname'] ?? '');
            $isPartTime = isset($_POST['isPartTime']) ? 1 : 0;

            if (!isValidName($fname) || !isValidName($lname)) {
                sendJsonResponse(400, "Bad Request", "Invalid teacher name.");
                exit;
            }
            $days = [
                'monday' => 'MONDAY',
                'tuesday' => 'TUESDAY',
                'wednesday' => 'WEDNESDAY',
                'thursday' => 'THURSDAY',
                'friday' => 'FRIDAY',
                'saturday' => 'SATURDAY'
            ];
            mysqli_begin_transaction($conn);
        
            $query = "INSERT INTO TEACHER (DEPARTMENT_ID,FIRST_NAME,LAST_NAME,ISPARTTIME) VALUES (?, ?, ?, ?)";

            $conn->execute_query($query, [$deptId,$fname,$lname,$isPartTime]);
            $teacherId = mysqli_insert_id($conn);
            if ($isPartTime) {
                foreach ($days as $dayKey => $weekday) {
                    if (!isset($_POST[$dayKey]))
                        continue;
                    $startSlot = (int)$_POST["punchIn_$dayKey"];
                    $endSlot   = (int)$_POST["punchOut_$dayKey"];
                    if ($startSlot > $endSlot) {
                        throw new Exception("$weekday: Punch In cannot be after Punch Out.");
                    }
                    for ($slot = $startSlot; $slot <= $endSlot; $slot++) {
                        $conn->execute_query(
                            "INSERT INTO AVAILABILITY (TEACHER_ID,SLOT_ID,WEEKDAY,STATUS) VALUES (?, ?, ?, 'AVAILABLE')",
                            [$teacherId,$slot,$weekday]
                        );
                    }
                }
            }
            else {
                $slotResult = $conn->execute_query(
                    "SELECT SLOT_ID FROM TIMESLOT ORDER BY SLOT_ID"
                );
                $slots = [];
                while ($row = mysqli_fetch_assoc($slotResult)) {
                    $slots[] = $row['SLOT_ID'];
                }
                foreach ($days as $weekday) {
                    foreach ($slots as $slotId) {
                        $conn->execute_query(
                            "INSERT INTO AVAILABILITY (TEACHER_ID,SLOT_ID,WEEKDAY,STATUS) VALUES (?, ?, ?, 'AVAILABLE')",
                            [$teacherId,$slotId,$weekday]
                        );
                    }
                }
            }
            mysqli_commit($conn);
            sendJsonResponse(201,"Created","Teacher created successfully.");         
        }
        elseif ($formtype == 'add_teacher') {

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
        'monday'    => 'MONDAY',
        'tuesday'   => 'TUESDAY',
        'wednesday' => 'WEDNESDAY',
        'thursday'  => 'THURSDAY',
        'friday'    => 'FRIDAY',
        'saturday'  => 'SATURDAY'
    ];

    mysqli_begin_transaction($conn);

    try {

        // Insert Teacher
        $query = "INSERT INTO TEACHER
                  (DEPARTMENT_ID, FIRST_NAME, LAST_NAME, ISPARTTIME)
                  VALUES (?, ?, ?, ?)";

        $conn->execute_query($query, [
            $deptId,
            $fname,
            $lname,
            $isPartTime
        ]);

        $teacherId = mysqli_insert_id($conn);

        if (!$teacherId) {
            throw new Exception("Unable to create teacher.");
        }

        // ============================
        // PART TIME TEACHER
        // ============================
        if ($isPartTime) {

            foreach ($days as $dayKey => $weekday) {

                if (!isset($_POST[$dayKey])) {
                    continue;
                }

                $startSlot = (int)$_POST["punchIn_$dayKey"];
                $endSlot   = (int)$_POST["punchOut_$dayKey"];

                if ($startSlot > $endSlot) {
                    throw new Exception("$weekday : Punch In must be before Punch Out.");
                }

                // Fetch actual slot ids
                $slotResult = $conn->execute_query(
                    "SELECT SLOT_ID
                     FROM TIMESLOT
                     WHERE SLOT_ID BETWEEN ? AND ?
                     ORDER BY SLOT_ID",
                    [$startSlot, $endSlot]
                );

                while ($slot = $slotResult->fetch_assoc()) {

                    $conn->execute_query(
                        "INSERT INTO AVAILABILITY
                        (TEACHER_ID,SLOT_ID,WEEKDAY,STATUS)
                        VALUES (?,?,?,'AVAILABLE')",
                        [
                            $teacherId,
                            $slot['SLOT_ID'],
                            $weekday
                        ]
                    );
                }
            }

        }

        // ============================
        // FULL TIME TEACHER
        // ============================
        else {

            $slotResult = $conn->execute_query(
                "SELECT SLOT_ID
                 FROM TIMESLOT
                 ORDER BY SLOT_ID"
            );

            $slots = [];

            while ($row = $slotResult->fetch_assoc()) {
                $slots[] = $row['SLOT_ID'];
            }

            foreach ($days as $weekday) {

                foreach ($slots as $slotId) {

                    $conn->execute_query(
                        "INSERT INTO AVAILABILITY
                        (TEACHER_ID,SLOT_ID,WEEKDAY,STATUS)
                        VALUES (?,?,?,'AVAILABLE')",
                        [
                            $teacherId,
                            $slotId,
                            $weekday
                        ]
                    );

                }

            }

        }

        mysqli_commit($conn);

        sendJsonResponse(
            201,
            "Created",
            "Teacher created successfully."
        );

    } catch (Exception $e) {

        mysqli_rollback($conn);

        sendJsonResponse(
            500,
            "Server Error",
            $e->getMessage()
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
        elseif($formtype == 'update_teacher'){}
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
    

    

} catch (Throwable $e) {
    // Catch ALL exceptions/errors and return them as valid JSON
    sendJsonResponse(500, 'Server Error', $e->getMessage());
}

?>