<?php
$conn = new mysqli('localhost', 'root', '', 'mpd');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "Signup table columns:\n";
$sql = 'DESCRIBE signup';
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    echo '- ' . $row['Field'] . ' (' . $row['Type'] . ')' . PHP_EOL;
}

$conn->close();
?>
