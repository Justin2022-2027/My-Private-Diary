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

// Get the specific entry
if ($entry_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM diary_entries WHERE entry_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $entry_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $entry = $result->fetch_assoc();
    $stmt->close();

    if (!$entry) {
        $error_message = "Entry not found or you don't have permission to view it.";
    }
} else {
    $error_message = "Invalid entry ID.";
}

// Handle form submission for inline editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_entry']) && isset($entry)) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $mood = $_POST['mood'];
    $tags = isset($_POST['tags']) ? trim($_POST['tags']) : '';

    if (empty($title) || empty($content)) {
        $message = "Title and content are required.";
        $message_type = "error";
    } else {
        $updateStmt = $conn->prepare("UPDATE diary_entries SET title = ?, content = ?, mood = ?, tags = ?, updated_at = NOW() WHERE entry_id = ? AND user_id = ?");
        $updateStmt->bind_param("ssssii", $title, $content, $mood, $tags, $entry_id, $user_id);

        if ($updateStmt->execute()) {
            $message = "Entry updated successfully!";
            $message_type = "success";
            // Refresh entry data after update
            $selectStmt = $conn->prepare("SELECT * FROM diary_entries WHERE entry_id = ? AND user_id = ?");
            $selectStmt->bind_param("ii", $entry_id, $user_id);
            $selectStmt->execute();
            $result = $selectStmt->get_result();
            $entry = $result->fetch_assoc();
            $selectStmt->close();
        } else {
            $message = "Error updating entry. Please try again.";
            $message_type = "error";
        }
        $updateStmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Entry - My Private Diary</title>
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

        .main-content {
            padding: 2rem;
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

        .entry-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
        }

        .entry-header {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid var(--light);
        }

        .entry-title {
            color: var(--dark);
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 1rem 0;
            line-height: 1.2;
        }

        .entry-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--gray);
            font-size: 0.95rem;
        }

        .entry-date {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .entry-date i {
            color: var(--primary);
        }

        .mood-badge {
            background: var(--primary-light);
            color: var(--primary-dark);
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .mood-badge i {
            font-size: 0.8rem;
        }

        .entry-content {
            color: var(--dark);
            font-size: 1.1rem;
            line-height: 1.8;
            margin: 2rem 0;
            padding: 2rem;
            background: var(--light);
            border-radius: var(--radius);
            border-left: 4px solid var(--primary);
        }

        .entry-actions {
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

        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 1rem;
            border-radius: var(--radius);
            margin: 2rem auto;
            max-width: 600px;
            text-align: center;
            border: 1px solid #fecaca;
        }

        .error-message i {
            margin-right: 0.5rem;
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

        /* Edit Mode Styles */
        .edit-mode-toggle {
            background: var(--primary-light);
            color: var(--primary-dark);
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .edit-mode-toggle i {
            font-size: 0.8rem;
        }

        .edit-form {
            display: none;
            margin: 2rem 0;
        }

        .edit-form.active {
            display: block;
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
            min-height: 200px;
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

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--light);
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

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .entry-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .entry-title {
                font-size: 1.5rem;
            }

            .entry-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .entry-actions {
                flex-direction: column;
            }

            .btn {
                justify-content: center;
                width: 100%;
            }

            .form-actions {
                flex-direction: column;
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
            <div class="entry-container">
                <?php if (isset($error_message)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                    <div style="text-align: center; margin-top: 2rem;">
                        <a href="view_entries.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Entries
                        </a>
                    </div>
                <?php elseif (isset($entry)): ?>
                    <a href="view_entries.php" class="back-button">
                        <i class="fas fa-arrow-left"></i>
                        Back to All Entries
                    </a>

                    <?php if (!empty($message)): ?>
                        <div class="message <?php echo $message_type; ?>">
                            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <!-- View Mode (Default) -->
                    <div id="view-mode">
                        <div class="entry-header">
                            <h1 class="entry-title" id="display-title"><?= htmlspecialchars($entry['title']) ?></h1>
                            <div class="entry-meta">
                                <div class="entry-date">
                                    <i class="fas fa-calendar-alt"></i>
                                    <?= date('F j, Y \a\t g:i A', strtotime($entry['created_at'])) ?>
                                </div>
                                <?php if (!empty($entry['mood'])): ?>
                                    <div class="mood-badge">
                                        <i class="fas fa-smile"></i>
                                        <span id="display-mood"><?= htmlspecialchars($entry['mood']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="entry-content" id="display-content">
                            <?= nl2br(htmlspecialchars($entry['content'])) ?>
                        </div>

                        <?php if (!empty($entry['tags'])): ?>
                            <div style="margin-top: 2rem;">
                                <h3 style="color: var(--dark); font-size: 1.1rem; margin-bottom: 0.5rem;">
                                    <i class="fas fa-tags" style="color: var(--primary); margin-right: 0.5rem;"></i>
                                    Tags
                                </h3>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;" id="display-tags">
                                    <?php
                                    $tags = explode(',', $entry['tags']);
                                    foreach ($tags as $tag):
                                        $tag = trim($tag);
                                        if (!empty($tag)):
                                    ?>
                                        <span style="background: var(--primary-light); color: var(--primary-dark); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.8rem; font-weight: 500;">
                                            <?= htmlspecialchars($tag) ?>
                                        </span>
                                    <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="entry-actions">
                            <button onclick="toggleEditMode()" class="btn btn-outline" id="edit-btn">
                                <i class="fas fa-edit"></i>
                                Edit Entry
                            </button>
                            <a href="view_entries.php" class="btn btn-primary">
                                <i class="fas fa-list"></i>
                                All Entries
                            </a>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div id="edit-mode" class="edit-form">
                        <div class="edit-mode-toggle">
                            <i class="fas fa-edit"></i>
                            Editing Mode
                        </div>

                        <form method="POST" action="">
                            <input type="hidden" name="update_entry" value="1">

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-heading"></i>
                                    Title
                                </label>
                                <input type="text" name="title" class="form-input" id="edit-title"
                                       value="<?= htmlspecialchars($entry['title']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-align-left"></i>
                                    Content
                                </label>
                                <textarea name="content" class="form-input" id="edit-content"
                                          required><?= htmlspecialchars($entry['content']) ?></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-smile"></i>
                                    Mood (Optional)
                                </label>
                                <div class="mood-selector">
                                    <input type="radio" name="mood" value="" id="edit-mood-none" class="mood-option" <?= empty($entry['mood']) ? 'checked' : '' ?>>
                                    <label for="edit-mood-none" class="mood-label">No Mood</label>

                                    <input type="radio" name="mood" value="Happy" id="edit-mood-happy" class="mood-option" <?= ($entry['mood'] ?? '') === 'Happy' ? 'checked' : '' ?>>
                                    <label for="edit-mood-happy" class="mood-label">😊 Happy</label>

                                    <input type="radio" name="mood" value="Sad" id="edit-mood-sad" class="mood-option" <?= ($entry['mood'] ?? '') === 'Sad' ? 'checked' : '' ?>>
                                    <label for="edit-mood-sad" class="mood-label">😢 Sad</label>

                                    <input type="radio" name="mood" value="Excited" id="edit-mood-excited" class="mood-option" <?= ($entry['mood'] ?? '') === 'Excited' ? 'checked' : '' ?>>
                                    <label for="edit-mood-excited" class="mood-label">🤩 Excited</label>

                                    <input type="radio" name="mood" value="Anxious" id="edit-mood-anxious" class="mood-option" <?= ($entry['mood'] ?? '') === 'Anxious' ? 'checked' : '' ?>>
                                    <label for="edit-mood-anxious" class="mood-label">😰 Anxious</label>

                                    <input type="radio" name="mood" value="Calm" id="edit-mood-calm" class="mood-option" <?= ($entry['mood'] ?? '') === 'Calm' ? 'checked' : '' ?>>
                                    <label for="edit-mood-calm" class="mood-label">😌 Calm</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-tags"></i>
                                    Tags (Optional)
                                </label>
                                <input type="text" name="tags" class="form-input" id="edit-tags"
                                       value="<?= htmlspecialchars($entry['tags'] ?? '') ?>">
                                <small style="color: var(--gray); font-size: 0.85rem;">Separate multiple tags with commas</small>
                            </div>

                            <div class="form-actions">
                                <button type="button" onclick="toggleEditMode()" class="btn btn-outline">
                                    <i class="fas fa-times"></i>
                                    Cancel
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i>
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        function toggleEditMode() {
            const viewMode = document.getElementById('view-mode');
            const editMode = document.getElementById('edit-mode');
            const editBtn = document.getElementById('edit-btn');

            if (viewMode.style.display === 'none') {
                // Switching from edit to view mode
                viewMode.style.display = 'block';
                editMode.classList.remove('active');
                editBtn.innerHTML = '<i class="fas fa-edit"></i> Edit Entry';
            } else {
                // Switching from view to edit mode
                viewMode.style.display = 'none';
                editMode.classList.add('active');
                editBtn.innerHTML = '<i class="fas fa-eye"></i> View Entry';
            }
        }

        // Auto-switch to view mode after any form submission
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_entry'])): ?>
            document.addEventListener('DOMContentLoaded', function() {
                // Always switch to view mode after form submission
                const viewMode = document.getElementById('view-mode');
                const editMode = document.getElementById('edit-mode');
                const editBtn = document.getElementById('edit-btn');

                if (viewMode && editMode && editBtn) {
                    viewMode.style.display = 'block';
                    editMode.classList.remove('active');
                    editBtn.innerHTML = '<i class="fas fa-edit"></i> Edit Entry';

                    // Update the displayed content with fresh data if save was successful
                    <?php if (!empty($message) && $message_type === 'success' && isset($entry)): ?>
                        // Update title
                        const titleElement = document.getElementById('display-title');
                        if (titleElement) {
                            titleElement.textContent = '<?= htmlspecialchars($entry['title']) ?>';
                        }

                        // Update content
                        const contentElement = document.getElementById('display-content');
                        if (contentElement) {
                            contentElement.innerHTML = '<?= nl2br(htmlspecialchars($entry['content'])) ?>';
                        }

                        // Update mood
                        const moodElement = document.getElementById('display-mood');
                        if (moodElement) {
                            if ('<?= $entry['mood'] ?>' !== '') {
                                moodElement.textContent = '<?= htmlspecialchars($entry['mood']) ?>';
                                // Make sure mood badge is visible
                                moodElement.parentElement.style.display = 'flex';
                            } else {
                                moodElement.parentElement.style.display = 'none';
                            }
                        }

                        // Update tags
                        const tagsElement = document.getElementById('display-tags');
                        if (tagsElement) {
                            <?php if (!empty($entry['tags'])): ?>
                                const tags = '<?= str_replace(',', '","', htmlspecialchars($entry['tags'])) ?>'.split(',');
                                tagsElement.innerHTML = tags.map(tag =>
                                    `<span style="background: var(--primary-light); color: var(--primary-dark); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.8rem; font-weight: 500;">${tag.trim()}</span>`
                                ).join('');
                                tagsElement.parentElement.style.display = 'block';
                            <?php else: ?>
                                tagsElement.parentElement.style.display = 'none';
                            <?php endif; ?>
                        }
                    <?php endif; ?>
                }

                // Handle messages based on result
                <?php if (!empty($message)): ?>
                    <?php if ($message_type === 'success'): ?>
                        // Auto-hide success message after 3 seconds
                        setTimeout(() => {
                            const messageDiv = document.querySelector('.message.success');
                            if (messageDiv) {
                                messageDiv.style.display = 'none';
                            }
                        }, 3000);
                    <?php else: ?>
                        // Auto-hide error message after 5 seconds
                        setTimeout(() => {
                            const messageDiv = document.querySelector('.message.error');
                            if (messageDiv) {
                                messageDiv.style.display = 'none';
                            }
                        }, 5000);
                    <?php endif; ?>
                <?php endif; ?>
            });
        <?php endif; ?>
    </script>
</body>
</html>
