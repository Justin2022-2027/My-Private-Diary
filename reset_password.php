<?php
session_start();
require 'config/db_connect.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "error";
    } else if (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
        $message_type = "error";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password in database
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $_SESSION['reset_email']);
        
        if ($stmt->execute()) {
            // Clear all session variables
            session_unset();
            session_destroy();
            
            // Start new session for success message
            session_start();
            $_SESSION['success_message'] = "Password has been reset successfully. You can now login with your new password.";
            header("Location: login.php");
            exit();
        } else {
            $message = "Failed to reset password. Please try again.";
            $message_type = "error";
        }
        
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - My Private Diary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Copy the same CSS from forgot_password.php */
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
            border: none;
        }

        .btn-primary {
            background-color: white;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-primary:hover {
            background-color: var(--primary);
            color: var(--white);
            transform: translateY(-1px);
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
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <i class="fas fa-book-open"></i>
                    My Private Diary
                </a>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="auth-form">
                <h1 class="form-title">Reset Password</h1>
                <p class="form-subtitle">Create a new password for your account</p>

                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="password" class="form-label">New Password</label>
                        <div class="password-input">
                            <input type="password" id="password" name="password" class="form-input" placeholder="Enter your new password" required>
                            <button type="button" class="toggle-password" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <div class="password-input">
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Confirm your new password" required>
                            <button type="button" class="toggle-password" id="toggleConfirmPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Reset Password
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Toggle password visibility
        function setupPasswordToggle(inputId, toggleId) {
            const input = document.getElementById(inputId);
            const toggle = document.getElementById(toggleId);
            
            toggle.addEventListener('click', function() {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }

        setupPasswordToggle('password', 'togglePassword');
        setupPasswordToggle('confirm_password', 'toggleConfirmPassword');
    </script>
</body>
</html> 