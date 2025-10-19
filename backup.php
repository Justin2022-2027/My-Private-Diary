<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config/db_connect.php';

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Check if user has premium access
$stmt = $conn->prepare("SELECT subscription_plan, subscription_status FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user['subscription_plan'] === 'basic' || $user['subscription_status'] !== 'active') {
    header("Location: premium.php");
    exit();
}

// Handle backup creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    try {
        // Start transaction
        $conn->begin_transaction();

        // Get all user's diary entries
        $stmt = $conn->prepare("SELECT * FROM diary_entries WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $entries = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Create backup directory if it doesn't exist
        $backup_dir = "backups/{$user_id}";
        if (!file_exists($backup_dir)) {
            mkdir($backup_dir, 0777, true);
        }

        // Create backup file
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "{$backup_dir}/backup_{$timestamp}.json";
        $backup_data = [
            'user_id' => $user_id,
            'created_at' => date('Y-m-d H:i:s'),
            'entries' => $entries
        ];

        $json_data = json_encode($backup_data, JSON_PRETTY_PRINT);
        file_put_contents($filename, $json_data);
        $filesize = filesize($filename);

        // Record backup in database
        $stmt = $conn->prepare("INSERT INTO backup_history (user_id, backup_file, backup_size) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $user_id, $filename, $filesize);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $conn->commit();

        $message = "Backup created successfully!";
        $message_type = "success";

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $message = "Failed to create backup: " . $e->getMessage();
        $message_type = "error";
    }
}

// Get backup history
$stmt = $conn->prepare("SELECT * FROM backup_history WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$backups = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cloud Backup - My Private Diary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reuse root variables from dashboard.php */
        :root {
            --primary: hsl(351, 63%, 76%);
            --primary-dark: hwb(350 76% 0%);
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

        .backup-header {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .backup-list {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
        }

        .backup-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--light);
        }

        .backup-item:last-child {
            border-bottom: none;
        }

        .backup-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .backup-date {
            font-weight: 500;
            color: var(--dark);
        }

        .backup-size {
            font-size: 0.875rem;
            color: var(--gray);
        }

        .backup-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            text-decoration: none;
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-outline {
            border: 1px solid var(--primary);
            color: var(--primary);
            background: transparent;
        }

        .btn-outline:hover {
            background: var(--primary);
            color: var(--white);
        }

        .message {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .message.success {
            background-color: #f0fdf4;
            color: var(--success);
            border: 1px solid #dcfce7;
        }

        .message.error {
            background-color: #fef2f2;
            color: var(--danger);
            border: 1px solid #fee2e2;
        }

        .no-backups {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <div class="backup-header">
                <h2>Cloud Backup</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-cloud-upload-alt"></i>
                        Create New Backup
                    </button>
                </form>
            </div>

            <div class="backup-list">
                <?php if (empty($backups)): ?>
                <div class="no-backups">
                    <i class="fas fa-cloud fa-3x"></i>
                    <p>No backups found. Create your first backup to protect your diary entries.</p>
                </div>
                <?php else: ?>
                <?php foreach ($backups as $backup): ?>
                <div class="backup-item">
                    <div class="backup-info">
                        <div class="backup-date">
                            <?php echo date('F j, Y g:i A', strtotime($backup['created_at'])); ?>
                        </div>
                        <div class="backup-size">
                            <?php echo number_format($backup['backup_size'] / 1024, 2); ?> KB
                        </div>
                    </div>
                    <div class="backup-actions">
                        <a href="<?php echo $backup['backup_file']; ?>" download class="btn btn-outline">
                            <i class="fas fa-download"></i>
                            Download
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html> 