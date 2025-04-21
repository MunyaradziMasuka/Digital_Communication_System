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
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $meetingDate = $_POST['meeting_date'];
    $duration = (int) $_POST['duration'];
    
    // Process participants
    $participantsInput = trim($_POST['participants']);
    $participantsArray = array_map('trim', explode(',', $participantsInput));
    
    // Create the meeting
    $meetingId = createMeeting($creatorEmail, $title, $description, $location, $meetingDate, $duration, $participantsArray);
    
    if ($meetingId) {
        // Redirect to the meeting details page
        header("Location: meetings.php?view=" . $meetingId);
        exit;
    } else {
        // Handle error
        header("Location: meetings.php?error=failed");
        exit;
    }
} else {
    // If not a POST request, redirect to meetings page
    header("Location: meetings.php");
    exit;
}
?>