<?php
// Alternative email test using PHP's built-in mail() function
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get the email address from POST data
$data = json_decode(file_get_contents('php://input'), true);
$email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit();
}

// Simple email using PHP's built-in mail function
$subject = 'Test Email from My Private Diary';
$message = "This is a test email to confirm that your email notifications are working correctly.\n\nIf you received this email, it means your email settings are properly configured.\n\nThis is an automated message, please do not reply.";
$headers = 'From: mpd241203@gmail.com' . "\r\n" .
           'Reply-To: mpd241203@gmail.com' . "\r\n" .
           'X-Mailer: PHP/' . phpversion();

if (mail($email, $subject, $message, $headers)) {
    echo json_encode(['success' => true, 'message' => 'Test email sent successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send email using built-in mail function']);
}
?>
