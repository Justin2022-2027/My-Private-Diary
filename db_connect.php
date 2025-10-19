<?php
$servername = "localhost";
$username = "root";
$password = ""; // your MySQL password, if any
$dbname = "mpd"; // your database name

// Connect to MySQL server without selecting a database first
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === FALSE) {
    die("Error creating database: " . $conn->error);
}

// Select the database
if (!$conn->select_db($dbname)) {
    die("Error selecting database: " . $conn->error);
}

// Create users table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    birthdate DATE NOT NULL,
    subscription_plan ENUM('basic', 'premium', 'lifetime') DEFAULT 'basic',
    subscription_status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    subscription_start_date DATETIME,
    subscription_end_date DATETIME,
    theme VARCHAR(50) DEFAULT 'default',
    google_signup BOOLEAN DEFAULT 0,
    google_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating users table: " . $conn->error);
}

// Create diary_entries table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS diary_entries (
    entry_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    mood VARCHAR(50),
    tags TEXT,
    is_favorite BOOLEAN DEFAULT 0,
    is_archived BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating diary_entries table: " . $conn->error);
}

// Create reminders table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS reminders (
    reminder_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reminder_time TIME NOT NULL,
    reminder_days VARCHAR(20) NOT NULL,
    enabled BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating reminders table: " . $conn->error);
}

// Create backup_history table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS backup_history (
    backup_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    backup_file VARCHAR(255) NOT NULL,
    backup_size INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating backup_history table: " . $conn->error);
}

// Create payments table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'INR',
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100) NOT NULL,
    plan_type ENUM('premium', 'lifetime') NOT NULL,
    status ENUM('pending', 'completed', 'failed') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating payments table: " . $conn->error);
}

// Create login_attempts table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS login_attempts (
    attempt_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(150) NOT NULL,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('success', 'failed') NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating login_attempts table: " . $conn->error);
}

// Create testimonials table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS testimonials (
    testimonial_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating testimonials table: " . $conn->error);
}

// Create settings table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    email_notifications BOOLEAN DEFAULT 0,
    dark_mode BOOLEAN DEFAULT 0,
    language VARCHAR(10) DEFAULT 'en',
    notification_email VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating settings table: " . $conn->error);
}

// Set charset to handle special characters correctly
$conn->set_charset("utf8mb4");

// Add missing columns to users table if they don't exist
$columns_to_add = [
    'profile_picture' => 'VARCHAR(255) DEFAULT NULL AFTER google_id',
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
    if($checkColumn->num_rows == 0) {
        $alterSql = "ALTER TABLE users ADD COLUMN `$column` $definition";
        if ($conn->query($alterSql) === FALSE) {
            die("Error adding $column column: " . $conn->error);
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->close();
?>