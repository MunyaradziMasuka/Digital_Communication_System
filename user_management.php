<?php
// Start session
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// Get user information from session
$user = $_SESSION['user'];
$fullName = $user['full_name'];
$department = $user['department'];
$username = $user['username'];
$userRole = $user['role'];

// Check if user has admin role
$isAdmin = ($userRole === 'admin');

// Database connection
$conn = new mysqli("localhost", "root", "", "zapf_connect");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions for adding/editing users
if ($_SERVER["REQUEST_METHOD"] == "POST" && $isAdmin) {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        // Add new user
        if ($action === 'add') {
            $newUsername = $conn->real_escape_string($_POST['username']);
            $newEmail = $conn->real_escape_string($_POST['email']);
            $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $newFullName = $conn->real_escape_string($_POST['full_name']);
            $newRole = $conn->real_escape_string($_POST['role']);
            $newDepartment = $conn->real_escape_string($_POST['department']);
            $newStatus = isset($_POST['is_active']) ? 1 : 0;
            
            $sql = "INSERT INTO users (username, email, password, full_name, role, department, is_active) 
                    VALUES ('$newUsername', '$newEmail', '$newPassword', '$newFullName', '$newRole', '$newDepartment', $newStatus)";
            
            if ($conn->query($sql) === TRUE) {
                $successMessage = "New user created successfully";
            } else {
                $errorMessage = "Error: " . $sql . "<br>" . $conn->error;
            }
        }
        
        // Edit existing user
        if ($action === 'edit' && isset($_POST['user_id'])) {
            $userId = $conn->real_escape_string($_POST['user_id']);
            $editUsername = $conn->real_escape_string($_POST['username']);
            $editEmail = $conn->real_escape_string($_POST['email']);
            $editFullName = $conn->real_escape_string($_POST['full_name']);
            $editRole = $conn->real_escape_string($_POST['role']);
            $editDepartment = $conn->real_escape_string($_POST['department']);
            $editStatus = isset($_POST['is_active']) ? 1 : 0;
            
            $sql = "UPDATE users SET 
                    username = '$editUsername',
                    email = '$editEmail',
                    full_name = '$editFullName',
                    role = '$editRole',
                    department = '$editDepartment',
                    is_active = $editStatus
                    WHERE user_id = $userId";
            
            // Update password only if provided
            if (!empty($_POST['password'])) {
                $editPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $sql = "UPDATE users SET 
                        username = '$editUsername',
                        email = '$editEmail',
                        password = '$editPassword',
                        full_name = '$editFullName',
                        role = '$editRole',
                        department = '$editDepartment',
                        is_active = $editStatus
                        WHERE user_id = $userId";
            }
            
            if ($conn->query($sql) === TRUE) {
                $successMessage = "User updated successfully";
            } else {
                $errorMessage = "Error: " . $sql . "<br>" . $conn->error;
            }
        }
        
        // Delete user
        if ($action === 'delete' && isset($_POST['user_id'])) {
            $userId = $conn->real_escape_string($_POST['user_id']);
            
            $sql = "DELETE FROM users WHERE user_id = $userId";
            
            if ($conn->query($sql) === TRUE) {
                $successMessage = "User deleted successfully";
            } else {
                $errorMessage = "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }
}

// Get users from database
$users = [];
if ($isAdmin) {
    $sql = "SELECT user_id, username, email, full_name, role, department, is_active, last_login FROM users";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZAPF-Connect | User Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="styles/users.css">
    <style>
    
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="images/zapf-logo.jpg" alt="ZAPF Connect Logo" class="sidebar-logo">
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
                    <div class="menu-item active">
                        <div class="menu-icon"><i class="fas fa-users"></i></div>
                        <div class="menu-text">User Management</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-icon"><i class="fas fa-comments"></i></div>
                        <div class="menu-text">Messages</div>
                        <div class="menu-notification">5</div>
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
                    <h1>User Management</h1>
                    <div class="header-subtitle">Manage system users and their permissions</div>
                </div>
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Search..." id="searchInput">
                    </div>
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>
                    <button class="message-btn">
                        <i class="fas fa-envelope"></i>
                        <span class="message-badge">5</span>
                    </button>
                    <button class="user-dropdown">
                        <div class="dropdown-avatar"><?php echo strtoupper(substr($username, 0, 2)); ?></div>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </button>
                </div>
            </div>

            <?php if (isset($successMessage)): ?>
                <div class="alert alert-success">
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($errorMessage)): ?>
                <div class="alert alert-danger">
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
                <!-- Admin User Management Content -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">User Management</h3>
                        <button class="btn btn-success" id="addUserBtn">
                            <i class="fas fa-user-plus"></i> Add User
                        </button>
                    </div>

                    <div class="toolbar">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="userSearchInput" placeholder="Search users...">
                        </div>
                        <div class="filter-options">
                            <select class="filter-select" id="roleFilter">
                                <option value="">All Roles</option>
                                <option value="admin">Administrator</option>
                                <option value="member">Member</option>
                            </select>
                            <select class="filter-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="user-table" id="userTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr data-id="<?php echo $user['user_id']; ?>" 
                                        data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                        data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                        data-fullname="<?php echo htmlspecialchars($user['full_name']); ?>"
                                        data-role="<?php echo htmlspecialchars($user['role']); ?>"
                                        data-department="<?php echo htmlspecialchars($user['department']); ?>"
                                        data-status="<?php echo $user['is_active']; ?>">
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                                        <td><?php echo htmlspecialchars($user['department']); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $user['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $user['last_login'] ? htmlspecialchars($user['last_login']) : 'Never'; ?></td>
                                        <td>
                                            <div class="action-btn edit" onclick="editUser(<?php echo $user['user_id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </div>
                                            <div class="action-btn delete" onclick="deleteUser(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <!-- No results message -->
                        <div class="no-results" id="noResults">
                            No users found matching your search criteria.
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Non-Admin Content -->
                <div class="contact-admin">
                    <h2><i class="fas fa-lock"></i> Access Restricted</h2>
                    <p>Sorry, you don't have permission to access the User Management area. This section is only available to administrators.</p>
                    <p>If you need access or have questions, please contact the system administrator.</p>
                    <a href="#" class="contact-btn" onclick="contactAdmin()">
                        <i class="fas fa-envelope"></i> Contact Administrator
                    </a>
                </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="footer-actions">
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Add/Edit User Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New User</h3>
                <span class="close">&times;</span>
            </div>
            <form id="userForm" method="post" action="user_management.php">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="user_id" id="userId" value="">
                
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="full_name">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password">
                    <small id="passwordHelp" style="display: none; color: #6c757d;">Leave blank to keep current password</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="role">Role</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="member">Member</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="department">Department</label>
                    <input type="text" class="form-control" id="department" name="department" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <div>
                        <label>
                            <input type="checkbox" id="is_active" name="is_active" value="1" checked> Active
                        </label>
                    </div>
                </div>
                
                <div class="form-buttons">
                    <button type="button" class="btn" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
                <span class="close">&times;</span>
            </div>
            <p>Are you sure you want to delete user <span id="deleteUserName"></span>? This action cannot be undone.</p>
            <form id="deleteForm" method="post" action="user_management.php">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="user_id" id="deleteUserId" value="">
                
                <div class="form-buttons">
                    <button type="button" class="btn" id="deleteCancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Contact Admin Modal for non-admin users -->
    <div id="contactModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Contact Administrator</h3>
                <span class="close">&times;</span>
            </div>
            <form id="contactForm">
                <div class="form-group">
                    <label class="form-label" for="messageSubject">Subject</label>
                    <input type="text" class="form-control" id="messageSubject" value="Request for User Management Access" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="messageContent">Message</label>
                    <textarea class="form-control" id="messageContent" rows="5" required>Hello Administrator,

I would like to request access to the User Management section of ZAPF Connect.

Thank you,
<?php echo htmlspecialchars($fullName); ?>
<?php echo htmlspecialchars($department); ?> Department</textarea>
                </div>
                
                <div class="form-buttons">
                    <button type="button" class="btn" id="contactCancelBtn">Cancel</button>
                    <button type="button" class="btn btn-primary" id="sendMessageBtn">Send Message</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // DOM Elements
        const userModal = document.getElementById('userModal');
        const deleteModal = document.getElementById('deleteModal');
        const contactModal = document.getElementById('contactModal');
        const addUserBtn = document.getElementById('addUserBtn');
        const modalTitle = document.getElementById('modalTitle');
        const formAction = document.getElementById('formAction');
        const userId = document.getElementById('userId');
        const passwordHelp = document.getElementById('passwordHelp');
        const userSearchInput = document.getElementById('userSearchInput');
        const roleFilter = document.getElementById('roleFilter');
        const statusFilter = document.getElementById('statusFilter');
        const noResults = document.getElementById('noResults');
        
        // Modal close buttons
        const closeButtons = document.querySelectorAll('.close');
        const cancelBtn = document.getElementById('cancelBtn');
        const deleteCancelBtn = document.getElementById('deleteCancelBtn');
        const contactCancelBtn = document.getElementById('contactCancelBtn');
        
        // Sidebar toggle functionality
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
        });
        
        // Add User button
        if (addUserBtn) {
            addUserBtn.addEventListener('click', function() {
                modalTitle.textContent = 'Add New User';
                formAction.value = 'add';
                userId.value = '';
                passwordHelp.style.display = 'none';
                document.getElementById('userForm').reset();
                document.getElementById('password').required = true;
                userModal.style.display = 'block';
                
                // Reset scroll position
                setTimeout(() => {
                    document.querySelector('.modal-content').scrollTop = 0;
                }, 10);
            });
        }
        
        // Edit User function
        function editUser(id) {
    const row = document.querySelector(`tr[data-id="${id}"]`);
    if (row) {
        modalTitle.textContent = 'Edit User';
        formAction.value = 'edit';
        userId.value = id;
        
        document.getElementById('username').value = row.getAttribute('data-username');
        document.getElementById('email').value = row.getAttribute('data-email');
        document.getElementById('full_name').value = row.getAttribute('data-fullname');
        document.getElementById('role').value = row.getAttribute('data-role');
        document.getElementById('department').value = row.getAttribute('data-department');
        document.getElementById('is_active').checked = row.getAttribute('data-status') === '1';
        
        // Password is optional when editing
        document.getElementById('password').required = false;
        passwordHelp.style.display = 'block';
        document.getElementById('password').value = '';
        
        userModal.style.display = 'block';
        
        // Reset scroll position to top of modal
        const modalContent = document.querySelector('.modal-content');
        if (modalContent) {
            // Ensure scroll reset after modal is displayed
            setTimeout(() => {
                modalContent.scrollTop = 0;
            }, 10);
        }
    }
}     
        // Delete User function
        function deleteUser(id, name) {
            document.getElementById('deleteUserName').textContent = name;
            document.getElementById('deleteUserId').value = id;
            deleteModal.style.display = 'block';
        }
        
        // Contact admin function for non-admin users
        function contactAdmin() {
            contactModal.style.display = 'block';
        }
        
        // Search and filter functionality
        function filterTable() {
            const searchTerm = userSearchInput.value.toLowerCase();
            const roleValue = roleFilter.value.toLowerCase();
            const statusValue = statusFilter.value;
            
            const rows = document.getElementById('userTable').querySelectorAll('tbody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const username = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const fullName = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const role = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                const department = row.querySelector('td:nth-child(5)').textContent.toLowerCase();
                const status = row.querySelector('td:nth-child(6)').textContent.toLowerCase();
                const rowStatus = row.getAttribute('data-status');
                
                const matchesSearch = username.includes(searchTerm) || 
                                    fullName.includes(searchTerm) || 
                                    email.includes(searchTerm) ||
                                    department.includes(searchTerm);
                                    
                const matchesRole = roleValue === '' || role === roleValue;
                const matchesStatus = statusValue === '' || rowStatus === statusValue;
                
                if (matchesSearch && matchesRole && matchesStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show/hide "no results" message
            if (visibleCount === 0) {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        }
        
        // Add event listeners for search and filters
        if (userSearchInput) {
            userSearchInput.addEventListener('input', filterTable);
        }
        if (roleFilter) {
            roleFilter.addEventListener('change', filterTable);
        }
        if (statusFilter) {
            statusFilter.addEventListener('change', filterTable);
        }
        
        // Send message in contact modal
        const sendMessageBtn = document.getElementById('sendMessageBtn');
        if (sendMessageBtn) {
            sendMessageBtn.addEventListener('click', function() {
                alert('Your message has been sent to the administrator.');
                contactModal.style.display = 'none';
            });
        }
        
        // Close modals when clicking on X or Cancel buttons
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                userModal.style.display = 'none';
                deleteModal.style.display = 'none';
                contactModal.style.display = 'none';
            });
        });
        
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                userModal.style.display = 'none';
            });
        }
        
        if (deleteCancelBtn) {
            deleteCancelBtn.addEventListener('click', function() {
                deleteModal.style.display = 'none';
            });
        }
        
        if (contactCancelBtn) {
            contactCancelBtn.addEventListener('click', function() {
                contactModal.style.display = 'none';
            });
        }
        
        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === userModal) {
                userModal.style.display = 'none';
            }
            if (event.target === deleteModal) {
                deleteModal.style.display = 'none';
            }
            if (event.target === contactModal) {
                contactModal.style.display = 'none';
            }
        });
        
        // Auto-hide alert messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>