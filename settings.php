<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

$user_id = $_SESSION['user_id'];
$message = '';
$messageType = '';

      // First, let's check if the user exists in signup table (only user table now)
      $user_check_sql = "SELECT user_id FROM signup WHERE user_id = ?";
      $user_check_stmt = $conn->prepare($user_check_sql);
      $user_check_stmt->bind_param("i", $user_id);
      $user_check_stmt->execute();
      $user_check_result = $user_check_stmt->get_result();

      if ($user_check_result->num_rows == 0) {
          // User doesn't exist in signup table
          $message = "User account not found. Please log in again.";
          $messageType = "error";
          $user_check_stmt->close();
          $conn->close();
      } else {
          $user_check_stmt->close();

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['update_profile'])) {
            $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
            $dark_mode = isset($_POST['dark_mode']) ? 1 : 0;
            $language = trim($_POST['language']);
            $notification_email = $email_notifications ? trim($_POST['notification_email']) : null;

            // Validate email if notifications are enabled
            if ($email_notifications && !filter_var($notification_email, FILTER_VALIDATE_EMAIL)) {
                $message = "Please enter a valid email address for notifications.";
                $messageType = "error";
            } else {
                // Check if settings exist for the user
                $check_stmt = $conn->prepare("SELECT user_id FROM settings WHERE user_id = ?");
                if (!$check_stmt) {
                    die("Error preparing statement: " . $conn->error);
                }
                $check_stmt->bind_param("i", $user_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $check_stmt->close();

                if ($check_result->num_rows > 0) {
                    // Update existing settings
                    $stmt = $conn->prepare("UPDATE settings SET email_notifications = ?, dark_mode = ?, language = ?, notification_email = ? WHERE user_id = ?");
                } else {
                    // Insert new settings
                    $stmt = $conn->prepare("INSERT INTO settings (email_notifications, dark_mode, language, notification_email, user_id) VALUES (?, ?, ?, ?, ?)");
                }

                if (!$stmt) {
                    die("Error preparing statement: " . $conn->error);
                }

                $stmt->bind_param("iissi", $email_notifications, $dark_mode, $language, $notification_email, $user_id);
                
                if ($stmt->execute()) {
                    $message = "Settings updated successfully!";
                    $messageType = "success";
                } else {
                    $message = "Failed to update settings: " . $stmt->error;
                    $messageType = "error";
                }
                $stmt->close();
            }
        }

        if (isset($_POST['change_password'])) {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password !== $confirm_password) {
                $message = "New passwords do not match.";
                $messageType = "error";
            } elseif (strlen($new_password) < 8) {
                $message = "Password must be at least 8 characters long.";
                $messageType = "error";
            } else {
                // Verify current password - check signup table only
                $password_sql = "SELECT password FROM signup WHERE user_id = ?";
                $stmt = $conn->prepare($password_sql);
                if (!$stmt) {
                    die("Error preparing statement: " . $conn->error);
                }
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();

                if ($user && password_verify($current_password, $user['password'])) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                    // Update password in signup table only
                    $update_signup_sql = "UPDATE signup SET password = ? WHERE user_id = ?";
                    $stmt = $conn->prepare($update_signup_sql);
                    if ($stmt) {
                        $stmt->bind_param("si", $hashed_password, $user_id);
                        $stmt->execute();
                        $stmt->close();
                    }

                    $message = "Password changed successfully!";
                    $messageType = "success";
                } else {
                    $message = "Current password is incorrect.";
                    $messageType = "error";
                }
            }
        }
    }

    // Fetch user settings
    $stmt = $conn->prepare("SELECT email_notifications, dark_mode, language, notification_email FROM settings WHERE user_id = ?");
    if (!$stmt) {
        // If the settings table doesn't exist yet, create default settings
        $settings = [
            'email_notifications' => 0,
            'dark_mode' => 0,
            'language' => 'en',
            'notification_email' => ''
        ];
    } else {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $settings = $result->fetch_assoc();
        $stmt->close();

        // If no settings found, use defaults
        if (!$settings) {
            $settings = [
                'email_notifications' => 0,
                'dark_mode' => 0,
                'language' => 'en',
                'notification_email' => ''
            ];
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" width="device-width, initial-scale=1.0">
    <title>Settings - My Private Diary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Settings.css */
        :root {
            --primary: #ff9fb0;
            --primary-dark: #ff7a93;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray: #64748b;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --radius: 0.5rem;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--light);
            color: var(--dark);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .settings-container {
            padding-top: 5rem;
            max-width: 800px;
            margin: 0 auto;
            padding-left: 2rem;
            padding-right: 2rem;
            position: relative;

        }

        .settings-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .settings-card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            margin-bottom: 2rem;
            transition: var(--transition);
        }

        .settings-card:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .settings-card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .settings-group {
            margin-bottom: 1.5rem;
        }

        .settings-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        /* Switch styles */
        .switch-container {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
        }

        .switch-input {
            display: none;
        }

        .switch {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #e2e8f0;
            transition: var(--transition);
            border-radius: 24px;
        }

        .switch::before {
            content: "";
            position: absolute;
            height: 20px;
            width: 20px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: var(--transition);
            border-radius: 50%;
        }

        .switch-input:checked + .switch {
            background-color: var(--primary);
        }

        .switch-input:checked + .switch::before {
            transform: translateX(24px);
        }

        /* Form elements */
        .settings-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .settings-group input:not([type="checkbox"]),
        .settings-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        .settings-group input:focus,
        .settings-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255, 159, 176, 0.2);
        }

        .settings-dependent-field {
            margin-top: 0.5rem;
            margin-left: 1.5rem;
            padding-left: 1rem;
            border-left: 2px solid var(--primary);
            display: none;
        }

        .settings-dependent-field.active {
            display: block;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Message styles */
        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .message-success {
            background-color: #dcfce7;
            border-left: 4px solid #22c55e;
            color: #166534;
        }

        .message-error {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        /* Button styles */
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius);
            font-weight: 500;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            background-color: var(--primary);
            color: white;
            width: 100%;
        }

        .button:hover {
            background-color: var(--primary-dark);
        }

        /* Back button styles */
        .back-button {
            position: fixed;
            top: 2rem;
            left: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: var(--white);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: var(--radius);
            color: var(--gray);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            box-shadow: var(--shadow);
            z-index: 1000;
        }

        .back-button:hover {
            background: var(--primary-light);
            color: var(--primary-dark);
            transform: translateX(-4px);
        }

        /* Email submit section styles */
        .email-test-container {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .email-test-container input[type="email"] {
            flex: 1;
        }

        .test-email-button {
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }

        .test-email-button:hover {
            background: var(--primary-dark);
        }

        .test-email-button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="settings-container">
        <a href="dashboard.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <h1 class="settings-title">Settings</h1>

        <?php if ($message): ?>
            <div class="message message-<?php echo $messageType; ?>">
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <div class="settings-card">
            <h2 class="settings-card-title">Profile Settings</h2>
            <form method="POST">
                <div class="settings-group">
                    <div class="settings-row">
                        <label for="email_notifications">Enable Email Notifications</label>
                        <div class="switch-container">
                            <input type="checkbox" id="email_notifications" name="email_notifications" class="switch-input" 
                                <?php echo $settings['email_notifications'] ? 'checked' : ''; ?>>
                            <label class="switch" for="email_notifications"></label>
                        </div>
                    </div>

                    <div id="notification_email_field" class="settings-dependent-field <?php echo $settings['email_notifications'] ? 'active' : ''; ?>">
                        <label for="notification_email">Notification Email</label>
                        <div class="email-test-container">
                            <input type="email" id="notification_email" name="notification_email" 
                                value="<?php echo htmlspecialchars($settings['notification_email'] ?? ''); ?>">
                            <button type="button" class="test-email-button" onclick="sendTestEmail()">
                                <i class="fas fa-paper-plane"></i> Test Email
                            </button>
                        </div>
                    </div>
                </div>

                <div class="settings-group">
                    <label for="language">Preferred Language</label>
                    <select id="language" name="language">
                        <option value="en" <?php echo $settings['language'] === 'en' ? 'selected' : ''; ?>>English</option>
                        <option value="es" <?php echo $settings['language'] === 'es' ? 'selected' : ''; ?>>Spanish</option>
                        <option value="fr" <?php echo $settings['language'] === 'fr' ? 'selected' : ''; ?>>French</option>
                        <option value="de" <?php echo $settings['language'] === 'de' ? 'selected' : ''; ?>>German</option>
                    </select>
                </div>

                <button type="submit" name="update_profile" class="button">Save Changes</button>
            </form>
        </div>

        <div class="settings-card">
            <h2 class="settings-card-title">Change Password</h2>
            <form method="POST">
                <div class="settings-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>

                <div class="settings-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8">
                </div>

                <div class="settings-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" name="change_password" class="button">Change Password</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('email_notifications').addEventListener('change', function() {
            const emailField = document.getElementById('notification_email_field');
            emailField.classList.toggle('active', this.checked);
        });

        // Password visibility toggle
        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        // Function to send test email
        function sendTestEmail() {
            const email = document.getElementById('notification_email').value;
            if (!email) {
                alert('Please enter an email address first.');
                return;
            }

            // Show loading state
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            button.disabled = true;

            // Send test email
            fetch('send_test_email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Test email sent successfully!');
                } else {
                    alert('Failed to send test email: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error sending test email: ' + error.message);
            })
            .finally(() => {
                // Restore button state
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
    </script>
</body>
</html>
