<?php
// New Database Setup Script - Signup Only Structure
// This script sets up the database with only the signup table for user management

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'db_connect.php';

echo "<h2>🚀 New Database Setup - Signup Only Structure</h2>";
echo "<p>This script sets up the database with only the 'signup' table for user management.</p>";
echo "<hr>";

// Create signup table (primary user table)
$sql = "CREATE TABLE IF NOT EXISTS `signup` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `birthdate` DATE NOT NULL,
    `subscription_plan` VARCHAR(50) DEFAULT 'basic',
    `subscription_status` ENUM('active', 'inactive', 'cancelled') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `profile_picture` VARCHAR(255) DEFAULT NULL,
    `bio` TEXT,
    `phone` VARCHAR(20),
    `address` VARCHAR(255),
    `city` VARCHAR(100),
    `state` VARCHAR(100),
    `country` VARCHAR(100),
    `postal_code` VARCHAR(20),
    `hobbies` TEXT,
    `favorite_music` TEXT,
    `favorite_films` TEXT,
    `favorite_books` TEXT,
    `favorite_places` TEXT,
    `gender` VARCHAR(20),
    `language` VARCHAR(10) DEFAULT 'en',
    `profile_public` BOOLEAN DEFAULT 0,
    `email_notifications` BOOLEAN DEFAULT 0,
    `dark_mode` BOOLEAN DEFAULT 0,
    `theme` VARCHAR(50) DEFAULT 'light'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Signup table created successfully or already exists</p>";
} else {
    echo "<p>❌ Error creating signup table: " . $conn->error . "</p>";
}

// Create login_attempts table
$sql = "CREATE TABLE IF NOT EXISTS `login_attempts` (
    `attempt_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `attempt_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('success', 'failed') NOT NULL,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    FOREIGN KEY (`user_id`) REFERENCES `signup`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Login attempts table created successfully or already exists</p>";
} else {
    echo "<p>❌ Error creating login_attempts table: " . $conn->error . "</p>";
}

// Create testimonials table
$sql = "CREATE TABLE IF NOT EXISTS `testimonials` (
    `testimonial_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `signup`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Testimonials table created successfully or already exists</p>";
} else {
    echo "<p>❌ Error creating testimonials table: " . $conn->error . "</p>";
}

// Create settings table
$sql = "CREATE TABLE IF NOT EXISTS `settings` (
    `setting_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `email_notifications` BOOLEAN DEFAULT 0,
    `dark_mode` BOOLEAN DEFAULT 0,
    `language` VARCHAR(10) DEFAULT 'en',
    `notification_email` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `signup`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Settings table created successfully or already exists</p>";
} else {
    echo "<p>❌ Error creating settings table: " . $conn->error . "</p>";
}

// Create diary_entries table
$sql = "CREATE TABLE IF NOT EXISTS `diary_entries` (
    `entry_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `mood` ENUM('happy', 'sad', 'excited', 'angry', 'neutral', 'anxious', 'peaceful', 'confused', 'grateful', 'tired') DEFAULT 'neutral',
    `weather` ENUM('sunny', 'cloudy', 'rainy', 'snowy', 'windy', 'stormy', 'foggy') DEFAULT 'sunny',
    `location` VARCHAR(255),
    `is_private` BOOLEAN DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `signup`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Diary entries table created successfully or already exists</p>";
} else {
    echo "<p>❌ Error creating diary_entries table: " . $conn->error . "</p>";
}

// Create reminders table
$sql = "CREATE TABLE IF NOT EXISTS `reminders` (
    `reminder_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `time` TIME NOT NULL,
    `days` VARCHAR(100) NOT NULL,
    `enabled` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `signup`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Reminders table created successfully or already exists</p>";
} else {
    echo "<p>❌ Error creating reminders table: " . $conn->error . "</p>";
}

// Create premium_subscriptions table
$sql = "CREATE TABLE IF NOT EXISTS `premium_subscriptions` (
    `subscription_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `plan_type` ENUM('basic', 'premium', 'lifetime') DEFAULT 'basic',
    `status` ENUM('active', 'cancelled', 'expired') DEFAULT 'active',
    `payment_method` VARCHAR(50),
    `transaction_id` VARCHAR(255),
    `amount_paid` DECIMAL(10,2),
    `currency` VARCHAR(3) DEFAULT 'USD',
    `start_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `end_date` TIMESTAMP NULL,
    `auto_renew` BOOLEAN DEFAULT 0,
    FOREIGN KEY (`user_id`) REFERENCES `signup`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Premium subscriptions table created successfully or already exists</p>";
} else {
    echo "<p>❌ Error creating premium_subscriptions table: " . $conn->error . "</p>";
}

// Check if admin user exists in signup table
$result = $conn->query("SELECT COUNT(*) as count FROM signup WHERE email = 'admin@example.com'");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Create default admin user
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO `signup` (`full_name`, `email`, `password`, `birthdate`, `subscription_plan`, `subscription_status`)
            VALUES ('Admin User', 'admin@example.com', '$default_password', '2000-01-01', 'lifetime', 'active')";

    if ($conn->query($sql) === TRUE) {
        echo "<p>✅ Default admin user created</p>";
        echo "<p>Email: admin@example.com</p>";
        echo "<p>Password: admin123</p>";
        echo "<p style='color: red;'>⚠️ Please change this password after logging in!</p>";
    } else {
        echo "<p>❌ Error creating admin user: " . $conn->error . "</p>";
    }
} else {
    echo "<p>✅ Admin user already exists</p>";
}

echo "<h3>Database setup completed!</h3>";
echo "<p>The system now uses only the 'signup' table for user authentication and management.</p>";

$conn->close();
?>

<h3>Next Steps:</h3>
<ol>
    <li>Try logging in with the admin credentials above</li>
    <li>Change the default password immediately after login</li>
    <li>Delete this setup file from your server for security</li>
    <li>Test user registration and login functionality</li>
</ol>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
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
    margin: 10px 0;
    padding: 10px;
    background: #fff;
    border-radius: 4px;
}
</style>
