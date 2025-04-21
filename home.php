<?php
// Start session
session_start();

// Include authentication functions
require_once 'auth.php';

// Process login form
$loginMessage = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password']; // In production, use password_hash() and password_verify()
    
    $authResult = authenticateUser($username, $password);
    
    if ($authResult['status']) {
        $_SESSION['user'] = $authResult['user'];
        header("Location: dashboard.php");
        exit;
    } else {
        $loginMessage = $authResult['message'];
    }
}

// Process signup form
$signupMessage = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $department = $_POST['department'];
    $password = $_POST['newPassword']; // In production, use password_hash()
    
    // Validate passwords match
    if ($_POST['newPassword'] != $_POST['confirmPassword']) {
        $signupMessage = 'Passwords do not match!';
    } else {
        $registrationResult = registerUser($fullName, $email, $department, $password);
        $signupMessage = $registrationResult['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ZAPF-Connect | Digital Communication Platform</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <link rel="stylesheet" href="styles/home.css" />
  </head>
  <body>
    <div class="container">
      <!-- Header Section -->
      <header>
        <div class="logo">
          <img src="images/zapf-logo.jpg" alt="ZAPF Connect Logo" />
          <div class="logo-text">ZAPF<span>Connect</span></div>
        </div>

        <nav>
          <ul>
            <li><a href="#features">Features</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>
          </ul>
        </nav>

        <div class="auth-buttons">
          <button class="btn btn-outline" id="loginBtn">Login</button>
          <button class="btn" id="signupBtn">Sign Up</button>
        </div>
      </header>

      <!-- Hero Section -->
      <section class="hero">
        <div class="hero-content">
          <h1 class="hero-title">Welcome to ZAPF<span>Connect</span></h1>
          <p class="hero-subtitle">
            A comprehensive digital communication platform designed to
            streamline remote collaboration and enhance team productivity.
          </p>
          <div class="hero-btns">
            <button class="btn" id="getStartedBtn">Get Started</button>
            <button class="btn btn-outline">Learn More</button>
          </div>
        </div>

        <div class="hero-image">
          <img src="images/dash.PNG" alt="ZAPF Connect Dashboard Preview" />
        </div>
      </section>

      <!-- Features Section -->
      <section class="features" id="features">
        <h2 class="section-title">Enhancing Communication</h2>
        <p class="section-subtitle">
          ZAPF Connect offers a suite of powerful features designed to solve
          your organization's communication challenges.
        </p>

        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-user-shield"></i>
            </div>
            <h3 class="feature-title">User Account Management</h3>
            <p class="feature-desc">
              Easily manage staff accounts with role-based access control and
              secure authentication.
            </p>
          </div>

          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-bell"></i>
            </div>
            <h3 class="feature-title">Real-time Notifications</h3>
            <p class="feature-desc">
              Never miss an important update with instant alerts and
              customizable notification preferences.
            </p>
          </div>

          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-comments"></i>
            </div>
            <h3 class="feature-title">Unified Messaging</h3>
            <p class="feature-desc">
              Consolidate all communications in one platform to eliminate
              fragmentation and improve clarity.
            </p>
          </div>

          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-chart-line"></i>
            </div>
            <h3 class="feature-title">Analytics & Reporting</h3>
            <p class="feature-desc">
              Gain valuable insights into communication patterns and track
              engagement metrics.
            </p>
          </div>

          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-smile"></i>
            </div>
            <h3 class="feature-title">Enhanced User Experience</h3>
            <p class="feature-desc">
              Enjoy an intuitive interface designed for efficiency and ease of
              use.
            </p>
          </div>
        </div>
      </section>

      <!-- Login/Signup Sidebar -->
      <div class="sidebar-overlay" id="sidebarOverlay"></div>

      <div class="login-sidebar" id="loginSidebar">
        <span class="sidebar-close" id="sidebarClose">
          <i class="fas fa-times"></i>
        </span>

        <h2 class="login-title" id="sidebarTitle">Login to ZAPF Connect</h2>

        <!-- Login Form -->
        <form class="login-form" id="loginForm" style="display: block" method="post" action="">
          <div class="form-group">
            <label for="username" class="form-label">Username</label>
            <input
              type="text"
              id="username"
              name="username"
              class="form-input"
              placeholder="Enter your username"
              required
            />
          </div>

          <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input
              type="password"
              id="password"
              name="password"
              class="form-input"
              placeholder="Enter your password"
              required
            />
          </div>

          <?php if ($loginMessage): ?>
            <div class="alert <?php echo (strpos($loginMessage, 'Invalid') !== false) ? 'alert-danger' : 'alert-success'; ?>">
                <?php echo $loginMessage; ?>
            </div>
          <?php endif; ?>

          <button type="submit" name="login" class="form-button">Login</button>

          <div class="form-switch">
            Don't have an account? <a href="#" id="switchToSignup">Sign Up</a>
          </div>
        </form>

        <!-- Signup Form -->
        <form class="login-form" id="signupForm" style="display: none" method="post" action="">
          <div class="form-group">
            <label for="fullName" class="form-label">Full Name</label>
            <input
              type="text"
              id="fullName"
              name="fullName"
              class="form-input"
              placeholder="Enter your full name"
              required
            />
          </div>

          <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input
              type="email"
              id="email"
              name="email"
              class="form-input"
              placeholder="Enter your email"
              required
            />
          </div>

          <div class="form-group">
            <label for="department" class="form-label">Department</label>
            <input
              type="text"
              id="department"
              name="department"
              class="form-input"
              placeholder="Enter your department"
              required
            />
          </div>

          <div class="form-group">
            <label for="newPassword" class="form-label">Password</label>
            <input
              type="password"
              id="newPassword"
              name="newPassword"
              class="form-input"
              placeholder="Create a password"
              required
            />
          </div>

          <div class="form-group">
            <label for="confirmPassword" class="form-label">Confirm Password</label>
            <input
              type="password"
              id="confirmPassword"
              name="confirmPassword"
              class="form-input"
              placeholder="Confirm your password"
              required
            />
          </div>

          <?php if ($signupMessage): ?>
            <div class="alert <?php echo (strpos($signupMessage, 'successfully') !== false) ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $signupMessage; ?>
            </div>
          <?php endif; ?>

          <button type="submit" name="signup" class="form-button">Sign Up</button>

          <div class="form-switch">
            Already have an account? <a href="#" id="switchToLogin">Login</a>
          </div>
        </form>
      </div>
    </div>

    <script>
      // DOM Elements
      const loginBtn = document.getElementById("loginBtn");
      const signupBtn = document.getElementById("signupBtn");
      const getStartedBtn = document.getElementById("getStartedBtn");
      const sidebarOverlay = document.getElementById("sidebarOverlay");
      const loginSidebar = document.getElementById("loginSidebar");
      const sidebarClose = document.getElementById("sidebarClose");
      const sidebarTitle = document.getElementById("sidebarTitle");
      const loginForm = document.getElementById("loginForm");
      const signupForm = document.getElementById("signupForm");
      const switchToSignup = document.getElementById("switchToSignup");
      const switchToLogin = document.getElementById("switchToLogin");

      // Open login sidebar
      function openLoginSidebar() {
        loginSidebar.classList.add("active");
        sidebarOverlay.classList.add("active");
        sidebarTitle.textContent = "Login to ZAPF Connect";
        loginForm.style.display = "block";
        signupForm.style.display = "none";
      }

      // Open signup sidebar
      function openSignupSidebar() {
        loginSidebar.classList.add("active");
        sidebarOverlay.classList.add("active");
        sidebarTitle.textContent = "Create ZAPF Connect Account";
        loginForm.style.display = "none";
        signupForm.style.display = "block";
      }

      // Close sidebar
      function closeSidebar() {
        loginSidebar.classList.remove("active");
        sidebarOverlay.classList.remove("active");
      }

      // Event Listeners
      loginBtn.addEventListener("click", openLoginSidebar);
      signupBtn.addEventListener("click", openSignupSidebar);
      getStartedBtn.addEventListener("click", openSignupSidebar);
      sidebarClose.addEventListener("click", closeSidebar);
      sidebarOverlay.addEventListener("click", closeSidebar);

      // Switch between login and signup forms
      switchToSignup.addEventListener("click", function (e) {
        e.preventDefault();
        sidebarTitle.textContent = "Create ZAPF Connect Account";
        loginForm.style.display = "none";
        signupForm.style.display = "block";
      });

      switchToLogin.addEventListener("click", function (e) {
        e.preventDefault();
        sidebarTitle.textContent = "Login to ZAPF Connect";
        signupForm.style.display = "none";
        loginForm.style.display = "block";
      });
    </script>
  </body>
</html>