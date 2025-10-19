<?php
header('Content-Type: application/json');
require 'db_connect.php';

// Support both country_id and country_name parameters
if (isset($_GET['country_id']) && is_numeric($_GET['country_id'])) {
    // Old system - using country ID
    $country_id = intval($_GET['country_id']);
    
    $stmt = $conn->prepare("SELECT id, name FROM states WHERE country_id = ? ORDER BY name");
    $stmt->bind_param("i", $country_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $states = [];
    while ($row = $result->fetch_assoc()) {
        $states[] = [
            'id' => $row['id'],
            'name' => $row['name']
        ];
    }
    
    $stmt->close();
    echo json_encode($states);
    
} elseif (isset($_GET['country_name']) && !empty($_GET['country_name'])) {
    // New system - using country name
    $country_name = $_GET['country_name'];
    
    $stmt = $conn->prepare("SELECT s.id, s.name FROM states s JOIN countries c ON s.country_id = c.id WHERE c.name = ? ORDER BY s.name");
    $stmt->bind_param("s", $country_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $states = [];
    while ($row = $result->fetch_assoc()) {
        $states[] = [
            'id' => $row['id'],
            'name' => $row['name']
        ];
    }
    
    $stmt->close();
    echo json_encode($states);
    
} else {
    echo json_encode(['error' => 'Invalid parameters. Use country_id or country_name']);
}

$conn->close();
?>
