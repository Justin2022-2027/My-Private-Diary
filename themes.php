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
$messageType = '';

// Predefined themes
$themes = [
    'default' => [
        'name' => 'Default',
        'colors' => ['#ff9fb0', '#1e293b'],
        'background' => '#ffffff',
        'text' => '#1e293b'
    ],
    'ocean' => [
        'name' => 'Ocean',
        'colors' => ['#0ea5e9', '#082f49'],
        'background' => '#f0f9ff',
        'text' => '#082f49'
    ],
    'forest' => [
        'name' => 'Forest',
        'colors' => ['#22c55e', '#14532d'],
        'background' => '#f0fdf4',
        'text' => '#14532d'
    ],
    'sunset' => [
        'name' => 'Sunset',
        'colors' => ['#f97316', '#431407'],
        'background' => '#fff7ed',
        'text' => '#431407'
    ],
    'lavender' => [
        'name' => 'Lavender',
        'colors' => ['#a855f7', '#3b0764'],
        'background' => '#faf5ff',
        'text' => '#3b0764'
    ]
];

// Handle theme selection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $theme_name = $_POST['theme'];
    $font_choice = $_POST['font_choice'];
    $background_color = $_POST['background_color'];
    $text_color = $_POST['text_color'];
    
    // Check if user already has a theme
    $check_stmt = $conn->prepare("SELECT id FROM themes WHERE user_id = ?");
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $check_stmt->close();

    if ($result->num_rows > 0) {
        // Update existing theme
        $stmt = $conn->prepare("UPDATE themes SET theme_name = ?, font_choice = ?, background_color = ?, text_color = ? WHERE user_id = ?");
        $stmt->bind_param("ssssi", $theme_name, $font_choice, $background_color, $text_color, $user_id);
    } else {
        // Insert new theme
        $stmt = $conn->prepare("INSERT INTO themes (user_id, theme_name, font_choice, background_color, text_color) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $theme_name, $font_choice, $background_color, $text_color);
    }
    
    if ($stmt->execute()) {
        $message = "Theme updated successfully!";
        $messageType = "success";
    } else {
        $message = "Failed to update theme: " . $stmt->error;
        $messageType = "error";
    }
    $stmt->close();
}

