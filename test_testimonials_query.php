<?php
// Test script to verify testimonials query works
include 'db_connect.php';

echo "Testing testimonials query...\n";

try {
    $sql = 'SELECT t.content, t.created_at, u.full_name, YEAR(u.signup_date) as joined_year FROM testimonials t JOIN signup u ON t.user_id = u.user_id ORDER BY t.created_at DESC LIMIT 3';
    $result = $conn->query($sql);

    if ($result) {
        $testimonials = $result->fetch_all(MYSQLI_ASSOC);
        echo "✅ Query executed successfully\n";
        echo "Found " . count($testimonials) . " testimonials\n";

        foreach ($testimonials as $t) {
            echo "- '{$t['content']}' by {$t['full_name']} (joined {$t['joined_year']})\n";
        }
    } else {
        echo "❌ Query failed\n";
    }
} catch (Exception $e) {
    echo "❌ Query error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
