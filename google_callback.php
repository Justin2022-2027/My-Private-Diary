<?php
session_start();
require_once 'google_config.php';
require_once 'db_connect.php';

// Basic error helper
function fail_and_redirect($msg) {
    $_SESSION['message'] = $msg;
    $_SESSION['message_type'] = 'error';
    header('Location: login.php');
    exit;
}

// verify state
if (!isset($_GET['state']) || !isset($_SESSION['oauth2state']) || $_GET['state'] !== $_SESSION['oauth2state']) {
    fail_and_redirect('Invalid OAuth state. Try again.');
}

if (!isset($_GET['code'])) {
    fail_and_redirect('Authorization code not provided.');
}

// Exchange code for tokens
$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'grant_type' => 'authorization_code',
        'client_id' => '1089414997213-ingip05t4g7diqgtdj54ra0dqr23283q.apps.googleusercontent.com',
        'client_secret' => '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',
        'redirect_uri' => 'http://localhost/mpd/google_callback.php',
        'code' => $_GET['code']
    ]),
    CURLOPT_POST => true
]);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    fail_and_redirect('Token request failed: ' . curl_error($ch));
}
curl_close($ch);

$token_data = json_decode($response, true);
if (!isset($token_data['access_token'])) {
    fail_and_redirect('Failed to get access token');
}

// Get user info from Google
$ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token_data['access_token']]
]);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    fail_and_redirect('Failed to get user info: ' . curl_error($ch));
}
curl_close($ch);

$userinfo = json_decode($response, true);
if (!isset($userinfo['email'])) {
    fail_and_redirect('Email not received from Google');
}

// Get the name from Google userinfo
$google_name = $userinfo['name'] ?? '';
$google_email = $userinfo['email'];

// Check if user exists
$stmt = $conn->prepare("SELECT user_id, full_name FROM signup WHERE email = ?");
$stmt->bind_param("s", $google_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Existing user - update name if different
    $user = $result->fetch_assoc();
    if ($user['full_name'] !== $google_name) {
        $update = $conn->prepare("UPDATE signup SET full_name = ? WHERE user_id = ?");
        $update->bind_param("si", $google_name, $user['user_id']);
        $update->execute();
    }
    $_SESSION['user_id'] = $user['user_id'];
} else {
    // New user - create account
    $random_password = bin2hex(random_bytes(16));
    $password_hash = password_hash($random_password, PASSWORD_DEFAULT);
    
    $insert = $conn->prepare("INSERT INTO signup (full_name, email, password) VALUES (?, ?, ?)");
    $insert->bind_param("sss", $google_name, $google_email, $password_hash);
    
    if (!$insert->execute()) {
        fail_and_redirect('Failed to create account');
    }
    $_SESSION['user_id'] = $conn->insert_id;
}

// Set session variables with Google data
$_SESSION['full_name'] = $google_name;
$_SESSION['email'] = $google_email;

// Redirect to dashboard
header('Location: dashboard.php');
exit;