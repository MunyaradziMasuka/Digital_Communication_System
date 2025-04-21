<?php
// Include database connection
require_once 'db.php';

// Function to authenticate a user
function authenticateUser($username, $password) {
    global $conn;
    
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT user_id, username, full_name, role, organization, department, is_active FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Check if user is active
        if ($user['is_active'] != 1) {
            return ['status' => false, 'message' => 'Account is inactive. Please contact administrator.'];
        }
        
        // Log the login
        logUserLogin($user['user_id']);
        
        return ['status' => true, 'user' => $user];
    } else {
        return ['status' => false, 'message' => 'Invalid username or password.'];
    }
}

// Function to register a new user
function registerUser($fullName, $email, $department, $password) {
    global $conn;
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['status' => false, 'message' => 'Email already exists. Please use a different email.'];
    }
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role, organization, department, is_active, created_at) 
                           VALUES (?, ?, ?, ?, 'Staff', 'ZAPF', ?, 1, NOW())");
    $stmt->bind_param("sssss", $email, $email, $password, $fullName, $department);
    
    if ($stmt->execute()) {
        return ['status' => true, 'message' => 'Account created successfully!'];
    } else {
        return ['status' => false, 'message' => 'Registration failed: ' . $conn->error];
    }
}

// Function to log user login
function logUserLogin($userId) {
    global $conn;
    
    // Update last_login timestamp
    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    // Could also add more detailed logging here if needed
    // e.g., insert into a login_logs table
}
?>