<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect.php';

echo "<h2>Migrating Signup Table</h2>";

// Check if signup table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'signup'");
if ($tableCheck->num_rows == 0) {
    echo "<p style='color: red;'>❌ Signup table does not exist. Please run setup_database.php first.</p>";
    exit;
}

// List of columns that should exist in signup table
$columns_to_add = [
    'google_signup' => 'BOOLEAN DEFAULT 0',
    'google_id' => 'VARCHAR(100) DEFAULT NULL',
    'profile_picture' => 'VARCHAR(255) DEFAULT NULL',
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
    'email_notifications' => 'BOOLEAN DEFAULT 0',
    'theme' => 'VARCHAR(50) DEFAULT \'default\'',
    'subscription_plan' => 'ENUM(\'basic\', \'premium\', \'lifetime\') DEFAULT \'basic\'',
    'subscription_status' => 'ENUM(\'active\', \'expired\', \'cancelled\') DEFAULT \'active\'',
    'subscription_start_date' => 'DATETIME DEFAULT NULL',
    'subscription_end_date' => 'DATETIME DEFAULT NULL',
    'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
];

echo "<h3>Adding Missing Columns:</h3>";

foreach ($columns_to_add as $column => $definition) {
    // Check if column exists
    $checkColumn = $conn->query("SHOW COLUMNS FROM signup LIKE '$column'");
    
    if ($checkColumn->num_rows == 0) {
        // Column doesn't exist, add it
        $alterSql = "ALTER TABLE signup ADD COLUMN `$column` $definition";
        
        if ($conn->query($alterSql) === TRUE) {
            echo "<p style='color: green;'>✅ Added column: <strong>$column</strong></p>";
        } else {
            echo "<p style='color: red;'>❌ Error adding column <strong>$column</strong>: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Column <strong>$column</strong> already exists</p>";
    }
}

echo "<h3>✅ Migration Complete!</h3>";
echo "<p><a href='profile.php' style='padding: 10px 20px; background: #ff9fb0; color: white; text-decoration: none; border-radius: 5px;'>Go to Profile</a></p>";
echo "<p><a href='dashboard.php' style='padding: 10px 20px; background: #ff9fb0; color: white; text-decoration: none; border-radius: 5px;'>Go to Dashboard</a></p>";

$conn->close();
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 50px auto;
        padding: 20px;
        background: #f8fafc;
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
</style>
