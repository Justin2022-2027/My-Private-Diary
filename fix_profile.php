<?php
// Start session and clear it
session_start();
session_destroy();

// Start new session
session_start();

// Connect to database
require_once 'db_connect.php';

// Check if admin user exists
$result = $conn->query("SELECT * FROM users WHERE email = 'admin@example.com'");
$admin = $result ? $result->fetch_assoc() : null;

if (!$admin) {
    // Create admin user if doesn't exist
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (full_name, email, password, birthdate, subscription_plan, subscription_status) 
            VALUES ('Admin User', 'admin@example.com', ?, '2000-01-01', 'lifetime', 'active')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $password);
    
    if ($stmt->execute()) {
        $admin_id = $conn->insert_id;
        echo "✅ Admin user created with ID: $admin_id<br>";
    } else {
        die("❌ Error creating admin user: " . $conn->error);
    }
} else {
    $admin_id = $admin['user_id'];
    echo "✅ Admin user already exists with ID: $admin_id<br>";
}

// Set session variables
$_SESSION['user_id'] = $admin_id;
$_SESSION['full_name'] = 'Admin User';
$_SESSION['email'] = 'admin@example.com';

// Verify user was created
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$count = $result->fetch_assoc()['count'];

echo "<h2>✅ Setup Complete!</h2>";
echo "<p>Total users in database: $count</p>";
echo "<p>You are now logged in as admin@example.com</p>";
echo "<p><a href='profile.php' style='color: blue; text-decoration: underline;'>Go to Profile</a> | ";
echo "<a href='dashboard.php' style='color: blue; text-decoration: underline;'>Go to Dashboard</a></p>";
echo "<p><strong>Important:</strong> Change the default password after logging in!</p>";
?>

<style>
    body { 
        font-family: Arial, sans-serif; 
        max-width: 800px; 
        margin: 40px auto; 
        padding: 20px;
        line-height: 1.6;
    }
    h2 { color: #2ecc71; }
    p { margin: 10px 0; }
    a { 
        color: #3498db;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
</style>
