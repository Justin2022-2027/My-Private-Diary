<?php
// Test script to verify settings functionality
include 'db_connect.php';

echo "Testing settings functionality...\n";

try {
    // Test database connection
    if (isset($conn) && $conn->ping()) {
        echo "✅ Database connection is working\n";
    } else {
        echo "❌ Database connection failed\n";
        exit;
    }

    // Test if settings table exists and is accessible
    $sql = "SELECT COUNT(*) as count FROM settings";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✅ Settings table is accessible with {$row['count']} records\n";
    } else {
        echo "❌ Settings table query failed\n";
    }

    // Test a simple query that settings.php would use
    $test_user_id = 1; // Test with user ID 1
    $sql = "SELECT email_notifications, dark_mode, language, notification_email FROM settings WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $test_user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $settings = $result->fetch_assoc();
        $stmt->close();

        if ($settings) {
            echo "✅ Settings query executed successfully for user ID {$test_user_id}\n";
        } else {
            echo "✅ Settings query executed successfully (no settings found for user, which is normal)\n";
        }
    } else {
        echo "❌ Failed to prepare settings query\n";
    }

} catch (Exception $e) {
    echo "❌ Error testing settings: " . $e->getMessage() . "\n";
}

$conn->close();
echo "Settings functionality test completed!\n";
?>
