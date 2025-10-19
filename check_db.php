<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mpd";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("<h2>Connection failed:</h2> " . $conn->connect_error);
}
echo "<h2>Connected successfully to MySQL server</h2>";

// Check if database exists
$result = $conn->query("SHOW DATABASES LIKE '$dbname'");
if ($result->num_rows == 0) {
    die("<h2>Database '$dbname' does not exist.</h2>");
}
echo "<h3>Database '$dbname' exists.</h3>";

// Select the database
$conn->select_db($dbname);

// Check tables
$result = $conn->query("SHOW TABLES");
if ($result->num_rows > 0) {
    echo "<h3>Tables in database:</h3><ul>";
    while($row = $result->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
    
    // Check users table
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    echo "<h3>Total users: " . $row['count'] . "</h3>";
    
    // Show first few users if any
    if ($row['count'] > 0) {
        $result = $conn->query("SELECT user_id, email, full_name FROM users LIMIT 5");
        echo "<h4>Sample users:</h4><ul>";
        while($user = $result->fetch_assoc()) {
            echo "<li>ID: " . $user['user_id'] . " - " . 
                 htmlspecialchars($user['email']) . " (" . 
                 htmlspecialchars($user['full_name']) . ")</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<h3>No tables found in database.</h3>";
}

$conn->close();
?>
