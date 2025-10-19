<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Get the email address from POST data
$data = json_decode(file_get_contents('php://input'), true);
$email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit();
}

try {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    // Server settings - Gmail SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'mpd241203@gmail.com';
    $mail->Password = 'lanh xneq wcog lwgh'; // App password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Enable SMTP debugging for troubleshooting
    $mail->SMTPDebug = SMTP::DEBUG_OFF; // Change to DEBUG_SERVER for detailed logs
    $mail->Debugoutput = function($str, $level) {
        error_log("PHPMailer Debug: $str");
    };

    // Disable SSL certificate verification (for local development only)
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    // Recipients
    $mail->setFrom('mpd241203@gmail.com', 'My Private Diary');
    $mail->addAddress($email);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from My Private Diary';
    $mail->Body = "
        <h2>Test Email</h2>
        <p>This is a test email to confirm that your email notifications are working correctly.</p>
        <p>If you received this email, it means your email settings are properly configured.</p>
        <p><strong>Time sent:</strong> " . date('Y-m-d H:i:s') . "</p>
        <p style='font-size: 0.9em; color: #666;'>This is an automated message, please do not reply.</p>";

    $mail->AltBody = "Test Email\n\nThis is a test email to confirm that your email notifications are working correctly.\n\nTime sent: " . date('Y-m-d H:i:s') . "\n\nThis is an automated message, please do not reply.";

    // Send the email
    if ($mail->send()) {
        echo json_encode(['success' => true, 'message' => 'Test email sent successfully']);
    } else {
        $errorInfo = $mail->ErrorInfo;
        error_log("PHPMailer Error: $errorInfo");
        echo json_encode(['success' => false, 'message' => "Failed to send email: $errorInfo"]);
    }

} catch (Exception $e) {
    $errorMessage = $e->getMessage();
    error_log("PHPMailer Exception: $errorMessage");
    echo json_encode(['success' => false, 'message' => "Email error: $errorMessage"]);

    // Fallback: Try using PHP's built-in mail function
    try {
        $subject = 'Test Email from My Private Diary';
        $message = "This is a test email to confirm that your email notifications are working correctly.\n\nTime sent: " . date('Y-m-d H:i:s') . "\n\nThis is an automated message, please do not reply.";
        $headers = 'From: mpd241203@gmail.com' . "\r\n" .
                   'Reply-To: mpd241203@gmail.com' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();

        if (mail($email, $subject, $message, $headers)) {
            echo json_encode(['success' => true, 'message' => 'Test email sent successfully (using fallback method)']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send email using both PHPMailer and built-in mail function']);
        }
    } catch (Exception $fallbackError) {
        echo json_encode(['success' => false, 'message' => 'All email methods failed. Please check server configuration.']);
    }
}
?>
