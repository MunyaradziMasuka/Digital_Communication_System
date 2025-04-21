<?php
require_once 'db.php';

// Function to send a message
function sendMessage($senderEmail, $recipientEmail, $subject, $messageText, $priority = 'normal') {
    global $conn;
    
    // Ensure emails are properly formatted
    $senderEmail = strtolower(trim($senderEmail));
    $recipientEmail = strtolower(trim($recipientEmail));
    
    $stmt = $conn->prepare("INSERT INTO messages (sender_email, recipient_email, subject, message_text, priority) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $senderEmail, $recipientEmail, $subject, $messageText, $priority);
    
    if ($stmt->execute()) {
        $messageId = $conn->insert_id;
        
        // Log initial status as "sent"
        $statusStmt = $conn->prepare("INSERT INTO message_status (message_id, status) VALUES (?, 'sent')");
        $statusStmt->bind_param("i", $messageId);
        $statusStmt->execute();
        
        return ['status' => true, 'message_id' => $messageId];
    } else {
        return ['status' => false, 'error' => $conn->error];
    }
}

// In message_functions.php
function getMessages($userEmail, $type = 'inbox', $filter = 'all') {
    global $conn;
    
    // Ensure the email is properly formatted for comparison
    $userEmail = strtolower(trim($userEmail));
    
    error_log("DEBUG: Getting messages for user: '$userEmail', type: '$type', filter: '$filter'");
    
    $query = "";
    if ($type == 'inbox') {
        // Use LIKE comparison for more flexibility
        $query = "SELECT m.*, 
                 (SELECT SUBSTRING(u.full_name, 1, 2) FROM users u WHERE u.email = m.sender_email) as sender_initial,
                 (SELECT u.full_name FROM users u WHERE u.email = m.sender_email) as sender_name,
                 (SELECT status FROM message_status WHERE message_id = m.message_id ORDER BY updated_at DESC LIMIT 1) as current_status
                 FROM messages m
                 WHERE LOWER(TRIM(m.recipient_email)) = ?";
        
        if ($filter == 'unread') {
            $query .= " AND m.is_read = 0";
        } else if ($filter == 'important') {
            $query .= " AND m.is_important = 1";
        }
    } else if ($type == 'sent') {
        // Same for sent messages
        $query = "SELECT m.*, 
                 (SELECT SUBSTRING(u.full_name, 1, 2) FROM users u WHERE u.email = m.recipient_email) as recipient_initial,
                 (SELECT u.full_name FROM users u WHERE u.email = m.recipient_email) as recipient_name,
                 (SELECT status FROM message_status WHERE message_id = m.message_id ORDER BY updated_at DESC LIMIT 1) as current_status
                 FROM messages m
                 WHERE LOWER(TRIM(m.sender_email)) = ?";
    }
    
    $query .= " ORDER BY m.created_at DESC";
    
    error_log("DEBUG: SQL Query: " . $query);
    error_log("DEBUG: Email parameter: '" . $userEmail . "'");
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("ERROR: Prepare statement failed: " . $conn->error);
        return [];
    }
    
    $stmt->bind_param("s", $userEmail);
    
    if (!$stmt->execute()) {
        error_log("ERROR: Execute failed: " . $stmt->error);
        return [];
    }
    
    $result = $stmt->get_result();
    error_log("DEBUG: Number of rows found: " . $result->num_rows);
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        // Add extra fields needed by JavaScript
        if ($type == 'inbox') {
            $row['sender_initial'] = strtoupper(substr($row['sender_name'] ?? $row['sender_email'], 0, 2));
        } else {
            $row['recipient_initial'] = strtoupper(substr($row['recipient_name'] ?? $row['recipient_email'], 0, 2));
        }
        $row['type'] = $type;
        $messages[] = $row;
    }
    
    error_log("DEBUG: Returning " . count($messages) . " messages");
    
    return $messages;
}

// Function to mark a message as read
function markMessageAsRead($messageId) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE messages SET is_read = 1, read_at = NOW() WHERE message_id = ?");
    $stmt->bind_param("i", $messageId);
    
    if ($stmt->execute()) {
        // Update status to "read"
        $statusStmt = $conn->prepare("INSERT INTO message_status (message_id, status) VALUES (?, 'read')");
        $statusStmt->bind_param("i", $messageId);
        $statusStmt->execute();
        
        return true;
    } else {
        return false;
    }
}

// Function to mark a message as important
function toggleMessageImportance($messageId) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE messages SET is_important = NOT is_important WHERE message_id = ?");
    $stmt->bind_param("i", $messageId);
    
    return $stmt->execute();
}

// Function to search for users
function searchUsers($searchTerm) {
    global $conn;
    
    $searchTerm = "%$searchTerm%";
    $stmt = $conn->prepare("SELECT email, full_name, department FROM users WHERE full_name LIKE ? OR email LIKE ? LIMIT 10");
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    return $users;
}

// Function to get user's online status
function getUserStatus($userEmail) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT is_online, last_active FROM user_status WHERE user_email = ?");
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return ['is_online' => 0, 'last_active' => null];
    }
}
// Add this function to message_functions.php
function normalizeEmailsInDatabase() {
    global $conn;
    
    // Fix recipient emails
    $stmt = $conn->prepare("UPDATE messages SET recipient_email = LOWER(TRIM(recipient_email))");
    $stmt->execute();
    
    // Fix sender emails
    $stmt = $conn->prepare("UPDATE messages SET sender_email = LOWER(TRIM(sender_email))");
    $stmt->execute();
    
    return true;
}
// Function to update user's online status
function updateUserStatus($userEmail, $isOnline = 1) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO user_status (user_email, is_online, last_active) 
                           VALUES (?, ?, NOW())
                           ON DUPLICATE KEY UPDATE is_online = ?, last_active = NOW()");
    $stmt->bind_param("sii", $userEmail, $isOnline, $isOnline);
    
    return $stmt->execute();
}

// Function to count unread messages
function countUnreadMessages($userEmail) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM messages WHERE recipient_email = ? AND is_read = 0");
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'];
}
?>