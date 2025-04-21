<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: home.php");
    exit;
}

// Include necessary files
require_once 'db.php';
require_once 'meeting_announcement_functions.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user information
    $user = $_SESSION['user'];
    $creatorEmail = strtolower(trim($user['username']));
    
    // Get form data
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $importance = $_POST['importance'];
    $expiresAt = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
    
    // Process recipients
    $recipientsInput = trim($_POST['recipients']);
    $recipientsArray = array_map('trim', explode(',', $recipientsInput));
    
    // Create the announcement
    $announcementId = createAnnouncement($creatorEmail, $title, $content, $importance, $expiresAt, $recipientsArray);
    
    if ($announcementId) {
        // Redirect to the announcement details page
        header("Location: announcements.php?view=" . $announcementId);
        exit;
    } else {
        // Handle error
        header("Location: announcements.php?error=failed");
        exit;
    }
} else {
    // If not a POST request, redirect to announcements page
    header("Location: announcements.php");
    exit;
}
?>