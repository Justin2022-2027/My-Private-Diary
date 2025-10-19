<?php
$conn = new mysqli('localhost', 'root', '', 'mpd');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "Checking if settings table exists...\n";
$sql = 'SHOW TABLES LIKE "settings"';
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    echo "✅ Settings table exists\n";
} else {
    echo "❌ Settings table does not exist\n";
}

$conn->close();
?>
