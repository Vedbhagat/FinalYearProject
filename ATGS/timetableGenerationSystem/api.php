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
function validateTimeslot($startTime, $endTime, $isBreak, $slotId = null) {
    global $conn;

    $parsedStartTime = (new DateTime($startTime))->getTimestamp();
    $parsedEndTime = (new DateTime($endTime))->getTimestamp();
    if ($parsedStartTime > $parsedEndTime) {
        return 2; // Inverted timeslot
    }
    $interval = ($parsedEndTime - $parsedStartTime) / 60;
    if ($interval >= 15) {
        $query = "SELECT slot_Id, start_time, end_time, isbreak FROM TIMESLOT WHERE ? < end_Time AND ? > start_Time AND SLOT_ID != ? LIMIT 1";
        $result = $conn->execute_query($query, [$startTime, $endTime, $slotId ?? 0]);
        if ($result->num_rows > 0) {
            return 3; // Overlapping Timeslot
        }
    } else {
        return 4; // Too Small slot
    }
    return 1;
}
function validateClassroom($floor,$room,$capacity){
    global $conn;
    if($capacity<60) return 2; //too small classroom

    $query = 'SELECT CLASSROOM_ID FROM CLASSROOM WHERE FLOOR_NUMBER = ?';
    $result = $conn->execute_query($query,[$floor]);
    if($result->num_rows>2) return 3; //Too many rooms at a floor

    $query = 'SELECT * FROM CLASSROOM WHERE ROOM_NUMBER = ?';
    $result = $conn->execute_query($query,[$room]);
    if($result->num_rows>0) return 4; //room number already exists

    return 1;
}


try {
    switch ($formtype) {
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
            $isbreak = $_POST['isbreak'] ?? "No";

            $isValid = validateTimeslot($startTime, $endTime, $isbreak);

            if ($isValid === 1) {
                $query = "INSERT INTO TIMESLOT(START_TIME, END_TIME, ISBREAK) VALUES(?, ?, ?)";
                $result = $conn->execute_query($query, [$startTime, $endTime, $isbreak]);
                if (mysqli_affected_rows($conn) > 0) {
                    sendJsonResponse(201, 'Created', 'Timeslot created successfully');
                } else {
                    sendJsonResponse(400, 'Try again', "Couldn't create timeslot");
                }
            } elseif ($isValid === 2) {
                sendJsonResponse(422, 'Inverted Timeslot', 'The Start Time is greater than the End Time.');
            } elseif ($isValid === 3) {
                sendJsonResponse(409, 'Overlapping Timeslot', 'The timeslot overlaps with an existing record.');
            } elseif ($isValid === 4) {
                sendJsonResponse(422, 'Too Small Slot', 'Timeslot must be at least 15 minutes.');
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
            $isbreak = $_POST['isbreak'] ?? "No";
            $isValid = validateTimeslot($startTime, $endTime, $isbreak, $slotId);
            if ($isValid === 1) {
                $query = "UPDATE TIMESLOT SET START_TIME = ?, END_TIME = ?, ISBREAK = ? WHERE SLOT_ID = ?";
                $result = $conn->execute_query($query, [$startTime, $endTime, $isbreak, $slotId]);
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
            $query = "DELETE FROM TIMESLOT WHERE SLOT_ID = ?";
            $result = $conn->execute_query($query,[$slotId]);
            if (mysqli_affected_rows($conn)>0 ){
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
                sendJsonResponse(200,"Timeslot found.",$result->fetch_assoc());
                exit;
            }
            sendJsonResponse(404,"Timeslot not found.");
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
                sendJsonResponse(400,'Try again',"Couldn't update timeslot");
            }
            break;
        


        case 'add_department':break;
        case 'get_one_department':break;
        case 'update_department':break;
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



        case 'add_teacher':break;
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