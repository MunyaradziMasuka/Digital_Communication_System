<?php
// Start session
session_start();

// Check if user is logged in, if not redirect to home page
if (!isset($_SESSION['user'])) {
    header("Location: home.php");
    // At the top of messages.php, after this line: $user = $_SESSION['user'];
error_log("DEBUG: Current user email from session: '" . $user['username'] . "'");
    exit;
}

// Include necessary files
require_once 'db.php';
require_once 'message_functions.php';
// Run this once to fix all emails in the database
require_once 'message_functions.php';
normalizeEmailsInDatabase();
// Get user information from session
$user = $_SESSION['user'];
// In messages.php, modify this line:
    $userEmail = strtolower(trim($user['username'])); // Ensure consistent formatting // Assuming username is the email
$fullName = $user['full_name'];
$department = $user['department'];
// Add this after getting userEmail in messages.php
// This will show all recipient emails in the database for comparison
$checkQuery = "SELECT DISTINCT recipient_email FROM messages";
$checkResult = $conn->query($checkQuery);
if ($checkResult && $checkResult->num_rows > 0) {
    error_log("DEBUG: Emails in database as recipients:");
    while ($row = $checkResult->fetch_assoc()) {
        error_log("- '" . $row['recipient_email'] . "'");
    }
}
error_log("DEBUG: Current user's email being used for comparison: '" . $userEmail . "'");
// Update user's online status
updateUserStatus($userEmail, 1);

