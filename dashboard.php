<?php
// Start session
session_start();

// Check if user is logged in, if not redirect to home page
if (!isset($_SESSION['user'])) {
    header("Location: home.php");
    exit;
}

// Include necessary files for message functions
require_once 'db.php';
require_once 'message_functions.php';
require_once 'meeting_announcement_functions.php';
// Add this function before the HTML rendering begins (e.g., near the top of the file after the includes)
function getTimeRemaining($meetingDate) {
  // Get current time and meeting time
  $now = new DateTime();
  $meetingTime = new DateTime($meetingDate);
  
  // Calculate difference
  $interval = $now->diff($meetingTime);
  
  // Format the remaining time
  $days = $interval->d;
  $hours = $interval->h;
  $minutes = $interval->i;
  
  // If the meeting is in the past, return a different message
  if ($now > $meetingTime) {
      return '<span class="countdown-past">Meeting has already started</span>';
  }
  
  // Format the countdown string
  $countdown = '';
  if ($days > 0) {
      $countdown .= "$days " . ($days == 1 ? "day" : "days") . " ";
  }
  if ($hours > 0 || $days > 0) {
      $countdown .= "$hours " . ($hours == 1 ? "hour" : "hours") . " ";
  }
  $countdown .= "$minutes " . ($minutes == 1 ? "minute" : "minutes");
  
  return '<span class="countdown-active">in ' . $countdown . '</span>';
}

// Get user information from session
$user = $_SESSION['user'];
$fullName = $user['full_name'];
$department = $user['department'];
$username = $user['username'];
$userEmail = strtolower(trim($username)); // Normalize email format for consistency

// Update user's online status
updateUserStatus($userEmail, 1);

// Count unread messages
$unreadCount = countUnreadMessages($userEmail);

// Get recent messages for dashboard display (limited to 3)
$recentMessages = getMessages($userEmail, 'inbox', 'all');
$recentMessages = array_slice($recentMessages, 0, 3); // Get only the first 3 messages

// Get meeting and announcement metrics
$meetingMetrics = getMeetingMetrics();
$announcementMetrics = getAnnouncementMetrics();

// Get recent meetings and announcements
$recentMeetings = getMeetings($userEmail);
$recentMeetings = array_slice($recentMeetings, 0, 3);

