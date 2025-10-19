<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - My Private Diary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #ff9fb0;
            --primary-dark: #ff7a93;
            --secondary: #f59e0b;
            --success: #10b981;
            --danger: #ef4444;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray: #64748b;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: var(--light);
            min-height: 100vh;
        }

        .header {
            background-color: var(--white);
            box-shadow: var(--shadow-sm);
            padding: 1rem 2rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--dark);
            font-weight: 700;
            font-size: 1.25rem;
        }

        .logo i {
            color: var(--primary);
            font-size: 1.5rem;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--gray);
        }

        .logout-btn {
            background: var(--danger);
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .logout-btn:hover {
            background: #dc2626;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            margin-top: 80px;
        }

        .dashboard-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 2rem;
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-left: 4px solid var(--primary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            background: var(--primary);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .stat-icon i {
            color: var(--white);
            font-size: 1.5rem;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--gray);
            font-size: 1rem;
        }

        .charts-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .chart-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
        }

        .chart-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .details-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .detail-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
        }

        .detail-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-list {
            list-style: none;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--light);
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: var(--gray);
        }

        .detail-value {
            font-weight: 600;
            color: var(--dark);
        }

        @media (max-width: 768px) {
            .charts-section {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 1rem;
            }

            .dashboard-title {
                font-size: 2rem;
            }

            .header-content {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">
                <i class="fas fa-book-open"></i>
                <span>My Private Diary - Admin</span>
            </a>
            <div class="admin-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <form method="POST" action="admin_logout.php" style="display: inline;">
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <div class="container">
        <h1 class="dashboard-title">
            <i class="fas fa-chart-line" style="color: var(--primary); margin-right: 0.5rem;"></i>
            Admin Dashboard
        </h1>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value" data-stat="total_users"><?php echo number_format($total_users); ?></div>
                <div class="stat-label">Total Users</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-value" data-stat="total_entries"><?php echo number_format($total_entries); ?></div>
                <div class="stat-label">Total Diary Entries</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="stat-value" data-stat="premium_users"><?php echo number_format($premium_users); ?></div>
                <div class="stat-label">Premium Users</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="stat-value" data-stat="total_revenue">₹<?php echo number_format($total_revenue, 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-value" data-stat="recent_users"><?php echo number_format($recent_users); ?></div>
                <div class="stat-label">New Users (30 days)</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-pen-fancy"></i>
                </div>
                <div class="stat-value" data-stat="recent_entries"><?php echo number_format($recent_entries); ?></div>
                <div class="stat-label">New Entries (30 days)</div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-card">
                <h3 class="chart-title">User Subscription Distribution</h3>
                <canvas id="subscriptionChart" width="400" height="300"></canvas>
            </div>

            <div class="chart-card">
                <h3 class="chart-title">Mood Distribution</h3>
                <canvas id="moodChart" width="400" height="300"></canvas>
            </div>
        </div>

        <!-- Subscription Analysis Section -->
        <div class="details-section">
            <div class="detail-card">
                <h3 class="detail-title">
                    <i class="fas fa-chart-pie"></i>
                    Subscription Analysis
                </h3>
                <ul class="detail-list">
                    <li class="detail-item">
                        <span class="detail-label">Basic Users</span>
                        <span class="detail-value" data-detail="basic_users"><?php echo number_format($basic_users); ?></span>
                    </li>
                    <li class="detail-item">
                        <span class="detail-label">Premium Users</span>
                        <span class="detail-value" data-detail="premium_users"><?php echo number_format($premium_users); ?></span>
                    </li>
                    <li class="detail-item">
                        <span class="detail-label">Lifetime Users</span>
                        <span class="detail-value" data-detail="lifetime_users"><?php echo number_format($subscription_breakdown['lifetime'] ?? 0); ?></span>
                    </li>
                    <li class="detail-item">
                        <span class="detail-label">Total Users</span>
                        <span class="detail-value" data-detail="total_users"><?php echo number_format($total_users); ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Details Section -->
        <div class="details-section">
            <div class="detail-card">
                <h3 class="detail-title">
                    <i class="fas fa-shield-alt"></i>
                    Security & Access
                </h3>
                <ul class="detail-list">
                    <li class="detail-item">
                        <span class="detail-label">Total Login Attempts</span>
                        <span class="detail-value" data-detail="total_login_attempts"><?php echo number_format($total_login_attempts); ?></span>
                    </li>
                    <li class="detail-item">
                        <span class="detail-label">Successful Logins</span>
                        <span class="detail-value" data-detail="successful_logins"><?php echo number_format($successful_logins); ?></span>
                    </li>
                    <li class="detail-item">
                        <span class="detail-label">Failed Login Attempts</span>
                        <span class="detail-value" data-detail="failed_logins"><?php echo number_format($failed_logins); ?></span>
                    </li>
                    <li class="detail-item">
                        <span class="detail-label">Total Payments</span>
                        <span class="detail-value" data-detail="total_payments"><?php echo number_format($total_payments); ?></span>
                    </li>
                </ul>
            </div>

            <div class="detail-card">
                <h3 class="detail-title">
                    <i class="fas fa-cogs"></i>
                    System Features
                </h3>
                <ul class="detail-list">
                    <li class="detail-item">
                        <span class="detail-label">Total Testimonials</span>
                        <span class="detail-value" data-detail="total_testimonials"><?php echo number_format($total_testimonials); ?></span>
                    </li>
                    <li class="detail-item">
                        <span class="detail-label">Total Backups</span>
                        <span class="detail-value" data-detail="total_backups"><?php echo number_format($total_backups); ?></span>
                    </li>
                    <li class="detail-item">
                        <span class="detail-label">Active Reminders</span>
                        <span class="detail-value" data-detail="total_reminders"><?php echo number_format($total_reminders); ?></span>
                    </li>
                    <li class="detail-item">
                        <span class="detail-label">Basic Users</span>
                        <span class="detail-value" data-detail="basic_users"><?php echo number_format($basic_users); ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        let subscriptionChart, moodChart;

        // Function to update stats
        function updateStats() {
            fetch('admin_stats_api.php')
                .then(response => response.json())
                .then(data => {
                    // Update stat values
                    document.querySelector('.stat-value[data-stat="total_users"]').textContent = new Intl.NumberFormat().format(data.total_users);
                    document.querySelector('.stat-value[data-stat="total_entries"]').textContent = new Intl.NumberFormat().format(data.total_entries);
                    document.querySelector('.stat-value[data-stat="premium_users"]').textContent = new Intl.NumberFormat().format(data.premium_users);
                    document.querySelector('.stat-value[data-stat="total_revenue"]').textContent = '₹' + new Intl.NumberFormat().format(data.total_revenue, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    document.querySelector('.stat-value[data-stat="recent_users"]').textContent = new Intl.NumberFormat().format(data.recent_users);
                    document.querySelector('.stat-value[data-stat="recent_entries"]').textContent = new Intl.NumberFormat().format(data.recent_entries);

                    // Update details
                    document.querySelector('.detail-value[data-detail="total_login_attempts"]').textContent = new Intl.NumberFormat().format(data.total_login_attempts);
                    document.querySelector('.detail-value[data-detail="successful_logins"]').textContent = new Intl.NumberFormat().format(data.successful_logins);
                    document.querySelector('.detail-value[data-detail="failed_logins"]').textContent = new Intl.NumberFormat().format(data.failed_logins);
                    document.querySelector('.detail-value[data-detail="total_payments"]').textContent = new Intl.NumberFormat().format(data.total_payments);
                    document.querySelector('.detail-value[data-detail="total_testimonials"]').textContent = new Intl.NumberFormat().format(data.total_testimonials);
                    document.querySelector('.detail-value[data-detail="total_backups"]').textContent = new Intl.NumberFormat().format(data.total_backups);
                    document.querySelector('.detail-value[data-detail="total_reminders"]').textContent = new Intl.NumberFormat().format(data.total_reminders);
                    document.querySelector('.detail-value[data-detail="basic_users"]').textContent = new Intl.NumberFormat().format(data.basic_users);

                    // Update subscription analysis
                    document.querySelector('.detail-value[data-detail="lifetime_users"]').textContent = new Intl.NumberFormat().format(data.subscription_breakdown.lifetime || 0);

                    // Update charts
                    if (subscriptionChart) {
                        subscriptionChart.data.datasets[0].data = [data.basic_users, data.premium_users];
                        subscriptionChart.update();
                    }

                    if (moodChart) {
                        moodChart.data.labels = Object.keys(data.mood_stats);
                        moodChart.data.datasets[0].data = Object.values(data.mood_stats).map(stat => stat.count);
                        moodChart.update();
                    }
                })
                .catch(error => console.error('Error updating stats:', error));
        }

        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            // Subscription Distribution Chart
            const subscriptionCtx = document.getElementById('subscriptionChart').getContext('2d');
            subscriptionChart = new Chart(subscriptionCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Basic Users', 'Premium Users'],
                    datasets: [{
                        data: [<?php echo $basic_users; ?>, <?php echo $premium_users; ?>],
                        backgroundColor: ['#64748b', '#ff9fb0'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });

            // Mood Distribution Chart
            const moodCtx = document.getElementById('moodChart').getContext('2d');
            moodChart = new Chart(moodCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_keys($mood_stats)); ?>,
                    datasets: [{
                        label: 'Entries',
                        data: <?php echo json_encode(array_column($mood_stats, 'count')); ?>,
                        backgroundColor: '#ff9fb0',
                        borderColor: '#ff7a93',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Update stats every 30 seconds
            setInterval(updateStats, 30000);

            // Initial update
            updateStats();
        });
    </script>
</body>
</html>