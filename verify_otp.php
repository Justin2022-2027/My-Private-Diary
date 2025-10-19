<?php
session_start();

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_otp'])) {
    header("Location: forgot_password.php");
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_otp = $_POST['otp'];
    $stored_otp = $_SESSION['reset_otp'];
    $otp_time = $_SESSION['otp_time'];
    
    // Check if OTP is expired (10 minutes)
    if (time() - $otp_time > 600) {
        $message = "OTP has expired. Please request a new one.";
        $message_type = "error";
    }
    // Verify OTP
    else if ($entered_otp === $stored_otp) {
        // OTP is valid, redirect to reset password page
        header("Location: reset_password.php");
        exit();
    } else {
        $message = "Invalid OTP. Please try again.";
        $message_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - My Private Diary</title>
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
            text-align: center;
            letter-spacing: 0.5rem;
            font-size: 1.5rem;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
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
                <h1 class="form-title">Verify OTP</h1>
                <p class="form-subtitle">Enter the 6-digit code sent to your email</p>

                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="otp" class="form-label">One-Time Password</label>
                        <input type="text" id="otp" name="otp" class="form-input" placeholder="000000" maxlength="6" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Verify OTP
                    </button>
                </form>

                <div class="form-footer">
                    Didn't receive the code? <a href="forgot_password.php">Resend OTP</a>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Auto focus to next input and only allow numbers
        const otpInput = document.getElementById('otp');
        otpInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html> 