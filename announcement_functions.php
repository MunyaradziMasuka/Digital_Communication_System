<?php
require_once 'db.php';
require_once 'notification_functions.php';
require_once 'mail_functions.php'; // We'll create this next

// Function to create a new announcement
function createAnnouncement($senderEmail, $title, $content, $importance = 'normal', $sendEmail = false) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO announcements (sender_email, title, content, importance, send_email) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $senderEmail, $title, $content, $importance, $sendEmail);
    
    $result = $stmt->execute();
    $announcementId = $stmt->insert_id;
    $stmt->close();
    
    if ($result) {
        // Create notifications for all users
        $notificationType = "announcement";
        $notificationTitle = "New Announcement: " . $title;
        $notificationContent = substr($content, 0, 100) . (strlen($content) > 100 ? '...' : '');
        $link = "announcements.php?view=" . $announcementId;
        
        addNotificationForAll($notificationType, $notificationTitle, $notificationContent, $link);
        
        // Send email if requested
        if ($sendEmail) {
            sendAnnouncementEmails($announcementId, $title, $content, $importance);
        }
    }
    
    return $result ? $announcementId : false;
}

// Function to get all announcements
function getAnnouncements($limit = 10, $activeOnly = true) {
    global $conn;
    
    $activeCondition = $activeOnly ? "WHERE is_active = 1" : "";
    
    $query = "SELECT a.*, u.full_name as sender_name 
              FROM announcements a 
              LEFT JOIN users u ON a.sender_email = u.email 
              $activeCondition 
              ORDER BY created_at DESC 
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $announcements = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $announcements[] = $row;
        }
    }
    
    $stmt->close();
    return $announcements;
}

// Function to get a single announcement
function getAnnouncement($announcementId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT a.*, u.full_name as sender_name 
                           FROM announcements a 
                           LEFT JOIN users u ON a.sender_email = u.email 
                           WHERE a.announcement_id = ?");
    $stmt->bind_param("i", $announcementId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $announcement = null;
    
    if ($result && $result->num_rows > 0) {
        $announcement = $result->fetch_assoc();
    }
    
    $stmt->close();
    return $announcement;
}

// Function to mark an announcement as read by a user
function markAnnouncementAsRead($announcementId, $userEmail) {
    global $conn;
    
    // Check if already marked as read
    $checkStmt = $conn->prepare("SELECT id FROM announcement_reads 
                                WHERE announcement_id = ? AND user_email = ?");
    $checkStmt->bind_param("is", $announcementId, $userEmail);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $checkStmt->close();
    
    // If already read, return true
    if ($checkResult->num_rows > 0) {
        return true;
    }
    
    // Otherwise, mark as read
    $stmt = $conn->prepare("INSERT INTO announcement_reads (announcement_id, user_email) 
                           VALUES (?, ?)");
    $stmt->bind_param("is", $announcementId, $userEmail);
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

// Function to check if an announcement has been read by a user
function isAnnouncementReadByUser($announcementId, $userEmail) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id FROM announcement_reads 
                           WHERE announcement_id = ? AND user_email = ?");
    $stmt->bind_param("is", $announcementId, $userEmail);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $isRead = $result->num_rows > 0;
    
    $stmt->close();
    return $isRead;
}

// Function to count unread announcements for a user
function countUnreadAnnouncements($userEmail) {
    global $conn;
    
    $query = "SELECT COUNT(*) as count FROM announcements a 
              WHERE a.is_active = 1 
              AND NOT EXISTS (
                  SELECT 1 FROM announcement_reads ar 
                  WHERE ar.announcement_id = a.announcement_id 
                  AND ar.user_email = ?
              )";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $stmt->close();
    return $row['count'];
}