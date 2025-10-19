<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require 'db_connect.php';

// Get total users
$total_users_query = "SELECT COUNT(*) as total FROM users";
$total_users_result = $conn->query($total_users_query);
$total_users = $total_users_result->fetch_assoc()['total'];

// Get total diary entries
$total_entries_query = "SELECT COUNT(*) as total FROM diary_entries";
$total_entries_result = $conn->query($total_entries_query);
$total_entries = $total_entries_result->fetch_assoc()['total'];

// Get subscription breakdown
$subscription_breakdown = [];
$subscription_plans = ['basic', 'premium', 'lifetime'];
foreach ($subscription_plans as $plan) {
    $plan_query = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE subscription_plan = ?");
    $plan_query->bind_param("s", $plan);
    $plan_query->execute();
    $plan_result = $plan_query->get_result();
    $count = $plan_result->fetch_assoc()['count'];
    $subscription_breakdown[$plan] = $count;
    $plan_query->close();
}

// Get premium users (including lifetime)
$premium_users = $subscription_breakdown['premium'] + $subscription_breakdown['lifetime'];

// Get basic users
$basic_users = $subscription_breakdown['basic'];

// Get total payments
$total_payments_query = "SELECT COUNT(*) as total FROM payments";
$total_payments_result = $conn->query($total_payments_query);
$total_payments = $total_payments_result->fetch_assoc()['total'];

// Get total revenue
$total_revenue_query = "SELECT SUM(amount) as total FROM payments WHERE status = 'completed'";
$total_revenue_result = $conn->query($total_revenue_query);
$total_revenue = $total_revenue_result->fetch_assoc()['total'] ?? 0;

// Get recent users (last 30 days)
$recent_users_query = "SELECT COUNT(*) as total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$recent_users_result = $conn->query($recent_users_query);
$recent_users = $recent_users_result->fetch_assoc()['total'];

// Get recent entries (last 30 days)
$recent_entries_query = "SELECT COUNT(*) as total FROM diary_entries WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$recent_entries_result = $conn->query($recent_entries_query);
$recent_entries = $recent_entries_result->fetch_assoc()['total'];

// Get mood distribution
$mood_stats = [];
$moods = ['Happy', 'Sad', 'Excited', 'Anxious', 'Calm', 'Angry'];
foreach ($moods as $mood) {
    $mood_query = $conn->prepare("SELECT COUNT(*) as count FROM diary_entries WHERE mood = ?");
    $mood_query->bind_param("s", $mood);
    $mood_query->execute();
    $mood_result = $mood_query->get_result();
    $count = $mood_result->fetch_assoc()['count'];
    $percentage = $total_entries > 0 ? round(($count / $total_entries) * 100, 1) : 0;
    $mood_stats[$mood] = ['count' => $count, 'percentage' => $percentage];
    $mood_query->close();
}

// Get login attempts stats
$login_attempts_query = "SELECT COUNT(*) as total FROM login_attempts";
$login_attempts_result = $conn->query($login_attempts_query);
$total_login_attempts = $login_attempts_result->fetch_assoc()['total'];

$successful_logins_query = "SELECT COUNT(*) as total FROM login_attempts WHERE status = 'success'";
$successful_logins_result = $conn->query($successful_logins_query);
$successful_logins = $successful_logins_result->fetch_assoc()['total'];

$failed_logins_query = "SELECT COUNT(*) as total FROM login_attempts WHERE status = 'failed'";
$failed_logins_result = $conn->query($failed_logins_query);
$failed_logins = $failed_logins_result->fetch_assoc()['total'];

// Get testimonials count
$testimonials_query = "SELECT COUNT(*) as total FROM testimonials";
$testimonials_result = $conn->query($testimonials_query);
$total_testimonials = $testimonials_result->fetch_assoc()['total'];

// Get backup history count
$backups_query = "SELECT COUNT(*) as total FROM backup_history";
$backups_result = $conn->query($backups_query);
$total_backups = $backups_result->fetch_assoc()['total'];

// Get reminders count
$reminders_query = "SELECT COUNT(*) as total FROM reminders";
$reminders_result = $conn->query($reminders_query);
$total_reminders = $reminders_result->fetch_assoc()['total'];

$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'total_users' => $total_users,
    'total_entries' => $total_entries,
    'premium_users' => $premium_users,
    'basic_users' => $basic_users,
    'subscription_breakdown' => $subscription_breakdown,
    'total_payments' => $total_payments,
    'total_revenue' => $total_revenue,
    'recent_users' => $recent_users,
    'recent_entries' => $recent_entries,
    'mood_stats' => $mood_stats,
    'total_login_attempts' => $total_login_attempts,
    'successful_logins' => $successful_logins,
    'failed_logins' => $failed_logins,
    'total_testimonials' => $total_testimonials,
    'total_backups' => $total_backups,
    'total_reminders' => $total_reminders
]);
?>