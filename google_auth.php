<?php
session_start();
require_once __DIR__ . '/google_config.php';

// generate a random state to protect against CSRF
$state = bin2hex(random_bytes(16));
$_SESSION['oauth2state'] = $state;

$params = [
    'response_type' => 'code',
    'client_id' => '1089414997213-ingip05t4g7diqgtdj54ra0dqr23283q.apps.googleusercontent.com',
    'redirect_uri' => 'http://localhost/mpd/google_callback.php',
    'scope' => GOOGLE_SCOPES,
    'state' => $state,
    'access_type' => 'offline',
    'prompt' => 'consent'
];

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
header('Location: ' . $authUrl);
exit;