<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

// Set user variables
$isGuest = false;
$userName = $_SESSION['full_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - My Private Diary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #818cf8;
            --primary-dark: #4338ca;
            --secondary: #8b5cf6;
            --success: #10b981;
            --danger: #ef4444;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-500: #64748b;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: var(--light);
            min-height: 100vh;
        }

        .header {
            background-color: var(--white);
            box-shadow: var(--shadow-sm);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 100;
        }

        .header-container {
            position: relative;
            text-align: center;
            width: 100%;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--dark);
            font-weight: 700;
            font-size: 1.25rem;
        }

        .logo i {
            color:  #ff9fb0;
            font-size: 1.5rem;
        }

        .user-welcome {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--dark);
        }

        .container {
            display: flex;
            margin-top: 4rem;
            min-height: calc(100vh - 4rem);
        }

        .sidebar {
            width: 250px;
            background: var(--white);
            padding: 2rem 1rem;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            border-right: 1px solid var(--gray-200);
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1rem;
            color: var(--dark);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: var(--transition);
            margin-bottom: 0.5rem;
        }

        .nav-link:hover {
            background-color: var(--light);
            color:  #ff9fb0;
            transform: translateX(5px);
        }

        .nav-link.active {
            background-color:  #ff9fb0;
            color: var(--white);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        .main-content {
            flex: 1;
            padding: 2rem 3rem;
            margin-left: 250px;
            background-color: var(--gray-100);
            min-height: 100vh;
        }

        .welcome-section {
            background: var(--white);
            padding: 2rem;
            border-radius: 1rem;
            color: var(--dark);
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .mood-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .mood-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            text-align: center;
            border-top: 4px solid var(--primary);
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .action-card h3 {
            color: var(--dark);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-card p {
            color: var(--gray);
            font-size: 0.875rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 99;
                transition: var(--transition);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .header-content {
                padding: 0 1rem;
            }
        }

        /* Guest Access Styles */
        .guest-access {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 2rem;
            border-radius: 1rem;
            color: var(--white);
            text-align: center;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
        }

        .guest-access h2 {
            font-size: 1.75rem;
            margin-bottom: 1rem;
        }

        .guest-access p {
            margin-bottom: 1.5rem;
            opacity: 0.9;
        }

        .guest-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .guest-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            text-decoration: none;
            transition: var(--transition);
            border: 2px solid transparent;
        }

        .guest-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .guest-btn.primary {
            background: var(--white);
            color: var(--primary);
        }

        .guest-btn.primary:hover {
            background: var(--light);
        }

        /* Disable features for guests */
        .guest-only-hide {
            opacity: 0.5;
            pointer-events: none;
            position: relative;
        }

        .guest-only-hide::after {
            content: 'Sign up to access';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            opacity: 0;
            transition: var(--transition);
        }

        .guest-only-hide:hover::after {
            opacity: 1;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container" style="display: flex; justify-content: space-between; align-items: center; padding: 0 2rem;">
            <a href="index.php" class="logo">
                <i class="fas fa-book-open"></i>
                <span>My Private Diary</span>
            </a>
            <div class="user-dropdown" style="position: relative;">
                <button id="userDropdownBtn" style="background: none; border: none; font-size: 1rem; color: var(--dark); cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                    Welcome, <?php echo htmlspecialchars($userName); ?>
                    <i class="fas fa-caret-down"></i>
                </button>
                <div id="userDropdownMenu" style="display: none; position: absolute; right: 0; top: 120%; background: #fff; box-shadow: 0 4px 16px rgba(0,0,0,0.08); border-radius: 0.75rem; min-width: 180px; z-index: 1000;">
                    <a href="profile.php" style="display: block; padding: 1rem 1.5rem; color: var(--dark); text-decoration: none; border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">My Profile</a>
                    <a href="settings.php" style="display: block; padding: 1rem 1.5rem; color: var(--dark); text-decoration: none; border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">Settings</a>
                    <a href="logout.php" style="display: block; padding: 1rem 1.5rem; color: #ef4444; text-decoration: none; transition: background 0.2s;">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container" style="display: flex; margin-top: 4rem;">
        <aside class="sidebar" style="width: 270px; background: var(--white); padding: 2rem 1rem; border-right: 1px solid var(--gray-200); min-height: 100vh;">
            <nav style="display: flex; flex-direction: column; gap: 0.5rem;">
                <a href="write_entry.php" class="nav-link"><i class="fas fa-pen-fancy"></i> Write New Entry</a>
                <a href="view_entries.php" class="nav-link"><i class="fas fa-book"></i> View Past Entries</a>
                <a href="mood_tracker.php" class="nav-link"><i class="fas fa-smile"></i> Mood Tracker</a>
                <a href="calendar.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Journal Timeline</a>
                <a href="reminders.php" class="nav-link"><i class="fas fa-bell"></i> Reminders</a>
                <a href="premium.php" class="nav-link"><i class="fas fa-crown"></i> Premium Features</a>
                <a href="settings.php" class="nav-link"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content" style="flex: 1; padding: 2rem 3rem; background: #f8fafc; min-height: 100vh;">
            <div style="background: #ff9fb0; color: #fff; padding: 2rem; border-radius: 1.25rem; font-size: 1.5rem; font-weight: 600; margin-bottom: 2.5rem; box-shadow: 0 4px 12px rgba(255,159,176,0.08); display: flex; flex-direction: column; align-items: flex-start;">
                Welcome to Your Digital Safe Space ✨
                <span style="font-size: 1rem; font-weight: 400; margin-top: 0.5rem;">Express yourself freely and capture your thoughts in a secure, private environment.</span>
            </div>
            <div style="display: flex; gap: 2rem;">
                <div style="flex: 1; background: #fff; border-radius: 1.25rem; box-shadow: 0 4px 12px rgba(0,0,0,0.06); padding: 2rem; display: flex; flex-direction: column; align-items: flex-start;">
                    <div style="font-size: 2rem; color:  #ff9fb0; margin-bottom: 1rem;"><i class="fas fa-pen-fancy"></i></div>
                    <div style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">Start Writing</div>
                    <div style="color: #64748b; font-size: 1rem;">Begin your journey today by writing your first diary entry.</div>
                </div>
                <div style="flex: 1; background: #fff; border-radius: 1.25rem; box-shadow: 0 4px 12px rgba(0,0,0,0.06); padding: 2rem; display: flex; flex-direction: column; align-items: flex-start;">
                    <div style="font-size: 2rem; color:  #ff9fb0; margin-bottom: 1rem;"><i class="fas fa-chart-line"></i></div>
                    <div style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">Track Your Mood</div>
                    <div style="color: #64748b; font-size: 1rem;">Monitor your emotional well-being with our mood tracking feature.</div>
                </div>
                <div style="flex: 1; background: #fff; border-radius: 1.25rem; box-shadow: 0 4px 12px rgba(0,0,0,0.06); padding: 2rem; display: flex; flex-direction: column; align-items: flex-start;">
                    <div style="font-size: 2rem; color: #ff9fb0; margin-bottom: 1rem;"><i class="fas fa-lock"></i></div>
                    <div style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">Private & Secure</div>
                    <div style="color: #64748b; font-size: 1rem;">Your thoughts are safe with us. We prioritize your privacy.</div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Add active class to current nav link
        const currentLocation = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            if (link.getAttribute('href') === currentLocation) {
                link.classList.add('active');
            }
        });

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