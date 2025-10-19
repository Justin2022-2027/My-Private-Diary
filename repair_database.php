<?php
// Database Repair Utility for My Private Diary
// This script helps fix authentication issues and ensures data consistency

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'db_connect.php';

echo "<h2>🔧 Database Repair Utility</h2>";
echo "<p>This utility will help fix authentication and data consistency issues.</p>";

// 1. Check for orphaned records and fix them
echo "<h3>1. Checking Table Consistency</h3>";

// Check if users exist in signup table but not in users table
$sql = "SELECT user_id, full_name, email FROM signup WHERE user_id NOT IN (SELECT user_id FROM users)";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<p>⚠️ Found " . $result->num_rows . " users in signup table but not in users table. This is normal now since we use only signup table.</p>";
} else {
    echo "<p>✅ No data migration needed.</p>";
}

// 2. Check for duplicate emails in signup table
echo "<h3>2. Checking for Duplicate Emails in Signup Table</h3>";

$sql = "SELECT email, COUNT(*) as count FROM signup GROUP BY email HAVING count > 1";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<p>⚠️ Found " . $result->num_rows . " duplicate emails in signup table. This may cause login issues.</p>";
    echo "<p>Please manually resolve these duplicates in your database.</p>";
} else {
}

// 3. Check for missing default admin user
echo "<h3>3. Checking Admin User</h3>";

$sql = "SELECT COUNT(*) as count FROM signup WHERE email = 'admin@example.com'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    echo "<p>⚠️ Admin user not found. Creating default admin user...</p>";

    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO signup (full_name, email, password, birthdate, subscription_plan, subscription_status)
            VALUES ('Admin User', 'admin@example.com', ?, '2000-01-01', 'lifetime', 'active')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $default_password);

    if ($stmt->execute()) {
        echo "<p>✅ Created admin user in signup table</p>";
        echo "<p><strong>Email:</strong> admin@example.com</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p style='color: red;'>⚠️ Please change this password after logging in!</p>";
    } else {
        echo "<p>❌ Failed to create admin user: " . $conn->error . "</p>";
    }
    $stmt->close();
} else {
    echo "<p>✅ Admin user exists in signup table</p>";
}

// 4. Check for table structure issues
echo "<h3>4. Checking Table Structure</h3>";
$required_columns = ['user_id', 'full_name', 'email', 'password', 'birthdate'];
foreach ($required_columns as $column) {
    $sql = "SHOW COLUMNS FROM signup LIKE '$column'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<p>✅ Column '$column' exists in signup table.</p>";
    } else {
        echo "<p>❌ Column '$column' is missing from signup table!</p>";
    }
}

// 5. Fix foreign key constraints
echo "<h3>5. Checking Foreign Key Constraints</h3>";

// Check if settings table exists and has correct constraints
$sql = "SHOW TABLES LIKE 'settings'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<p>✅ Settings table exists.</p>";

    // Check if foreign key constraint is correct
    $sql = "SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'settings' AND CONSTRAINT_NAME LIKE '%user_id%'";

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $constraint = $result->fetch_assoc();
        if ($constraint['REFERENCED_TABLE_NAME'] === 'users') {
            echo "<p>✅ Settings table foreign key constraint is correct.</p>";
        } else {
            echo "<p>⚠️ Settings table references wrong table: " . $constraint['REFERENCED_TABLE_NAME'] . "</p>";
        }
    }
} else {
    echo "<p>❌ Settings table does not exist. Please run setup_database.php</p>";
}

// 6. Check for any users with missing required data
echo "<h3>6. Checking Data Integrity</h3>";

$sql = "SELECT user_id, email, full_name FROM users WHERE email IS NULL OR full_name IS NULL OR password IS NULL";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<p>⚠️ Found " . $result->num_rows . " users with missing required data:</p>";
    while ($row = $result->fetch_assoc()) {
        echo "<p>- User ID " . $row['user_id'] . ": " . htmlspecialchars($row['email'] ?? 'No email') . "</p>";
    }
} else {
    echo "<p>✅ All users have required data.</p>";
}

// 7. Create a summary report
echo "<h3>7. Summary Report</h3>";

$total_users_sql = "SELECT COUNT(*) as count FROM signup";
$total_signup_sql = "SELECT COUNT(*) as count FROM signup";

$total_users_result = $conn->query($total_users_sql);
$total_signup_result = $conn->query($total_signup_sql);

$total_users = $total_users_result->fetch_assoc()['count'];
$total_signup = $total_signup_result->fetch_assoc()['count'];

echo "<p><strong>Total users in signup table:</strong> $total_users</p>";
echo "<p><strong>Total users in signup table:</strong> $total_signup</p>";

if ($total_users > 0) {
    echo "<p>✅ Primary user table (signup) has data.</p>";
} else {
    echo "<p>❌ Primary user table (signup) is empty!</p>";
}

echo "<h3>8. Recommendations</h3>";
echo "<ul>";
echo "<li>Always use the 'signup' table as the primary user table for new registrations</li>";
echo "<li>Regularly backup your database</li>";
echo "<li>Test login functionality after any database changes</li>";
echo "<li>Run this repair utility periodically to check for issues</li>";
echo "</ul>";

echo "<h3>9. Quick Actions</h3>";
echo "<p><a href='setup_database.php' style='padding: 10px 20px; background: #ff9fb0; color: white; text-decoration: none; border-radius: 5px;'>Re-run Database Setup</a></p>";
echo "<p><a href='login.php' style='padding: 10px 20px; background: #10b981; color: white; text-decoration: none; border-radius: 5px;'>Test Login</a></p>";
echo "<p><a href='signup.php' style='padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 5px;'>Test Signup</a></p>";

echo "<p style='margin-top: 30px; color: #666; font-size: 14px;'>Database repair completed. If you're still experiencing issues, please check your database manually or contact support.</p>";

$conn->close();
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 900px;
    margin: 50px auto;
    padding: 20px;
    background: #f8fafc;
    line-height: 1.6;
}
h2 {
    color: #1e293b;
    border-bottom: 3px solid #ff9fb0;
    padding-bottom: 10px;
}
h3 {
    color: #ff9fb0;
    margin-top: 30px;
}
p {
    padding: 10px;
    margin: 5px 0;
    border-radius: 5px;
    background: white;
}
ul {
    background: white;
    padding: 15px;
    border-radius: 5px;
}
</style>
