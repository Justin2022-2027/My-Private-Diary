<?php
$conn = new mysqli('localhost', 'root', '', 'mpd');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "Signup table columns:\n";
$sql = 'DESCRIBE signup';
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . ($row['Null'] == 'NO' ? 'NOT NULL' : 'NULL') . ' - ' . ($row['Default'] ?? 'NULL') . ' - ' . $row['Extra'] . "\n";
}

echo "\nUsers table columns:\n";
$sql = 'DESCRIBE users';
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . ($row['Null'] == 'NO' ? 'NOT NULL' : 'NULL') . ' - ' . ($row['Default'] ?? 'NULL') . ' - ' . $row['Extra'] . "\n";
}

$conn->close();
?>