$recentAnnouncements = getAnnouncements($userEmail);
$recentAnnouncements = array_slice($recentAnnouncements, 0, 3);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ZAPF-Connect | Dashboard</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <link rel="stylesheet" href="styles/dashboard.css" />
  </head>
  <body>
    <div class="dashboard-container">
      <!-- Sidebar -->
      <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
          <img
            src="images/zapf-logo.jpg"
            alt="ZAPF Connect Logo"
            class="sidebar-logo"
          />
          <h2 class="sidebar-title">ZAPF<span>Connect</span></h2>
          <div class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-chevron-left"></i>
          </div>
        </div>

        <div class="sidebar-menu">
          <div class="menu-section">
            <div class="menu-header">Main</div>
            <div class="menu-item active">
              <div class="menu-icon"><i class="fas fa-th-large"></i></div>
              <div class="menu-text">Dashboard</div>
            </div>
            <!-- User Management Menu Item -->
            <div class="menu-item">
              <div class="menu-icon"><i class="fas fa-users"></i></div>
              <div class="menu-text"><a href="user_management.php" style="text-decoration: none; color: inherit;">User Management</a></div>
            </div>
            <div class="menu-item">
              <div class="menu-icon"><i class="fas fa-comments"></i></div>
              <div class="menu-text"><a href="messages.php" style="text-decoration: none; color: inherit;">Messages</a></div>
              <div class="menu-notification"><?php echo $unreadCount; ?></div>
            </div>
            <div class="menu-item">
              <div class="menu-icon"><i class="fas fa-bell"></i></div>
              <div class="menu-text">Notifications</div>
              <div class="menu-notification">12</div>
            </div>
            <div class="menu-item">
              <div class="menu-icon"><i class="fas fa-chart-bar"></i></div>
              <div class="menu-text">Analytics & Reporting</div>
            </div>
            <div class="menu-item">
              <div class="menu-icon"><i class="fas fa-calendar-alt"></i></div>
              <div class="menu-text">Calendar</div>
            </div>
            <!-- New Menu Items for Meetings and Announcements -->
            <div class="menu-item">
              <div class="menu-icon"><i class="fas fa-calendar-check"></i></div>
              <div class="menu-text"><a href="meetings.php" style="text-decoration: none; color: inherit;">Meetings</a></div>
            </div>
            <div class="menu-item">
              <div class="menu-icon"><i class="fas fa-bullhorn"></i></div>
              <div class="menu-text"><a href="announcements.php" style="text-decoration: none; color: inherit;">Announcements</a></div>
            </div>
          </div>

          <div class="menu-section">
            <div class="menu-header">Workspace</div>
            <div class="menu-item">
              <div class="menu-icon"><i class="fas fa-user-friends"></i></div>
              <div class="menu-text">Team</div>
            </div>
            <div class="menu-item">
              <div class="menu-icon"><i class="fas fa-folder"></i></div>
              <div class="menu-text">Documents</div>
            </div>
            <div class="menu-item">
              <div class="menu-icon"><i class="fas fa-tasks"></i></div>
              <div class="menu-text">Tasks</div>
            </div>
          </div>
        </div>

        <div class="sidebar-footer">
          <div class="user-profile">
            <div class="user-avatar"><?php echo strtoupper(substr($username, 0, 2)); ?></div>
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
            <h1>Dashboard</h1>
            <div class="header-subtitle">Welcome back, <?php echo htmlspecialchars($fullName); ?> from <?php echo htmlspecialchars($department); ?> department!</div>
          </div>
          <div class="header-actions">
            <div class="search-box">
              <i class="fas fa-search search-icon"></i>
              <input type="text" class="search-input" placeholder="Search..." />
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
              <div class="dropdown-avatar"><?php echo strtoupper(substr($username, 0, 2)); ?></div>
              <i class="fas fa-chevron-down dropdown-icon"></i>
            </button>
          </div>
        </div>

        <!-- Dashboard Stats -->
        <div class="dashboard-grid">
          <div class="dashboard-card">
            <div class="card-header">
              <h3 class="card-title">Total Users</h3>
              <div class="card-icon"><i class="fas fa-users"></i></div>
            </div>
            <div class="card-value">2,453</div>
            <div class="card-description">+12% from last month</div>
            <div class="progress-bar progress-success">
              <div class="progress-value" style="width: 85%"></div>
            </div>
          </div>

          <div class="dashboard-card">
            <div class="card-header">
              <h3 class="card-title">Delivery Rate</h3>
              <div class="card-icon status-success">
                <i class="fas fa-project-diagram"></i>
              </div>
            </div>
            <div class="card-value">19</div>
            <div class="card-description">93%</div>
            <div class="progress-bar progress-success">
              <div class="progress-value" style="width: 70%"></div>
            </div>
          </div>

          <div class="dashboard-card">
            <div class="card-header">
              <h3 class="card-title">Meetings</h3>
              <div class="card-icon"><i class="fas fa-calendar-check"></i></div>
            </div>
            <div class="card-value"><?php echo $meetingMetrics['total']; ?></div>
            <div class="card-description">Response rate: <?php echo $meetingMetrics['response_rate']; ?>%</div>
            <div class="progress-bar progress-primary">
              <div class="progress-value" style="width: <?php echo $meetingMetrics['response_rate']; ?>%"></div>
            </div>
          </div>

          <div class="dashboard-card">
            <div class="card-header">
              <h3 class="card-title">Announcements</h3>
              <div class="card-icon"><i class="fas fa-bullhorn"></i></div>
            </div>
            <div class="card-value"><?php echo $announcementMetrics['total']; ?></div>
            <div class="card-description">View rate: <?php echo $announcementMetrics['view_rate']; ?>%</div>
            <div class="progress-bar progress-info">
              <div class="progress-value" style="width: <?php echo $announcementMetrics['view_rate']; ?>%"></div>
            </div>
          </div>
        </div>

        <!-- Messages and Activities -->
        <div class="dashboard-expanded">
          <div class="messages-section">
            <div class="section-header">
              <h3 class="section-title">
                <i class="fas fa-comments"></i> Recent Messages
              </h3>
              <div class="section-tabs">
                <div class="section-tab active">All</div>
                <div class="section-tab">Unread</div>
                <div class="section-tab">Important</div>
              </div>
            </div>

            <!-- Message Items -->
            <?php if (empty($recentMessages)): ?>
              <div class="empty-message">
                <p>No recent messages</p>
              </div>
            <?php else: ?>
              <?php foreach ($recentMessages as $message): ?>
                <?php 
                  $isUnread = $message['is_read'] == 0;
                  $isImportant = $message['is_important'] == 1;
                  $messageClass = $isUnread ? ' unread' : '';
                ?>
                <div class="message-item<?php echo $messageClass; ?>" data-id="<?php echo $message['message_id']; ?>">
                  <?php if ($isUnread): ?>
                    <div class="unread-indicator"></div>
                  <?php endif; ?>
                  <div class="message-avatar">
                    <?php echo isset($message['sender_initial']) ? $message['sender_initial'] : substr($message['sender_email'], 0, 2); ?>
                  </div>
                  <div class="message-content">
                    <div class="message-sender">
                      <?php echo htmlspecialchars($message['sender_name'] ?? $message['sender_email']); ?> 
                      <span class="message-time"><?php echo date('h:i A', strtotime($message['created_at'])); ?></span>
                    </div>
                    <div class="message-text">
                      <?php echo htmlspecialchars(substr($message['message_text'], 0, 100)) . (strlen($message['message_text']) > 100 ? '...' : ''); ?>
                    </div>
                    <div class="message-actions">
                      <div class="message-action">
                        <i class="fas fa-reply"></i> Reply
                      </div>
                      <div class="message-action">
                        <i class="fas fa-star"></i> Mark Important
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>

            <div class="section-action"><a href="messages.php" style="text-decoration: none; color: inherit;">View All Messages</a></div>
          </div>

          <!-- Upcoming Meetings Section (Replacing Activities) -->
          <div class="meetings-section">
            <div class="section-header">
              <h3 class="section-title">
                <i class="fas fa-calendar-check"></i> Upcoming Meetings
              </h3>
            </div>

            <!-- Meeting Items -->
            <?php if (empty($recentMeetings)): ?>
              <div class="empty-message">
                <p>No upcoming meetings</p>
              </div>
            <!-- Meeting Items -->

  <div class="empty-message">
    <p>No upcoming meetings</p>
  </div>
