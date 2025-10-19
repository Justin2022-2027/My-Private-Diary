<?php
// Test script to verify themes functionality
session_start();

// Test including user_theme.php
echo "Testing includes/user_theme.php...\n";
try {
    ob_start(); // Capture output
    include 'includes/user_theme.php';
    $output = ob_get_clean();
    if (strpos($output, '<style>') !== false) {
        echo "✅ includes/user_theme.php loaded and generated CSS successfully\n";
    } else {
        echo "⚠️ includes/user_theme.php loaded but no CSS generated (user not logged in)\n";
    }
} catch (Exception $e) {
    echo "❌ Error loading includes/user_theme.php: " . $e->getMessage() . "\n";
}

// Test database connection and themes table
echo "\nTesting database connection...\n";
try {
    include 'db_connect.php';
    echo "✅ db_connect.php loaded successfully\n";

    // Test if themes table exists and has data
    $result = $conn->query("SELECT COUNT(*) as count FROM themes");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✅ Themes table exists with " . $row['count'] . " records\n";
    } else {
        echo "❌ Themes table query failed\n";
    }

    $conn->close();
} catch (Exception $e) {
    echo "❌ Error with database: " . $e->getMessage() . "\n";
}

echo "\nAll tests completed!\n";
?>
