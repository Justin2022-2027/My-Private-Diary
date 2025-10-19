<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

$user_id = $_SESSION['user_id'];
$entry_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$message_type = '';

// Get the existing entry data
if ($entry_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM diary_entries WHERE entry_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $entry_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $entry = $result->fetch_assoc();
    $stmt->close();

    if (!$entry) {
        $message = "Entry not found or you don't have permission to edit it.";
        $message_type = "error";
    }
} else {
    $message = "Invalid entry ID.";
    $message_type = "error";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($message)) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $mood = $_POST['mood'];
    $tags = isset($_POST['tags']) ? trim($_POST['tags']) : '';

    if (empty($title) || empty($content)) {
        $message = "Title and content are required.";
        $message_type = "error";
    } else {
        $stmt = $conn->prepare("UPDATE diary_entries SET title = ?, content = ?, mood = ?, tags = ?, updated_at = NOW() WHERE entry_id = ? AND user_id = ?");
        $stmt->bind_param("ssssii", $title, $content, $mood, $tags, $entry_id, $user_id);

        if ($stmt->execute()) {
            $message = "Entry updated successfully!";
            $message_type = "success";
            // Refresh entry data after update
            $stmt = $conn->prepare("SELECT * FROM diary_entries WHERE entry_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $entry_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $entry = $result->fetch_assoc();
            $stmt->close();
        } else {
            $message = "Error updating entry. Please try again.";
            $message_type = "error";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Entry - My Private Diary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .edit-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
            background: var(--white);
            box-shadow: var(--shadow);
        }

        .entry-form {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 2rem;
            margin: 0 auto 2rem;
            max-width: 1000px;
        }

        .entry-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
        }

        .edit-header {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--light);
        }

        .edit-header h2 {
            font-size: 2rem;
            color: var(--primary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .edit-header h2 i {
            color: var(--primary);
        }

        .edit-header p {
            color: var(--gray);
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1.5px solid var(--light);
            border-radius: var(--radius-sm);
            font-size: 1rem;
            color: var(--dark);
            transition: var(--transition);
            background: var(--white);
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        textarea.form-input {
            min-height: 300px;
            resize: vertical;
            line-height: 1.6;
        }

        .mood-selector {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .mood-option {
            display: none;
        }

        .mood-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
            background: var(--light);
            color: var(--dark);
            font-weight: 500;
            border: 1px solid rgba(0, 0, 0, 0.08);
        }

        .mood-option:checked + .mood-label {
            background: var(--primary);
            color: var(--white);
        }

        .tags-input {
            position: relative;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--light);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-sm);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary-dark);
            border: 2px solid var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn-secondary {
            background: var(--gray);
            color: var(--white);
        }

        .btn-secondary:hover {
            background: var(--dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .message {
            padding: 1rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .message.success {
            background: #ecfdf5;
            color: #10b981;
            border: 1px solid #d1fae5;
        }

        .message.error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray);
            text-decoration: none;
            margin-bottom: 1rem;
            transition: var(--transition);
        }

        .back-button:hover {
            color: var(--primary-dark);
        }

        .back-button i {
            font-size: 0.8rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .edit-container {
                margin: 1rem;
                padding: 1rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                justify-content: center;
                width: 100%;
            }

            .mood-selector {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <main class="main-content">
            <div class="edit-container">
                <?php if (!empty($message)): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($entry) && !isset($message) || (isset($message) && $message_type === 'error')): ?>
                    <a href="view_entry.php?id=<?= $entry['entry_id'] ?>" class="back-button">
                        <i class="fas fa-arrow-left"></i>
                        Back to Entry
                    </a>

                    <div class="entry-form">
                        <div class="edit-header">
                            <h2><i class="fas fa-edit"></i> Edit Entry</h2>
                            <p>Make changes to your diary entry below</p>
                        </div>

                        <form method="POST" action="">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-heading"></i>
                                    Title
                                </label>
                                <input type="text" name="title" class="form-input"
                                       value="<?php echo htmlspecialchars($entry['title'] ?? ''); ?>"
                                       placeholder="Enter your entry title..." required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-align-left"></i>
                                    Content
                                </label>
                                <textarea name="content" class="form-input"
                                          placeholder="Write your thoughts here..."
                                          required><?php echo htmlspecialchars($entry['content'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-smile"></i>
                                    Mood (Optional)
                                </label>
                                <div class="mood-selector">
                                    <input type="radio" name="mood" value="" id="mood-none" class="mood-option" <?php echo empty($entry['mood']) ? 'checked' : ''; ?>>
                                    <label for="mood-none" class="mood-label">No Mood</label>

                                    <input type="radio" name="mood" value="Happy" id="mood-happy" class="mood-option" <?php echo ($entry['mood'] ?? '') === 'Happy' ? 'checked' : ''; ?>>
                                    <label for="mood-happy" class="mood-label">😊 Happy</label>

                                    <input type="radio" name="mood" value="Sad" id="mood-sad" class="mood-option" <?php echo ($entry['mood'] ?? '') === 'Sad' ? 'checked' : ''; ?>>
                                    <label for="mood-sad" class="mood-label">😢 Sad</label>

                                    <input type="radio" name="mood" value="Excited" id="mood-excited" class="mood-option" <?php echo ($entry['mood'] ?? '') === 'Excited' ? 'checked' : ''; ?>>
                                    <label for="mood-excited" class="mood-label">🤩 Excited</label>

                                    <input type="radio" name="mood" value="Anxious" id="mood-anxious" class="mood-option" <?php echo ($entry['mood'] ?? '') === 'Anxious' ? 'checked' : ''; ?>>
                                    <label for="mood-anxious" class="mood-label">😰 Anxious</label>

                                    <input type="radio" name="mood" value="Calm" id="mood-calm" class="mood-option" <?php echo ($entry['mood'] ?? '') === 'Calm' ? 'checked' : ''; ?>>
                                    <label for="mood-calm" class="mood-label">😌 Calm</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-tags"></i>
                                    Tags (Optional)
                                </label>
                                <input type="text" name="tags" class="form-input"
                                       value="<?php echo htmlspecialchars($entry['tags'] ?? ''); ?>"
                                       placeholder="Add tags separated by commas...">
                                <small style="color: var(--gray); font-size: 0.85rem;">Separate multiple tags with commas</small>
                            </div>

                            <div class="form-actions">
                                <a href="view_entry.php?id=<?= $entry['entry_id'] ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i>
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Update Entry
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
