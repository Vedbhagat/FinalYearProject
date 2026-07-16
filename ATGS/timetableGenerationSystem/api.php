<?php

include 'dbConnect.php';
header('Content-Type: application/json');

$formtype = $_REQUEST['formType'] ?? '';
$user_id = $_SESSION['user_id'] ?? null;

// Response Jsonify
function sendJsonResponse($httpStatusCode, $httpStatusDescription,$jsonResponseBody){
    http_response_code($httpStatusCode);
    echo json_encode(['statusDescription'=> $httpStatusDescription, 'responseBody'=>$jsonResponseBody]);
    exit;
}

// Check login
function requireLogin($uid) {
    if (!$uid) {
        sendJsonResponse(401,'Login Required','');
        header("Location: login.php");
        exit;
    }
}

try {
    switch ($formtype) {
        
        case 'add_timeslot':
            echo 'Hello world';
            /*
            // echo $_POST['startTime'];
            // echo $_POST['endTime'];
            // echo $_POST['isbreak'];
            $startTime = $_POST['startTime'];
            $endTime = $_POST['endTime'];
            $isbreak = $_POST['isbreak'];
            if(false){
                // overlapping condition check
                sendJsonResponse(400,'Overlapping timeslot','');
            }
            else{
                $query = "INSERT INTO TIMESLOT(START_TIME,END_TIME,ISBREAK) VALUES(?,?,?)";
                $result = $conn->execute_query($query,[$startTime,$endTime,$isbreak]);
                if (mysqli_affected_rows($conn)>0 ){
                    sendJsonResponse(201,'Created','');
                }
                else{
                    sendJsonResponse(400,'Try again','');
                }
            }    
            break;
        case 'get_one_timeslot': break;
        case 'get_all_timeslot': break;
        case 'update_timeslot': break;
        case 'delete_timeslot': break;*/
    }

} catch (mysqli_sql_exception $e) {
    // http_response_code(500);
    // echo json_encode([
    //     'status' => 'Database error', 
    //     'message' => $e->getMessage(),
    // ]);
}
?>