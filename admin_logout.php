<?php
session_start();

// Destroy admin session
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_username']);

// Redirect to admin login
header("Location: admin_login.php");
exit();
?>