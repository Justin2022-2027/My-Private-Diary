<?php
require_once 'db_connect.php';

echo "<h2>Testing Signup Table Insert</h2>";

// Check if signup table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'signup'");
if ($tableCheck->num_rows == 0) {
    echo "<p style='color: red;'>❌ Signup table does not exist!</p>";
    exit;
}

echo "<p style='color: green;'>✅ Signup table exists</p>";

// Show columns
echo "<h3>Columns in Signup Table:</h3>";
$result = $conn->query("SHOW COLUMNS FROM signup");
echo "<ul>";
while ($row = $result->fetch_assoc()) {
    echo "<li><strong>" . $row['Field'] . "</strong> (" . $row['Type'] . ")</li>";
}
echo "</ul>";

// Test if we can prepare the INSERT statement
echo "<h3>Testing INSERT Statement:</h3>";
$test_stmt = $conn->prepare("INSERT INTO signup (full_name, email, password, birthdate) VALUES (?, ?, ?, ?)");

if ($test_stmt) {
    echo "<p style='color: green;'>✅ INSERT statement prepared successfully!</p>";
    echo "<p>The signup table has all required columns.</p>";
    $test_stmt->close();
} else {
    echo "<p style='color: red;'>❌ Failed to prepare INSERT statement</p>";
    echo "<p><strong>Error:</strong> " . $conn->error . "</p>";
}

// Check if birthdate column exists specifically
$birthdate_check = $conn->query("SHOW COLUMNS FROM signup LIKE 'birthdate'");
if ($birthdate_check->num_rows > 0) {
    echo "<p style='color: green;'>✅ Birthdate column exists</p>";
    $col = $birthdate_check->fetch_assoc();
    echo "<p>Column details: Type = " . $col['Type'] . ", Null = " . $col['Null'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ Birthdate column does NOT exist</p>";
    echo "<p><strong>Solution:</strong> Add birthdate column with this SQL:</p>";
    echo "<code>ALTER TABLE signup ADD COLUMN birthdate DATE NOT NULL;</code>";
}

echo "<hr>";
echo "<p><a href='signup.php' style='padding: 10px 20px; background: #ff9fb0; color: white; text-decoration: none; border-radius: 5px;'>Try Signup Again</a></p>";

$conn->close();
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 50px auto;
        padding: 20px;
        background: #f8fafc;
    }
    h2 {
        color: #1e293b;
        border-bottom: 3px solid #ff9fb0;
        padding-bottom: 10px;
    }
    h3 {
        color: #ff9fb0;
        margin-top: 30px;
    }
    code {
        background: #1e293b;
        color: #10b981;
        padding: 10px;
        display: block;
        border-radius: 5px;
        margin: 10px 0;
    }
</style>
