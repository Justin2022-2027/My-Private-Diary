<?php
// Start the session
session_start();
require 'db_connect.php'; // Updated path to point to the root directory

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $birthdate = trim($_POST['birthdate']);
    
    // Validation array to store all errors
    $errors = [];
    
    // Full Name Validation
    if (empty($full_name)) {
        $errors['full_name'] = "Full name is required.";
    } elseif (strlen($full_name) < 2) {
        $errors['full_name'] = "Full name must be at least 2 characters long.";
    } elseif (!preg_match("/^[a-zA-Z\s'-]+$/", $full_name)) {
        $errors['full_name'] = "Full name can only contain letters, spaces, hyphens, and apostrophes.";
    }
    
    // Email Validation
    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    } elseif (strlen($email) > 150) {
        $errors['email'] = "Email must not exceed 150 characters.";
    } else {
        // Check if email already exists in signup table (only user table now)
        $stmt = $conn->prepare("SELECT email FROM signup WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors['email'] = "Email already exists.";
        }
        $stmt->close();
    }
    
    // Password Validation
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } else {
        if (strlen($password) < 8) {
            $errors['password'] = "Password must be at least 8 characters long.";
        }
        if (!preg_match("/[A-Z]/", $password)) {
            $errors['password'] = "Password must contain at least one uppercase letter.";
        }
        if (!preg_match("/[a-z]/", $password)) {
            $errors['password'] = "Password must contain at least one lowercase letter.";
        }
        if (!preg_match("/[0-9]/", $password)) {
            $errors['password'] = "Password must contain at least one number.";
        }
        if (!preg_match("/[^A-Za-z0-9]/", $password)) {
            $errors['password'] = "Password must contain at least one special character.";
        }
    }
    
    // Confirm Password Validation
    if (empty($confirm_password)) {
        $errors['confirm_password'] = "Please confirm your password.";
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }
    
    // Birthdate Validation
    if (empty($birthdate)) {
        $errors['birthdate'] = "Birthdate is required.";
    } else {
        $birthdate_obj = new DateTime($birthdate);
        $today = new DateTime();
        $age = $today->diff($birthdate_obj)->y;
        
        if ($age < 13) {
            $errors['birthdate'] = "You must be at least 13 years old to register.";
        } elseif ($age > 120) {
            $errors['birthdate'] = "Please enter a valid birthdate.";
        }
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into signup table (only user table now)
        $stmt = $conn->prepare("INSERT INTO signup (full_name, email, password, birthdate) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $full_name, $email, $hashed_password, $birthdate);

        if ($stmt->execute()) {
            // Set session variables
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;

            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Something went wrong during registration. Please try again.";
        }
        $stmt->close();
    } else {
        // Store errors in session to display them
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
    <title>Sign Up - My Private Diary</title>
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

        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .form-check input {
            width: 1rem;
            height: 1rem;
        }

        .form-check label {
            font-size: 0.875rem;
            color: var(--gray);
        }

        .form-check a {
            color: var(--primary);
            text-decoration: none;
        }

        .form-check a:hover {
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
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .error-message.show {
            display: block;
            opacity: 1;
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

        @media (max-width: 768px) {
            .auth-form {
                margin: 2rem auto;
                padding: 1.5rem;
            }

            .form-title {
                font-size: 1.75rem;
            }
        }

        .form-input.error {
            border-color: var(--danger);
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .password-requirements {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: var(--gray);
        }
        
        .password-requirements ul {
            list-style: none;
            padding-left: 1rem;
            margin-top: 0.5rem;
        }
        
        .password-requirements li {
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .password-requirements li i {
            font-size: 0.75rem;
        }
        
        .password-requirements li.valid {
            color: var(--success);
        }
        
        .password-requirements li.invalid {
            color: var(--danger);
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
            font-size: 1.25rem;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="index.html" class="logo">
                    <i class="fas fa-book-open"></i>
                    My Private Diary
                </a>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="auth-form">
                <button class="back-button" onclick="window.location.href='index.html'">
                    <i class="fas fa-arrow-left"></i>
                    Back 
                </button>
                
                <h1 class="form-title">Create Your Account</h1>
                <p class="form-subtitle">Join My Private Diary and start your personal journey today.</p>

                <?php if ($error): ?>
                    <div class="message error">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form id="signupForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-input <?php echo isset($errors['full_name']) ? 'error' : ''; ?>" 
                               placeholder="Enter your full name" required
                               value="<?php echo htmlspecialchars($form_data['full_name'] ?? ''); ?>">
                        <div class="error-message <?php echo isset($errors['full_name']) ? 'show' : ''; ?>" id="nameError">
                            <?php echo $errors['full_name'] ?? ''; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input <?php echo isset($errors['email']) ? 'error' : ''; ?>" 
                               placeholder="Enter your email" required
                               value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">
                        <div class="error-message <?php echo isset($errors['email']) ? 'show' : ''; ?>" id="emailError">
                            <?php echo $errors['email'] ?? ''; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-input">
                            <input type="password" id="password" name="password" class="form-input <?php echo isset($errors['password']) ? 'error' : ''; ?>" 
                                   placeholder="Create a password" required>
                            <button type="button" class="toggle-password" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="error-message <?php echo isset($errors['password']) ? 'show' : ''; ?>" id="passwordError">
                            <?php echo $errors['password'] ?? ''; ?>
                        </div>
                        <div class="password-requirements">
                            <p>Password must contain:</p>
                            <ul>
                                <li id="length" class="invalid"><i class="fas fa-times"></i> At least 8 characters</li>
                                <li id="uppercase" class="invalid"><i class="fas fa-times"></i> One uppercase letter</li>
                                <li id="lowercase" class="invalid"><i class="fas fa-times"></i> One lowercase letter</li>
                                <li id="number" class="invalid"><i class="fas fa-times"></i> One number</li>
                                <li id="special" class="invalid"><i class="fas fa-times"></i> One special character</li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="password-input">
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input <?php echo isset($errors['confirm_password']) ? 'error' : ''; ?>" 
                                   placeholder="Confirm your password" required>
                            <button type="button" class="toggle-password" id="toggleConfirmPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="error-message <?php echo isset($errors['confirm_password']) ? 'show' : ''; ?>" id="confirmPasswordError">
                            <?php echo $errors['confirm_password'] ?? ''; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="birthdate" class="form-label">Birthdate</label>
                        <input type="date" id="birthdate" name="birthdate" class="form-input <?php echo isset($errors['birthdate']) ? 'error' : ''; ?>" 
                               required value="<?php echo htmlspecialchars($form_data['birthdate'] ?? ''); ?>">
                        <div class="error-message <?php echo isset($errors['birthdate']) ? 'show' : ''; ?>" id="birthdateError">
                            <?php echo $errors['birthdate'] ?? ''; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Create Account
                    </button>
                </form>

                <div class="form-footer">
                    Already have an account? <a href="login.php">Sign in</a>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('signupForm');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            const birthdate = document.getElementById('birthdate');
            
            // Password requirements elements
            const lengthReq = document.getElementById('length');
            const uppercaseReq = document.getElementById('uppercase');
            const lowercaseReq = document.getElementById('lowercase');
            const numberReq = document.getElementById('number');
            const specialReq = document.getElementById('special');
            
            // Password validation function
            function validatePassword(password) {
                const hasLength = password.length >= 8;
                const hasUppercase = /[A-Z]/.test(password);
                const hasLowercase = /[a-z]/.test(password);
                const hasNumber = /[0-9]/.test(password);
                const hasSpecial = /[^A-Za-z0-9]/.test(password);
                
                lengthReq.classList.toggle('valid', hasLength);
                lengthReq.classList.toggle('invalid', !hasLength);
                lengthReq.querySelector('i').className = hasLength ? 'fas fa-check' : 'fas fa-times';
                
                uppercaseReq.classList.toggle('valid', hasUppercase);
                uppercaseReq.classList.toggle('invalid', !hasUppercase);
                uppercaseReq.querySelector('i').className = hasUppercase ? 'fas fa-check' : 'fas fa-times';
                
                lowercaseReq.classList.toggle('valid', hasLowercase);
                lowercaseReq.classList.toggle('invalid', !hasLowercase);
                lowercaseReq.querySelector('i').className = hasLowercase ? 'fas fa-check' : 'fas fa-times';
                
                numberReq.classList.toggle('valid', hasNumber);
                numberReq.classList.toggle('invalid', !hasNumber);
                numberReq.querySelector('i').className = hasNumber ? 'fas fa-check' : 'fas fa-times';
                
                specialReq.classList.toggle('valid', hasSpecial);
                specialReq.classList.toggle('invalid', !hasSpecial);
                specialReq.querySelector('i').className = hasSpecial ? 'fas fa-check' : 'fas fa-times';
                
                return hasLength && hasUppercase && hasLowercase && hasNumber && hasSpecial;
            }
            
            // Real-time password validation
            password.addEventListener('input', function() {
                validatePassword(this.value);
            });

            // Form validation
            form.addEventListener('submit', function(e) {
                let isValid = true;

                // Name validation
                const name = document.getElementById('full_name');
                const nameError = document.getElementById('nameError');
                if (name.value.trim() === '') {
                    name.classList.add('error');
                    nameError.classList.add('show');
                    nameError.textContent = 'Full name is required.';
                    isValid = false;
                } else if (name.value.trim().length < 2) {
                    name.classList.add('error');
                    nameError.classList.add('show');
                    nameError.textContent = 'Full name must be at least 2 characters long.';
                    isValid = false;
                } else if (!/^[a-zA-Z\s'-]+$/.test(name.value.trim())) {
                    name.classList.add('error');
                    nameError.classList.add('show');
                    nameError.textContent = 'Full name can only contain letters, spaces, hyphens, and apostrophes.';
                    isValid = false;
                } else {
                    name.classList.remove('error');
                    nameError.classList.remove('show');
                }

                // Email validation
                const email = document.getElementById('email');
                const emailError = document.getElementById('emailError');
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
                const passwordError = document.getElementById('passwordError');
                if (!validatePassword(password.value)) {
                    password.classList.add('error');
                    passwordError.classList.add('show');
                    passwordError.textContent = 'Password does not meet all requirements.';
                    isValid = false;
                } else {
                    password.classList.remove('error');
                    passwordError.classList.remove('show');
                }

                // Confirm password validation
                const confirmPasswordError = document.getElementById('confirmPasswordError');
                if (password.value !== confirmPassword.value) {
                    confirmPassword.classList.add('error');
                    confirmPasswordError.classList.add('show');
                    confirmPasswordError.textContent = 'Passwords do not match.';
                    isValid = false;
                } else {
                    confirmPassword.classList.remove('error');
                    confirmPasswordError.classList.remove('show');
                }

                // Birthdate validation
                const birthdateError = document.getElementById('birthdateError');
                if (!birthdate.value) {
                    birthdate.classList.add('error');
                    birthdateError.classList.add('show');
                    birthdateError.textContent = 'Birthdate is required.';
                    isValid = false;
                } else {
                    const birthdateObj = new Date(birthdate.value);
                    const today = new Date();
                    const age = today.getFullYear() - birthdateObj.getFullYear();
                    
                    if (age < 13) {
                        birthdate.classList.add('error');
                        birthdateError.classList.add('show');
                        birthdateError.textContent = 'You must be at least 13 years old to register.';
                        isValid = false;
                    } else if (age > 120) {
                        birthdate.classList.add('error');
                        birthdateError.classList.add('show');
                        birthdateError.textContent = 'Please enter a valid birthdate.';
                        isValid = false;
                    } else {
                        birthdate.classList.remove('error');
                        birthdateError.classList.remove('show');
                    }
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
            
            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            
            function togglePasswordVisibility(input, button) {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                button.querySelector('i').classList.toggle('fa-eye');
                button.querySelector('i').classList.toggle('fa-eye-slash');
            }
            
            togglePassword.addEventListener('click', () => togglePasswordVisibility(password, togglePassword));
            toggleConfirmPassword.addEventListener('click', () => togglePasswordVisibility(confirmPassword, toggleConfirmPassword));

            // Add real-time email validation
            const email = document.getElementById('email');
            const emailError = document.getElementById('emailError');
            
            email.addEventListener('input', function() {
                const emailValue = this.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (emailValue === '') {
                    this.classList.add('error');
                    emailError.classList.add('show');
                    emailError.textContent = 'Email is required.';
                } else if (!emailRegex.test(emailValue)) {
                    this.classList.add('error');
                    emailError.classList.add('show');
                    emailError.textContent = 'Please enter a valid email address.';
                } else if (emailValue.length > 150) {
                    this.classList.add('error');
                    emailError.classList.add('show');
                    emailError.textContent = 'Email must not exceed 150 characters.';
                } else {
                    this.classList.remove('error');
                    emailError.classList.remove('show');
                    emailError.textContent = '';
                }
            });
        });
    </script>
</body>
</html>