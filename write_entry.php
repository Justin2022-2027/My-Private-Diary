<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $mood = $_POST['mood'];
    $tags = isset($_POST['tags']) ? trim($_POST['tags']) : '';

    if (empty($title) || empty($content)) {
        $message = "Title and content are required.";
        $message_type = "error";
    } else {
        $stmt = $conn->prepare("INSERT INTO diary_entries (user_id, title, content, mood, tags, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("issss", $user_id, $title, $content, $mood, $tags);

        if ($stmt->execute()) {
            $message = "Entry saved successfully!";
            $message_type = "success";
            // Clear form after successful submission
            $_POST = array();
        } else {
            $message = "Error saving entry. Please try again.";
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
    <title>Write Entry - My Private Diary</title>
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

        .write-container {
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
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
        }

        .write-header {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--light);
        }

        .write-header h2 {
            font-size: 2rem;
            color: var(--primary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .write-header h2 i {
            color: var(--primary);
        }

        .write-header p {
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

        .tags-input::after {
            content: '#';
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 1rem;
        }

        .tags-input .form-input {
            padding-left: 2rem;
        }

        .form-help {
            font-size: 0.875rem;
            color: var(--gray);
            margin-top: 0.5rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--light);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.75rem;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-outline {
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
        }

        .message {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .message.success {
            background: var(--primary-light);
            color: var(--primary-dark);
            border: 1px solid var(--primary);
        }

        .message.error {
            background: #fef2f2;
            color: var(--danger);
            border: 1px solid #fee2e2;
        }

        body {
            background-color: var(--light);
        }

        .container {
            background-color: var(--light);
        }

        @media (max-width: 768px) {
            .write-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .mood-selector {
                gap: 0.5rem;
            }

            .mood-label {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }

            .form-actions {
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
        <main class="main-content" style="margin-left: 0; width: 100%; max-width: 1200px; margin: 0 auto; padding: 2rem;">
            <div class="write-container">
                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="write-header">
                    <h2><i class="fas fa-pen"></i> Write New Entry</h2>
                    <p>Express your thoughts, feelings, and experiences in your private diary.</p>
                </div>

                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label" for="title">Title</label>
                        <input type="text" id="title" name="title" class="form-input" 
                               value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                               placeholder="Give your entry a title..." required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="content">Your Thoughts</label>
                        <textarea id="content" name="content" class="form-input" 
                                placeholder="Start writing your thoughts..." required><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">How are you feeling?</label>
                        <div class="mood-selector">
                            <?php
                            $moods = ['Happy', 'Sad', 'Excited', 'Anxious', 'Calm'];
                            foreach ($moods as $mood):
                            ?>
                            <input type="radio" name="mood" value="<?php echo $mood; ?>" 
                                   id="mood_<?php echo strtolower($mood); ?>" class="mood-option"
                                   <?php echo (isset($_POST['mood']) && $_POST['mood'] === $mood) ? 'checked' : ''; ?>>
                            <label for="mood_<?php echo strtolower($mood); ?>" class="mood-label">
                                <i class="fas fa-<?php echo strtolower($mood) === 'happy' ? 'smile' : 
                                    (strtolower($mood) === 'sad' ? 'frown' : 
                                    (strtolower($mood) === 'excited' ? 'grin-stars' : 
                                    (strtolower($mood) === 'anxious' ? 'meh' : 'smile-beam'))); ?>"></i>
                                <?php echo $mood; ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="tags">Tags</label>
                        <div class="tags-input">
                            <input type="text" id="tags" name="tags" class="form-input"
                                   value="<?php echo isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : ''; ?>"
                                   placeholder="memories, thoughts, family">
                        </div>
                        <div class="form-help">Separate tags with commas</div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Save Entry
                        </button>
                        <a href="view_entries.php" class="btn btn-outline">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>