<?php else: ?>
  <?php foreach ($recentMeetings as $meeting): ?>
    <div class="meeting-item" data-id="<?php echo $meeting['meeting_id']; ?>">
      <div class="meeting-timeline">
        <div class="meeting-date">
          <div class="date-day"><?php echo date('d', strtotime($meeting['meeting_date'])); ?></div>
          <div class="date-month"><?php echo date('M', strtotime($meeting['meeting_date'])); ?></div>
        </div>
        <div class="meeting-time"><?php echo date('h:i A', strtotime($meeting['meeting_date'])); ?></div>
        <div class="meeting-countdown"><?php echo getTimeRemaining($meeting['meeting_date']); ?></div>
      </div>
      <div class="meeting-content">
        <div class="meeting-title"><?php echo htmlspecialchars($meeting['title']); ?></div>
        <div class="meeting-location">
          <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($meeting['location']); ?>
        </div>
        <div class="meeting-actions">
          <button class="btn-accept">Accept</button>
          <button class="btn-decline">Decline</button>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
            <div class="section-action"><a href="meetings.php" style="text-decoration: none; color: inherit;">View All Meetings</a></div>
          </div>
        </div>

        <!-- Recent Announcements Section -->
        <div class="dashboard-expanded">
          <div class="announcements-section">
            <div class="section-header">
              <h3 class="section-title">
                <i class="fas fa-bullhorn"></i> Recent Announcements
              </h3>
            </div>

            <!-- Announcement Items -->
            <?php if (empty($recentAnnouncements)): ?>
              <div class="empty-message">
                <p>No recent announcements</p>
              </div>
            <?php else: ?>
              <?php foreach ($recentAnnouncements as $announcement): ?>
                <div class="announcement-item" data-id="<?php echo $announcement['announcement_id']; ?>">
                  <div class="announcement-priority 
                    <?php
                    switch ($announcement['importance']) {
                      case 'high':
                        echo 'priority-high';
                        break;
                      case 'medium':
                        echo 'priority-medium';
                        break;
                      default:
                        echo 'priority-normal';
                    }
                    ?>">
                  </div>
                  <div class="announcement-content">
                    <div class="announcement-header">
                      <div class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></div>
                      <div class="announcement-time"><?php echo date('M d, Y', strtotime($announcement['created_at'])); ?></div>
                    </div>
                    <div class="announcement-text">
                      <?php echo htmlspecialchars(substr($announcement['content'], 0, 150)) . (strlen($announcement['content']) > 150 ? '...' : ''); ?>
                    </div>
                    <div class="announcement-footer">
                      <div class="announcement-author">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($announcement['creator_email']); ?>
                      </div>
                      <div class="announcement-action">
                        <i class="fas fa-check-circle"></i> Mark as Read
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>

            <div class="section-action"><a href="announcements.php" style="text-decoration: none; color: inherit;">View All Announcements</a></div>
          </div>
        </div>

        <!-- Logout section -->
        <div class="footer-actions">
          <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </div>

        <!-- Script for sidebar toggle functionality -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script>
          document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.getElementById("sidebar");
            const sidebarToggle = document.getElementById("sidebarToggle");

            sidebarToggle.addEventListener("click", function () {
              sidebar.classList.toggle("collapsed");

              // Toggle the icon direction
              const icon = sidebarToggle.querySelector("i");
              if (sidebar.classList.contains("collapsed")) {
                icon.classList.replace("fa-chevron-left", "fa-chevron-right");
              } else {
                icon.classList.replace("fa-chevron-right", "fa-chevron-left");
              }
            });
            
            // Add message click handling
            const messageItems = document.querySelectorAll('.message-item');
            messageItems.forEach(item => {
              item.addEventListener('click', function() {
                const messageId = this.getAttribute('data-id');
                window.location.href = `messages.php?view=${messageId}`;
              });
            });

            // Add meeting click handling
            const meetingItems = document.querySelectorAll('.meeting-item');
            meetingItems.forEach(item => {
              item.addEventListener('click', function() {
                const meetingId = this.getAttribute('data-id');
                window.location.href = `meetings.php?view=${meetingId}`;
              });
            });

            // Add announcement click handling
            const announcementItems = document.querySelectorAll('.announcement-item');
            announcementItems.forEach(item => {
              item.addEventListener('click', function() {
                const announcementId = this.getAttribute('data-id');
                window.location.href = `announcements.php?view=${announcementId}`;
              });
            });
          });
        </script>
      </div>
    </div>
  </body>
</html>