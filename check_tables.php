<?php
$conn = new mysqli('localhost', 'root', '', 'mpd');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "Available tables in MPD database:\n";
$sql = 'SHOW TABLES';
$result = $conn->query($sql);
while ($row = $result->fetch_array()) {
    echo '- ' . $row[0] . PHP_EOL;
}

$conn->close();
?>
