<?php
// Test script to verify index.html PHP functionality
session_start();

// Simulate the same PHP code from index.html
if (isset($_SESSION['user_id'])) {
    echo "User is logged in - should show write_entry.php link\n";
} else {
    echo "User is not logged in - should show guest_entry.php link\n";
}

// Check if user_id is set in session for testing
$_SESSION['user_id'] = 123; // Simulate logged in user

if (isset($_SESSION['user_id'])) {
    echo "User is logged in - should show write_entry.php link\n";
} else {
    echo "User is not logged in - should show guest_entry.php link\n";
}
?>
