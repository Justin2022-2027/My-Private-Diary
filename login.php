<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

// Start the session
session_start();

// Clear any problematic session data that might cause issues
if (isset($_GET['clear_session'])) {
    session_destroy();
    session_start();
}

// Include database connection
require 'db_connect.php'; // Updated path to point to the root directory

$error = '';
$success = '';

// Display success message if password was reset
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_email = trim($_POST['login_email']);
    $login_password = trim($_POST['login_password']);
    
    // Validation array to store all errors
    $errors = [];
    
    // Email Validation
    if (empty($login_email)) {
        $errors['login_email'] = "Email is required.";
    } elseif (!filter_var($login_email, FILTER_VALIDATE_EMAIL)) {
        $errors['login_email'] = "Invalid email format.";
    } elseif (strlen($login_email) > 150) {
        $errors['login_email'] = "Email must not exceed 150 characters.";
    }
    
    // Password Validation
    if (empty($login_password)) {
        $errors['login_password'] = "Password is required.";
    }
    
    // If no validation errors, check credentials
    if (empty($errors)) {
        // Check if user exists in signup table (only user table now)
        $stmt = $conn->prepare("SELECT user_id, full_name, email, password FROM signup WHERE email = ?");
        if (!$stmt) {
            die("Database query preparation failed: " . $conn->error);
        }
        $stmt->bind_param("s", $login_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($login_password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];

                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $errors['login_password'] = "Invalid email or password.";
            }
        } else {
            $errors['login_email'] = "Invalid email or password.";
        }
        $stmt->close();
    }
    
    // Store errors in session to display them
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $_POST;
    }
}