// Get messages (default to inbox)
$messageType = isset($_GET['type']) ? $_GET['type'] : 'inbox';
$messageFilter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$messages = getMessages($userEmail, $messageType, $messageFilter);
// Add this to messages.php after getting messages
error_log("DEBUG: User email: " . $userEmail);
error_log("DEBUG: Retrieved " . count($messages) . " messages");
foreach ($messages as $index => $msg) {
    error_log("DEBUG: Message #" . $index . " - recipient: '" . $msg['recipient_email'] . "', sender: '" . $msg['sender_email'] . "'");
}
// Count unread messages
$unreadCount = countUnreadMessages($userEmail);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ZAPF-Connect | Messages</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="styles/dashboard.css" />
    <link rel="stylesheet" href="styles/msg.css" />
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar (Same as dashboard.php) -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="images/zapf-logo.jpg" alt="ZAPF Connect Logo" class="sidebar-logo" />
                <h2 class="sidebar-title">ZAPF<span>Connect</span></h2>
                <div class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-chevron-left"></i>
                </div>
            </div>

            <div class="sidebar-menu">
                <div class="menu-section">
                    <div class="menu-header">Main</div>
                    <div class="menu-item">
                        <div class="menu-icon"><i class="fas fa-th-large"></i></div>
                        <div class="menu-text"><a href="dashboard.php" style="text-decoration: none; color: inherit;">Dashboard</a></div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-icon"><i class="fas fa-users"></i></div>
                        <div class="menu-text"><a href="user_management.php" style="text-decoration: none; color: inherit;">User Management</a></div>
                    </div>
                    <div class="menu-item active">
                        <div class="menu-icon"><i class="fas fa-comments"></i></div>
                        <div class="menu-text">Messages</div>
                        <div class="menu-notification"><?php echo $unreadCount; ?></div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-icon"><i class="fas fa-bell"></i></div>
                        <div class="menu-text">Notifications</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-icon"><i class="fas fa-chart-bar"></i></div>
                        <div class="menu-text">Analytics & Reporting</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-icon"><i class="fas fa-calendar-alt"></i></div>
                        <div class="menu-text">Calendar</div>
                    </div>
                </div>

                <!-- Other sidebar sections as in dashboard.php -->
            </div>

            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar"><?php echo strtoupper(substr($userEmail, 0, 2)); ?></div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($fullName); ?></div>
                        <div class="user-role"><?php echo htmlspecialchars($department); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="main-header">
                <div class="header-title">
                    <h1>Messages</h1>
                    <div class="header-subtitle">Manage your communications</div>
                </div>
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" id="searchInput" placeholder="Search messages..." />
                    </div>
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>
                    <button class="message-btn">
                        <i class="fas fa-envelope"></i>
                        <span class="message-badge"><?php echo $unreadCount; ?></span>
                    </button>
                    <button class="user-dropdown">
                        <div class="dropdown-avatar"><?php echo strtoupper(substr($userEmail, 0, 2)); ?></div>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </button>
                </div>
            </div>

            <!-- Message Interface -->
            <div class="message-container">
                <!-- Message Sidebar -->
                <div class="message-sidebar">
                    <!-- Message Actions -->
                    <div class="message-actions">
                        <button class="compose-btn" id="composeBtn">
                            <i class="fas fa-pen"></i> Compose
                        </button>
                    </div>

                    <!-- Message Folders -->
                    <div class="message-folders">
                        <a href="?type=inbox&filter=all" class="folder-item <?php echo ($messageType == 'inbox' && $messageFilter == 'all') ? 'active' : ''; ?>">
                            <i class="fas fa-inbox"></i> Inbox
                            <?php if ($unreadCount > 0): ?>
                                <span class="folder-badge"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="?type=inbox&filter=unread" class="folder-item <?php echo ($messageType == 'inbox' && $messageFilter == 'unread') ? 'active' : ''; ?>">
                            <i class="fas fa-envelope"></i> Unread
                        </a>
                        <a href="?type=inbox&filter=important" class="folder-item <?php echo ($messageType == 'inbox' && $messageFilter == 'important') ? 'active' : ''; ?>">
                            <i class="fas fa-star"></i> Important
                        </a>
                        <a href="?type=sent&filter=all" class="folder-item <?php echo ($messageType == 'sent') ? 'active' : ''; ?>">
                            <i class="fas fa-paper-plane"></i> Sent
                        </a>
                    </div>
                </div>

                <!-- Message List -->
                <div class="message-list" id="messageList">
                    <?php if (empty($messages)): ?>
                        <div class="empty-message">
                            <i class="fas fa-inbox empty-icon"></i>
                            <p>No messages found</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                            <?php 
                                $isUnread = $message['is_read'] == 0;
                                $isImportant = $message['is_important'] == 1;
                                $isUrgent = $message['priority'] == 'urgent';
                                
                                $messageClass = '';
                                if ($isUnread) $messageClass .= ' unread';
                                if ($isImportant) $messageClass .= ' important';
                                if ($isUrgent) $messageClass .= ' urgent';
                                
                                $name = $messageType == 'inbox' ? $message['sender_name'] : $message['recipient_name'];
                                $displayTime = date('h:i A', strtotime($message['created_at']));
                                if (date('Y-m-d') != date('Y-m-d', strtotime($message['created_at']))) {
                                    $displayTime = date('M d', strtotime($message['created_at']));
                                }
                            ?>
                            <div class="message-item<?php echo $messageClass; ?>" data-id="<?php echo $message['message_id']; ?>">
                                <?php if ($isUnread): ?>
                                    <div class="unread-indicator"></div>
                                <?php endif; ?>
                                
                                <div class="message-avatar">
                                    <?php echo strtoupper(substr($name, 0, 2)); ?>
                                </div>
                                
                                <div class="message-content">
                                    <div class="message-header">
                                        <div class="message-sender"><?php echo htmlspecialchars($name); ?></div>
                                        <div class="message-time"><?php echo $displayTime; ?></div>
                                    </div>
                                    
                                    <div class="message-subject">
                                        <?php if ($isImportant): ?>
                                            <i class="fas fa-star star-icon"></i>
                                        <?php endif; ?>
                                        
                                        <?php if ($isUrgent): ?>
                                            <span class="urgent-badge">Urgent</span>
                                        <?php endif; ?>
                                        
                                        <?php echo htmlspecialchars($message['subject']); ?>
                                    </div>
                                    
                                    <div class="message-preview">
                                        <?php echo htmlspecialchars(substr($message['message_text'], 0, 100)) . (strlen($message['message_text']) > 100 ? '...' : ''); ?>
                                    </div>
                                    
                                    <?php if ($messageType == 'sent'): ?>
                                        <div class="message-status">
                                            <?php if ($message['current_status'] == 'read'): ?>
                                                <i class="fas fa-check-double read-icon"></i> Read
                                            <?php elseif ($message['current_status'] == 'delivered'): ?>
                                                <i class="fas fa-check-double"></i> Delivered
                                            <?php else: ?>
                                                <i class="fas fa-check"></i> Sent
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Message Detail View -->
                <div class="message-detail" id="messageDetail">
                    <div class="no-message-selected">
                        <i class="fas fa-envelope-open-text"></i>
                        <p>Select a message to view</p>
                    </div>
                    
                    <!-- Message content will be loaded here via AJAX -->
                </div>
            </div>

            <!-- Compose Message Modal -->
            <div class="modal" id="composeModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Compose Message</h3>
                        <span class="close-modal" id="closeComposeModal">&times;</span>
                    </div>
                    
                    <div class="modal-body">
                        <form id="composeForm">
                            <div class="form-group">
                                <label for="recipient">To:</label>
                                <div class="recipient-search-container">
                                    <input type="text" id="recipient" name="recipient" autocomplete="off" placeholder="Type to search..." required>
                                    <div id="recipientResults" class="search-results"></div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="subject">Subject:</label>
                                <input type="text" id="subject" name="subject" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="priority">Priority:</label>
                                <select id="priority" name="priority">
                                    <option value="normal">Normal</option>
                                    <option value="important">Important</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="messageText">Message:</label>
                                <textarea id="messageText" name="messageText" rows="8" required></textarea>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="send-btn">
                                    <i class="fas fa-paper-plane"></i> Send
                                </button>
                                <button type="button" class="cancel-btn" id="cancelComposeBtn">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Logout section -->
            <div class="footer-actions">
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Audio elements for notifications -->
    <audio id="messageSound" src="sounds/message.mp3" preload="auto"></audio>
    
    <!-- Include jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    
    <!-- Include JavaScript file -->
    <script src="js/messages.js"></script>
</body>
</html>