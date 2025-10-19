<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
require_once 'db_connect.php';

// Debugging: Check if user_id is set in the session
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    die("<h2 style='color: #ff7a93; text-align:center; margin-top:3rem;'>Session user_id is not set or invalid.</h2>");
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
if (!$stmt) {
    die("<h2 style='color: #ff7a93; text-align:center; margin-top:3rem;'>Database query preparation failed: " . $conn->error . "</h2>");
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result ? $result->fetch_assoc() : null;

// Debugging: Check if the user exists
if (!$user) {
    echo "<h2 style='color: #ff7a93; text-align:center; margin-top:3rem;'>User profile not found. Please ensure your account exists.</h2>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - My Private Diary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #818cf8;
            --primary-dark: #4338ca;
            --secondary-color: #f472b6;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --bg-light: #f9fafb;
            --bg-card: #ffffff;
            --border-color: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
            --pink: #ff9fb0;
            --pink-dark: #ff7a93;
            --dark: #1e293b;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --white: #ffffff;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: var(--gray-600);
            line-height: 1.6;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        /* Header Styles */
        .profile-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 2rem 0 3rem;
            position: relative;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            border-bottom-left-radius: 2rem;
            border-bottom-right-radius: 2rem;
            overflow: hidden;
        }
        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 10% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 20%);
            pointer-events: none;
        }
        .header-content {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 2rem;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            backdrop-filter: blur(5px);
        }
        .back-button:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-3px);
        }
        /* Profile Card */
        .profile-card {
            background: var(--bg-card);
            border-radius: var(--rounded-lg);
            box-shadow: var(--shadow-md);
            margin-top: -4rem;
            position: relative;
            z-index: 10;
            padding: 2.5rem;
            margin-bottom: 3rem;
            border: 1px solid var(--border-color);
        }
        .profile-header-content {
            display: flex;
            align-items: center;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
            gap: 2rem;
        }
        .profile-avatar-container {
            position: relative;
            width: 150px;
            height: 150px;
        }
        .profile-avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--white);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            background: var(--gray-100);
        }
        .avatar-edit {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: var(--primary);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            border: 2px solid white;
            transition: var(--transition);
        }
        .avatar-edit:hover {
            background: var(--primary-dark);
            transform: scale(1.1);
        }
        .profile-info {
            flex: 1;
            min-width: 250px;
        }
        .profile-name {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        .profile-email {
            color: var(--gray-500);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .profile-bio {
            color: var(--gray-500);
            margin: 1rem 0;
            max-width: 600px;
            line-height: 1.7;
        }
        .profile-stats {
            display: flex;
            gap: 2rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }
        .stat-item {
            text-align: center;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            display: block;
            line-height: 1.2;
        }
        .stat-label {
            font-size: 0.85rem;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }
        /* Profile Sections */
        .profile-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }
        
        @media (max-width: 768px) {
            .profile-sections {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 900px) {
            .profile-sections {
                grid-template-columns: 1fr;
            }
        }
        .section-card {
            background: var(--bg-card);
            border-radius: var(--rounded);
            padding: 1.75rem;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            border: 1px solid var(--border-color);
        }
        .section-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow);
            border-color: var(--primary-light);
        }
        .section-title {
            font-size: 1.25rem;
            color: var(--dark);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .section-title i {
            color: var(--primary);
            margin-right: 0.5rem;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            color: var(--gray-500);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .info-label i {
            color: var(--primary);
            width: 20px;
            text-align: center;
        }
        .info-value {
            color: var(--dark);
            font-weight: 500;
            padding: 0.5rem 0 0.5rem 1.75rem;
            border-bottom: 1px dashed var(--gray-200);
        }
        .empty-value {
            color: var(--gray-400);
            font-style: italic;
        }
        /* Tags */
        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .tag {
            background: var(--gray-100);
            color: var(--gray-600);
            padding: 0.35rem 0.85rem;
            border-radius: 50px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
        }
        .tag:hover {
            background: var(--gray-200);
        }
        .tag i {
            color: var(--primary);
            font-size: 0.9em;
        }
        /* Edit Button */
        .edit-btn {
            color: var(--primary);
            background: rgba(79, 70, 229, 0.1);
            border: none;
            border-radius: 6px;
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
        }
        .edit-btn:hover {
            background: rgba(79, 70, 229, 0.2);
        }
        .edit-btn i {
            font-size: 0.9em;
        }
        /* Responsive */
        @media (max-width: 768px) {
            .profile-header-content {
                flex-direction: column;
                text-align: center;
            }
            .profile-stats {
                justify-content: center;
            }
            .profile-email {
                justify-content: center;
            }
        }
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 1000;
            overflow-y: auto;
            padding: 2rem 1rem;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        
        .modal.show {
            opacity: 1;
            visibility: visible;
        }
        .modal-content {
            background: var(--bg-card);
            border-radius: var(--rounded-lg);
            max-width: 600px;
            margin: 2rem auto;
            padding: 2.5rem;
            position: relative;
            box-shadow: var(--shadow-lg);
            transform: translateY(20px);
            transition: transform 0.3s ease, opacity 0.3s ease;
            opacity: 0;
            border: 1px solid var(--border-color);
        }
        
        .modal.show .modal-content {
            transform: translateY(0);
            opacity: 1;
        }
        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray-500);
            background: none;
            border: none;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: var(--transition);
        }
        .close-modal:hover {
            background: var(--gray-100);
            color: var(--dark);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-primary);
            font-size: 0.9375rem;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--rounded);
            font-size: 1rem;
            transition: var(--transition);
            background-color: var(--bg-light);
            color: var(--text-primary);
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
            background-color: var(--bg-card);
        }
        .form-control::placeholder {
            color: var(--text-secondary);
            opacity: 0.7;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: var(--rounded);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: 1px solid transparent;
            font-size: 0.9375rem;
            line-height: 1.5;
            text-align: center;
            text-decoration: none;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            position: relative;
            overflow: hidden;
        }
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.1);
            opacity: 0;
            transition: var(--transition);
        }
        .btn:hover::before {
            opacity: 1;
        }
        .btn:active {
            transform: translateY(1px);
        }
        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.2);
        }
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
        .btn-outline {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }
        .btn-outline:hover {
            background: var(--bg-light);
            transform: translateY(-2px);
            border-color: var(--gray-400);
        }
        .text-center {
            text-align: center;
        }
        .mt-3 {
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="profile-header">
        <div class="header-content">
            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
            <div class="profile-header-content">
                <div class="profile-avatar-container">
                    <img src="profile-picture.jpg" alt="Profile Picture" class="profile-avatar">
                    <button class="avatar-edit">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
                <div class="profile-info">
                    <h1 class="profile-name"><?= htmlspecialchars($user['full_name'] ?? $user['email']) ?></h1>
                    <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
                    <p class="profile-bio"><?= htmlspecialchars($user['bio'] ?? 'No bio available.') ?></p>
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-value">
                                <?php
                                // Use correct table name for diary entries
                                $entry_count = 0;
                                $entry_result = $conn->query("SELECT COUNT(*) as count FROM diary_entries WHERE user_id = $user_id");
                                if ($entry_result) {
                                    $entry_count = $entry_result->fetch_assoc()['count'];
                                }
                                echo $entry_count;
                                ?>
                            </div>
                            <div class="stat-label">Total Entries</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">
                                <?php
                                $join_date = isset($user['created_at']) ? new DateTime($user['created_at']) : new DateTime();
                                $now = new DateTime();
                                $days = $now->diff($join_date)->days;
                                echo $days;
                                ?>
                            </div>
                            <div class="stat-label">Days as Member</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">
                                <?php
                                // If you have a user_stats table, otherwise show 0
                                $streak = 0;
                                $streak_result = $conn->query("SELECT streak FROM user_stats WHERE user_id = $user_id");
                                if ($streak_result && $row = $streak_result->fetch_assoc()) {
                                    $streak = $row['streak'];
                                }
                                echo $streak;
                                ?>
                            </div>
                            <div class="stat-label">Day Streak</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="profile-card">
            <div class="profile-sections">
                <!-- Personal Information -->
                <div class="section-card">
                    <div class="section-title">
                        <span><i class="fas fa-user-circle"></i> Personal Information</span>
                        <button class="edit-btn" onclick="openEditModal('personal')">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-user"></i> Full Name</div>
                            <div class="info-value"><?= !empty($user['full_name']) ? htmlspecialchars($user['full_name']) : '<span class="empty-value">Not set</span>' ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-birthday-cake"></i> Date of Birth</div>
                        <div class="info-value"><?= !empty($user['birthdate']) ? date('F j, Y', strtotime($user['birthdate'])) : '<span class="empty-value">Not set</span>' ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-venus-mars"></i> Gender</div>
                        <div class="info-value"><?= !empty($user['gender']) ? htmlspecialchars(ucfirst($user['gender'])) : '<span class="empty-value">Not specified</span>' ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-phone"></i> Phone</div>
                        <div class="info-value"><?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : '<span class="empty-value">Not provided</span>' ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-biohazard"></i> Bio</div>
                        <div class="info-value"><?= !empty($user['bio']) ? nl2br(htmlspecialchars($user['bio'])) : '<span class="empty-value">Tell us about yourself</span>' ?></div>
                    </div>
                </div>
            </div>

            <!-- Location Information -->
            <div class="section-card">
                <div class="section-title">
                    <span><i class="fas fa-map-marker-alt"></i> Location</span>
                    <button class="edit-btn" onclick="openEditModal('location')">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-home"></i> Address</div>
                        <div class="info-value"><?= !empty($user['address']) ? htmlspecialchars($user['address']) : '<span class="empty-value">Not provided</span>' ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-city"></i> City</div>
                        <div class="info-value"><?= !empty($user['city']) ? htmlspecialchars($user['city']) : '<span class="empty-value">Not provided</span>' ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-flag"></i> State/Province</div>
                        <div class="info-value"><?= !empty($user['state']) ? htmlspecialchars($user['state']) : '<span class="empty-value">Not provided</span>' ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-globe-americas"></i> Country</div>
                        <div class="info-value"><?= !empty($user['country']) ? htmlspecialchars($user['country']) : '<span class="empty-value">Not provided</span>' ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-mail-bulk"></i> Postal Code</div>
                        <div class="info-value"><?= !empty($user['postal_code']) ? htmlspecialchars($user['postal_code']) : '<span class="empty-value">Not provided</span>' ?></div>
                    </div>
                </div>
            </div>

            <!-- Preferences -->
            <div class="section-card">
                <div class="section-title">
                    <span><i class="fas fa-cog"></i> Preferences</span>
                    <button class="edit-btn" onclick="openEditModal('preferences')">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-palette"></i> Theme</div>
                        <div class="info-value"><?= !empty($user['theme']) ? htmlspecialchars(ucfirst($user['theme'])) : 'Default' ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-bell"></i> Email Notifications</div>
                        <div class="info-value"><?= isset($user['email_notifications']) && $user['email_notifications'] ? 'Enabled' : 'Disabled' ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-language"></i> Language</div>
                        <div class="info-value"><?= !empty($user['language']) ? htmlspecialchars($user['language']) : 'English' ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-eye"></i> Profile Visibility</div>
                        <div class="info-value"><?= isset($user['profile_public']) && $user['profile_public'] ? 'Public' : 'Private' ?></div>
                    </div>
                </div>
            </div>

            <!-- Interests -->
            <div class="section-card">
                <div class="section-title">
                    <span><i class="fas fa-heart"></i> Interests</span>
                    <button class="edit-btn" onclick="openEditModal('interests')">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-music"></i> Favorite Music</div>
                        <div class="info-value">
                            <?php if (!empty($user['favorite_music'])): ?>
                                <div class="tags-container">
                                    <?php 
                                    $music_genres = explode(',', $user['favorite_music']);
                                    foreach ($music_genres as $genre): 
                                    ?>
                                        <span class="tag"><?= trim(htmlspecialchars($genre)) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="empty-value">Not specified</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-film"></i> Favorite Movies</div>
                        <div class="info-value">
                            <?php if (!empty($user['favorite_movies'])): ?>
                                <div class="tags-container">
                                    <?php 
                                    $movies = explode(',', $user['favorite_movies']);
                                    foreach ($movies as $movie): 
                                    ?>
                                        <span class="tag"><?= trim(htmlspecialchars($movie)) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="empty-value">Not specified</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-book"></i> Favorite Books</div>
                        <div class="info-value">
                            <?php if (!empty($user['favorite_books'])): ?>
                                <div class="tags-container">
                                    <?php 
                                    $books = explode(',', $user['favorite_books']);
                                    foreach ($books as $book): 
                                    ?>
                                        <span class="tag"><?= trim(htmlspecialchars($book)) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="empty-value">Not specified</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-map-marked-alt"></i> Favorite Places</div>
                        <div class="info-value">
                            <?php if (!empty($user['favorite_places'])): ?>
                                <div class="tags-container">
                                    <?php 
                                    $places = explode(',', $user['favorite_places']);
                                    foreach ($places as $place): 
                                    ?>
                                        <span class="tag"><?= trim(htmlspecialchars($place)) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="empty-value">Not specified</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-hiking"></i> Hobbies</div>
                        <div class="info-value">
                            <?php if (!empty($user['hobbies'])): ?>
                                <div class="tags-container">
                                    <?php 
                                    $hobbies = explode(',', $user['hobbies']);
                                    foreach ($hobbies as $hobby): 
                                    ?>
                                        <span class="tag"><?= trim(htmlspecialchars($hobby)) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="empty-value">Not specified</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeModal()">&times;</button>
            <h2 id="modalTitle">Edit Profile</h2>
            <form id="profileForm" method="POST" action="update_profile.php" enctype="multipart/form-data">
                <div id="personalFields" style="display: none;">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="birthdate">Date of Birth</label>
                        <input type="date" id="birthdate" name="birthdate" class="form-control" value="<?= !empty($user['birthdate']) ? $user['birthdate'] : '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" class="form-control">
                            <option value="">Select Gender</option>
                            <option value="male" <?= (isset($user['gender']) && $user['gender'] === 'male') ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= (isset($user['gender']) && $user['gender'] === 'female') ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= (isset($user['gender']) && $user['gender'] === 'other') ? 'selected' : '' ?>>Other</option>
                            <option value="prefer_not_to_say" <?= (isset($user['gender']) && $user['gender'] === 'prefer_not_to_say') ? 'selected' : '' ?>>Prefer not to say</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio" class="form-control" rows="4"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    </div>
                </div>

                <div id="locationFields" style="display: none;">
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" class="form-control" value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="state">State/Province</label>
                        <input type="text" id="state" name="state" class="form-control" value="<?= htmlspecialchars($user['state'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="country">Country</label>
                        <select id="country" name="country" class="form-control">
                            <option value="">Select Country</option>
                            <option value="United States" <?= (isset($user['country']) && $user['country'] === 'United States') ? 'selected' : '' ?>>United States</option>
                            <option value="Canada" <?= (isset($user['country']) && $user['country'] === 'Canada') ? 'selected' : '' ?>>Canada</option>
                            <option value="United Kingdom" <?= (isset($user['country']) && $user['country'] === 'United Kingdom') ? 'selected' : '' ?>>United Kingdom</option>
                            <option value="Australia" <?= (isset($user['country']) && $user['country'] === 'Australia') ? 'selected' : '' ?>>Australia</option>
                            <option value="India" <?= (isset($user['country']) && $user['country'] === 'India') ? 'selected' : '' ?>>India</option>
                            <!-- Add more countries as needed -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="postal_code">Postal Code</label>
                        <input type="text" id="postal_code" name="postal_code" class="form-control" value="<?= htmlspecialchars($user['postal_code'] ?? '') ?>">
                    </div>
                </div>

                <div id="preferencesFields" style="display: none;">
                    <div class="form-group">
                        <label for="theme">Theme</label>
                        <select id="theme" name="theme" class="form-control">
                            <option value="light" <?= (isset($user['theme']) && $user['theme'] === 'light') ? 'selected' : '' ?>>Light</option>
                            <option value="dark" <?= (isset($user['theme']) && $user['theme'] === 'dark') ? 'selected' : '' ?>>Dark</option>
                            <option value="system" <?= (isset($user['theme']) && $user['theme'] === 'system') ? 'selected' : '' ?>>System Default</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="email_notifications">Email Notifications</label>
                        <select id="email_notifications" name="email_notifications" class="form-control">
                            <option value="1" <?= (isset($user['email_notifications']) && $user['email_notifications']) ? 'selected' : '' ?>>Enabled</option>
                            <option value="0" <?= (!isset($user['email_notifications']) || !$user['email_notifications']) ? 'selected' : '' ?>>Disabled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="language">Language</label>
                        <select id="language" name="language" class="form-control">
                            <option value="en" <?= (isset($user['language']) && $user['language'] === 'en') ? 'selected' : '' ?>>English</option>
                            <option value="es" <?= (isset($user['language']) && $user['language'] === 'es') ? 'selected' : '' ?>>Español</option>
                            <option value="fr" <?= (isset($user['language']) && $user['language'] === 'fr') ? 'selected' : '' ?>>Français</option>
                            <option value="de" <?= (isset($user['language']) && $user['language'] === 'de') ? 'selected' : '' ?>>Deutsch</option>
                            <option value="hi" <?= (isset($user['language']) && $user['language'] === 'hi') ? 'selected' : '' ?>>हिंदी</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="profile_public">Profile Visibility</label>
                        <select id="profile_public" name="profile_public" class="form-control">
                            <option value="1" <?= (isset($user['profile_public']) && $user['profile_public']) ? 'selected' : '' ?>>Public</option>
                            <option value="0" <?= (!isset($user['profile_public']) || !$user['profile_public']) ? 'selected' : '' ?>>Private</option>
                        </select>
                    </div>
                </div>

                <div id="interestsFields" style="display: none;">
                    <div class="form-group">
                        <label for="favorite_music">Favorite Music Genres <small>(comma separated)</small></label>
                        <input type="text" id="favorite_music" name="favorite_music" class="form-control" value="<?= htmlspecialchars($user['favorite_music'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="favorite_movies">Favorite Movies <small>(comma separated)</small></label>
                        <input type="text" id="favorite_movies" name="favorite_movies" class="form-control" value="<?= htmlspecialchars($user['favorite_movies'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="favorite_books">Favorite Books <small>(comma separated)</small></label>
                        <input type="text" id="favorite_books" name="favorite_books" class="form-control" value="<?= htmlspecialchars($user['favorite_books'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="favorite_places">Favorite Places <small>(comma separated)</small></label>
                        <input type="text" id="favorite_places" name="favorite_places" class="form-control" value="<?= htmlspecialchars($user['favorite_places'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="hobbies">Hobbies <small>(comma separated)</small></label>
                        <input type="text" id="hobbies" name="hobbies" class="form-control" value="<?= htmlspecialchars($user['hobbies'] ?? '') ?>">
                    </div>
                </div>

                <input type="hidden" name="section" id="section" value="">
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Profile Picture Upload Form -->
    <form id="avatarForm" action="upload_avatar.php" method="POST" enctype="multipart/form-data" style="display: none;">
        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
    </form>

    <script>
        // Handle profile picture change
        document.getElementById('changeAvatarBtn').addEventListener('click', function() {
            document.getElementById('profile_picture').click();
        });

        document.getElementById('profile_picture').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Check file type
            const validImageTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!validImageTypes.includes(file.type)) {
                showToast('Please select a valid image file (JPEG, PNG, WebP, or GIF)', 'error');
                return;
            }
            
            // Check file size (max 2MB)
            const maxSizeMB = 2;
            const maxSizeBytes = maxSizeMB * 1024 * 1024;
            if (file.size > maxSizeBytes) {
                showToast(`Image size should be less than ${maxSizeMB}MB`, 'error');
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.createElement('div');
                preview.className = 'image-preview';
                preview.innerHTML = `
                    <div class="preview-container">
                        <img src="${e.target.result}" alt="Preview" class="preview-image">
                        <div class="preview-actions">
                            <button type="button" class="preview-confirm">
                                <i class="fas fa-check"></i> Confirm
                            </button>
                            <button type="button" class="preview-cancel">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </div>
                `;
                
                // Add to DOM
                document.body.appendChild(preview);
                
                // Handle confirm
                preview.querySelector('.preview-confirm').addEventListener('click', () => {
                    uploadProfilePicture(file, preview);
                });
                
                // Handle cancel
                preview.querySelector('.preview-cancel').addEventListener('click', () => {
                    document.body.removeChild(preview);
                    document.getElementById('profile_picture').value = '';
                });
                
                // Close on outside click
                preview.addEventListener('click', (e) => {
                    if (e.target === preview) {
                        document.body.removeChild(preview);
                        document.getElementById('profile_picture').value = '';
                    }
                });
                
                // Close on Escape key
                document.addEventListener('keydown', function closeOnEscape(e) {
                    if (e.key === 'Escape') {
                        document.body.removeChild(preview);
                        document.getElementById('profile_picture').value = '';
                        document.removeEventListener('keydown', closeOnEscape);
                    }
                });
            };
            reader.readAsDataURL(file);
        });
        
        // Function to handle the actual upload
        function uploadProfilePicture(file, previewElement = null) {
            // Show loading state
            const uploadBtn = document.querySelector('.avatar-edit');
            const originalHTML = uploadBtn.innerHTML;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            uploadBtn.style.pointerEvents = 'none';
            
            // Show loading in preview if exists
            if (previewElement) {
                const previewContainer = previewElement.querySelector('.preview-container');
                if (previewContainer) {
                    previewContainer.innerHTML = `
                        <div class="uploading-overlay">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                    `;
                }
            }
            
            // Create FormData and append the file
            const formData = new FormData();
            formData.append('profile_picture', file);
            
            // Send the file to the server
            fetch('upload_avatar.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update the profile picture with cache busting
                    const timestamp = new Date().getTime();
                    const profileImg = document.querySelector('.profile-avatar');
                    profileImg.src = 'uploads/profile_pictures/' + data.filename + '?t=' + timestamp;
                    
                    // Update any other instances of the profile picture on the page
                    document.querySelectorAll('.user-avatar').forEach(img => {
                        img.src = 'uploads/profile_pictures/' + data.filename + '?t=' + timestamp;
                    });
                } else {
                    throw new Error(data.message || 'Failed to upload profile picture');
                }
            })
            .catch(error => {
                console.error('Upload error:', error);
                showToast(error.message || 'An error occurred while uploading the image', 'error');
            })
            .finally(() => {
                // Clean up
                if (previewElement && previewElement.parentNode) {
                    document.body.removeChild(previewElement);
                }
                
                // Reset the file input
                document.getElementById('profile_picture').value = '';
                
                // Restore the button
                uploadBtn.innerHTML = originalHTML;
                uploadBtn.style.pointerEvents = 'auto';
            });
        }

        // Modal functionality
        function openEditModal(section) {
            const modal = document.getElementById('editModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalFields = document.querySelectorAll('[id$="Fields"]');
            
            // Hide all sections first
            modalFields.forEach(field => field.style.display = 'none');
            
            // Show the selected section
            document.getElementById(section + 'Fields').style.display = 'block';
            
            // Update modal title
            const sectionTitles = {
                'personal': 'Personal Information',
                'location': 'Location',
                'preferences': 'Preferences',
                'interests': 'Interests'
            };
            modalTitle.textContent = 'Edit ' + (sectionTitles[section] || section);
            
            // Show modal with animation
            modal.style.display = 'block';
            // Force reflow
            void modal.offsetWidth;
            modal.classList.add('show');
            
            // Lock body scroll
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal() {
            const modal = document.getElementById('editModal');
            modal.classList.remove('show');
            // Allow body scroll
            document.body.style.overflow = '';
            
            // Hide modal after animation
            setTimeout(() => {
                if (!modal.classList.contains('show')) {
                    modal.style.display = 'none';
                }
            }, 300);
        }
        
        // Close modal when clicking outside or pressing Escape
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeModal();
            }
        });
        
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        // Toast notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            // Set icon based on type
            let icon = 'check-circle';
            if (type === 'error') icon = 'times-circle';
            else if (type === 'warning') icon = 'exclamation-triangle';
            else if (type === 'info') icon = 'info-circle';
            
            toast.innerHTML = `
                <i class="fas fa-${icon}"></i>
                <span>${message}</span>
            `;
            
            document.body.appendChild(toast);
            
            // Trigger reflow
            void toast.offsetWidth;
            
            // Show toast with animation
            setTimeout(() => {
                toast.classList.add('show');
            }, 10);
            
            // Remove toast after delay
            setTimeout(() => {
                toast.classList.remove('show');
                
                // Remove from DOM after animation
                setTimeout(() => {
                    if (toast.parentNode) {
                        document.body.removeChild(toast);
                    }
                }, 300);
            }, 4000);
            
            // Close on click
            toast.addEventListener('click', () => {
                toast.classList.remove('show');
                setTimeout(() => {
                    if (toast.parentNode) {
                        document.body.removeChild(toast);
                    }
                }, 300);
            });
        }

        // Handle form submission with AJAX
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Profile updated successfully!');
                    // Reload the page to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showToast('Error: ' + (data.message || 'Failed to update profile'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while updating your profile', 'error');
            });
        });
    </script>
</body>
</html>