// Get current theme
$stmt = $conn->prepare("SELECT theme_name, font_choice, background_color, text_color FROM themes WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$current_theme = $result->fetch_assoc() ?? [
    'theme_name' => 'default',
    'font_choice' => 'Inter',
    'background_color' => '#ffffff',
    'text_color' => '#1e293b'
];
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalize Your Diary</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@400;600&family=Roboto:wght@400;500&family=Lora:wght@400;500&family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
    <?php if (isset($_SESSION['user_id'])) include 'includes/user_theme.php'; ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: <?php echo $current_theme['font_choice']; ?>, sans-serif;
            background-color: <?php echo $current_theme['background_color']; ?>;
            color: <?php echo $current_theme['text_color']; ?>;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
        }

        .title {
            font-size: 2.5rem;
            color: <?php echo $themes[$current_theme['theme_name']]['colors'][1]; ?>;
            margin-bottom: 2rem;
            text-align: center;
        }

        .themes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .theme-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .theme-card:hover {
            transform: translateY(-5px);
        }

        .theme-card.active {
            border: 2px solid <?php echo $themes[$current_theme['theme_name']]['colors'][0]; ?>;
        }

        .theme-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .color-dots {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .color-dot {
            width: 24px;
            height: 24px;
            border-radius: 50%;
        }

        .customization-form {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: <?php echo $current_theme['text_color']; ?>;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: <?php echo $themes[$current_theme['theme_name']]['colors'][0]; ?>;
            box-shadow: 0 0 0 3px rgba(<?php echo $themes[$current_theme['theme_name']]['colors'][0]; ?>, 0.2);
        }

        .preview-section {
            background: <?php echo $current_theme['background_color']; ?>;
            padding: 2rem;
            border-radius: 1rem;
            margin: 2rem 0;
        }

        .preview-content {
            color: <?php echo $current_theme['text_color']; ?>;
            font-family: <?php echo $current_theme['font_choice']; ?>, sans-serif;
        }

        .save-button {
            background: <?php echo $themes[$current_theme['theme_name']]['colors'][0]; ?>;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        .save-button:hover {
            background: <?php echo $themes[$current_theme['theme_name']]['colors'][1]; ?>;
        }

        .message {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .message-success {
            background-color: #dcfce7;
            color: #166534;
            border-left: 4px solid #22c55e;
        }

        .message-error {
            background-color: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .back-button {
            position: absolute;
            top: 2rem;
            left: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: white;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            color: <?php echo $current_theme['text_color']; ?>;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .back-button:hover {
            background: <?php echo $themes[$current_theme['theme_name']]['colors'][0]; ?>;
            color: white;
            transform: translateX(-4px);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Back 
        </a>
        
        <h1 class="title">Personalize Your Diary</h1>

        <?php if ($message): ?>
            <div class="message message-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="themes-grid">
            <?php foreach ($themes as $key => $theme): ?>
                <div class="theme-card <?php echo $current_theme['theme_name'] === $key ? 'active' : ''; ?>" 
                     data-theme="<?php echo $key; ?>"
                     data-bg="<?php echo $theme['background']; ?>"
                     data-text="<?php echo $theme['text']; ?>">
                    <h3><?php echo $theme['name']; ?></h3>
                    <div class="color-dots">
                        <?php foreach ($theme['colors'] as $color): ?>
                            <div class="color-dot" style="background-color: <?php echo $color; ?>"></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <form method="POST" class="customization-form">
            <input type="hidden" name="theme" id="selected_theme" value="<?php echo $current_theme['theme_name']; ?>">
            
            <div class="form-group">
                <label for="font_choice">Font Family</label>
                <select name="font_choice" id="font_choice">
                    <option value="Inter" <?php echo $current_theme['font_choice'] === 'Inter' ? 'selected' : ''; ?>>Inter</option>
                    <option value="Playfair Display" <?php echo $current_theme['font_choice'] === 'Playfair Display' ? 'selected' : ''; ?>>Playfair Display</option>
                    <option value="Roboto" <?php echo $current_theme['font_choice'] === 'Roboto' ? 'selected' : ''; ?>>Roboto</option>
                    <option value="Lora" <?php echo $current_theme['font_choice'] === 'Lora' ? 'selected' : ''; ?>>Lora</option>
                    <option value="Montserrat" <?php echo $current_theme['font_choice'] === 'Montserrat' ? 'selected' : ''; ?>>Montserrat</option>
                </select>
            </div>

            <div class="form-group">
                <label for="background_color">Background Color</label>
                <input type="color" name="background_color" id="background_color" 
                       value="<?php echo $current_theme['background_color']; ?>">
            </div>

            <div class="form-group">
                <label for="text_color">Text Color</label>
                <input type="color" name="text_color" id="text_color" 
                       value="<?php echo $current_theme['text_color']; ?>">
            </div>

            <div class="preview-section">
                <h3>Preview</h3>
                <div class="preview-content" id="preview">
                    <h4>Sample Diary Entry</h4>
                    <p>This is how your diary entries will look with the selected theme and customization options.</p>
                </div>
            </div>

            <button type="submit" class="save-button">Save Theme</button>
        </form>
    </div>

    <script>
        // Theme selection
        document.querySelectorAll('.theme-card').forEach(card => {
            card.addEventListener('click', () => {
                // Remove active class from all cards
                document.querySelectorAll('.theme-card').forEach(c => c.classList.remove('active'));
                // Add active class to selected card
                card.classList.add('active');
                
                // Update form values
                const theme = card.dataset.theme;
                const bg = card.dataset.bg;
                const text = card.dataset.text;
                
                document.getElementById('selected_theme').value = theme;
                document.getElementById('background_color').value = bg;
                document.getElementById('text_color').value = text;
                
                updatePreview();
            });
        });

        // Live preview
        function updatePreview() {
            const preview = document.getElementById('preview');
            const font = document.getElementById('font_choice').value;
            const bgColor = document.getElementById('background_color').value;
            const textColor = document.getElementById('text_color').value;

            preview.style.fontFamily = font;
            document.querySelector('.preview-section').style.backgroundColor = bgColor;
            preview.style.color = textColor;
        }

        // Add event listeners for live preview
        document.getElementById('font_choice').addEventListener('change', updatePreview);
        document.getElementById('background_color').addEventListener('input', updatePreview);
        document.getElementById('text_color').addEventListener('input', updatePreview);

        // Initialize preview
        updatePreview();

        // Add this to your existing JavaScript section
        document.querySelector('.back-button').addEventListener('click', function(e) {
            // Only if there are unsaved changes
            if (document.querySelector('form').dataset.changed === 'true') {
                const confirmed = confirm('Do you want to leave without saving your changes?');
                if (!confirmed) {
                    e.preventDefault();
                }
            }
        });

        // Add change detection to form
        document.querySelector('form').addEventListener('change', function() {
            this.dataset.changed = 'true';
        });

        // After successful save
        document.querySelector('form').addEventListener('submit', function() {
            this.dataset.changed = 'false';
        });
    </script>
</body>
</html>