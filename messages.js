// messages.js - Client-side functionality for ZAPF Connect messaging system

$(document).ready(function() {
    // Core state variables
    let selectedMessageId = null;
    let recipientData = null;
    
    // Initialize core functionality
    initSidebar();
    initMessageHandling();
    initCompose();
    initSearch();
    initNotifications();
    initPolling();
    initScrollStyles();
    
    // Update user online status
    setInterval(() => $.ajax({url: "ajax/update_user_status.php", type: "POST", data: {is_online: 1}}), 60000);
    
    // Set offline status when leaving
    $(window).on("beforeunload", () => $.ajax({
      url: "ajax/update_user_status.php", type: "POST", data: {is_online: 0}, async: false
    }));

    // Initialize scrolling styles
    function initScrollStyles() {
      $("head").append(`
        <style>
          /* Scrolling styles for message components */
          .message-detail-body {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 10px;
          }
          
          #messageText {
            min-height: 120px;
            max-height: 500px;
            overflow-y: auto;
            resize: vertical;
          }
          
          #recipientResults {
            max-height: 200px;
            overflow-y: auto;
            scrollbar-width: thin;
          }
          
          #messageDetail {
            max-height: calc(100vh - 150px);
            overflow-y: auto;
            padding-right: 10px;
          }
          
          .message-list-container {
            max-height: calc(100vh - 180px);
            overflow-y: auto;
          }
          
          /* Custom scrollbar styling */
          ::-webkit-scrollbar {
            width: 8px;
          }
          
          ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
          }
          
          ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
          }
          
          ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
          }
        </style>
      `);
    }
    
    // Sidebar functionality
    function initSidebar() {
      $("#sidebarToggle").click(function() {
        $("#sidebar").toggleClass("collapsed");
        const icon = $(this).find("i");
        icon.toggleClass("fa-chevron-right fa-chevron-left");
      });
    }
    
    // Message viewing functionality
    function initMessageHandling() {
      // Handle message selection
      $(document).on("click", ".message-item", function() {
        selectedMessageId = $(this).data("id");
        $(".message-item").removeClass("selected");
        $(this).addClass("selected");
        
        // Mark as read if unread
        if ($(this).hasClass("unread")) {
          markMessageAsRead(selectedMessageId);
          $(this).removeClass("unread");
          updateUnreadCount();
        }
        
        // Show loading state
        $("#messageDetail").html('<div class="loading-message"><i class="fas fa-spinner fa-spin"></i> Loading message...</div>');
        $("#messageDetail").scrollTop(0); // Scroll to top when loading new message
        
        // Fetch and display message
        $.ajax({
          url: "ajax/get_message_detail.php",
          type: "POST",
          data: { message_id: selectedMessageId },
          dataType: "json",
          success: function(response) {
            response.status ? renderMessageDetail(response.message) : 
              $("#messageDetail").html('<div class="error-message"><i class="fas fa-exclamation-circle"></i> ' + response.error + '</div>');
            
            // Ensure content is scrolled to top after loading
            $("#messageDetail").scrollTop(0);
          },
          error: function() {
            $("#messageDetail").html('<div class="error-message"><i class="fas fa-exclamation-circle"></i> Failed to load message. Please try again.</div>');
          }
        });
      });
      
      // Handle reply button click
      $(document).on("click", ".reply-btn", function(e) {
        e.stopPropagation();
        const messageId = $(this).data("id");
        
        $.ajax({
          url: "ajax/get_message_detail.php",
          type: "POST",
          data: { message_id: messageId },
          dataType: "json",
          success: function(response) {
            if (response.status) {
              const message = response.message;
              
              // Set recipient and subject
              $("#recipient").val(message.sender_email);
              recipientData = {
                email: message.sender_email,
                name: message.sender_name
              };
              
              let subject = message.subject;
              if (!subject.startsWith("Re:")) {
                subject = "Re: " + subject;
              }
              $("#subject").val(subject);
              $("#priority").val(message.priority);
              
              // Format quote of original message
              const originalDate = new Date(message.created_at);
              const quotedText = "\n\n--------- Original Message ---------\n" +
                               "From: " + message.sender_name + " <" + message.sender_email + ">\n" +
                               "Date: " + originalDate.toLocaleString() + "\n" +
                               "Subject: " + message.subject + "\n\n" +
                               message.message_text.split('\n').map(line => "> " + line).join('\n');
              
              // Set cursor at beginning
              $("#messageText").val("\n" + quotedText);
              $("#composeModal").show();
              
              // Reset text area height to accommodate content
              adjustTextareaHeight($("#messageText")[0]);
              
              // Set focus at beginning of text area
              $("#messageText").focus();
              $("#messageText").get(0).setSelectionRange(0, 0);
              $("#messageText").scrollTop(0); // Scroll to top of text area
            } else {
              showNotification("Error", "Could not load message details", "error");
            }
          },
          error: function() {
            showNotification("Error", "Failed to load message details", "error");
          }
        });
      });
      
      // Toggle message importance
      $(document).on("click", ".important-btn", function(e) {
        e.stopPropagation();
        const messageId = $(this).data("id");
        const button = $(this);
        
        $.ajax({
          url: "ajax/toggle_importance.php",
          type: "POST",
          data: { message_id: messageId },
          dataType: "json",
          success: function(response) {
            if (response.status) {
              button.toggleClass("active");
              button.find("i").toggleClass("far fas");
              $(".message-item[data-id='" + messageId + "']").toggleClass("important");
            }
          }
        });
      });
    }
    
    // Message composition functionality
    function initCompose() {
      // Compose modal toggle
      $("#composeBtn, #cancelComposeBtn").click(function() {
        $("#composeModal").toggle();
        if ($("#composeModal").is(":visible")) {
          $("#recipient").focus();
          // Reset text area to default height
          $("#messageText").css('height', 'auto');
        } else {
          $("#composeForm")[0].reset();
          $("#recipientResults").empty().hide();
          recipientData = null;
        }
      });
      
      // Close modal
      $("#closeComposeModal").click(function() {
        $("#composeModal").hide();
        $("#composeForm")[0].reset();
        $("#recipientResults").empty().hide();
        recipientData = null;
      });
      
      // Auto-resize text area as user types
      $("#messageText").on("input", function() {
        adjustTextareaHeight(this);
      });
      
      // Recipient search
      $("#recipient").on("input", function() {
        const searchTerm = $(this).val().trim();
        
        if (searchTerm.length < 2) {
          $("#recipientResults").empty().hide();
          return;
        }
        
        $.ajax({
          url: "ajax/search_users.php",
          type: "GET",
          data: { term: searchTerm },
          dataType: "json",
          success: function(response) {
            if (response.status && response.users.length > 0) {
              $("#recipientResults").empty().show();
              
              response.users.forEach(function(user) {
                $("#recipientResults").append(`
                  <div class="search-result-item" data-email="${user.email}">
                    <div class="result-avatar">${user.initial}</div>
                    <div class="result-info">
                      <div class="result-name">${user.full_name}</div>
                      <div class="result-detail">${user.department} • ${user.email}</div>
                    </div>
                    ${user.is_online ? '<span class="online-indicator"></span>' : ''}
                  </div>
                `);
              });
              
              // Ensure recipient results are scrollable if too many
              if (response.users.length > 5) {
                $("#recipientResults").css('max-height', '200px').css('overflow-y', 'auto');
              }
            } else {
              $("#recipientResults").html('<div class="no-results">No users found</div>').show();
            }
          }
        });
      });
      
      // Handle recipient selection
      $(document).on("click", ".search-result-item", function() {
        const email = $(this).data("email");
        const name = $(this).find(".result-name").text();
        
        $("#recipient").val(email);
        recipientData = { email, name };
        $("#recipientResults").empty().hide();
      });
      
      // Hide search results when clicking outside
      $(document).on("click", function(e) {
        if (!$(e.target).closest(".recipient-search-container").length) {
          $("#recipientResults").empty().hide();
        }
      });
      
      // Handle sending message
      $("#composeForm").submit(function(e) {
        e.preventDefault();
        
        const recipient = $("#recipient").val();
        const subject = $("#subject").val();
        const priority = $("#priority").val();
        const messageText = $("#messageText").val();
        
        if (!recipient || !subject || !messageText) {
          alert("Please fill in all required fields");
          return;
        }
        
        $(".send-btn").prop("disabled", true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');
        
        $.ajax({
          url: "ajax/send_message.php",
          type: "POST",
          data: {
            recipient_email: recipient,
            subject,
            priority,
            message_text: messageText
          },
          dataType: "json",
          success: function(response) {
            if (response.status) {
              $("#composeModal").hide();
              $("#composeForm")[0].reset();
              
              const recipientName = recipientData ? recipientData.name : recipient;
              showSuccessPopup(`Your message to ${recipientName} has been sent successfully.`);
              showNotification("Success", `Message successfully sent to ${recipientName}`, "success");
              
              if (window.location.search.includes("type=sent")) {
                setTimeout(() => window.location.reload(), 1000);
              }
            } else {
              showNotification("Error", response.message || "Failed to send message", "error");
            }
            
            $(".send-btn").prop("disabled", false).html('<i class="fas fa-paper-plane"></i> Send');
          },
          error: function() {
            showNotification("Error", "Failed to send message. Please try again.", "error");
            $(".send-btn").prop("disabled", false).html('<i class="fas fa-paper-plane"></i> Send');
          }
        });
      });
    }
    
    // Search functionality
    function initSearch() {
      $("#searchInput").on("input", function() {
        const searchTerm = $(this).val().toLowerCase().trim();
        
        if (searchTerm === "") {
          $(".message-item").show();
          return;
        }
        
        $(".message-item").each(function() {
          const sender = $(this).find(".message-sender").text().toLowerCase();
          const subject = $(this).find(".message-subject").text().toLowerCase();
          const preview = $(this).find(".message-preview").text().toLowerCase();
          
          $(this).toggle(sender.includes(searchTerm) || subject.includes(searchTerm) || preview.includes(searchTerm));
        });
      });
    }
    
    // Setup notification system
    function initNotifications() {
      // Add success popup to document
      $("body").append(`
        <div id="messageSuccessPopup" class="message-success-popup">
          <div class="success-popup-content">
            <div class="success-icon"><i class="fas fa-check-circle"></i></div>
            <div class="success-title">Message Sent Successfully</div>
            <div class="success-message"></div>
            <button class="success-close-btn">Close</button>
          </div>
        </div>
      `);
      
      // Add styles for notifications
      $("head").append(`
        <style>
          .message-success-popup {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
          }
          .success-popup-content {
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            animation: popupFadeIn 0.3s ease;
          }
          @keyframes popupFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
          }
          .success-icon {
            font-size: 50px;
            color: #4BB543;
            margin-bottom: 20px;
          }
          .success-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
          }
          .success-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
          }
          .success-close-btn {
            background-color: #4BB543;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.2s;
          }
          .success-close-btn:hover {
            background-color: #3d9c36;
          }
          
          /* Notification styles */
          .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-left: 4px solid #4a6cf7;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 15px;
            min-width: 280px;
            max-width: 350px;
            z-index: 9999;
            transform: translateX(400px);
            transition: transform 0.3s ease;
          }
          .notification.show {
            transform: translateX(0);
          }
          .notification-icon {
            float: left;
            margin-right: 15px;
          }
          .notification-content {
            margin-left: 35px;
          }
          .notification-title {
            font-weight: bold;
            margin-bottom: 5px;
          }
          .notification-close {
            position: absolute;
            top: 12px;
            right: 12px;
            cursor: pointer;
          }
          .notification-success { border-left-color: #4BB543; }
          .notification-error { border-left-color: #FF5252; }
          .notification-info { border-left-color: #2196F3; }
          
          /* Highlight for new messages */
          .message-item.new-highlight {
            animation: highlightNew 5s ease;
          }
          @keyframes highlightNew {
            0%, 20% { background-color: rgba(74, 108, 247, 0.1); }
            100% { background-color: inherit; }
          }
        </style>
      `);
      
      // Handle success popup events
      $(document).on("click", ".success-close-btn", function() {
        $("#messageSuccessPopup").hide();
      });
      
      $(document).on("click", "#messageSuccessPopup", function(e) {
        if ($(e.target).is("#messageSuccessPopup")) {
          $("#messageSuccessPopup").hide();
        }
      });
      
      // Request notification permission
      if ("Notification" in window && Notification.permission !== "granted" && Notification.permission !== "denied") {
        $("body").append(`
          <div id="notificationPermission" class="notification-permission">
            <div class="permission-content">
              <div class="permission-icon"><i class="fas fa-bell"></i></div>
              <div class="permission-text">
                <strong>Enable Notifications</strong>
                <p>Get instant alerts for new messages</p>
              </div>
              <button id="enableNotifications">Allow</button>
              <button id="dismissNotifications">Later</button>
            </div>
          </div>
        `);
        
        $("head").append(`
          <style>
            .notification-permission {
              position: fixed;
              bottom: 20px;
              right: 20px;
              background-color: #fff;
              border-radius: 8px;
              box-shadow: 0 4px 12px rgba(0,0,0,0.15);
              z-index: 1000;
              width: 320px;
              overflow: hidden;
              animation: slideIn 0.3s ease;
            }
            @keyframes slideIn {
              from { transform: translateY(100px); opacity: 0; }
              to { transform: translateY(0); opacity: 1; }
            }
            .permission-content {
              display: flex;
              align-items: center;
              padding: 15px;
              flex-wrap: wrap;
            }
            .permission-icon {
              margin-right: 15px;
              font-size: 24px;
              color: #4a6cf7;
            }
            .permission-text {
              flex: 1;
            }
            .permission-text p {
              margin: 5px 0 0;
              color: #666;
              font-size: 14px;
            }
            #enableNotifications, #dismissNotifications {
              border: none;
              padding: 8px 15px;
              border-radius: 5px;
              margin-top: 10px;
              cursor: pointer;
              font-weight: 500;
            }
            #enableNotifications {
              background-color: #4a6cf7;
              color: white;
              margin-right: 10px;
            }
            #dismissNotifications {
              background-color: #f0f0f0;
              color: #333;
            }
          </style>
        `);
      }
      
      // Handle notification permission buttons
      $(document).on("click", "#enableNotifications", function() {
        Notification.requestPermission().then(function(permission) {
          if (permission === "granted") {
            $("#notificationPermission").slideUp(300, function() {
              $(this).remove();
            });
            showDesktopNotification("ZAPF Connect", "Notifications enabled successfully!");
          }
        });
      });
      
      $(document).on("click", "#dismissNotifications", function() {
        $("#notificationPermission").slideUp(300, function() {
          $(this).remove();
        });
      });
    }
    
    // Setup fallback polling for notifications
    function initPolling() {
      let lastCheckedTimestamp = new Date().getTime();
      
      // Poll for new messages every 1.5 seconds
      setInterval(function() {
        $.ajax({
          url: "ajax/check_new_messages.php",
          type: "GET",
          data: { last_checked: lastCheckedTimestamp },
          dataType: "json",
          success: function(response) {
            if (response.status) {
              lastCheckedTimestamp = response.current_timestamp;
              
              if (response.has_new) {
                // Play sound if available
                if ($("#messageSound").length) {
                  $("#messageSound")[0].play();
                }
                
                // Show notification
                showNotification("New Message", "You have new messages", "info");
                
                // Update unread count
                updateUnreadCount();
                
                // Reload if in inbox with no unread messages currently visible
                if ((window.location.search.includes("type=inbox") || !window.location.search.includes("type=")) && 
                    !$("#messageList .message-item.unread").length) {
                  window.location.reload();
                }
              }
            }
          }
        });
        
        // Check for read receipts if in sent folder
        if (window.location.search.includes("type=sent")) {
          $.ajax({
            url: "ajax/check_read_receipts.php",
            type: "GET",
            dataType: "json",
            success: function(response) {
              if (response.status && response.updates.length > 0) {
                response.updates.forEach(function(update) {
                  const messageItem = $(`.message-item[data-id='${update.message_id}']`);
                  messageItem.find(".message-status").html('<i class="fas fa-check-double read-icon"></i> Read');
                  
                  if (selectedMessageId === update.message_id) {
                    const statusInfo = `<div class="status-info read"><i class="fas fa-check-double"></i> Read on ${update.read_at}</div>`;
                    $(".message-detail .status-info").replaceWith(statusInfo);
                  }
                });
              }
            }
          });
        }
      }, 1500);
    }
    
    // Utility Functions
    
    // Mark message as read
    function markMessageAsRead(messageId) {
      $.ajax({
        url: "ajax/mark_as_read.php",
        type: "POST",
        data: { message_id: messageId },
        dataType: "json"
      });
    }
    
    // Update unread count badge
    function updateUnreadCount() {
      $.ajax({
        url: "ajax/get_unread_count.php",
        type: "GET",
        dataType: "json",
        success: function(response) {
          if (response.status) {
            const count = response.count;
            $(".menu-notification, .message-badge, .folder-badge").text(count);
            $(".menu-notification, .message-badge, .folder-badge").toggle(count > 0);
          }
        }
      });
    }
    
    // Adjust textarea height based on content
    function adjustTextareaHeight(element) {
      element.style.height = "auto";
      element.style.height = (element.scrollHeight) + "px";
      
      // If content is very large, cap height and enable scrolling
      if (element.scrollHeight > 500) {
        element.style.height = "500px";
        element.style.overflowY = "auto";
      } else {
        element.style.overflowY = "hidden";
      }
    }
    
    // Render message detail 
    function renderMessageDetail(message) {
      const priorityClass = message.priority === 'urgent' ? 'priority-urgent' : 
                            (message.priority === 'important' ? 'priority-important' : '');
      
      const importantStar = message.is_important === "1" ? 
        '<button class="important-btn active" data-id="' + message.message_id + '"><i class="fas fa-star"></i></button>' : 
        '<button class="important-btn" data-id="' + message.message_id + '"><i class="far fa-star"></i></button>';
      
      let statusInfo = '';
      if (message.type === 'sent') {
        if (message.current_status === 'read') {
          statusInfo = '<div class="status-info read"><i class="fas fa-check-double"></i> Read on ' + message.read_at + '</div>';
        } else if (message.current_status === 'delivered') {
          statusInfo = '<div class="status-info delivered"><i class="fas fa-check-double"></i> Delivered</div>';
        } else {
          statusInfo = '<div class="status-info sent"><i class="fas fa-check"></i> Sent on ' + message.created_at + '</div>';
        }
      }
      
      $("#messageDetail").html(`
        <div class="message-detail-header ${priorityClass}">
          <div class="message-detail-subject">
            ${message.subject}
            ${message.priority === 'urgent' ? '<span class="urgent-badge">Urgent</span>' : ''}
          </div>
          <div class="message-detail-actions">
            ${importantStar}
            <button class="reply-btn" data-id="${message.message_id}"><i class="fas fa-reply"></i></button>
            <button class="delete-btn" data-id="${message.message_id}"><i class="fas fa-trash"></i></button>
          </div>
        </div>
        <div class="message-detail-info">
          <div class="sender-info">
            <div class="sender-avatar">${message.sender_initial}</div>
            <div class="sender-details">
              <div class="sender-name">${message.sender_name}</div>
              <div class="sender-address">${message.sender_email}</div>
            </div>
          </div>
          <div class="message-time-info">
            <div class="message-time">${message.created_at}</div>
            <div class="message-recipient">to ${message.recipient_name}</div>
          </div>
        </div>
        ${statusInfo}
        <div class="message-detail-body">
          ${message.message_text.replace(/\n/g, '<br>')}
        </div>
      `);
      
      // Ensure scroll position is at the top after rendering
      $("#messageDetail").scrollTop(0);
    }
    
    // Show success popup
    function showSuccessPopup(message) {
      $("#messageSuccessPopup .success-message").text(message);
      $("#messageSuccessPopup").css("display", "flex");
      
      if ($("#messageSound").length) {
        $("#messageSound")[0].play();
      }
    }
    
    // Show notification
    function showNotification(title, message, type, duration = 5000) {
      const notifId = 'notif-' + Date.now();
      const notif = $(`
        <div id="${notifId}" class="notification notification-${type}">
          <div class="notification-icon">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 
                           type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
          </div>
          <div class="notification-content">
            <div class="notification-title">${title}</div>
            <div class="notification-message">${message}</div>
          </div>
          <div class="notification-close"><i class="fas fa-times"></i></div>
        </div>
      `);
      
      $("body").append(notif);
      
      setTimeout(() => notif.addClass("show"), 100);
      
      const timeout = setTimeout(() => {
        notif.removeClass("show");
        setTimeout(() => notif.remove(), 300);
      }, duration);
      
      notif.find(".notification-close").click(function() {
        clearTimeout(timeout);
        notif.removeClass("show");
        setTimeout(() => notif.remove(), 300);
      });
      
      return notifId;
    }
    
    // Show desktop notification
    function showDesktopNotification(sender, subject) {
      if ("Notification" in window && Notification.permission === "granted") {
        const notification = new Notification("New Message from ZAPF Connect", {
          body: `From: ${sender}\nSubject: ${subject}`,
          icon: "images/zapf-logo.jpg"
        });
        
        notification.onclick = function() {
          window.focus();
          notification.close();
        };
        
        setTimeout(() => notification.close(), 10000);
        return true;
      }
      return false;
    }
  });