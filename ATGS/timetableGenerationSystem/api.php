<?php

use LDAP\Result;

include_once 'dbConnect.php';
header('Content-Type: application/json');

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
    $containsSpecChar = preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/',trim($str)) === 1;
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
    switch ($formtype) {
        case 'getMinimalJson':
            $generatedJSON = [
                "timeslots"  => [],
                "classrooms" => [],
                "departments" => []
            ];
            $timeslot = "SELECT slot_id, Start_time, End_time, Slot_type FROM TIMESLOT;";
            $classroom = "SELECT Classroom_id, floor_number, room_number, capacity FROM CLASSROOM;";
            $department = "SELECT Department_id, Long_name FROM DEPARTMENT;";
            $teacher = "SELECT teacher_id, Department_id, first_name, last_name FROM TEACHER;";
            $programme = "SELECT programme_id, Department_id, long_name, short_name FROM PROGRAMME;";
            $division = "SELECT division_id, programme_id, NAME FROM DIVISION;";
            $course = "SELECT course_id, programme_id, long_name, short_name, isPractical FROM COURSE;";

            $result = mysqli_query($conn, $timeslot);
            while ($row = mysqli_fetch_assoc($result)) {
                $generatedJSON["timeslots"][] = $row;
            }
            // Classrooms
            $result = mysqli_query($conn, $classroom);
            while ($row = mysqli_fetch_assoc($result)) {
                $generatedJSON["classrooms"][] = $row;
            }
            // Departments
            $deptIndex = [];
            $result = mysqli_query($conn, $department);
            while ($row = mysqli_fetch_assoc($result)) {
                $row["teachers"] = [];
                $row["programmes"] = [];
                $generatedJSON["departments"][] = $row;
                $deptIndex[$row["Department_id"]] =
                    count($generatedJSON["departments"]) - 1;
            }
            // Teachers
            $result = mysqli_query($conn, $teacher);
            while ($row = mysqli_fetch_assoc($result)) {
                $deptId = $row["Department_id"];
                if (isset($deptIndex[$deptId])) {
                    $generatedJSON["departments"]
                        [$deptIndex[$deptId]]
                        ["teachers"][] = $row;
                }
            }
            // Programmes
            $programmeIndex = [];
            $result = mysqli_query($conn, $programme);
            while ($row = mysqli_fetch_assoc($result)) {
                $deptId = $row["Department_id"];
                $row["divisions"] = [];
                $row["courses"] = [];
                if (isset($deptIndex[$deptId])) {
                    $generatedJSON["departments"]
                        [$deptIndex[$deptId]]
                        ["programmes"][] = $row;
                    $progPosition = count(
                        $generatedJSON["departments"]
                        [$deptIndex[$deptId]]
                        ["programmes"]
                    ) - 1;
                    $programmeIndex[$row["programme_id"]] = [
                        "dept" => $deptIndex[$deptId],
                        "prog" => $progPosition
                    ];
                }
            }
            // Divisions
            $result = mysqli_query($conn, $division);
            while ($row = mysqli_fetch_assoc($result)) {
                $progId = $row["programme_id"];
                if (isset($programmeIndex[$progId])) {
                    $loc = $programmeIndex[$progId];
                    $generatedJSON["departments"]
                        [$loc["dept"]]
                        ["programmes"]
                        [$loc["prog"]]
                        ["divisions"][] = $row;
                }
            }
            // Courses
            $result = mysqli_query($conn, $course);
            while ($row = mysqli_fetch_assoc($result)) {
                $progId = $row["programme_id"];
                if (isset($programmeIndex[$progId])) {
                    $loc = $programmeIndex[$progId];
                    $generatedJSON["departments"]
                        [$loc["dept"]]
                        ["programmes"]
                        [$loc["prog"]]
                        ["courses"][] = $row;
                }
            }
            header('Content-Type: application/json');
            sendJsonResponse(200, "The Minimal JSON generated", $generatedJSON);
            break;
        case 'load_existing':
            $tablename = $_POST['tablename'];
            $query = "SELECT * FROM ". $tablename;
            $result = $conn->execute_query($query);
            $parsedResult = $result->fetch_all();
            sendJsonResponse(200,'Existing Data of '.$tablename.' table',$parsedResult);
            break;
        case 'add_timeslot':
            $startTime = $_POST['startTime'] ?? '';
            $endTime = $_POST['endTime'] ?? '';
            $slotType = $_POST['slotType'] ?? "lecture";

            $isValid = validateTimeslot($startTime, $endTime, $slotType);

            if ($isValid === 1) {
                $query = "INSERT INTO TIMESLOT(START_TIME, END_TIME, WEEK_DAY, SLOT_TYPE) VALUES(?,?,?,?)";
                for($i=1; $i < 7; $i++){
                    $result = $conn->execute_query($query, [$startTime, $endTime, $i, $slotType]);
                }
                if (mysqli_affected_rows($conn) == 6) {
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
            break;
        
        case 'get_one_timeslot': 
            $slotId = $_POST['slotId'];
            $query = "SELECT * FROM timeslot WHERE slot_id = ?;";
            $result = $conn->execute_query($query,[$slotId]);
            if($result->num_rows>0){
                sendJsonResponse(200,"Timeslot found.",$result->fetch_assoc());
                exit;
            }
            sendJsonResponse(400,"Timeslot NOT found.");
            break;
        case 'update_timeslot':
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
            break;
        case 'delete_timeslot':
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
            break;
        

        
        
        
        
        case 'add_classroom':
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
            break;
        case 'get_one_classroom': 
            $classroomId = $_POST['classroomId'];
            $query = "SELECT * FROM CLASSROOM WHERE classroom_id = ?;";
            $result = $conn->execute_query($query,[$classroomId]);
            if($result->num_rows>0){
                sendJsonResponse(200,"Classroom found.",$result->fetch_assoc());
                exit;
            }
            sendJsonResponse(404,"Classroom not found.");
            break;
        case 'update_classroom':
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
            break;
        case 'delete_classroom':
            $classroomId = $_POST['classroomId'];
            $query = "DELETE FROM CLASSROOM WHERE CLASSROOM_ID = ?";
            $result = $conn->execute_query($query,[$classroomId]);
            if (mysqli_affected_rows($conn)>0 ){
                sendJsonResponse(200,'Deleted','Classroom deleted succesfully');
            }else{
                sendJsonResponse(400,'Try again',"Couldn't delete classroom");
            }
            break;
        

        
        
        
        
        case 'add_department':
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
            break;
        case 'get_one_department': 
            $departmentId = $_POST['departmentId'];
            $query = "SELECT * FROM DEPARTMENT WHERE DEPARTMENT_ID = ?;";
            $result = $conn->execute_query($query,[$departmentId]);
            if($result->num_rows>0){
                sendJsonResponse(200,"Classroom found.",$result->fetch_assoc());
                exit;
            }
            sendJsonResponse(404,"Classroom not found.");
            break;
        case 'update_department':
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
            break;
        case 'delete_department':
            $departmentId = $_POST['departmentId'];
            $query = "DELETE FROM DEPARTMENT WHERE DEPARTMENT_ID = ?";
            $result = $conn->execute_query($query,[$departmentId]);
            if (mysqli_affected_rows($conn)>0 ){
                sendJsonResponse(200,'Deleted','Department deleted succesfully');
            }else{
                sendJsonResponse(400,'Try again',"Couldn't update timeslot");
            }
            break;





        case 'add_teacher':
    
            //$_POST['teacherId'] ?? '';
            $deptId = $_POST['departmentDropdown'] ?? '';
            $isPartTime = $_POST['isPartTime'] ?? '';
            $availabilityArray = [];
            $monOn = $_POST['monday'] ?? '';
            if($isPartTime){
                if($monOn){
                    $monIn = $_POST['punchIn_monday'] ?? '';
                    $monOut = $_POST['punchOut_monday'] ?? '';
                    $availabilityArray[] = [$monIn,$monOut];
                }
                $tueOn = $_POST['tuesday'] ?? '';
                if($tueOn){
                    $tueIn = $_POST['punchIn_tuesday'] ?? '';
                    $tueOut = $_POST['punchOut_tuesday'] ?? '';
                    $availabilityArray[] = [$tueIn,$tueOut];
                }
                $wedOn = $_POST['wednesday'] ?? '';
                if($wedOn){
                    $wedIn = $_POST['punchIn_wednesday'] ?? '';
                    $wedOut = $_POST['punchOut_wednesday'] ?? '';
                    $availabilityArray[] = [$wedIn,$wedOut];
                }
                $thuOn = $_POST['thursday'] ?? '';
                if($thuOn){
                    $thuIn = $_POST['punchIn_thursday'] ?? '';
                    $thuOut = $_POST['punchOut_thursday'] ?? '';
                    $availabilityArray[] = [$thuIn,$thuOut];
                    }
                    $friOn = $_POST['friday'] ?? '';
                if($friOn){
                    $friIn = $_POST['punchIn_friday'] ?? '';
                    $friOut = $_POST['punchOut_friday'] ?? '';
                    $availabilityArray[] = [$friIn,$friOut];
                }
                $satOn = $_POST['saturday'] ?? '';
                if($satOn){
                    $satIn = $_POST['punchIn_saturday'] ?? '';
                    $satOut = $_POST['punchOut_saturday'] ?? '';
                }
            }
            print_r ($availabilityArray);

            if($isPartTime){
                var_dump($_POST);
                print_r($_POST);
            }
            /*
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
            }*/
            break;
        case 'get_one_teacher':break;
        case 'update_teacher':break;
        case 'delete_teacher':
            $teacherId = $_POST['teacherId'];
            $query = "DELETE FROM TEACHER WHERE TEACHER_ID = ?";
            $result = $conn->execute_query($query,[$teacherId]);
            if (mysqli_affected_rows($conn)>0 ){
                sendJsonResponse(200,'Deleted','Teacher deleted succesfully');
            }else{
                sendJsonResponse(400,'Try again',"Couldn't update timeslot");
            }
            break;
        
    }

} catch (Throwable $e) {
    // Catch ALL exceptions/errors and return them as valid JSON
    sendJsonResponse(500, 'Server Error', $e->getMessage());
}

?>