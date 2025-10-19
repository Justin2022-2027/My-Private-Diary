<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

$user_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? $_GET['search'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';
$mood = isset($_GET['mood']) ? $_GET['mood'] : '';

// Get all moods for the dropdown
$moods_query = "SELECT DISTINCT mood FROM diary_entries WHERE user_id = ? AND mood IS NOT NULL";
$stmt = $conn->prepare($moods_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$moods_result = $stmt->get_result();
$available_moods = [];
while ($row = $moods_result->fetch_assoc()) {
    if (!empty($row['mood'])) {
        $available_moods[] = $row['mood'];
    }
}
$stmt->close();

// Build the query with filters
$query = "SELECT * FROM diary_entries WHERE user_id = ?";
$params = array($user_id);
$types = "i";

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR content LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($date)) {
    $query .= " AND DATE(created_at) = ?";
    $params[] = $date;
    $types .= "s";
}

if (!empty($mood)) {
    $query .= " AND mood = ?";
    $params[] = $mood;
    $types .= "s";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$entries = [];
while ($row = $result->fetch_assoc()) {
    $entries[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Entries - My Private Diary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <style>
        body {
            overflow-x: hidden;
        }
        .main-content {
            padding: 2rem 0;
        }
        .container {
            max-width: 100%;
            padding: 0 1.5rem;
        }

        /* Update color variables */
        :root {
            --primary: #ff9fb0;
            --primary-dark: #ff7a93;
            --primary-light: #ffe5e9;
            --secondary: #6b7280;
            --dark: #1e293b;
            --gray: #64748b;
            --light: #f8fafc;
            --white: #ffffff;
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --radius-sm: 0.5rem;
            --radius: 1rem;
            --radius-lg: 1.5rem;
        }

        .entries-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        /* Filter container styles */
        .filters-section {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 1200px;
        }

        .filter-header {
            margin-bottom: 2rem;
        }

        .filter-header h2 {
            color: var(--dark);
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Filter grid layout */
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        /* Filter group styles */
        .filter-group {
            background: var(--primary);
            padding: 1.25rem;
            border-radius: var(--radius-sm);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .filter-label {
            display: block;
            margin-bottom: 0.75rem;
            color: var(--dark);
            font-weight: 500;
            font-size: 0.95rem;
        }

        .filter-input {
            width: 100%;
            padding: 0.75rem 0.01rem;
            border: 1.5px solid var(--light);
            border-radius: var(--radius-sm);
            background: var(--primary-light);
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .filter-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
            outline: none;
        }

        /* Button group styles */
        .filter-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--light);
        }

        .btn {
            background: var(--primary);
            color: var(--white);
            border: none;
            font-size: 1rem;
            text-decoration: none;
            cursor: pointer;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-sm);
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-search {
            background: var(--primary);
            color: var(--white);
            border: none;
        }

        .btn-reset {
            background: var(--primary);
            color: var(--white);
            border: none;
        }

        .entries-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .entry-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .entry-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .entry-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--light);
        }

        .entry-title {
            color: var(--dark);
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        .entry-content {
            color: var(--gray);
            font-size: 1rem;
            line-height: 1.7;
            margin: 1rem 0;
        }

        .entry-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 1rem;
        }

        .entry-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray);
            font-size: 0.9rem;
        }

        .mood-badge {
            background: var(--primary-light);
            color: var(--primary);
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <main class="main-content">
            <div class="entries-container">
                <div class="filters-section">
                    <div class="filter-header">
                        <h2><i class="fas fa-filter"></i> Filter Entries</h2>
                        <p>Find your past entries by applying filters</p>
                    </div>

                    <form method="GET" action="">
                        <div class="filters-grid">
                            <div class="filter-group">
                                <label class="filter-label">
                                    <i class="fas fa-search"></i>
                                    Search Entries
                                </label>
                                <input type="text" name="search" class="filter-input" 
                                       value="<?= htmlspecialchars($search) ?>" 
                                       placeholder="Search by title or content...">
                            </div>

                            <div class="filter-group">
                                <label class="filter-label">
                                    <i class="fas fa-calendar"></i>
                                    Filter by Date
                                </label>
                                <input type="date" name="date" class="filter-input" 
                                       value="<?= htmlspecialchars($date) ?>">
                            </div>

                            <div class="filter-group">
                                <label class="filter-label">
                                    <i class="fas fa-smile"></i>
                                    Filter by Mood
                                </label>
                                <select name="mood" class="filter-input">
                                    <option value="">All Moods</option>
                                    <option value="Happy" <?= $mood === 'Happy' ? 'selected' : '' ?>>Happy</option>
                                    <option value="Sad" <?= $mood === 'Sad' ? 'selected' : '' ?>>Sad</option>
                                    <option value="Excited" <?= $mood === 'Excited' ? 'selected' : '' ?>>Excited</option>
                                    <option value="Anxious" <?= $mood === 'Anxious' ? 'selected' : '' ?>>Anxious</option>
                                    <option value="Calm" <?= $mood === 'Calm' ? 'selected' : '' ?>>Calm</option>
                                    <?php foreach ($available_moods as $available_mood): ?>
                                        <?php if (!in_array($available_mood, ['Happy', 'Sad', 'Excited', 'Anxious', 'Calm'])): ?>
                                            <option value="<?= htmlspecialchars($available_mood) ?>" 
                                                    <?= $mood === $available_mood ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($available_mood) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn btn-search">
                                <i class="fas fa-search"></i>
                                Search
                            </button>
                            <a href="view_entries.php" class="btn btn-reset">
                                <i class="fas fa-undo"></i>
                                Reset Filters
                            </a>
                        </div>
                    </form>
                </div>

                <?php if (empty($entries)): ?>
                    <div class="no-entries" style="text-align: center; padding: 3rem; background: var(--light); border-radius: var(--radius);">
                        <i class="fas fa-book-open" style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;"></i>
                        <h3 style="color: var(--dark); margin-bottom: 0.5rem;">No entries found</h3>
                        <p style="color: var(--gray); margin-bottom: 1.5rem;">Try adjusting your search filters or create a new entry.</p>
                        <a href="write_entry.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Entry
                        </a>
                    </div>
                <?php else: ?>
                    <div class="entries-grid">
                        <?php foreach ($entries as $entry): ?>
                            <div class="entry-card">
                                <div class="entry-header">
                                    <div class="entry-title"><?= htmlspecialchars($entry['title']) ?></div>
                                    <div class="entry-meta">
                                        <span><?= date('F j, Y', strtotime($entry['created_at'])) ?></span>
                                        <?php if (!empty($entry['mood'])): ?>
                                            <span class="mood-badge" style="margin-left: 0.5rem;">
                                                <i class="far fa-smile" style="margin-right: 0.25rem;"></i>
                                                <?= htmlspecialchars($entry['mood']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="entry-content">
                                    <?= nl2br(htmlspecialchars(substr($entry['content'], 0, 250) . (strlen($entry['content']) > 250 ? '...' : ''))) ?>
                                </div>
                                <div class="entry-actions">
                                    <a href="view_entry.php?id=<?= $entry['entry_id'] ?>" class="btn btn-outline btn-sm">
                                        <i class="fas fa-eye"></i> View
                                        </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>