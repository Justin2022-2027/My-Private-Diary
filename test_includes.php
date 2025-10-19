<?php
// Test script to verify includes work correctly
session_start();

// Test including user_theme.php
try {
    include 'includes/user_theme.php';
    echo "✅ includes/user_theme.php loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Error loading includes/user_theme.php: " . $e->getMessage() . "\n";
}

// Test if we can connect to database (basic check)
try {
    include 'db_connect.php';
    echo "✅ db_connect.php loaded successfully\n";
    if (isset($conn) && $conn->ping()) {
        echo "✅ Database connection is working\n";
    }
    $conn->close();
} catch (Exception $e) {
    echo "❌ Error with db_connect.php: " . $e->getMessage() . "\n";
}
?>
