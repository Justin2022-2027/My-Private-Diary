<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

$user_id = $_SESSION['user_id'];

// Get user's login history
$stmt = $conn->prepare("SELECT login_id, email, login_time, ip_address, user_agent, status FROM login WHERE user_id = ? ORDER BY login_time DESC LIMIT 20");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$login_history = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get total login count
$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM login WHERE user_id = ?");
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_logins = $count_result->fetch_assoc()['total'];
$count_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login History - My Private Diary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ff9fb0;
            --primary-dark: #ff7a93;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray: #64748b;
            --white: #ffffff;
            --success: #10b981;
            --danger: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--light);
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            background: var(--white);
            padding: 1.5rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .back-btn {
            padding: 0.5rem 1rem;
            background: var(--primary);
            color: var(--white);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .back-btn:hover {
            background: var(--primary-dark);
        }

        .page-title {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .stats {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .stat-card {
            text-align: center;
            padding: 1rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-label {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .history-table {
            background: var(--white);
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--primary);
            color: var(--white);
        }

        th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--light);
        }

        tbody tr:hover {
            background: var(--light);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-success {
            background: #d1fae5;
            color: var(--success);
        }

        .status-failed {
            background: #fee2e2;
            color: var(--danger);
        }

        .no-data {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
        }

        .user-agent {
            font-size: 0.85rem;
            color: var(--gray);
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            table {
                font-size: 0.85rem;
            }

            th, td {
                padding: 0.5rem;
            }

            .user-agent {
                max-width: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-book-open"></i> My Private Diary
            </div>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="container">
        <h1 class="page-title">
            <i class="fas fa-history"></i> Login History
        </h1>

        <div class="stats">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $total_logins; ?></div>
                    <div class="stat-label">Total Logins</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo count($login_history); ?></div>
                    <div class="stat-label">Recent Logins (Last 20)</div>
                </div>
            </div>
        </div>

        <div class="history-table">
            <?php if (count($login_history) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date & Time</th>
                            <th>IP Address</th>
                            <th>Device/Browser</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($login_history as $index => $login): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($login['login_time'])); ?></td>
                                <td><?php echo htmlspecialchars($login['ip_address']); ?></td>
                                <td>
                                    <div class="user-agent" title="<?php echo htmlspecialchars($login['user_agent']); ?>">
                                        <?php echo htmlspecialchars($login['user_agent']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $login['status']; ?>">
                                        <?php echo ucfirst($login['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-inbox" style="font-size: 3rem; color: var(--gray); margin-bottom: 1rem;"></i>
                    <p>No login history found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
