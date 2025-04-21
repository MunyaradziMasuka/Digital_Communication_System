/**
 * ZAPF Connect Notification System
 * Handles real-time notifications and UI interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Notification sound
    const notificationSound = new Audio("data:audio/wav;base64,UklGRiQEAABXQVZFZm10IBAAAAABAAEAESsAABErAAABAAgAZGF0YQAEAADpYOf+Cv+24aTd8wBh+6bv9wCN/N/2xPYd/OL5FPbL+6L+EPgZ+W38vgJxAaH6w/2qBFQDIvlN/xgENPtH+1//cQBg/CP80Pyk/Vb9zvtl/fACPgA6+vr+UQaLA134ifxGB8sEgvY6+/gD8QM//PP5+P8qAeL93fnL+xEB4v+N+UT8CQTYA8f6U/xxA7YC1/4n/CEAXQFzAI7+dvzCADYC7P4j/Pn9CgOHBYz/xfuaALIFjQUCAQj9FQCWBM0Hxfzg9JH/8An3AQ34ofh/AHUCdP+I/Zr9cP29/yICfQFI/noASALZAur/of0x/5ACWQQtACL9CwC7A+UE/ANe/iv8BAA4BrEEIvsY+FD9LQRYA6/+5/vR/PIA7wJ/ACH9ff3Y/+QBagEm/8f+DQBCAW0B5ADS/3L/YAA6Ac0A8P+D/5n/8/8ZAAAA7P+7/zYAof8XAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAA=");

    // DOM elements
    const notificationIcon = document.getElementById('notificationIcon');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationPopup = document.getElementById('notificationPopup');
    const notificationItems = document.querySelector('.dropdown-items');
    const notificationList = document.querySelector('.notification-list');
    const notificationBadges = document.querySelectorAll('.notification-badge, .header-badge');
    const soundToggle = document.getElementById('soundToggle');
    const markAllRead = document.getElementById('markAllRead');
    const closePopup = document.getElementById('closePopup');

    // Settings
    let notificationSettings = {
        sound: localStorage.getItem('notification_sound') !== 'false',
        desktopNotifications: localStorage.getItem('desktop_notifications') !== 'false',
        checkInterval: 30000 // Check for new notifications every 30 seconds
    };

    // Initialize sound setting
    if (soundToggle) {
        soundToggle.checked = notificationSettings.sound;
        soundToggle.addEventListener('change', function() {
            notificationSettings.sound = this.checked;
            localStorage.setItem('notification_sound', this.checked);
        });
    }

    // Toggle notification dropdown
    if (notificationIcon) {
        notificationIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');
            
            // If dropdown is shown, mark notifications as viewed
            if (notificationDropdown.classList.contains('show')) {
                markNotificationsAsViewed();
            }
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (notificationDropdown && notificationDropdown.classList.contains('show') && 
            !notificationIcon.contains(e.target)) {
            notificationDropdown.classList.remove('show');
        }
    });

    // Close notification popup
    if (closePopup) {
        closePopup.addEventListener('click', function() {
            notificationPopup.classList.remove('show');
        });
    }

    // Mark all notifications as read
    if (markAllRead) {
        markAllRead.addEventListener('click', function() {
            markAllNotificationsAsRead();
        });
    }

    /**
     * Check for new notifications via AJAX
     */
    function checkForNewNotifications() {
        fetch('ajax/check_notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.status && data.new_notifications) {
                    // Update badge count
                    updateNotificationBadge(data.unread_count);
                    
                    // Display notifications
                    data.notifications.forEach(notification => {
                        showNotification(notification);
                    });
                    
                    // Update notification list
                    updateNotificationList(data.notifications);
                }
            })
            .catch(error => console.error('Error checking notifications:', error));
    }

    /**
     * Show a notification popup
     * @param {Object} notification Notification data
     */
    function showNotification(notification) {
        // Update popup content
        if (notificationPopup) {
            // Get initials for avatar
            let initials = 'SY'; // System default
            if (notification.sender_name) {
                initials = notification.sender_name.split(' ')
                    .map(n => n[0])
                    .join('').substring(0, 2).toUpperCase();
            } else if (notification.sender_email) {
                initials = notification.sender_email.substring(0, 2).toUpperCase();
            }
            
            // Update popup content
            document.querySelector('.popup-avatar').textContent = initials;
            
            let popupMessage = '';
            switch(notification.type) {
                case 'message':
                    popupMessage = `<strong>${notification.sender_name || notification.sender_email}</strong> sent you a message`;
                    break;
                case 'meeting':
                    popupMessage = `<strong>Meeting:</strong> ${notification.title}`;
                    break;
                case 'announcement':
                    popupMessage = `<strong>Announcement:</strong> ${notification.title}`;
                    break;
                case 'reminder':
                    popupMessage = `<strong>Reminder:</strong> ${notification.title}`;
                    break;
                default:
                    popupMessage = notification.content;
            }
            
            document.querySelector('.popup-message').innerHTML = popupMessage;
            document.querySelector('.popup-time').textContent = 'Just now';
            
            // Show popup
            notificationPopup.classList.add('show');
            
            // Play sound if enabled
            if (notificationSettings.sound) {
                notificationSound.play();
            }
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                notificationPopup.classList.remove('show');
            }, 5000);
            
            // Show desktop notification if enabled and supported
            if (notificationSettings.desktopNotifications && 'Notification' in window) {
                if (Notification.permission === 'granted') {
                    showDesktopNotification(notification);
                } else if (Notification.permission !== 'denied') {
                    Notification.requestPermission().then(permission => {
                        if (permission === 'granted') {
                            showDesktopNotification(notification);
                        }
                    });
                }
            }
        }
    }
    
    /**
     * Show a desktop notification
     * @param {Object} notification Notification data
     */
    function showDesktopNotification(notification) {
        const title = notification.type.charAt(0).toUpperCase() + notification.type.slice(1);
        const options = {
            body: notification.content,
            icon: '/images/zapf-logo.jpg',
            tag: 'zapf-notification-' + notification.notification_id
        };
        
        const desktopNotification = new Notification(title, options);
        
        desktopNotification.onclick = function() {
            window.focus();
            navigateToNotificationTarget(notification);
            this.close();
        };
    }
    
    /**
     * Navigate to the appropriate page based on notification type
     * @param {Object} notification Notification data
     */
    function navigateToNotificationTarget(notification) {
        switch(notification.type) {
            case 'message':
                window.location.href = `messages.php?view=${notification.reference_id}`;
                break;
            case 'meeting':
                window.location.href = `meetings.php?view=${notification.reference_id}`;
                break;
            case 'announcement':
                window.location.href = `announcements.php?view=${notification.reference_id}`;
                break;
            case 'reminder':
                // For reminders, we'll show the notification details
                window.location.href = `notifications.php?view=${notification.notification_id}`;
                break;
            default:
                window.location.href = 'notifications.php';
        }
    }

    /**
     * Update notification badge count
     * @param {number} count Number of unread notifications
     */
    function updateNotificationBadge(count) {
        // Update all badge elements with the new count
        notificationBadges.forEach(badge => {
            if (count > 0) {
                badge.textContent = count;
                badge.classList.add('active');
            } else {
                badge.textContent = '';
                badge.classList.remove('active');
            }
        });
    }

    /**
     * Mark notifications as viewed (not necessarily read)
     * When user opens the dropdown
     */
    function markNotificationsAsViewed() {
        fetch('ajax/mark_notifications_viewed.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            // Update badge count with remaining unread notifications (if any)
            if (data.status) {
                updateNotificationBadge(data.unread_count);
            }
        })
        .catch(error => console.error('Error marking notifications as viewed:', error));
    }

    /**
     * Mark all notifications as read
     */
    function markAllNotificationsAsRead() {
        fetch('ajax/mark_all_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                // Clear badges
                updateNotificationBadge(0);
                
                // Update notification items in the dropdown
                if (notificationItems) {
                    const items = notificationItems.querySelectorAll('.notification-item');
                    items.forEach(item => {
                        item.classList.remove('unread');
                    });
                }
                
                // Close the dropdown
                notificationDropdown.classList.remove('show');
            }
        })
        .catch(error => console.error('Error marking notifications as read:', error));
    }

    /**
     * Update notification list in the dropdown
     * @param {Array} notifications Array of notification objects
     */
    function updateNotificationList(notifications) {
        if (!notificationItems) return;
        
        // Clear existing notifications or show "no notifications" message
        if (notifications.length === 0) {
            notificationItems.innerHTML = '<div class="no-notifications">No notifications</div>';
            return;
        }
        
        // Create HTML for notifications
        let notificationHTML = '';
        
        notifications.forEach(notification => {
            // Get icon based on notification type
            let icon = '';
            switch(notification.type) {
                case 'message':
                    icon = '<i class="fas fa-envelope"></i>';
                    break;
                case 'meeting':
                    icon = '<i class="fas fa-calendar"></i>';
                    break;
                case 'announcement':
                    icon = '<i class="fas fa-bullhorn"></i>';
                    break;
                case 'reminder':
                    icon = '<i class="fas fa-bell"></i>';
                    break;
                default:
                    icon = '<i class="fas fa-info-circle"></i>';
            }
            
            // Format notification item
            notificationHTML += `
                <div class="notification-item ${notification.is_read ? '' : 'unread'}" data-id="${notification.notification_id}">
                    <div class="notification-icon">${icon}</div>
                    <div class="notification-content">
                        <div class="notification-title">${notification.title || getNotificationTitle(notification)}</div>
                        <div class="notification-text">${notification.content}</div>
                        <div class="notification-time">${formatTimeAgo(notification.created_at)}</div>
                    </div>
                </div>
            `;
        });
        
        // Update the notification items container
        notificationItems.innerHTML = notificationHTML;
        
        // Add click event to each notification item
        const items = notificationItems.querySelectorAll('.notification-item');
        items.forEach(item => {
            item.addEventListener('click', function() {
                const notificationId = this.getAttribute('data-id');
                markNotificationAsRead(notificationId);
                
                // Find the corresponding notification object
                const notification = notifications.find(n => n.notification_id == notificationId);
                if (notification) {
                    navigateToNotificationTarget(notification);
                }
            });
        });
    }

    /**
     * Get a formatted title based on notification type
     * @param {Object} notification Notification data
     * @return {string} Formatted title
     */
    function getNotificationTitle(notification) {
        switch(notification.type) {
            case 'message':
                return `New message from ${notification.sender_name || notification.sender_email}`;
            case 'meeting':
                return 'Meeting Notification';
            case 'announcement':
                return 'New Announcement';
            case 'reminder':
                return 'Reminder';
            default:
                return 'Notification';
        }
    }

    /**
     * Mark a single notification as read
     * @param {string|number} notificationId ID of the notification
     */
    function markNotificationAsRead(notificationId) {
        fetch('ajax/mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                notification_id: notificationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                // Update UI to mark this notification as read
                const notificationItem = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
                if (notificationItem) {
                    notificationItem.classList.remove('unread');
                }
                
                // Update badge count
                updateNotificationBadge(data.unread_count);
            }
        })
        .catch(error => console.error('Error marking notification as read:', error));
    }

    /**
     * Format time in "time ago" format (e.g., "5 minutes ago")
     * @param {string} timestamp ISO timestamp or date string
     * @return {string} Formatted time ago string
     */
    function formatTimeAgo(timestamp) {
        const now = new Date();
        const past = new Date(timestamp);
        const diffMs = now - past;
        
        // Convert to seconds
        const diffSec = Math.floor(diffMs / 1000);
        
        if (diffSec < 60) {
            return 'Just now';
        }
        
        // Convert to minutes
        const diffMin = Math.floor(diffSec / 60);
        
        if (diffMin < 60) {
            return `${diffMin} minute${diffMin > 1 ? 's' : ''} ago`;
        }
        
        // Convert to hours
        const diffHour = Math.floor(diffMin / 60);
        
        if (diffHour < 24) {
            return `${diffHour} hour${diffHour > 1 ? 's' : ''} ago`;
        }
        
        // Convert to days
        const diffDay = Math.floor(diffHour / 24);
        
        if (diffDay < 7) {
            return `${diffDay} day${diffDay > 1 ? 's' : ''} ago`;
        }
        
        // For older notifications, return the date
        const options = { month: 'short', day: 'numeric' };
        return past.toLocaleDateString(undefined, options);
    }

    // Start periodic notification checks
    if (notificationIcon) {
        // Check immediately on page load
        checkForNewNotifications();
        
        // Then set interval for periodic checking
        setInterval(checkForNewNotifications, notificationSettings.checkInterval);
    }

    // Request permission for desktop notifications
    if ('Notification' in window && Notification.permission !== 'granted' && Notification.permission !== 'denied') {
        // Request on user interaction to comply with browser policies
        document.addEventListener('click', function requestNotificationPermission() {
            Notification.requestPermission();
            // Remove this event listener after first click
            document.removeEventListener('click', requestNotificationPermission);
        });
    }
});