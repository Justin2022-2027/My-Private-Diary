<?php
session_start();

// Simple database diagnostic script
require 'db_connect.php';

echo "<h2>Database Connection Test</h2>";
echo "<p>Testing database connection and table structure...</p>";

// Test connection
if ($conn) {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
    exit();
}

// Test users table
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Users table exists</p>";
} else {
    echo "<p style='color: red;'>✗ Users table missing</p>";
}

// Test payments table
$result = $conn->query("SHOW TABLES LIKE 'payments'");
if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Payments table exists</p>";
} else {
    echo "<p style='color: red;'>✗ Payments table missing</p>";
}

// Test user data if logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    echo "<p>Testing user data for user_id: $user_id</p>";

    $stmt = $conn->prepare("SELECT user_id, full_name, email FROM users WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                echo "<p style='color: green;'>✓ User data found:</p>";
                echo "<ul>";
                echo "<li>Name: " . htmlspecialchars($user['full_name'] ?? 'N/A') . "</li>";
                echo "<li>Email: " . htmlspecialchars($user['email'] ?? 'N/A') . "</li>";
                echo "</ul>";
            } else {
                echo "<p style='color: red;'>✗ No user data found for user_id: $user_id</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Failed to execute user query</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color: red;'>✗ Failed to prepare user query</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠ No user logged in (no session user_id)</p>";
}

// Test session data
echo "<h3>Session Data:</h3>";
echo "<ul>";
echo "<li>user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . "</li>";
echo "<li>full_name: " . (isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Not set') . "</li>";
echo "<li>email: " . (isset($_SESSION['email']) ? $_SESSION['email'] : 'Not set') . "</li>";
echo "<li>payment_receipt_data: " . (isset($_SESSION['payment_receipt_data']) ? 'Set' : 'Not set') . "</li>";
if (isset($_SESSION['payment_receipt_data'])) {
    echo "<li>Receipt data keys: " . implode(', ', array_keys($_SESSION['payment_receipt_data'])) . "</li>";
}
echo "</ul>";

$conn->close();
?>
