<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if (!isset($_FILES['profile_picture'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$upload_dir = 'uploads/profile_pictures/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$file = $_FILES['profile_picture'];
$user_id = $_SESSION['user_id'];

// Validate file type
$allowed = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($file['type'], $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG and GIF allowed']);
    exit;
}

// Generate unique filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
$filepath = $upload_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $filepath)) {
    require_once 'db_connect.php';
    
    // Update database
    $stmt = $conn->prepare("UPDATE signup SET profile_picture = ? WHERE user_id = ?");
    $stmt->bind_param("si", $filename, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Profile picture updated successfully',
            'filename' => $filename
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Database update failed: ' . $stmt->error
        ]);
    }
    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to move uploaded file'
    ]);
}
