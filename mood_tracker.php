<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

$user_id = $_SESSION['user_id'];
$current_month = date('F Y');
$current_year_month = date('Y-m');

// Get total entries
$total_query = $conn->prepare("SELECT COUNT(*) as total FROM diary_entries WHERE user_id = ?");
$total_query->bind_param("i", $user_id);
$total_query->execute();
$total_result = $total_query->get_result();
$total_entries = $total_result->fetch_assoc()['total'];

// Get current month entries
$month_query = $conn->prepare("SELECT COUNT(*) as monthly FROM diary_entries WHERE user_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?");
$month_query->bind_param("is", $user_id, $current_year_month);
$month_query->execute();
$month_result = $month_query->get_result();
$monthly_entries = $month_result->fetch_assoc()['monthly'];

// Get most common mood
$mood_query = $conn->prepare("SELECT mood, COUNT(*) as count FROM diary_entries WHERE user_id = ? GROUP BY mood ORDER BY count DESC LIMIT 1");
$mood_query->bind_param("i", $user_id);
$mood_query->execute();
$mood_result = $mood_query->get_result();
$most_common_mood = $mood_result->fetch_assoc();
$predominant_mood = $most_common_mood ? $most_common_mood['mood'] : 'None';

// Get mood distribution
$moods = ['Happy', 'Sad', 'Excited', 'Angry', 'Calm', 'Anxious'];
$mood_stats = [];

foreach ($moods as $mood) {
    $stat_query = $conn->prepare("SELECT COUNT(*) as count FROM diary_entries WHERE user_id = ? AND mood = ?");
    $stat_query->bind_param("is", $user_id, $mood);
    $stat_query->execute();
    $stat_result = $stat_query->get_result();
    $count = $stat_result->fetch_assoc()['count'];
    $percentage = $total_entries > 0 ? round(($count / $total_entries) * 100) : 0;
    $mood_stats[$mood] = ['count' => $count, 'percentage' => $percentage];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mood Tracker - My Private Diary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #ff9fb0;
            --primary-dark: #ff7a93;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray: #64748b;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --radius: 0.5rem;
            --transition: all 0.3s ease;
            
            /* Theme variables */
            --bg-color: var(--light);
            --text-color: var(--dark);
            --card-bg: var(--white);
            --border-color: #e2e8f0;
        }

        [data-theme="dark"] {
            --bg-color: #1a1a1a;
            --text-color: #ffffff;
            --card-bg: #2d2d2d;
            --border-color: #404040;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            transition: var(--transition);
        }

        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2.5rem 2rem 2rem 2rem;
            margin-top: 90px; /* for fixed header */
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .stat-title {
            font-size: 1.1rem;
            color: var(--gray);
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .stat-subtitle {
            font-size: 0.9rem;
            color: var(--gray);
        }

        .mood-distribution {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
        }

        .mood-bar {
            background: #e2e8f0;
            height: 24px;
            border-radius: 12px;
            margin: 1rem 0;
            position: relative;
            overflow: hidden;
        }

        .mood-progress {
            height: 100%;
            background: var(--primary);
            border-radius: 12px;
            transition: width 1s ease-in-out;
        }

        .mood-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .mood-name {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .mood-icon {
            font-size: 1.2rem;
        }

        .mood-percentage {
            font-weight: 600;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--text-color);
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <main class="main-content">
        <h1 class="page-title">
            <i class="fas fa-chart-line" style="color: var(--primary);"></i>
            Mood Tracker
        </h1>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">Total Entries</div>
                <div class="stat-value"><?php echo $total_entries; ?></div>
                <div class="stat-subtitle">Logged in your journal</div>
            </div>

            <div class="stat-card">
                <div class="stat-title">Most Common Mood</div>
                <div class="stat-value"><?php echo $predominant_mood; ?></div>
                <div class="stat-subtitle">Your predominant feeling</div>
            </div>

            <div class="stat-card">
                <div class="stat-title">Current Month Entries</div>
                <div class="stat-value"><?php echo $monthly_entries; ?></div>
                <div class="stat-subtitle">For <?php echo date('F Y'); ?></div>
            </div>
        </div>

        <div class="mood-distribution">
            <h2 class="section-title">Mood Distribution</h2>
            <?php foreach ($mood_stats as $mood => $stats): ?>
                <div class="mood-label">
                    <div class="mood-name">
                        <i class="mood-icon fas fa-<?php 
                            echo match($mood) {
                                'Happy' => 'smile',
                                'Sad' => 'frown',
                                'Excited' => 'grin-stars',
                                'Angry' => 'angry',
                                'Calm' => 'peace',
                                'Anxious' => 'meh',
                                default => 'meh'
                            };
                        ?>"></i>
                        <?php echo $mood; ?>
                    </div>
                    <div class="mood-percentage"><?php echo $stats['percentage']; ?>%</div>
                </div>
                <div class="mood-bar">
                    <div class="mood-progress" style="width: <?php echo $stats['percentage']; ?>%"></div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
        // Check for saved theme preference
        window.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('darkMode');
            if (savedTheme === 'true') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        });
    </script>
</body>
</html>
