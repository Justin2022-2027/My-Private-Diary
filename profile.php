<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Function to generate user initials
function getInitials($name, $email) {
    if (!empty($name) && is_string($name)) {
        $parts = array_filter(explode(' ', trim($name)));
        $initials = '';
        foreach ($parts as $p) {
            if (!empty($p)) {
                $initials .= strtoupper($p[0]);
                if (strlen($initials) >= 2) break;
            }
        }
        return $initials;
    }
    return !empty($email) && is_string($email) ? strtoupper($email[0]) : 'U';
}

// Helper function to safely handle htmlspecialchars with null values
function safe_htmlspecialchars($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Debug: Log session data
error_log("Session data: " . print_r($_SESSION, true));

// Initialize user data array with default values
$user = [
    'full_name' => '',
    'email' => '',
    'username' => '',
    'created_at' => '',
    'profile_picture' => ''
];

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in, redirecting to login.php");
    header('Location: login.php');
    exit();
}

try {
    require_once 'db_connect.php';
    
    // Debug: Check database connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    $user_id = $_SESSION['user_id'];
    error_log("Fetching user with ID: " . $user_id);
    
    // Check if signup table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'signup'");
    if ($tableCheck->num_rows == 0) {
        throw new Exception("Signup table does not exist in the database.");
    }
    
    $stmt = $conn->prepare("SELECT * FROM signup WHERE user_id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Check if any users exist in the database
        $checkUsers = $conn->query("SELECT COUNT(*) as count FROM signup");
        $userCount = $checkUsers ? $checkUsers->fetch_assoc()['count'] : 0;
        
        throw new Exception("User profile not found. User ID: " . $user_id . ". Total users in database: " . $userCount);
    }
    
    // Fetch user data and merge with defaults
    $dbUser = $result->fetch_assoc();
    if ($dbUser) {
        $user = array_merge($user, $dbUser);
    }
    
    $stmt->close();
    
    // Debug: Log user data
    error_log("User data: " . print_r($user, true));
} catch (Exception $e) {
    $error_message = "Error: " . $e->getMessage();
    error_log($error_message);
    die("<div style='max-width: 800px; margin: 50px auto; padding: 20px; background: #fff3f3; border: 1px solid #ffb8c6; border-radius: 8px; color: #d32f2f;'>
            <h2>Profile Loading Error</h2>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
            <p>Please contact support or try logging in again.</p>
            <p><a href='logout.php' style='color: #d32f2f; text-decoration: underline;'>Logout</a></p>
            <p><small>Check the server error log for more details.</small></p>
        </div>");
}

// Handle profile picture
$profilePic = '';
if (!empty($user['profile_picture']) && is_string($user['profile_picture'])) {
    $picPath = 'uploads/profile_pictures/' . basename($user['profile_picture']);
    if (file_exists($picPath)) {
        $profilePic = $picPath;
    }
}

// Get user initials
$initials = getInitials(
    $user['full_name'] ?? '',
    $user['email'] ?? ''
);

// Get entry count
$entry_count = 0;
if (isset($conn)) {
    $entry_result = $conn->query("SELECT COUNT(*) as count FROM diary_entries WHERE user_id = " . intval($user_id));
    if ($entry_result) {
        $entry_count = $entry_result->fetch_assoc()['count'];
    }
}

// Get update status from URL parameters
$update_message = '';
$update_type = '';

if (isset($_GET['update']) && isset($_GET['message'])) {
    $update_type = $_GET['update'];
    $update_message = urldecode($_GET['message']);
}

// Format join date
$join_date = isset($user['created_at']) ? new DateTime($user['created_at']) : new DateTime();
$join_date_formatted = $join_date->format('F Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - My Private Diary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #ff9fb0;
            --primary-dark: #ff7a93;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray: #64748b;
            --white: #fff;
            --shadow: 0 4px 16px rgba(0,0,0,0.07);
            --radius: 1.25rem;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--light);
            color: var(--dark);
            margin: 0;
            min-height: 100vh;
        }

        /* Back button styles */
        .back-button {
            position: fixed;
            top: 2rem;
            left: 2rem;
            z-index: 1000;
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 8px;
            width: 70px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            box-shadow: var(--shadow);
            transition: all 0.2s ease;
        }

        .back-button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }

        .container {
            max-width: 1100px;
            margin: 2rem auto;
            padding: 2rem;
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        .profile-header {
            display: flex;
            align-items: center;
            gap: 2rem;
            flex-wrap: wrap;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--primary);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 700;
            position: relative;
            overflow: hidden;
        }
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        .change-pic-btn {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: rgba(0,0,0,0.5);
            color: var(--white);
            text-align: center;
            padding: 0.5rem 0;
            font-size: 0.95rem;
            cursor: pointer;
            border: none;
            border-radius: 0 0 50% 50%;
            transition: background 0.2s;
        }
        .change-pic-btn:hover {
            background: var(--primary-dark);
        }
        .profile-info {
            flex: 1;
        }
        .profile-info h2 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            font-weight: 700;
        }
        .profile-info .email {
            color: var(--gray);
            margin-bottom: 1rem;
        }
        .profile-info .bio {
            color: var(--dark);
            margin-bottom: 1rem;
        }
        .profile-stats {
            display: flex;
            gap: 2rem;
            margin-bottom: 1.5rem;
        }
        .stat {
            text-align: center;
        }
        .stat .value {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-dark);
        }
        .stat .label {
            color: var(--gray);
            font-size: 0.95rem;
        }
        .edit-btn {
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 1.25rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
            transition: background 0.2s;
        }
        .edit-btn:hover {
            background: var(--primary-dark);
        }
        .profile-details {
            margin-top: 2.5rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        .details-section {
            background: var(--light);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }
        .details-section h3 {
            margin-top: 0;
            color: var(--primary-dark);
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }
        .details-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .details-list li {
            margin-bottom: 0.75rem;
            color: var(--dark);
        }
        .details-list li span {
            color: var(--gray);
            font-weight: 500;
            margin-right: 0.5rem;
        }
        .tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .tag {
            background: var(--primary);
            color: var(--white);
            border-radius: 1rem;
            padding: 0.25rem 0.85rem;
            font-size: 0.95rem;
        }
        @media (max-width: 900px) {
            .profile-details {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 600px) {
            .back-button {
                top: 1rem;
                left: 1rem;
                width: 45px;
                height: 45px;
                font-size: 1rem;
            }

            .container {
                padding: 0.5rem;
            }
            .profile-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Back Button -->
    <button class="back-button" onclick="window.location.href='dashboard.php'" title="Back to Dashboard">
        Back
    </button>

    <div class="container">
        <?php if (!empty($update_message)): ?>
            <div class="update-message <?= $update_type === 'success' ? 'success' : 'error' ?>" style="padding: 1rem; margin-bottom: 1rem; border-radius: 0.5rem; <?= $update_type === 'success' ? 'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;' ?>">
                <?= htmlspecialchars($update_message) ?>
            </div>
        <?php endif; ?>
        <div class="profile-header">
            <div class="profile-avatar">
                <?php if ($profilePic): ?>
                    <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile Picture">
                <?php else: ?>
                    <?= $initials ?>
                <?php endif; ?>
                <form id="picForm" action="upload_avatar.php" method="POST" enctype="multipart/form-data" style="position:absolute;bottom:0;left:0;width:100%;margin:0;">
                    <input type="file" name="profile_picture" id="profile_picture" accept="image/*" style="display:none;">
                    <button type="button" class="change-pic-btn" onclick="document.getElementById('profile_picture').click();"><i class="fas fa-camera"></i> Change</button>
                </form>
            </div>
            <div class="profile-info">
                <h2><?= htmlspecialchars($user['full_name']) ?></h2>
                <div class="email"><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></div>
                <div class="bio"><?= !empty($user['bio']) ? nl2br(htmlspecialchars($user['bio'])) : '<span style="color:#aaa;">No bio set. Tell us about yourself!</span>' ?></div>
                <div class="profile-stats">
                    <div class="stat">
                        <div class="value"><?= $entry_count ?></div>
                        <div class="label">Diary Entries</div>
                    </div>
                    <div class="stat">
                        <div class="value"><?= $join_date_formatted ?></div>
                        <div class="label">Member Since</div>
                    </div>
                </div>
                <button class="edit-btn" onclick="document.getElementById('editModal').style.display='block'">Edit Profile</button>
            </div>
        </div>
        <div class="profile-details">
            <div class="details-section">
                <h3>Personal Details</h3>
                <ul class="details-list">
                    <li><span>Full Name:</span> <?= htmlspecialchars($user['full_name']) ?></li>
                    <li><span>Email:</span> <?= htmlspecialchars($user['email']) ?></li>
                    <li><span>Date of Birth:</span> <?= !empty($user['birthdate']) ? date('F j, Y', strtotime($user['birthdate'])) : 'Not set' ?></li>
                    <li><span>Bio:</span> <?= !empty($user['bio']) ? nl2br(htmlspecialchars($user['bio'])) : 'No bio set' ?></li>
                </ul>
            </div>
            <div class="details-section">
                <h3>Contact & Location</h3>
                <ul class="details-list">
                    <li><span>Phone:</span> <?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not provided' ?></li>
                    <li><span>Address:</span> <?= !empty($user['address']) ? htmlspecialchars($user['address']) : 'Not provided' ?></li>
                    <li><span>Postal Code:</span> <?= !empty($user['postal_code']) ? htmlspecialchars($user['postal_code']) : 'Not provided' ?></li>
                    <li><span>Country:</span> <?= !empty($user['country']) ? htmlspecialchars($user['country']) : 'Not provided' ?></li>
                    <li><span>State:</span> <?= !empty($user['state']) ? htmlspecialchars($user['state']) : 'Not provided' ?></li>
                    <li><span>City:</span> <?= !empty($user['city']) ? htmlspecialchars($user['city']) : 'Not provided' ?></li>
                </ul>
            </div>
            <div class="details-section">
                <h3>Diary Preferences</h3>
                <ul class="details-list">
                    <li><span>Language:</span> <?= !empty($user['language']) ? htmlspecialchars($user['language']) : 'English' ?></li>
                    <li><span>Profile Visibility:</span> <?= isset($user['profile_public']) && $user['profile_public'] ? 'Public' : 'Private' ?></li>
                </ul>
            </div>
            <div class="details-section">
                <h3>Favourites & Hobbies</h3>
                <ul class="details-list">
                    <li><span>Hobbies:</span> <span class="tags"><?php if (!empty($user['hobbies'])) { foreach (explode(',', $user['hobbies']) as $h) echo '<span class="tag">' . htmlspecialchars(trim($h)) . '</span>'; } else { echo 'Not specified'; } ?></span></li>
                    <li><span>Favourite Music:</span> <span class="tags"><?php if (!empty($user['favorite_music'])) { foreach (explode(',', $user['favorite_music']) as $m) echo '<span class="tag">' . htmlspecialchars(trim($m)) . '</span>'; } else { echo 'Not specified'; } ?></span></li>
                    <li><span>Favourite Films:</span> <span class="tags"><?php if (!empty($user['favorite_films'])) { foreach (explode(',', $user['favorite_films']) as $f) echo '<span class="tag">' . htmlspecialchars(trim($f)) . '</span>'; } else { echo 'Not specified'; } ?></span></li>
                    <li><span>Favourite Books:</span> <span class="tags"><?php if (!empty($user['favorite_books'])) { foreach (explode(',', $user['favorite_books']) as $b) echo '<span class="tag">' . htmlspecialchars(trim($b)) . '</span>'; } else { echo 'Not specified'; } ?></span></li>
                    <li><span>Favourite Places:</span> <span class="tags"><?php if (!empty($user['favorite_places'])) { foreach (explode(',', $user['favorite_places']) as $p) echo '<span class="tag">' . htmlspecialchars(trim($p)) . '</span>'; } else { echo 'Not specified'; } ?></span></li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Edit Modal (simple version, you can expand as needed) -->
    <div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); z-index:9999; align-items:center; justify-content:center; overflow-y:auto;">
        <div style="background:#fff; border-radius:1rem; max-width:600px; width:90%; max-height:90vh; margin:2rem auto; padding:2rem; position:relative; overflow-y:auto;">
            <button onclick="document.getElementById('editModal').style.display='none'" style="position:absolute; top:1rem; right:1rem; background:none; border:none; font-size:1.5rem; color:#aaa; cursor:pointer; z-index:10;">&times;</button>
            <h2 style="margin-top:0; color:var(--primary-dark); padding-right:2rem;">Edit Profile</h2>
            <form action="update_profile.php" method="POST">
                <div style="margin-bottom:1rem;">
                    <label>Bio</label>
                    <textarea name="bio" rows="3" style="width:100%; border-radius:0.5rem; border:1px solid #e5e7eb; padding:0.5rem;"><?= safe_htmlspecialchars($user['bio']) ?></textarea>
                </div>
                <div style="margin-bottom:1rem;">
                    <label>Phone Number</label><br>
                    <input type="text" name="phone" value="<?= safe_htmlspecialchars($user['phone']) ?>" style="width:100%; border-radius:0.5rem; border:1px solid #e5e7eb; padding:0.5rem;">
                </div>
                <div style="margin-bottom:1rem;">
                    <label>Address</label><br>
                    <input type="text" name="address" value="<?= safe_htmlspecialchars($user['address']) ?>" style="width:100%; border-radius:0.5rem; border:1px solid #e5e7eb; padding:0.5rem;">
                </div>
                <div style="margin-bottom:1rem;">
                <label>Postal Code</label><br>
                <input type="text" name="postal_code" value="<?= safe_htmlspecialchars($user['postal_code']) ?>" style="width:100%; border-radius:0.5rem; border:1px solid #e5e7eb; padding:0.5rem;">
                </div>
                <div style="margin-bottom:1rem;">
                <label>Country</label><br>
                <select name="country" id="country" style="width:100%; border-radius:0.5rem; border:1px solid #e5e7eb; padding:0.5rem;">
                    <option value="">Select Country</option>
                    <option value="India" <?php echo ($user['country'] ?? '') == 'India' ? 'selected' : ''; ?>>India</option>
                </select>
                </div>
                <div style="margin-bottom:1rem;">
                <label>State</label><br>
                <select name="state" id="state" style="width:100%; border-radius:0.5rem; border:1px solid #e5e7eb; padding:0.5rem;">
                    <option value="">Select Country First</option>
                </select>
                </div>
                <div style="margin-bottom:1rem;">
                <label>City</label><br>
                <select name="city" id="city" style="width:100%; border-radius:0.5rem; border:1px solid #e5e7eb; padding:0.5rem;">
                    <option value="">Select State First</option>
                </select>
                </div>
                <div style="margin-bottom:1rem;">
                    <label>Hobbies</label><br>
                    <input type="text" name="hobbies" value="<?= safe_htmlspecialchars($user['hobbies']) ?>" style="width:100%; border-radius:0.5rem; border:1px solid #e5e7eb; padding:0.5rem;">
                </div>
                <div style="margin-bottom:1rem;">
                    <label>Favourite Music</label><br>
                    <input type="text" name="favorite_music" value="<?= safe_htmlspecialchars($user['favorite_music']) ?>" style="width:100%; border-radius:0.5rem; border:1px solid #e5e7eb; padding:0.5rem;">
                </div>
                <div style="margin-bottom:1rem;">
                    <label>Favourite Films</label><br>
                    <input type="text" name="favorite_films" value="<?= safe_htmlspecialchars($user['favorite_films']) ?>" style="width:100%; border-radius:0.5rem; border:1px solid #e5e7eb; padding:0.5rem;">
                </div>
                <div style="margin-bottom:1rem;">
                    <label>Favourite Books</label><br>
                    <input type="text" name="favorite_books" value="<?= safe_htmlspecialchars($user['favorite_books']) ?>" style="width:100%; border-radius:0.5rem; border:1px solid #e5e7eb; padding:0.5rem;">
                </div>
                <div style="margin-bottom:1rem;">
                    <label>Favourite Places</label><br>
                    <input type="text" name="favorite_places" value="<?= safe_htmlspecialchars($user['favorite_places']) ?>" style="width:100%; border-radius:0.5rem; border:1px solid #e5e7eb; padding:0.5rem;">
                </div>
                <button type="submit" class="edit-btn">Save Changes</button>
            </form>
        </div>
    </div>
    <script>
        // Profile picture upload
        document.getElementById('profile_picture').addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;

            // Validate file type
            if (!['image/jpeg', 'image/png', 'image/gif'].includes(file.type)) {
                alert('Please select an image file (JPG, PNG, or GIF)');
                return;
            }

            // Validate file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('File is too large. Maximum size is 5MB');
                return;
            }

            const form = document.getElementById('picForm');
            const formData = new FormData(form);

            // Show loading indicator
            const avatar = document.querySelector('.profile-avatar');
            avatar.style.opacity = '0.5';

            fetch('upload_avatar.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Refresh the page after successful upload
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to upload profile picture'));
                    avatar.style.opacity = '1';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error uploading profile picture. Please try again.');
                avatar.style.opacity = '1';
            });
        });
        
        // Close modal on outside click
        window.onclick = function(event) {
            var modal = document.getElementById('editModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
        
        // Cascading dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const countrySelect = document.getElementById('country');
            const stateSelect = document.getElementById('state');
            const citySelect = document.getElementById('city');
            
            // Load states when country changes
            countrySelect.addEventListener('change', function() {
                const countryName = this.value;
                stateSelect.innerHTML = '<option value="">Select State</option>';
                citySelect.innerHTML = '<option value="">Select State First</option>';
                
                if (countryName === 'India') {
                    // Load Indian states
                    fetch('get_indian_states.php')
                        .then(response => response.json())
                        .then(data => {
                            if (data.states) {
                                data.states.forEach(state => {
                                    const option = document.createElement('option');
                                    option.value = state;
                                    option.textContent = state;
                                    stateSelect.appendChild(option);
                                });
                            }
                        })
                        .catch(error => console.error('Error loading Indian states:', error));
                } else if (countryName) {
                    // For other countries, use the existing database system
                    // Find country ID from database
                    fetch(`get_states.php?country_name=${encodeURIComponent(countryName)}`)
                        .then(response => response.json())
                        .then(states => {
                            if (states && !states.error) {
                                states.forEach(state => {
                                    const option = document.createElement('option');
                                    option.value = state.name;
                                    option.textContent = state.name;
                                    stateSelect.appendChild(option);
                                });
                            }
                        })
                        .catch(error => console.error('Error loading states:', error));
                }
            });
            
            // Load cities when state changes
            stateSelect.addEventListener('change', function() {
                const stateName = this.value;
                const countryName = countrySelect.value;
                citySelect.innerHTML = '<option value="">Select City</option>';
                
                if (stateName && countryName === 'India') {
                    // Load Indian cities
                    fetch(`get_indian_cities.php?state=${encodeURIComponent(stateName)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.cities) {
                                data.cities.forEach(city => {
                                    const option = document.createElement('option');
                                    option.value = city;
                                    option.textContent = city;
                                    citySelect.appendChild(option);
                                });
                            } else if (data.error) {
                                console.error('Error loading cities:', data.error);
                            }
                        })
                        .catch(error => console.error('Error loading Indian cities:', error));
                } else if (stateName && countryName) {
                    // For other countries, use the existing database system
                    fetch(`get_cities.php?state_name=${encodeURIComponent(stateName)}`)
                        .then(response => response.json())
                        .then(cities => {
                            if (cities && !cities.error) {
                                cities.forEach(city => {
                                    const option = document.createElement('option');
                                    option.value = city.name;
                                    option.textContent = city.name;
                                    citySelect.appendChild(option);
                                });
                            }
                        })
                        .catch(error => console.error('Error loading cities:', error));
                }
            });
            
            // Load initial state and city if user has existing data
            <?php if (!empty($user['country'])): ?>
            const selectedCountryName = '<?= addslashes($user['country']) ?>';
            const selectedStateName = '<?= addslashes($user['state'] ?? '') ?>';
            const selectedCityName = '<?= addslashes($user['city'] ?? '') ?>';
            
            // Set the country
            countrySelect.value = selectedCountryName;
            
            // Trigger country change to load states
            if (selectedCountryName) {
                countrySelect.dispatchEvent(new Event('change'));
                
                // Wait a bit for states to load, then set state
                setTimeout(() => {
                    if (selectedStateName) {
                        stateSelect.value = selectedStateName;
                        
                        // Trigger state change to load cities
                        stateSelect.dispatchEvent(new Event('change'));
                        
                        // Wait a bit for cities to load, then set city
                        setTimeout(() => {
                            if (selectedCityName) {
                                citySelect.value = selectedCityName;
                            }
                        }, 500);
                    }
                }, 500);
            }
            <?php endif; ?>
        });
    </script>
</body>
</html>