$(document).ready(function() {
    // Variables
    const messageSound = document.getElementById('messageSound');
    let lastMessageCheck = new Date().getTime();
    
    // Initialize the page
    initMessagePage();

    // Check for new messages every 5 seconds
    setInterval(checkNewMessages, 5000);
    
    // Initialize message page
    function initMessagePage() {
        // Show/hide sidebar when toggle is clicked
        $("#sidebarToggle").click(function() {
            $("#sidebar").toggleClass("collapsed");
            $(".main-content").toggleClass("expanded");
            $(this).find("i").toggleClass("fa-chevron-left fa-chevron-right");
        });
        
        // Setup compose message modal
        $("#composeBtn, #cancelComposeBtn").click(function() {
            $("#composeModal").toggle();
        });
        
        $("#closeComposeModal").click(function() {
            $("#composeModal").hide();
        });
        
        // Setup message item click
        $(document).on("click", ".message-item", function() {
            const messageId = $(this).data("id");
            loadMessageDetail(messageId);
            
            // Mark as read if unread
            if ($(this).hasClass("unread")) {
                markMessageAsRead(messageId);
                $(this).removeClass("unread");
                updateUnreadBadge();
            }
        });
        
        // Setup recipient search
        $("#recipient").on("input", function() {
            const searchTerm = $(this).val();
            if (searchTerm.length >= 2) {
                searchUsers(searchTerm);
            } else {
                $("#recipientResults").empty().hide();
            }
        });
        
        // Handle recipient selection
        $(document).on("click", ".search-result-item", function() {
            $("#recipient").val($(this).data("email"));
            $("#recipientResults").empty().hide();
        });
        
        // Handle send message form
        $("#composeForm").submit(function(e) {
            e.preventDefault();
            sendMessage();
        });
        
        // Handle message importance toggle
        $(document).on("click", ".toggle-important", function(e) {
            e.stopPropagation();
            const messageId = $(this).closest(".message-detail-content").data("id");
            toggleMessageImportance(messageId);
        });
    }
    
    // Function to check for new messages
    function checkNewMessages() {
        $.ajax({
            url: "get_new_messages.php",
            type: "POST",
            data: { last_check: lastMessageCheck },
            dataType: "json",
            success: function(response) {
                if (response.status && response.messages.length > 0) {
                    // Update last check time
                    lastMessageCheck = new Date().getTime();
                    
                    // Play sound
                    messageSound.play();
                    
                    // Show notification
                    showNotification("New Message", `You have ${response.messages.length} new message(s)`);
                    
                    // Update message list if we're in inbox
                    if (window.location.href.includes("type=inbox") || !window.location.href.includes("type=")) {
                        refreshMessageList();
                    }
                    
                    // Update unread badge count
                    updateUnreadBadge(response.unread_count);
                }
            }
        });
    }
    
    // Function to load message detail
    function loadMessageDetail(messageId) {
        $.ajax({
            url: "get_message_detail.php",
            type: "POST",
            data: { message_id: messageId },
            dataType: "json",
            success: function(response) {
                if (response.status) {
                    // Create message detail HTML
                    let html = `
                        <div class="message-detail-content" data-id="${response.message.message_id}">
                            <div class="message-detail-header">
                                <div class="message-avatar">
                                    ${response.message.sender_name.substring(0, 2).toUpperCase()}
                                </div>
                                <div class="message-info">
                                    <div class="message-sender">${response.message.sender_name}</div>
                                    <div class="message-time">${formatDateTime(response.message.created_at)}</div>
                                </div>
                                <div class="message-actions">
                                    <button class="toggle-important">
                                        <i class="fas ${response.message.is_important ? 'fa-star' : 'fa-star-o'}"></i>
                                    </button>
                                    <button class="reply-btn">
                                        <i class="fas fa-reply"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="message-detail-subject">
                                ${response.message.priority === 'urgent' ? '<span class="urgent-badge">Urgent</span>' : ''}
                                ${response.message.subject}
                            </div>
                            
                            <div class="message-detail-body">
                                ${response.message.message_text.replace(/\n/g, '<br>')}
                            </div>
                            
                            <div class="message-status-info">
                                <div class="status-label">${response.message.current_status.charAt(0).toUpperCase() + response.message.current_status.slice(1)}</div>
                                <div class="status-time">${formatDateTime(response.message.status_time)}</div>
                            </div>
                        </div>
                    `;
                    
                    // Display message
                    $("#messageDetail").html(html);
                    
                    // Show the user's online status
                    getUserStatus(response.message.sender_email);
                } else {
                    // Show error
                    $("#messageDetail").html(`
                        <div class="message-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <p>Error loading message: ${response.error}</p>
                        </div>
                    `);
                }
            }
        });
    }
    
    // Function to mark message as read
    function markMessageAsRead(messageId) {
        $.ajax({
            url: "mark_message_read.php",
            type: "POST",
            data: { message_id: messageId },
            dataType: "json"
        });
    }
    
    // Function to toggle message importance
    function toggleMessageImportance(messageId) {
        $.ajax({
            url: "toggle_importance.php",
            type: "POST",
            data: { message_id: messageId },
            dataType: "json",
            success: function(response) {
                if (response.status) {
                    // Toggle star icon
                    $(".toggle-important i").toggleClass("fa-star fa-star-o");
                    
                    // Refresh message list to update UI
                    refreshMessageList();
                }
            }
        });
    }
    
    // Function to search users for recipient field
    function searchUsers(term) {
        $.ajax({
            url: "search_users.php",
            type: "POST",
            data: { search_term: term },
            dataType: "json",
            success: function(response) {
                if (response.status && response.users.length > 0) {
                    let html = '';
                    
                    response.users.forEach(function(user) {
                        html += `
                            <div class="search-result-item" data-email="${user.email}">
                                <div class="result-avatar">${user.full_name.substring(0, 2).toUpperCase()}</div>
                                <div class="result-info">
                                    <div class="result-name">${user.full_name}</div>
                                    <div class="result-email">${user.email}</div>
                                </div>
                                <div class="result-dept">${user.department}</div>
                            </div>
                        `;
                    });
                    
                    $("#recipientResults").html(html).show();
                } else {
                    $("#recipientResults").html('<div class="no-results">No users found</div>').show();
                }
            }
        });
    }
    
    // Function to send message
    function sendMessage() {
        $.ajax({
            url: "send_message.php",
            type: "POST",
            data: $("#composeForm").serialize(),
            dataType: "json",
            success: function(response) {
                if (response.status) {
                    // Show success message
                    showNotification("Success", "Message sent successfully");
                    
                    // Clear form
                    $("#composeForm")[0].reset();
                    
                    // Close modal
                    $("#composeModal").hide();
                    
                    // If in sent items, refresh list
                    if (window.location.href.includes("type=sent")) {
                        refreshMessageList();
                    }
                } else {
                    // Show error
                    showNotification("Error", response.error);
                }
            }
        });
    }
    
    // Function to refresh message list
    function refreshMessageList() {
        const currentUrl = window.location.href;
        const messageType = currentUrl.includes("type=") ? currentUrl.split("type=")[1].split("&")[0] : "inbox";
        const messageFilter = currentUrl.includes("filter=") ? currentUrl.split("filter=")[1] : "all";
        
        $.ajax({
            url: "get_messages.php",
            type: "POST",
            data: { type: messageType, filter: messageFilter },
            dataType: "json",
            success: function(response) {
                if (response.status) {
                    $("#messageList").html(response.html);
                }
            }
        });
    }
    // Add this to your messages.js file if not already present

function pollForNewMessages() {
    // Check for new messages every 5 seconds
    setInterval(function() {
        $.ajax({
            url: 'ajax/check_new_messages.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.new_messages && response.new_messages.length > 0) {
                    // Play notification sound
                    messageSound.play();
                    
                    // Update unread count
                    updateUnreadCount();
                    
                    // Show notification
                    response.new_messages.forEach(function(msg) {
                        showNotification(`New message from ${msg.sender_name}: ${msg.subject}`, 'fa-envelope', 'info');
                    });
                    
                    // Refresh message list if we're in inbox
                    if (window.location.search.includes('type=inbox')) {
                        refreshMessageList();
                    }
                }
            }
        });
    }, 5000);
}

