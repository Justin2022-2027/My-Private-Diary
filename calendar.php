<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

$user_id = $_SESSION['user_id'];
$current_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Get entries for the current month
$stmt = $conn->prepare("
    SELECT 
        DATE(created_at) as date,
        title,
        SUBSTRING(content, 1, 100) as preview,
        mood,
        entry_id
    FROM diary_entries 
    WHERE user_id = ? 
    AND DATE_FORMAT(created_at, '%Y-%m') = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("is", $user_id, $current_month);
$stmt->execute();
$result = $stmt->get_result();

$entries = [];
while ($row = $result->fetch_assoc()) {
    $entries[$row['date']][] = $row;
}
$stmt->close();

// Define mood colors and icons
$mood_styles = [
    'Happy' => [
        'color' => '#10b981',
        'icon' => 'fa-smile',
        'bg' => '#ecfdf5'
    ],
    'Sad' => [
        'color' => '#6b7280',
        'icon' => 'fa-frown',
        'bg' => '#f3f4f6'
    ],
    'Excited' => [
        'color' => '#f59e0b',
        'icon' => 'fa-grin-stars',
        'bg' => '#fef3c7'
    ],
    'Anxious' => [
        'color' => '#ef4444',
        'icon' => 'fa-meh',
        'bg' => '#fee2e2'
    ],
    'Calm' => [
        'color' => '#3b82f6',
        'icon' => 'fa-smile-beam',
        'bg' => '#eff6ff'
    ]
];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal Timeline - My Private Diary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ff9fb0;
            --primary-dark: #ff7a93;
            --primary-light: #ffd9e0;
            --secondary: #6b7280;
            --success: #10b981;
            --danger: #ef4444;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray: #64748b;
            --white: #ffffff;
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --radius-sm: 0.5rem;
            --radius: 1rem;
            --radius-lg: 1.5rem;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .calendar-container {
            display: flex;
            gap: 2rem;
            margin: 2rem 0;
            min-height: calc(100vh - 12rem);
        }

        .calendar-main {
            flex: 1;
            background: var(--white);
            padding: 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .calendar-main::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            width: 100%;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--light);
        }

        .header {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .page-title {
            text-align: center;
            font-size: 2.5rem;
            color: var(--primary-dark);
            margin: 0;
            font-weight: 600;
        }

        .back-button {
            position: absolute;
            left: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: var(--white);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: var(--radius);
            color: var(--gray);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
            z-index: 10;
        }

        .month-selector {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0 auto;
        }

        .month-selector button {
            background: var(--white);
            border: none;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray);
            box-shadow: var(--shadow-sm);
        }

        .month-selector button:hover {
            background: var(--primary);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .month-selector input[type="month"] {
            border: none;
            background: var(--white);
            padding: 0.75rem 1rem;
            border-radius: var(--radius-sm);
            font-size: 0.95rem;
            color: var(--dark);
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }

        .month-selector input[type="month"]:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1rem;
            margin-top: 1rem;
        }

        .calendar-day-header {
            text-align: center;
            font-weight: 600;
            color: var(--gray);
            padding: 0.75rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .calendar-day {
            aspect-ratio: 1;
            background: var(--light);
            border-radius: var(--radius);
            padding: 1rem;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .calendar-day:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow);
            background: var(--white);
        }

        .calendar-day.has-entries {
            background: var(--white);
            border: 2px solid var(--primary-light);
        }

        .calendar-day.selected {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            transform: scale(1.05);
            box-shadow: var(--shadow);
        }

        .calendar-day.selected .date {
            color: var(--white);
        }

        .day-number {
            font-weight: 600;
            font-size: 1.125rem;
            margin-bottom: 0.5rem;
            color: inherit;
        }

        .mood-indicators {
            display: flex;
            flex-wrap: wrap;
            gap: 0.375rem;
            padding: 0.5rem;
            background: rgba(255, 255, 255, 0.9);
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .mood-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            transition: var(--transition);
        }

        .calendar-day:hover .mood-dot {
            transform: scale(1.2);
        }

        .entries-preview {
            flex: 0 0 380px;
            background: var(--white);
            padding: 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            max-height: calc(100vh - 8rem);
            overflow-y: auto;
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .entries-preview::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
        }

        .entries-preview h3 {
            font-size: 1.5rem;
            color: var(--dark);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--light);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .entries-preview h3 i {
            color: var(--primary);
        }

        .entry-card {
            padding: 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 1.25rem;
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .entry-card:hover {
            transform: translateX(8px);
            box-shadow: var(--shadow);
        }

        .entry-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .entry-mood {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            background: var(--primary-light);
            color: var(--primary-dark);
            font-weight: 500;
        }

        .entry-title {
            font-weight: 600;
            font-size: 1.125rem;
            margin-bottom: 0.75rem;
            color: var(--dark);
            line-height: 1.4;
        }

        .entry-preview {
            font-size: 0.95rem;
            color: var(--gray);
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .entry-actions {
            margin-top: 1.25rem;
            display: flex;
            gap: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--light);
        }

        .entry-actions a {
            font-size: 0.875rem;
            color: var(--primary-dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-sm);
            transition: var(--transition);
            background: var(--light);
            font-weight: 500;
        }

        .entry-actions a:hover {
            background: var(--primary);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .no-entries {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--gray);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .no-entries i {
            font-size: 3rem;
            color: var(--primary);
            opacity: 0.7;
        }

        .no-entries p {
            font-size: 1.125rem;
            margin: 1rem 0;
            color: var(--secondary);
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.75rem;
            background: linear-gradient(to right, var(--primary), var(--primary-dark));
            color: var(--white);
            border-radius: var(--radius);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            background: linear-gradient(to right, var(--primary-dark), var(--primary));
        }

        /* Scrollbar styling */
        .entries-preview::-webkit-scrollbar {
            width: 8px;
        }

        .entries-preview::-webkit-scrollbar-track {
            background: var(--light);
            border-radius: 4px;
        }

        .entries-preview::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }

        .entries-preview::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }

        @media (max-width: 1200px) {
            .calendar-container {
                flex-direction: column;
            }

            .entries-preview {
                flex: none;
                max-height: none;
            }
        }

        @media (max-width: 768px) {
            .calendar-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .calendar-grid {
                gap: 0.5rem;
            }

            .calendar-day {
                padding: 0.5rem;
            }

            .day-number {
                font-size: 0.875rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="calendar-container">
                <div class="calendar-main">
                    <div class="calendar-header">
                        <div class="month-selector">
                            <?php
                            $prev_month = date('Y-m', strtotime($current_month . ' -1 month'));
                            $next_month = date('Y-m', strtotime($current_month . ' +1 month'));
                            ?>
                            <button onclick="window.location.href='?month=<?= $prev_month ?>'">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <input type="month" value="<?= $current_month ?>" 
                                   onchange="window.location.href='?month=' + this.value">
                            <button onclick="window.location.href='?month=<?= $next_month ?>'">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>

                    <div class="calendar-grid">
                        <?php
                        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                        foreach ($days as $day) {
                            echo "<div class='calendar-day-header'>$day</div>";
                        }

                        $first_day = new DateTime("$current_month-01");
                        $last_day = new DateTime($first_day->format('Y-m-t'));
                        
                        // Fill in leading empty days
                        $leading_days = $first_day->format('w');
                        for ($i = 0; $i < $leading_days; $i++) {
                            echo "<div class='calendar-day'></div>";
                        }

                        // Fill in the days
                        $current = clone $first_day;
                        while ($current <= $last_day) {
                            $date = $current->format('Y-m-d');
                            $has_entries = isset($entries[$date]);
                            $is_selected = $date === $selected_date;
                            
                            $class = 'calendar-day';
                            if ($has_entries) $class .= ' has-entries';
                            if ($is_selected) $class .= ' selected';
                            
                            echo "<div class='$class' onclick=\"window.location.href='?month=$current_month&date=$date'\">";
                            echo "<div class='day-number'>" . $current->format('j') . "</div>";
                            
                            if ($has_entries) {
                                echo "<div class='mood-indicators'>";
                                foreach ($entries[$date] as $entry) {
                                    $mood = $entry['mood'];
                                    if (isset($mood_styles[$mood])) {
                                        echo "<div class='mood-dot' style='background: {$mood_styles[$mood]['color']}'></div>";
                                    }
                                }
                                echo "</div>";
                            }
                            
                            echo "</div>";
                            
                            $current->modify('+1 day');
                        }
                        ?>
                    </div>
                </div>

                <div class="entries-preview">
                    <h3><i class="fas fa-book"></i> <?= date('F j, Y', strtotime($selected_date)) ?></h3>
                    <?php if (isset($entries[$selected_date])): ?>
                        <?php foreach ($entries[$selected_date] as $entry): ?>
                            <div class="entry-card" style="background: <?= $mood_styles[$entry['mood']]['bg'] ?>">
                                <div class="entry-header">
                                    <div class="entry-mood" style="color: <?= $mood_styles[$entry['mood']]['color'] ?>">
                                        <i class="fas <?= $mood_styles[$entry['mood']]['icon'] ?>"></i>
                                        <?= $entry['mood'] ?>
                                    </div>
                                </div>
                                <div class="entry-title"><?= htmlspecialchars($entry['title']) ?></div>
                                <div class="entry-preview"><?= htmlspecialchars($entry['preview']) ?>...</div>
                                <div class="entry-actions">
                                    <a href="view_entry.php?id=<?= $entry['entry_id'] ?>">
                                        <i class="fas fa-book-open"></i>
                                        Read More
                                    </a>
                                    <a href="view_entry.php?id=<?= $entry['entry_id'] ?>">
                                        <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-entries">
                            <i class="fas fa-book"></i>
                            <p>No entries for this date</p>
                            <a href="write_entry.php" class="btn-primary">
                                <i class="fas fa-pen"></i>
                                Write New Entry
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>