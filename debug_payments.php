<?php
// Simple diagnostic script to check database
try {
    require 'db_connect.php';

    echo "<h2>Database Diagnostic</h2>";

    // Check if payments table exists
    $result = $conn->query("SHOW TABLES LIKE 'payments'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Payments table exists</p>";

        // Count payments
        $result = $conn->query("SELECT COUNT(*) as count FROM payments");
        $row = $result->fetch_assoc();
        $payment_count = $row['count'];

        echo "<p>Total payments: $payment_count</p>";

        if ($payment_count > 0) {
            // Show recent payments
            echo "<h3>Recent Payments:</h3>";
            $result = $conn->query("SELECT payment_id, user_id, plan_type, amount, status, created_at FROM payments ORDER BY created_at DESC LIMIT 5");
            echo "<table border='1' style='border-collapse:collapse; margin:10px 0;'>";
            echo "<tr><th>Payment ID</th><th>User ID</th><th>Plan</th><th>Amount</th><th>Status</th><th>Date</th></tr>";
            while ($payment = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$payment['payment_id']}</td>";
                echo "<td>{$payment['user_id']}</td>";
                echo "<td>{$payment['plan_type']}</td>";
                echo "<td>₹{$payment['amount']}</td>";
                echo "<td>{$payment['status']}</td>";
                echo "<td>{$payment['created_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠ No payments found in database</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Payments table not found</p>";
    }

    $conn->close();

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
