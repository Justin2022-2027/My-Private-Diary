<?php
// Email debugging script
echo "<h2>Email Configuration Test</h2>";

// Test 1: Check if PHPMailer files exist
echo "<h3>1. PHPMailer Files Check:</h3>";
$phpmailer_files = [
    'PHPMailer/src/Exception.php',
    'PHPMailer/src/PHPMailer.php',
    'PHPMailer/src/SMTP.php'
];

foreach ($phpmailer_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $file exists</p>";
    } else {
        echo "<p style='color: red;'>❌ $file missing</p>";
    }
}

// Test 2: Check PHP mail function
echo "<h3>2. PHP Mail Function Test:</h3>";
if (function_exists('mail')) {
    echo "<p style='color: green;'>✅ mail() function is available</p>";
} else {
    echo "<p style='color: red;'>❌ mail() function is not available</p>";
}

// Test 3: Check SMTP configuration
echo "<h3>3. SMTP Configuration:</h3>";
echo "<p>Gmail SMTP settings configured for:</p>";
echo "<ul>";
echo "<li>Host: smtp.gmail.com</li>";
echo "<li>Port: 587</li>";
echo "<li>Security: STARTTLS</li>";
echo "<li>Username: mpd241203@gmail.com</li>";
echo "</ul>";

// Test 4: Check if sendmail_path is configured
echo "<h3>4. Sendmail Configuration:</h3>";
$sendmail_path = ini_get('sendmail_path');
if ($sendmail_path) {
    echo "<p style='color: green;'>✅ sendmail_path is configured: $sendmail_path</p>";
} else {
    echo "<p style='color: orange;'>⚠️ sendmail_path is not configured (this may not be needed for SMTP)</p>";
}

// Test 5: Check PHP version
echo "<h3>5. PHP Version:</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";

echo "<h3>Next Steps:</h3>";
echo "<p>If emails are still not working:</p>";
echo "<ol>";
echo "<li>Check your Gmail account settings to ensure app passwords are enabled</li>";
echo "<li>Make sure 2-factor authentication is enabled on your Gmail account</li>";
echo "<li>Check the error logs in your web server for detailed error messages</li>";
echo "<li>Try the simple email test script: <a href='send_test_email_simple.php'>send_test_email_simple.php</a></li>";
echo "</ol>";

echo "<p><a href='settings.php'>← Back to Settings</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
h2 { color: #2c3e50; }
h3 { color: #3498db; margin-top: 30px; }
p { margin: 10px 0; }
ul { margin: 10px 0; padding-left: 20px; }
</style>
