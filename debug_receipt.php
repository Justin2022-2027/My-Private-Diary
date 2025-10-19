<?php
// Simple diagnostic script to check database and payment issues
session_start();

echo "<h1>Receipt System Diagnostic</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .error{color:red;} .success{color:green;} .warning{color:orange;}</style>";

try {
    require 'db_connect.php';

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    echo "<h2>Database Connection</h2>";
    echo "<p class='success'>✓ Database connection successful</p>";

    // Check if payments table exists
    echo "<h2>Payments Table</h2>";
    $result = $conn->query("SHOW TABLES LIKE 'payments'");
    if ($result && $result->num_rows > 0) {
        echo "<p class='success'>✓ Payments table exists</p>";

        // Check payment count
        $result = $conn->query("SELECT COUNT(*) as count FROM payments");
        $row = $result->fetch_assoc();
        $payment_count = $row['count'];

        if ($payment_count > 0) {
            echo "<p class='success'>✓ Found $payment_count payment(s) in database</p>";

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
            echo "<p class='warning'>⚠ No payments found in database. You may need to make a test payment first.</p>";
        }
    } else {
        echo "<p class='error'>✗ Payments table not found</p>";
    }

    // Check current session
    echo "<h2>Current Session</h2>";
    if (isset($_SESSION['user_id'])) {
        echo "<p class='success'>✓ User logged in (ID: {$_SESSION['user_id']})</p>";
        echo "<p>User: " . ($_SESSION['full_name'] ?? 'Not set') . "</p>";
        echo "<p>Email: " . ($_SESSION['email'] ?? 'Not set') . "</p>";
    } else {
        echo "<p class='warning'>⚠ No user logged in</p>";
    }

    // Check if payment_receipt_data exists
    if (isset($_SESSION['payment_receipt_data'])) {
        echo "<p class='success'>✓ Receipt data exists in session</p>";
        echo "<pre>" . print_r($_SESSION['payment_receipt_data'], true) . "</pre>";
    } else {
        echo "<p class='warning'>⚠ No receipt data in session</p>";
    }

    // Test URL parameter
    if (isset($_GET['payment_id'])) {
        echo "<h2>Testing Payment ID: {$_GET['payment_id']}</h2>";
        $payment_id = (int)$_GET['payment_id'];

        $stmt = $conn->prepare("SELECT * FROM payments WHERE payment_id = ? AND user_id = ?");
        if ($stmt) {
            $user_id = $_SESSION['user_id'] ?? 0;
            $stmt->bind_param("ii", $payment_id, $user_id);

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result && $result->num_rows > 0) {
                    $payment = $result->fetch_assoc();
                    echo "<p class='success'>✓ Payment found in database</p>";
                    echo "<pre>" . print_r($payment, true) . "</pre>";
                } else {
                    echo "<p class='error'>✗ Payment not found or doesn't belong to current user</p>";
                }
            } else {
                echo "<p class='error'>✗ Query execution failed</p>";
            }
            $stmt->close();
        } else {
            echo "<p class='error'>✗ Statement preparation failed</p>";
        }
    } else {
        echo "<p class='warning'>⚠ No payment_id in URL</p>";
    }

    $conn->close();

} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='premium.php'>← Back to Premium</a></p>";
?>
