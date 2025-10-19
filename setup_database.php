<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mpd";

// Create connection without selecting a database
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("<h2>Connection failed:</h2> " . $conn->connect_error);
}

echo "<h2>Connected successfully to MySQL server</h2>";

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
if ($conn->query($sql) === TRUE) {
    echo "<h3>Database created successfully or already exists</h3>";
} else {
    die("<h3>Error creating database: " . $conn->error . "</h3>");
}

// Select the database
$conn->select_db($dbname);

// SQL to create users table
$sql = "CREATE TABLE IF NOT EXISTS `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `birthdate` DATE NOT NULL,
    `subscription_plan` ENUM('basic', 'premium', 'lifetime') DEFAULT 'basic',
    `subscription_status` ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    `subscription_start_date` DATETIME DEFAULT NULL,
    `subscription_end_date` DATETIME DEFAULT NULL,
    `theme` VARCHAR(50) DEFAULT 'default',
    `google_signup` BOOLEAN DEFAULT 0,
    `google_id` VARCHAR(100) DEFAULT NULL,
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
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Users table created successfully or already exists</p>";
} else {
    echo "<p>❌ Error creating users table: " . $conn->error . "</p>";
}

// Create diary_entries table
$sql = "CREATE TABLE IF NOT EXISTS `diary_entries` (
    `entry_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `mood` VARCHAR(50) DEFAULT NULL,
    `tags` TEXT,
    `is_favorite` BOOLEAN DEFAULT 0,
    `is_archived` BOOLEAN DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Diary entries table created successfully or already exists</p>";
} else {
    echo "<p>❌ Error creating diary_entries table: " . $conn->error . "</p>";
}

// Create reminders table
$sql = "CREATE TABLE IF NOT EXISTS `reminders` (
    `reminder_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `reminder_time` TIME NOT NULL,
    `reminder_days` VARCHAR(20) NOT NULL,
    `enabled` BOOLEAN DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Reminders table created successfully or already exists</p>";
} else {
    echo "<p>❌ Error creating reminders table: " . $conn->error . "</p>";
}

// Create backup_history table
$sql = "CREATE TABLE IF NOT EXISTS `backup_history` (
    `backup_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `backup_file` VARCHAR(255) NOT NULL,
    `backup_size` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Backup history table created successfully or already exists</p>";
} else {
    echo "<p>❌ Error creating backup_history table: " . $conn->error . "</p>";
}

// Create payments table
$sql = "CREATE TABLE IF NOT EXISTS `payments` (
    `payment_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `currency` VARCHAR(3) DEFAULT 'INR',
    `payment_method` VARCHAR(50) NOT NULL,
    `transaction_id` VARCHAR(100) NOT NULL,
    `plan_type` ENUM('premium', 'lifetime') NOT NULL,
    `status` ENUM('pending', 'completed', 'failed') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Payments table created successfully or already exists</p>";
} else {
    echo "<p>❌ Error creating payments table: " . $conn->error . "</p>";
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
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

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
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

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
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Settings table created successfully or already exists</p>";
} else {
    echo "<p>❌ Error creating settings table: " . $conn->error . "</p>";
}

// Add missing columns to users table if they don't exist
$columns_to_add = [
    'bio' => 'TEXT',
    'phone' => 'VARCHAR(20)',
    'address' => 'VARCHAR(255)',
    'city' => 'VARCHAR(100)',
    'state' => 'VARCHAR(100)',
    'country' => 'VARCHAR(100)',
    'postal_code' => 'VARCHAR(20)',
    'hobbies' => 'TEXT',
    'favorite_music' => 'TEXT',
    'favorite_films' => 'TEXT',
    'favorite_books' => 'TEXT',
    'favorite_places' => 'TEXT',
    'gender' => 'VARCHAR(20)',
    'language' => 'VARCHAR(10) DEFAULT \'en\'',
    'profile_public' => 'BOOLEAN DEFAULT 0',
    'email_notifications' => 'BOOLEAN DEFAULT 0'
];

foreach ($columns_to_add as $column => $definition) {
    $checkColumn = $conn->query("SHOW COLUMNS FROM users LIKE '$column'");
    if ($checkColumn->num_rows == 0) {
        $alterSql = "ALTER TABLE users ADD COLUMN `$column` $definition";
        if ($conn->query($alterSql) === TRUE) {
            echo "<p>✅ Added $column column to users table</p>";
        } else {
            echo "<p>❌ Error adding $column column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>✅ $column column already exists in users table</p>";
    }
}

$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE email = 'admin@example.com'");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Create default admin user
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO `users` (`full_name`, `email`, `password`, `birthdate`, `subscription_plan`, `subscription_status`) 
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
$conn->close();
?>

<h3>Next Steps:</h3>
<ol>
    <li>Try logging in with the admin credentials above</li>
    <li>Change the default password immediately after login</li>
    <li>Delete this setup file from your server for security</li>
</ol>

<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
    h2 { color: #2c3e50; }
    h3 { color: #3498db; margin-top: 30px; }
    p { margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 4px; }
</style>