// Start polling when page loads
document.addEventListener('DOMContentLoaded', function() {
    pollForNewMessages();
});
    // Function to update unread badge count
    function updateUnreadBadge(count = null) {
        if (count === null) {
            $.ajax({
                url: "count_unread.php",
                type: "POST",
                dataType: "json",
                success: function(response) {
                    if (response.status) {
                        $(".message-badge, .folder-badge").text(response.count);
                        
                        if (response.count === 0) {
                            $(".folder-badge").hide();
                        } else {
                            $(".folder-badge").show();
                        }
                    }
                }
            });
        } else {
            $(".message-badge, .folder-badge").text(count);
            
            if (count === 0) {
                $(".folder-badge").hide();
            } else {
                $(".folder-badge").show();
            }
        }
    }
    
    // Function to show user's online status
    function getUserStatus(userEmail) {
        $.ajax({
            url: "get_user_status.php",
            type: "POST",
            data: { user_email: userEmail },
            dataType: "json",
            success: function(response) {
                if (response.status) {
                    let statusHtml = '';
                    
                    if (response.user_status.is_online) {
                        statusHtml = '<span class="online-status">Online</span>';
                    } else {
                        statusHtml = `<span class="offline-status">Last seen: ${formatLastSeen(response.user_status.last_active)}</span>`;
                    }
                    
                    // Add status to message detail
                    $(".message-info").append(statusHtml);
                }
            }
        });
    }
    
    // Function to show browser notification
    function showNotification(title, message) {
        // Check if browser supports notifications
        if (!("Notification" in window)) {
            alert(title + ": " + message);
            return;
        }
        
        // Check if permission is granted
        if (Notification.permission === "granted") {
            const notification = new Notification(title, {
                body: message,
                icon: "images/zapf-logo.jpg"
            });
            
            // Close notification after 5 seconds
            setTimeout(() => {
                notification.close();
            }, 5000);
        } else if (Notification.permission !== "denied") {
            // Request permission
            Notification.requestPermission().then(function(permission) {
                if (permission === "granted") {
                    showNotification(title, message);
                }
            });
        }
    }
    
    // Helper function to format date time
    function formatDateTime(dateTime) {
        const date = new Date(dateTime);
        const now = new Date();
        
        // If today
        if (date.toDateString() === now.toDateString()) {
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        } else {
            return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
        }
    }
    
    // Helper function to format last seen time
    function formatLastSeen(lastActive) {
        const date = new Date(lastActive);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        
        if (diffMins < 1) {
            return "Just now";
        } else if (diffMins < 60) {
            return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
        } else if (diffMins < 1440) { // Less than a day
            const hours = Math.floor(diffMins / 60);
            return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        } else {
            return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
        }
    }
});