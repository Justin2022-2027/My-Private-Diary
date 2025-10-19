<?php
if (!isset($_SESSION)) {
    session_start();
}

// Check if user is logged in or is a guest
$isGuest = !isset($_SESSION['user_id']);
?>

<nav class="sidebar">
    <a href="write_entry.php" class="nav-link">
        <i class="fas fa-pen-fancy"></i>
        Write New Entry
    </a>
    <a href="view_entries.php" class="nav-link <?php echo $isGuest ? 'guest-only-hide' : ''; ?>">
        <i class="fas fa-book-reader"></i>
        View Past Entries
    </a>
    <a href="mood_tracker.php" class="nav-link <?php echo $isGuest ? 'guest-only-hide' : ''; ?>">
        <i class="fas fa-smile"></i>
        Mood Tracker
    </a>
    <a href="calendar.php" class="nav-link <?php echo $isGuest ? 'guest-only-hide' : ''; ?>">
        <i class="fas fa-calendar-alt"></i>
        Journal Timeline
    </a>
    <a href="reminders.php" target="_blank" class="nav-link <?php echo $current_page === 'reminders' ? 'active' : ''; ?>">
        <i class="fas fa-bell"></i>
        Reminders
    </a>
    <a href="themes.php" class="nav-link <?php echo $isGuest ? 'guest-only-hide' : ''; ?>">
        <i class="fas fa-paint-brush"></i>
        Personalize Diary
    </a>
    <a href="premium.php" class="nav-link <?php echo $isGuest ? 'guest-only-hide' : ''; ?>">
        <i class="fas fa-crown"></i>
        Premium Features
    </a>
    <a href="settings.php" class="nav-link <?php echo $isGuest ? 'guest-only-hide' : ''; ?>">
        <i class="fas fa-cog"></i>
        Settings
    </a>
    <?php if ($isGuest): ?>
    <a href="signup.php" class="nav-link">
        <i class="fas fa-user-plus"></i>
        Sign Up
    </a>
    <a href="login.php" class="nav-link">
        <i class="fas fa-sign-in-alt"></i>
        Login
    </a>
    <?php else: ?>
    <a href="logout.php" class="nav-link">
        <i class="fas fa-sign-out-alt"></i>
        Logout
    </a>
    <?php endif; ?>
</nav>

<style>
.sidebar {
    width: 280px;
    background: var(--white);
    padding: 2rem 1.5rem;
    box-shadow: var(--shadow);
    position: fixed;
    height: calc(100vh - 4rem);
    overflow-y: auto;
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
    color: var(--primary);
    transform: translateX(5px);
}

.nav-link.active {
    background-color: var(--primary);
    color: var(--white);
}

.nav-link i {
    width: 20px;
    text-align: center;
}

/* Guest Access Styles */
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
}
</style>

<script>
// Add active class to current nav link
document.addEventListener('DOMContentLoaded', function() {
    const currentLocation = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentLocation.split('/').pop()) {
            link.classList.add('active');
        }
    });
});
</script>