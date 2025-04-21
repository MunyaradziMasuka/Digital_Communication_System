<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// Include necessary files
require_once 'db.php';
require_once 'meeting_announcement_functions.php';

// Get user information
$user = $_SESSION['user'];
$userEmail = strtolower(trim($user['username']));

// Determine which announcements to show (all, created)
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$announcements = getAnnouncements($userEmail, $type);

// Check if viewing a specific announcement
$viewAnnouncement = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $viewAnnouncement = getAnnouncementDetails($_GET['view']);
    
    // Mark as viewed
    markAnnouncementAsViewed($_GET['view'], $userEmail);
    
    // Refresh data after marking as viewed
    $viewAnnouncement = getAnnouncementDetails($_GET['view']);
}

// Handle announcement response
if (isset($_POST['respond_announcement'])) {
    $announcementId = $_POST['announcement_id'];
    $response = $_POST['response'];
    
    respondToAnnouncement($announcementId, $userEmail, $response);
    
    // Redirect to avoid form resubmission
    header("Location: announcements.php?view=" . $announcementId);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZAPF-Connect | Announcements</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="styles/dashboard.css">
    <style>
        /* Add announcement-specific styles here - similar to meetings.php */
        .announcement-list {
            display: grid;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .announcement-item {
            background-color: #fff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.2s ease;
            border-left: 4px solid #4f46e5;
        }
        
        .announcement-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .announcement-item.urgent {
            border-left-color: #dc2626;
        }
        
        .announcement-item.high {
            border-left-color: #ea580c;
        }
        
        .announcement-item.normal {
            border-left-color: #4f46e5;
        }
        
        .announcement-item.low {
            border-left-color: #65a30d;
        }
        
        .announcement-title {
            font-weight: 600;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .announcement-badge {
            font-size: 0.7em;
            padding: 2px 8px;
            border-radius: 12px;
            text-transform: uppercase;
        }
        
        .badge-urgent {
            background-color: #fef2f2;
            color: #dc2626;
        }
        
        .badge-high {
            background-color: #fff7ed;
            color: #ea580c;
        }
        
        .badge-normal {
            background-color: #eef2ff;
            color: #4f46e5;
        }
        
        .badge-low {
            background-color: #f0fdf4;
            color: #65a30d;
        }
        
        .announcement-meta {
            display: flex;
            gap: 15px;
            font-size: 0.9em;
            color: #666;
            margin-bottom: 10px;
        }
        
        .announcement-detail {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .announcement-preview {
            margin-top: 10px;
            color: #4b5563;
            font-size: 0.95em;
            line-height: 1.5;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .announcement-view {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 15px;
        }
        
        .announcement-header-title h2 {
            margin: 0 0 5px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .announcement-header-meta {
            color: #666;
            margin-top: 5px;
        }
        
        .announcement-content {
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .response-form {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #f3f4f6;
        }
        
        .response-form h3 {
            margin-bottom: 15px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
            margin-bottom: 15px;
        }
        
        .submit-btn {
            background-color: #4f46e5;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .submit-btn:hover {
            background-color: #4338ca;
        }
        
        .tracking-info {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #f3f4f6;
        }
        
        .tracking-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .tracking-table th {
            text-align: left;
            padding: 10px;
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .tracking-table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 70%;
            max-width: 700px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .close {
            font-size: 1.5em;
            cursor: pointer;
        }
        
        .close:hover {
            color: #4f46e5;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar (reuse from dashboard.php) -->
        <!-- Main content -->
        <div class="main-content">
            <div class="main-header">
                <div class="header-title">
                    <h1>Announcements</h1>
                    <div class="header-subtitle">Company-wide announcements and updates</div>
                </div>
                <div class="header-actions">
                    <button id="createAnnouncementBtn" class="action-button">
                        <i class="fas fa-plus"></i> New Announcement
                    </button>
                </div>
            </div>
            
            <div class="content-tabs">
                <div class="tab <?php echo $type == 'all' ? 'active' : ''; ?>">
                    <a href="announcements.php?type=all">All Announcements</a>
                </div>
                <div class="tab <?php echo $type == 'created' ? 'active' : ''; ?>">
                    <a href="announcements.php?type=created">Created by Me</a>
                </div>
            </div>
            
            <div class="content-container">
                <?php if ($viewAnnouncement): ?>
                    <!-- Announcement details view -->
                    <div class="announcement-view">
                        <div class="announcement-header">
                            <div class="announcement-header-title">
                                <h2>
                                    <?php echo htmlspecialchars($viewAnnouncement['title']); ?>
                                    <span class="announcement-badge badge-<?php echo $viewAnnouncement['importance']; ?>">
                                        <?php echo ucfirst($viewAnnouncement['importance']); ?>
                                    </span>
                                </h2>
                                <div class="announcement-header-meta">
                                    Posted by <?php echo htmlspecialchars($viewAnnouncement['creator_email']); ?> on 
                                    <?php echo date('M d, Y h:i A', strtotime($viewAnnouncement['created_at'])); ?>
                                </div>
                            </div>
                            <div class="announcement-actions">
                                <a href="announcements.php" class="back-button"><i class="fas fa-arrow-left"></i> Back to List</a>
                            </div>
                        </div>
                        
                        <div class="announcement-content">
                            <?php echo nl2br(htmlspecialchars($viewAnnouncement['content'])); ?>
                        </div>
                        
                        <?php if ($viewAnnouncement['creator_email'] != $userEmail): ?>
                        <div class="response-form">
                            <h3>Your Response</h3>
                            <form method="post" action="announcements.php">
                                <input type="hidden" name="announcement_id" value="<?php echo $viewAnnouncement['announcement_id']; ?>">
                                <textarea name="response" class="form-control" rows="4" placeholder="Enter your response here..."></textarea>
                                <button type="submit" name="respond_announcement" value="1" class="submit-btn">Submit Response</button>
                            </form>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($viewAnnouncement['creator_email'] == $userEmail): ?>
                        <div class="tracking-info">
                            <h3>Tracking Information</h3>
                            <table class="tracking-table">
                                <thead>
                                    <tr>
                                        <th>Recipient</th>
                                        <th>Viewed</th>
                                        <th>Response</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($viewAnnouncement['tracking'] as $track): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($track['full_name'] ?? $track['user_email']); ?></td>
                                        <td>
                                            <?php if ($track['viewed_at']): ?>
                                                <?php echo date('M d, Y h:i A', strtotime($track['viewed_at'])); ?>
                                            <?php else: ?>
                                                Not viewed yet
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($track['responded_at']): ?>
                                                <span class="view-response" data-response="<?php echo htmlspecialchars($track['response']); ?>">
                                                    View Response
                                                </span>
                                            <?php else: ?>
                                                No response yet
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Announcement list view -->
                    <div class="announcement-list">
                        <?php if (empty($announcements)): ?>
                            <div class="empty-message">No announcements found.</div>
                        <?php else: ?>
                            <?php foreach ($announcements as $announcement): ?>
                                <div class="announcement-item <?php echo $announcement['importance']; ?>" 
                                     onclick="window.location='announcements.php?view=<?php echo $announcement['announcement_id']; ?>'">
                                    <div class="announcement-title">
                                        <?php echo htmlspecialchars($announcement['title']); ?>
                                        <span class="announcement-badge badge-<?php echo $announcement['importance']; ?>">
                                            <?php echo ucfirst($announcement['importance']); ?>
                                        </span>
                                    </div>
                                    <div class="announcement-meta">
                                        <div class="announcement-detail">
                                            <i class="fas fa-user"></i>
                                            <?php echo htmlspecialchars($announcement['creator_email']); ?>
                                        </div>
                                        <div class="announcement-detail">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('M d, Y', strtotime($announcement['created_at'])); ?>
                                        </div>
                                    </div>
                                    <div class="announcement-preview">
                                        <?php echo htmlspecialchars(substr($announcement['content'], 0, 150)) . (strlen($announcement['content']) > 150 ? '...' : ''); ?>
                                    </div>
                                    
                                    <?php if ($type == 'created'): ?>
                                        <div class="tracking-summary">
                                            Views: <?php echo $announcement['view_count']; ?> | 
                                            Responses: <?php echo $announcement['response_count']; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="response-status">
                                            <?php if ($announcement['viewed_at']): ?>
                                                Viewed on <?php echo date('M d, Y', strtotime($announcement['viewed_at'])); ?>
                                            <?php else: ?>
                                                Not viewed yet
                                            <?php endif; ?>
                                            
                                            <?php if ($announcement['responded_at']): ?>
                                                | Responded
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Response view modal -->
                <div id="responseModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Response Details</h3>
                            <span class="close">&times;</span>
                        </div>
                        <div class="modal-body">
                            <div id="responseContent"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Create Announcement Modal -->
                <div id="createAnnouncementModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Create New Announcement</h2>
                            <span class="close">&times;</span>
                        </div>
                        <div class="modal-body">
                            <form action="create_announcement.php" method="post" id="announcementForm">
                                <div class="form-group">
                                    <label for="title">Announcement Title</label>
                                    <input type="text" id="title" name="title" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="content">Content</label>
                                    <textarea id="content" name="content" class="form-control" rows="6" required></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="importance">Importance Level</label>
                                    <select id="importance" name="importance" class="form-control">
                                        <option value="low">Low</option>
                                        <option value="normal" selected>Normal</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="expires_at">Expires At (Optional)</label>
                                    <input type="datetime-local" id="expires_at" name="expires_at" class="form-control">
                                </div>
                                
                                <div class="form-group">
                                    <label for="recipients">Recipients (Email addresses, comma separated)</label>
                                    <textarea id="recipients" name="recipients" class="form-control" rows="3" required></textarea>
                                </div>
                                
                                <button type="submit" class="submit-btn">Post Announcement</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Modal functionality
        const announcementModal = document.getElementById("createAnnouncementModal");
        const announcementBtn = document.getElementById("createAnnouncementBtn");
        const announcementClose = announcementModal.querySelector(".close");
        
        announcementBtn.onclick = function() {
            announcementModal.style.display = "block";
        }
        
        announcementClose.onclick = function() {
            announcementModal.style.display = "none";
        }
        
        // Response modal functionality
        const responseModal = document.getElementById("responseModal");
        const responseClose = responseModal.querySelector(".close");
        const responseContent = document.getElementById("responseContent");
        
        document.querySelectorAll('.view-response').forEach(item => {
            item.addEventListener('click', function(e) {
                e.stopPropagation();
                const response = this.getAttribute('data-response');
                responseContent.innerHTML = response;
                responseModal.style.display = "block";
            });
        });
        
        responseClose.onclick = function() {
            responseModal.style.display = "none";
        }
        
        window.onclick = function(event) {
            if (event.target == announcementModal) {
                announcementModal.style.display = "none";
            }
            if (event.target == responseModal) {
                responseModal.style.display = "none";
            }
        }
    </script>
</body>
</html>