<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default user name if not set
$userName = $_SESSION['full_name'] ?? 'User';
?>
<header class="header">
    <div class="header-container">
        <!-- Left Section: Back Button -->
        <div class="header-left">
            <a href="javascript:history.back()" class="back-button">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        
        <!-- Center Section: Title -->
        <div class="header-center">
            <i class="fas fa-book-open" style="color: #ff9fb0; margin-right: 0.5rem;"></i>
            <h1 class="header-title">My Private Diary</h1>
        </div>
        
        <!-- Right Section: Profile -->
        <div class="header-right">
            <div class="profile-dropdown">
                <button class="profile-button" onclick="toggleDropdown()">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu" id="profileDropdown">
                    <a href="view_profile.php"><i class="fas fa-user"></i> My Profile</a>
                    <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                    <hr>
                    <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>
</header>

<style>
.header {
    background: #ffffff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    padding: 1rem 0;
}

/* Removed navigation menu styles */

/* Header layout */
.header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 60px;
    width: 100%;
    margin: 0 auto;
    padding: 0 2rem;
    position: relative;
    box-sizing: border-box;
}

/* Header sections */
.header-left {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1001;
}

.header-center {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    display: flex;
    align-items: center;
    z-index: 1000;
}

.header-right {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1001;
}

.header-title {
    margin: 0;
    font-size: 1.4rem;
    font-weight: 600;
    color: #333;
    white-space: nowrap;
}

/* Remove any flex properties that might interfere */
.header-left,
.header-right {
    flex: 0 0 auto !important;
    margin: 0 !important;
}

.header-title {
    font-size: 1.5rem;
    color: black;
    margin: 0;
    text-align: center;
    white-space: nowrap;
}

/* Keep back button on the left */
.back-button {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    color: black;
    text-decoration: none;
    font-weight: 500;
    border-radius: var(--radius);
    transition: var(--transition);
    background: none;
    border: none;
    cursor: pointer;
}

.back-button:hover {
    background: var(--light);
    color: black;
    transform: translateX(-4px);
}

/* Update the profile dropdown styles */
.profile-dropdown {
    position: relative;
    margin-left: auto;
}

.profile-button {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.25rem;
    background: var(--white);
    border: 1px solid rgba(0, 0, 0, 0.08);
    color: rgb(237, 157, 157);
    font-weight: 500;
    cursor: pointer;
    border-radius: var(--radius);
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    min-width: 180px;
    justify-content: space-between;
}

.profile-button:hover {
    background: rgb(232, 190, 190);
    border-color: rgb(237, 157, 157);
    color: rgb(237, 157, 157);
    transform: translateY(-1px);
}

.profile-button i.fa-user-circle {
    font-size: 1.2rem;
    color: rgb(231, 174, 174);
}

.profile-button i.fa-chevron-down {
    font-size: 0.8rem;
    transition: transform 0.2s ease;
}

.profile-dropdown.active i.fa-chevron-down {
    transform: rotate(180deg);
}

.dropdown-menu {
    position: absolute;
    top: calc(100% + 0.75rem);
    right: 0;
    min-width: 240px;
    background: var(--white);
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    padding: 0.75rem;
    margin-top: 0.5rem;
    display: none;
    border: 1px solid rgba(0, 0, 0, 0.08);
    backdrop-filter: blur(10px);
}

.dropdown-menu.active {
    display: block;
    animation: dropdownSlide 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    transform-origin: top right;
}

.dropdown-menu a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    color: rgb(237, 157, 157);
    text-decoration: none;
    transition: all 0.2s ease;
    border-radius: 8px;
    margin-bottom: 0.25rem;
}

.dropdown-menu a:hover {
    background: #fff0f3;
    color: rgb(237, 157, 157);
    transform: translateX(4px);
}

.dropdown-menu a i {
    font-size: 1rem;
    color: rgb(237, 157, 157);
    opacity: 0.9;
}

.dropdown-menu hr {
    margin: 0.75rem 0;
    border: none;
    border-top: 1px solid rgba(0, 0, 0, 0.06);
}

.dropdown-menu .logout {
    color: var(--danger);
}

.dropdown-menu .logout:hover {
    background: #fee2e2;
    color: var(--danger);
}

.dropdown-menu .logout i {
    color: var(--danger);
}

@keyframes dropdownSlide {
    0% {
        opacity: 0;
        transform: scale(0.95) translateY(-10px);
    }
    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* Add this if you want to show a subtle arrow pointer */
.dropdown-menu::before {
    content: '';
    position: absolute;
    top: -6px;
    right: 20px;
    width: 12px;
    height: 12px;
    background: var(--white);
    border-left: 1px solid rgba(0, 0, 0, 0.08);
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    transform: rotate(45deg);
}

.header-spacer {
    width: 1px;
    visibility: hidden;
}
</style>

<script>
function toggleDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    dropdown.classList.toggle('active');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('profileDropdown');
    const profileButton = event.target.closest('.profile-button');
    
    if (!profileButton && dropdown.classList.contains('active')) {
        dropdown.classList.remove('active');
    }
});
</script>