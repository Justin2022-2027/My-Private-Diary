<?php
session_start();

// Check if already logged in as admin
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Simple admin credentials (you can change these)
    $admin_username = 'admin';
    $admin_password = 'admin123'; // Change this to a secure password

    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - My Private Diary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ff9fb0;
            --primary-dark: #ff7a93;
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

        @media (max-width: 768px) {
            .auth-form {
                margin: 2rem auto;
                padding: 1.5rem;
            }

            .form-title {
                font-size: 1.75rem;
            }
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

                <h1 class="form-title">Admin Login</h1>
                <p class="form-subtitle">Access the admin dashboard to view analytics and manage the system.</p>

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

                <form id="adminLoginForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-input"
                               placeholder="Enter admin username" required>
                        <div class="error-message" id="usernameError"></div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-input">
                            <input type="password" id="password" name="password" class="form-input"
                                   placeholder="Enter admin password" required>
                            <button type="button" class="toggle-password" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="error-message" id="passwordError"></div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Login 
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('adminLoginForm');
            const username = document.getElementById('username');
            const password = document.getElementById('password');
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

                // Username validation
                const usernameError = document.getElementById('usernameError');
                if (username.value.trim() === '') {
                    username.classList.add('error');
                    usernameError.classList.add('show');
                    usernameError.textContent = 'Username is required.';
                    isValid = false;
                } else {
                    username.classList.remove('error');
                    usernameError.classList.remove('show');
                }

                // Password validation
                const passwordError = document.getElementById('passwordError');
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