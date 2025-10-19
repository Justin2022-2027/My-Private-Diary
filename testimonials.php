<?php
session_start();
require 'db_connect.php';

$message = '';
$is_logged_in = isset($_SESSION['user_id']);

// Handle new testimonial submission (only for logged-in users)
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['testimonial'])) {
    $testimonial = trim($_POST['testimonial']);
    if ($testimonial !== '') {
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare('INSERT INTO testimonials (user_id, content, created_at) VALUES (?, ?, NOW())');
        $stmt->bind_param('is', $user_id, $testimonial);
        if ($stmt->execute()) {
            $message = 'Thank you for your feedback!';
        } else {
            $message = 'Error submitting your testimonial. Please try again.';
        }
        $stmt->close();
    }
}
// Fetch testimonials with user info
$sql = 'SELECT t.content, t.created_at, u.full_name, YEAR(u.signup_date) as joined_year FROM testimonials t JOIN signup u ON t.user_id = u.user_id ORDER BY t.created_at DESC';
$result = $conn->query($sql);
$testimonials = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testimonials - My Private Diary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; }
        .container { max-width: 800px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 1rem; box-shadow: 0 4px 16px rgba(0,0,0,0.07); }
        h1 { text-align: center; color:rgb(227, 159, 189); margin-bottom: 0.5rem; }
        .subtitle { text-align: center; color: #64748b; margin-bottom: 2rem; }
        .testimonial-list { margin-bottom: 2.5rem; }
        .testimonial-card { background: #f9fafb; border-radius: 1rem; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.03); }
        .testimonial-content { font-style: italic; margin-bottom: 1rem; }
        .testimonial-user { display: flex; align-items: center; gap: 1rem; }
        .avatar { width: 48px; height: 48px; border-radius: 50%; background: #ff9fb0; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.2rem; }
        .user-details { }
        .user-name { font-weight: 600; }
        .user-joined { color: #64748b; font-size: 0.95rem; }
        .testimonial-form { background: #f3f4f6; border-radius: 1rem; padding: 1.5rem; }
        textarea { width: 100%; min-height: 80px; border-radius: 0.5rem; border: 1px solid #e5e7eb; padding: 0.75rem; font-size: 1rem; margin-bottom: 1rem; resize: vertical; }
        button { background: #e573a6; color: #fff; border: none; border-radius: 0.5rem; padding: 0.75rem 1.5rem; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        button:hover { background: #d14b8f; }
        .message { text-align: center; color: #10b981; margin-bottom: 1rem; }
        .login-prompt { text-align: center; margin-top: 2rem; padding: 1.5rem; background: #f3f4f6; border-radius: 0.5rem; }
    </style>
</head>
<body>
    <header class="header" style="background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 1rem 2rem; position: fixed; top: 0; left: 0; right: 0; z-index: 1000; display: flex; justify-content: space-between; align-items: center;">
        <!-- Back Button - Top Left -->
        <a href="index.php" style="text-decoration: none; color: #64748b; font-weight: 500; display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.5rem; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transient'">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        
        <!-- Page Title - Centered (optional, can be removed if not needed) -->
        <h2 style="margin: 0; font-size: 1.5rem; color: #e573a6; font-weight: 700; position: absolute; left: 50%; transform: translateX(-50%);">
            Welcome to Testimonials
        </h2>
        
        <!-- Profile Dropdown - Top Right -->
        <div class="user-dropdown" style="position: relative;">
            <button id="userDropdownBtn" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-size: 1rem; color: #1e293b; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; transition: all 0.2s;" onmouseover="this.style.borderColor='#cbd5e1'" onmouseout="this.style.borderColor='#e2e8f0'">
                <i class="fas fa-user" style="color: #64748b;"></i>
                <?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Profile'; ?>
                <i class="fas fa-caret-down" style="font-size: 0.9em; color: #64748b;"></i>
            </button>
            <div id="userDropdownMenu" style="display: none; position: absolute; right: 0; top: 120%; background: #fff; box-shadow: 0 4px 16px rgba(0,0,0,0.08); border-radius: 0.75rem; min-width: 200px; z-index: 1000; overflow: hidden; border: 1px solid #e2e8f0;">
                <a href="view_profile.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.25rem; color: #1e293b; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                    <i class="fas fa-user" style="width: 20px; text-align: center;"></i> View Profile
                </a>
                <a href="settings.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.25rem; color: #1e293b; text-decoration: none; border-top: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                    <i class="fas fa-cog" style="width: 20px; text-align: center;"></i> Settings
                </a>
                <a href="logout.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.25rem; color: #ef4444; text-decoration: none; border-top: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#fff'">
                    <i class="fas fa-sign-out-alt" style="width: 20px; text-align: center;"></i> Logout
                </a>
            </div>
        </div>
    </header>
    
    <!-- Add padding to the top of the main content to account for fixed header -->
    <div style="padding-top: 80px;"></div>
    <div class="container">
        <h1>What Our Users Say</h1>
        <div class="subtitle">Share your experience or read what others say about My Private Diary.</div>
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <div class="testimonial-list">
            <?php foreach ($testimonials as $t): ?>
                <div class="testimonial-card">
                    <div class="testimonial-content">"<?php echo htmlspecialchars($t['content']); ?>"</div>
                    <div class="testimonial-user">
                        <div class="avatar"><?php echo isset($t['full_name']) && $t['full_name'] ? strtoupper(substr($t['full_name'], 0, 1)) : '?'; ?></div>
                        <div class="user-details">
                            <div class="user-name"><?php echo isset($t['full_name']) ? htmlspecialchars($t['full_name']) : 'Anonymous'; ?></div>
                            <div class="user-joined">Using since <?php echo isset($t['joined_year']) ? $t['joined_year'] : 'N/A'; ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($testimonials)): ?>
                <div style="text-align:center; color:#64748b;">No testimonials yet. Be the first to share your experience!</div>
            <?php endif; ?>
        </div>
        <?php if ($is_logged_in): ?>
            <form class="testimonial-form" method="post">
                <label for="testimonial"><strong>Add Your Testimonial</strong></label>
                <textarea name="testimonial" id="testimonial" maxlength="500" required placeholder="Share your experience..."></textarea>
                <button type="submit"><i class="fas fa-paper-plane"></i> Submit</button>
            </form>
        <?php else: ?>
            <div class="login-prompt">
                <h3>Want to share your experience?</h3>
                <p>Please <a href="login.php" style="color: #e573a6; font-weight: 600;">Login</a> to submit your testimonial.</p>
            </div>
        <?php endif; ?>
    </div>
    <script>
        // Dropdown logic for user menu
        const userDropdownBtn = document.getElementById('userDropdownBtn');
        const userDropdownMenu = document.getElementById('userDropdownMenu');
        if (userDropdownBtn && userDropdownMenu) {
            userDropdownBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdownMenu.style.display = userDropdownMenu.style.display === 'block' ? 'none' : 'block';
            });
            document.addEventListener('click', function(e) {
                if (!userDropdownMenu.contains(e.target) && e.target !== userDropdownBtn) {
                    userDropdownMenu.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>