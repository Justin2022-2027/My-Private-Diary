<?php
$conn = new mysqli('localhost', 'root', '', 'mpd');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "Foreign key constraints referencing users table:\n";
$sql = 'SELECT TABLE_NAME, CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = "users" AND TABLE_SCHEMA = "mpd"';
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    echo $row['TABLE_NAME'] . ' - ' . $row['CONSTRAINT_NAME'] . PHP_EOL;
}

$conn->close();
?>
