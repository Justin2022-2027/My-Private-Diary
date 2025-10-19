<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "mpd";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = strtolower(trim($_POST['email']));
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format";
        $message_type = "error";
    } else {
        // Check if email exists in database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            // Generate OTP
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Store OTP in session
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['otp_time'] = time();
            
            // Send OTP via email using PHPMailer
            require_once 'PHPMailer/src/Exception.php';
            require_once 'PHPMailer/src/PHPMailer.php';
            require_once 'PHPMailer/src/SMTP.php';
            
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'mpd241203@gmail.com'; // <-- Replace with your Gmail
                $mail->Password = 'lanh xneq wcog lwgh'; // Gmail app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                
                // Recipients
                $mail->setFrom('mpd241203@gmail.com', 'My Private Diary');
                $mail->addAddress($email);
                
                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Your OTP for Password Reset';
                $mail->Body = "Your OTP for password reset is: <b>$otp</b>";
                $mail->AltBody = "Your OTP for password reset is: $otp";
                
                $mail->send();
                $message = "OTP sent to $email";
                $message_type = "success";
                header("Location: verify_otp.php");
                exit;
            } catch (Exception $e) {
                $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                $message_type = "error";
            }
        } else {
            $message = "No account found with this email address";
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
    <title>Forgot Password - My Private Diary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #ff9fb0;
            --primary-dark: #ff7a93;
            --secondary: #6b7280;
            --success: #10b981;
            --danger: #ef4444;
            --background: #f8fafc;
            --white: #ffffff;
            --dark: #1e293b;
            --gray: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--dark);
            line-height: 1.6;
        }

        .header {
            background-color: var(--white);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .logo i {
            color: var(--primary);
            font-size: 1.5rem;
        }

        .logo span {
            font-size: 1.5rem;
            font-weight: 600;
        }

        main {
            min-height: calc(100vh - 70px);
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }

        .auth-form {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 2.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
        }

        .form-title {
            font-size: 1.875rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .form-subtitle {
            color: var(--gray);
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255, 159, 176, 0.2);
        }

        .btn {
            display: block;
            width: 100%;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .form-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--gray);
        }

        .form-footer a {
            color: var(--primary-dark);
            text-decoration: none;
            font-weight: 500;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .message {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .message.success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .message.error {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        /* Add back button styles */
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 1.5rem;
            transition: color 0.2s;
        }

        .back-button:hover {
            color: var(--primary);
        }

        .back-button i {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="index.html" class="logo">
                    <i class="fas fa-book-open"></i>
                    <span>My Private Diary</span>
                </a>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="auth-form">
                <!-- Add back button here, before the form title -->
                <a href="login.php" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    Back 
                </a>

                <h1 class="form-title">Forgot Password</h1>
                <p class="form-subtitle">Enter your email address to reset your password</p>

                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Send OTP
                    </button>
                </form>

                <div class="form-footer">
                    Remember your password? <a href="login.php">Sign in</a>
                </div>
            </div>
        </div>
    </main>

    <script src="script.js"></script>
</body>
</html>