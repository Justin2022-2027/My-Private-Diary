<?php
require_once 'db_connect.php';

echo "<h2>Checking Signup Table Structure</h2>";

// Check if signup table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'signup'");
if ($tableCheck->num_rows == 0) {
    echo "<p style='color: red;'>❌ Signup table does not exist!</p>";
    echo "<p><strong>Solution:</strong> Run <a href='setup_database.php'>setup_database.php</a> to create all tables.</p>";
    exit;
}

echo "<p style='color: green;'>✅ Signup table exists</p>";

// Show all columns in signup table
echo "<h3>Current Columns in Signup Table:</h3>";
$result = $conn->query("SHOW COLUMNS FROM signup");

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>" . $row['Field'] . "</strong></td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No columns found.</p>";
}

echo "<hr>";
echo "<h3>Required Columns for Signup:</h3>";
echo "<ul>";
echo "<li>user_id (INT, PRIMARY KEY, AUTO_INCREMENT)</li>";
echo "<li>full_name (VARCHAR)</li>";
echo "<li>email (VARCHAR, UNIQUE)</li>";
echo "<li>password (VARCHAR)</li>";
echo "<li><strong>birthdate (DATE)</strong> ← Missing this causes the error</li>";
echo "</ul>";

echo "<hr>";
echo "<h3>Solutions:</h3>";
echo "<p><a href='setup_database.php' style='padding: 10px 20px; background: #ff9fb0; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px;'>Run Setup Database</a></p>";
echo "<p><a href='migrate_signup_table.php' style='padding: 10px 20px; background: #10b981; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px;'>Run Migration Script</a></p>";

$conn->close();
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 900px;
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
    table {
        width: 100%;
        background: white;
        margin: 20px 0;
    }
    th {
        background: #ff9fb0;
        color: white;
        padding: 10px;
    }
    td {
        padding: 8px;
    }
    tr:nth-child(even) {
        background: #f8fafc;
    }
</style>
