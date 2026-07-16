<?php

require_once 'dbConnect.php';
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

//REGISTRATION
if ($action == 'register') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email    = $_POST['email'] ?? '';

    //Cancel on empty Field
    if (empty($username) || empty($password) || empty($email)) {
        echo json_encode(['status' => 'error', 'statusCode' => 412,'message' => 'All fields are required for registration.']);
        exit;
    }

    //Check if user exists
    $query = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
    $result = $conn->execute_query($query,[$username, $email]);
    if ($result->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['status' => 'error', 'statusCode' => 409, 'message' => 'Username or Email already taken.']);
        exit;
    }

    // Hash password and insert
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $query = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    
    if ($conn->execute_query($query,[$username, $email, $hashed_password])) {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['username'] = $username;
        http_response_code(201);
        echo json_encode(['status' => 'success', 'statusCode' => 201, 'message' => 'Registration successful!']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'statusCode' => 500, 'message' => 'Registration failed.']);
    }
    exit;
}

//LOGIN 
if ($action == 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $query = $conn->prepare("SELECT user_id, password_hash FROM users WHERE username = ?");
    $result = $conn->execute_query($query,[$username]);
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Start session if config.php didn't (it should)
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $username;
        echo json_encode(['status' => 'success', 'message' => 'Login successful!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials.']);
    }
    exit;
}

//LOGOUT
if ($action == 'logout') {
    session_unset();
    session_destroy();
    echo json_encode(['status' => 'success']);
    exit;
}

//STATUS CHECK
if ($action == 'status') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode(['logged_in' => true, 'username' => $_SESSION['username']]);
    } else {
        echo json_encode(['logged_in' => false]);
    }
    exit;
}