// Get any stored errors from session
$errors = isset($_SESSION['form_errors']) ? $_SESSION['form_errors'] : [];
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - My Private Diary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: hsl(351, 63%, 76%);
            --primary-dark: hwb(350 76% 0%);
            --secondary: #f59e0b;
            --success: #10b981;
            --danger: #ef4444;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray: #64748b;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: var(--light);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .header {
            background-color: var(--white);
            box-shadow: var(--shadow-sm);
            padding: 1rem 0;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--dark);
            font-weight: 700;
            font-size: 1.25rem;
        }

        .logo i {
            color: var(--primary);
            font-size: 1.5rem;
        }

        .auth-form {
            max-width: 500px;
            margin: 4rem auto;
            padding: 2rem;
            background-color: var(--white);
            border-radius: 1rem;
            box-shadow: var(--shadow);
            position: relative;
        }

        .form-title {
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--dark);
        }

        .form-subtitle {
            text-align: center;
            color: var(--gray);
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
        }

        .form-input::placeholder {
            color: var(--gray);
        }

        .password-input {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
        }

        .toggle-password:hover {
            color: var(--primary);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .forgot-password {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.875rem;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: white;
            color: var(--primary);
            border: 2px solid var(--primary);
            font-size: medium;
        }

        .btn-primary:hover {
            background-color: var(--primary);
            color: var(--white);
            transform: translateY(-1px);
        }

        .form-footer {
            text-align: center;
            margin-top: 2rem;
            color: var(--gray);
        }

        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: var(--danger);
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: none;
        }

        .form-input.error {
            border-color: var(--danger);
        }

        .form-input.error:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .error-message.show {
            display: block;
        }

        .message {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .message.error {
            background-color: #fef2f2;
            color: var(--danger);
            border: 1px solid #fee2e2;
        }

        .message.success {
            background-color: #f0fdf4;
            color: var(--success);
            border: 1px solid #dcfce7;
        }

        .social-login {
            margin-top: 2rem;
            text-align: center;
        }

        .social-login p {
            color: var(--gray);
            margin-bottom: 1rem;
            position: relative;
        }

        .social-login p::before,
        .social-login p::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 35%;
            transform: translateY(-50%);
            height: 1px;
            background-color: var(--gray);
        }

        .social-login p::before {
            left: 0;
        }

        .social-login p::after {
            right: 0;
        }

        .social-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            background-color: var(--white);
            border: 1px solid var(--gray);
            color: var(--dark);
            transition: var(--transition);
        }

        .social-btn:hover {
            background-color: var(--light);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .auth-form {
                margin: 2rem auto;
                padding: 1.5rem;
            }

            .form-title {
                font-size: 1.75rem;
            }

            .form-options {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
        }

        .back-button {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .back-button:hover {
            color: var(--primary);
            transform: translateX(-5px);
        }
        
        .back-button i {
            transition: var(--transition);
        }
        
        .social-btn:hover {
            background-color: var(--light);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <main>
        <div class="container">
            <div class="auth-form">
                <button class="back-button" onclick="window.location.href='index.php'">
                    <i class="fas fa-arrow-left"></i>
                    Back 
                </button>
                
                <h1 class="form-title">Welcome Back</h1>
                <p class="form-subtitle">Sign in to access your private diary and continue your journey.</p>

                <?php if ($error): ?>
                    <div class="message error">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="message success">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="message <?php echo $_SESSION['message_type'] === 'success' ? 'success' : 'error'; ?>">
                        <?php 
                        echo htmlspecialchars($_SESSION['message']);
                        unset($_SESSION['message']);
                        unset($_SESSION['message_type']);
                        ?>
                    </div>
                <?php endif; ?>

                <form id="loginForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="login_email" class="form-label">Email Address</label>
                        <input type="email" id="login_email" name="login_email" class="form-input <?php echo isset($errors['login_email']) ? 'error' : ''; ?>" 
                               placeholder="Enter your email" required
                               value="<?php echo htmlspecialchars($form_data['login_email'] ?? ''); ?>">
                        <div class="error-message <?php echo isset($errors['login_email']) ? 'show' : ''; ?>" id="login_emailError">
                            <?php echo $errors['login_email'] ?? ''; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="login_password" class="form-label">Password</label>
                        <div class="password-input">
                            <input type="password" id="login_password" name="login_password" class="form-input <?php echo isset($errors['login_password']) ? 'error' : ''; ?>" 
                                   placeholder="Enter your password" required>
                            <button type="button" class="toggle-password" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="error-message <?php echo isset($errors['login_password']) ? 'show' : ''; ?>" id="login_passwordError">
                            <?php echo $errors['login_password'] ?? ''; ?>
                        </div>
                    </div>

                    <div class="form-options">
                        <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Sign In
                    </button>

                    <div class="social-login">
                        <p>Or continue with</p>
                        <div class="social-buttons">
                            <a href="google_auth.php" class="social-btn" title="Sign in with Google" aria-label="Sign in with Google">
                                <i class="fab fa-google"></i>
                            </a>
                        </div>
                    </div>
                </form>

                <div class="form-footer">
                    Don't have an account? <a href="signup.php">Sign up</a>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <br><br>
                        <details style="margin-top: 1rem; text-align: left;">
                            <summary style="cursor: pointer; color: var(--gray); font-size: 0.875rem;">Having trouble logging in?</summary>
                            <div style="margin-top: 0.5rem; padding: 1rem; background: #f8fafc; border-radius: 0.5rem; font-size: 0.875rem;">
                                <p><strong>Common solutions:</strong></p>
                                <ul style="margin: 0.5rem 0;">
                                    <li>Check if your email is correctly spelled</li>
                                    <li>Ensure your password is correct (case-sensitive)</li>
                                    <li>Try <a href="login.php?clear_session=1" style="color: var(--primary);">clearing your session</a> if you're having issues</li>
                                    <li>Run the <a href="repair_database.php" style="color: var(--primary);">database repair utility</a> if problems persist</li>
                                </ul>
                                <p>If issues continue, please contact support.</p>
                            </div>
                        </details>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const email = document.getElementById('login_email');
            const password = document.getElementById('login_password');
            const togglePassword = document.getElementById('togglePassword');
            
            // Toggle password visibility
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
            
            // Form validation
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Email validation
                const emailError = document.getElementById('login_emailError');
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email.value)) {
                    email.classList.add('error');
                    emailError.classList.add('show');
                    emailError.textContent = 'Please enter a valid email address.';
                    isValid = false;
                } else {
                    email.classList.remove('error');
                    emailError.classList.remove('show');
                }
                
                // Password validation
                const passwordError = document.getElementById('login_passwordError');
                if (password.value.trim() === '') {
                    password.classList.add('error');
                    passwordError.classList.add('show');
                    passwordError.textContent = 'Password is required.';
                    isValid = false;
                } else {
                    password.classList.remove('error');
                    passwordError.classList.remove('show');
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>