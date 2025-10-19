<?php
header('Content-Type: application/json');
require 'db_connect.php';

// Support both state_id and state_name parameters
if (isset($_GET['state_id']) && is_numeric($_GET['state_id'])) {
    // Old system - using state ID
    $state_id = intval($_GET['state_id']);
    
    $stmt = $conn->prepare("SELECT id, name FROM cities WHERE state_id = ? ORDER BY name");
    $stmt->bind_param("i", $state_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $cities = [];
    while ($row = $result->fetch_assoc()) {
        $cities[] = [
            'id' => $row['id'],
            'name' => $row['name']
        ];
    }
    
    $stmt->close();
    echo json_encode($cities);
    
} elseif (isset($_GET['state_name']) && !empty($_GET['state_name'])) {
    // New system - using state name
    $state_name = $_GET['state_name'];
    
    $stmt = $conn->prepare("SELECT c.id, c.name FROM cities c JOIN states s ON c.state_id = s.id WHERE s.name = ? ORDER BY c.name");
    $stmt->bind_param("s", $state_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $cities = [];
    while ($row = $result->fetch_assoc()) {
        $cities[] = [
            'id' => $row['id'],
            'name' => $row['name']
        ];
    }
    
    $stmt->close();
    echo json_encode($cities);
    
} else {
    echo json_encode(['error' => 'Invalid parameters. Use state_id or state_name']);
}

$conn->close();
?>
