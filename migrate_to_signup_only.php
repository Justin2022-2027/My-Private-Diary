<?php
// Migration Script: Move from users table to signup table only
// This script migrates all data from users table to signup table and updates all references

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'db_connect.php';

echo "<h2>🚀 Database Migration: Users → Signup Table Only</h2>";
echo "<p>This script will migrate all data from the 'users' table to the 'signup' table and remove the 'users' table completely.</p>";
echo "<hr>";

// Step 1: Check current table structure
echo "<h3>Step 1: Checking Current Tables</h3>";

$tables = ['users', 'signup', 'login_attempts', 'testimonials', 'settings'];
foreach ($tables as $table) {
    $sql = "SHOW TABLES LIKE '$table'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        echo "<p>✅ $table table exists</p>";
    } else {
        echo "<p>❌ $table table does not exist</p>";
    }
}

// Step 2: Backup data from users table
echo "<h3>Step 2: Backing up users table data</h3>";

$sql = "SELECT * FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $backup_file = 'users_table_backup_' . date('Y_m_d_H_i_s') . '.sql';
    $backup = "-- Backup of users table - " . date('Y-m-d H:i:s') . "\n";
    $backup .= "CREATE TABLE IF NOT EXISTS `users_backup` (\n";

    // Get column information
    $columns_sql = "SHOW COLUMNS FROM users";
    $columns_result = $conn->query($columns_sql);

    $columns = [];
    while ($col = $columns_result->fetch_assoc()) {
        $columns[] = "`{$col['Field']}` {$col['Type']}" . ($col['Null'] == 'NO' ? ' NOT NULL' : '') .
                    ($col['Default'] !== null ? " DEFAULT '{$col['Default']}'" : '') .
                    ($col['Extra'] ? " {$col['Extra']}" : '');
    }
    $backup .= implode(",\n", $columns) . "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

    // Get data
    $backup .= "-- Insert data\n";
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $values = [];
        foreach ($row as $key => $value) {
            $values[] = $value === null ? 'NULL' : "'" . $conn->real_escape_string($value) . "'";
        }
        $data[] = "INSERT INTO users_backup (" . implode(', ', array_keys($row)) . ") VALUES (" . implode(', ', $values) . ")";
    }
    $backup .= implode(";\n", $data) . ";\n";

    // Save backup
    if (file_put_contents($backup_file, $backup)) {
        echo "<p>✅ Users table backed up to: $backup_file</p>";
    } else {
        echo "<p>❌ Failed to create backup file</p>";
    }
} else {
    echo "<p>ℹ️ Users table is empty, no backup needed</p>";
}

// Step 3: Migrate data from users to signup table
echo "<h3>Step 3: Migrating data from users to signup table</h3>";

// Check if signup table has the same structure as users table
$sql = "DESCRIBE signup";
$result = $conn->query($sql);
$signup_columns = [];
while ($row = $result->fetch_assoc()) {
    $signup_columns[] = $row['Field'];
}

$sql = "DESCRIBE users";
$result = $conn->query($sql);
$users_columns = [];
while ($row = $result->fetch_assoc()) {
    $users_columns[] = $row['Field'];
}

// Find missing columns in signup table
$missing_columns = array_diff($users_columns, $signup_columns);
if (!empty($missing_columns)) {
    echo "<p>⚠️ Signup table is missing some columns. Adding them...</p>";
    foreach ($missing_columns as $column) {
        // Check if column already exists in signup table
        $check_sql = "SHOW COLUMNS FROM signup LIKE '$column'";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows == 0) {
            $sql = "SHOW COLUMNS FROM users WHERE Field = '$column'";
            $col_result = $conn->query($sql);
            $col_info = $col_result->fetch_assoc();

            // Skip columns that might cause issues
            if (in_array($column, ['created_at', 'updated_at'])) {
                echo "<p>⚠️ Skipping column '$column' - may cause compatibility issues</p>";
                continue;
            }

            $alter_sql = "ALTER TABLE signup ADD COLUMN `{$col_info['Field']}` {$col_info['Type']}";
            if ($col_info['Null'] == 'NO') $alter_sql .= ' NOT NULL';
            if ($col_info['Default'] !== null && $col_info['Default'] !== 'CURRENT_TIMESTAMP' && $col_info['Default'] !== 'current_timestamp()') $alter_sql .= " DEFAULT '{$col_info['Default']}'";
            if ($col_info['Extra']) $alter_sql .= " {$col_info['Extra']}";

            if ($conn->query($alter_sql)) {
                echo "<p>✅ Added column: {$col_info['Field']}</p>";
            } else {
                echo "<p>❌ Failed to add column: {$col_info['Field']} - " . $conn->error . "</p>";
            }
        } else {
            echo "<p>✅ Column '$column' already exists in signup table</p>";
        }
    }
}

// Copy data from users to signup (excluding created_at since signup has signup_date)
$sql = "INSERT INTO signup (user_id, full_name, email, password, birthdate, subscription_plan, subscription_status, subscription_start_date, subscription_end_date, theme, google_signup, google_id, profile_picture, bio, phone, address, city, state, country, postal_code, hobbies, favorite_music, favorite_films, favorite_books, favorite_places, gender, language, profile_public, email_notifications)
       SELECT user_id, full_name, email, password, birthdate, subscription_plan, subscription_status, subscription_start_date, subscription_end_date, theme, google_signup, google_id, profile_picture, bio, phone, address, city, state, country, postal_code, hobbies, favorite_music, favorite_films, favorite_books, favorite_places, gender, language, profile_public, email_notifications
       FROM users
       ON DUPLICATE KEY UPDATE
        full_name = VALUES(full_name),
        email = VALUES(email),
        password = VALUES(password),
        birthdate = VALUES(birthdate),
        subscription_plan = VALUES(subscription_plan),
        subscription_status = VALUES(subscription_status),
        subscription_start_date = VALUES(subscription_start_date),
        subscription_end_date = VALUES(subscription_end_date),
        theme = VALUES(theme),
        google_signup = VALUES(google_signup),
        google_id = VALUES(google_id),
        profile_picture = VALUES(profile_picture),
        bio = VALUES(bio),
        phone = VALUES(phone),
        address = VALUES(address),
        city = VALUES(city),
        state = VALUES(state),
        country = VALUES(country),
        postal_code = VALUES(postal_code),
        hobbies = VALUES(hobbies),
        favorite_music = VALUES(favorite_music),
        favorite_films = VALUES(favorite_films),
        favorite_books = VALUES(favorite_books),
        favorite_places = VALUES(favorite_places),
        gender = VALUES(gender),
        language = VALUES(language),
        profile_public = VALUES(profile_public),
        email_notifications = VALUES(email_notifications)";

if ($conn->query($sql)) {
    $affected_rows = $conn->affected_rows;
    echo "<p>✅ Migrated $affected_rows records from users to signup table</p>";
} else {
    echo "<p>❌ Failed to migrate data: " . $conn->error . "</p>";
}

// Step 4: Update foreign key constraints
echo "<h3>Step 4: Updating Foreign Key Constraints</h3>";

// Update login_attempts table
$sql = "ALTER TABLE login_attempts DROP FOREIGN KEY IF EXISTS login_attempts_ibfk_1";
if ($conn->query($sql)) {
    echo "<p>✅ Dropped old foreign key from login_attempts</p>";
}

$sql = "ALTER TABLE login_attempts ADD CONSTRAINT login_attempts_ibfk_1 FOREIGN KEY (user_id) REFERENCES signup(user_id) ON DELETE CASCADE";
if ($conn->query($sql)) {
    echo "<p>✅ Updated login_attempts foreign key to reference signup table</p>";
} else {
    echo "<p>❌ Failed to update login_attempts foreign key: " . $conn->error . "</p>";
}

// Update testimonials table
$sql = "ALTER TABLE testimonials DROP FOREIGN KEY IF EXISTS testimonials_ibfk_1";
if ($conn->query($sql)) {
    echo "<p>✅ Dropped old foreign key from testimonials</p>";
}

$sql = "ALTER TABLE testimonials ADD CONSTRAINT testimonials_ibfk_1 FOREIGN KEY (user_id) REFERENCES signup(user_id) ON DELETE CASCADE";
if ($conn->query($sql)) {
    echo "<p>✅ Updated testimonials foreign key to reference signup table</p>";
} else {
    echo "<p>❌ Failed to update testimonials foreign key: " . $conn->error . "</p>";
}

// Update additional foreign key constraints
$additional_tables = [
    'backup_history' => 'backup_history_ibfk_1',
    'diary_entries' => 'diary_entries_ibfk_1',
    'payments' => 'payments_user_fk',
    'reminders' => 'reminders_ibfk_1'
];
foreach ($additional_tables as $table => $constraint) {
    $sql = "ALTER TABLE $table DROP FOREIGN KEY IF EXISTS $constraint";
    if ($conn->query($sql)) {
        echo "<p>✅ Dropped old foreign key '$constraint' from $table</p>";
    }

    // Check if the new constraint already exists
    $check_sql = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_NAME = '$table' AND CONSTRAINT_NAME = '{$table}_ibfk_1' AND TABLE_SCHEMA = DATABASE()";
    $check_result = $conn->query($check_sql);
    $check_row = $check_result->fetch_assoc();

    if ($check_row['count'] == 0) {
        $sql = "ALTER TABLE $table ADD CONSTRAINT {$table}_ibfk_1 FOREIGN KEY (user_id) REFERENCES signup(user_id) ON DELETE CASCADE";
        if ($conn->query($sql)) {
            echo "<p>✅ Updated $table foreign key to reference signup table</p>";
        } else {
            echo "<p>❌ Failed to update $table foreign key: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>✅ Foreign key constraint already exists for $table</p>";
    }
}

// Step 5: Drop the users table
echo "<h3>Step 5: Dropping users table</h3>";

$sql = "DROP TABLE IF EXISTS users";
if ($conn->query($sql)) {
    echo "<p>✅ Successfully dropped users table</p>";
} else {
    echo "<p>❌ Failed to drop users table: " . $conn->error . "</p>";
}

// Step 6: Create admin user in signup table if not exists
echo "<h3>Step 6: Ensuring admin user exists in signup table</h3>";

$sql = "SELECT COUNT(*) as count FROM signup WHERE email = 'admin@example.com'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO signup (full_name, email, password, birthdate, subscription_plan, subscription_status)
            VALUES ('Admin User', 'admin@example.com', '$default_password', '2000-01-01', 'lifetime', 'active')";

    if ($conn->query($sql)) {
        echo "<p>✅ Created admin user in signup table</p>";
        echo "<p><strong>Email:</strong> admin@example.com</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p style='color: red;'>⚠️ Please change this password after logging in!</p>";
    } else {
        echo "<p>❌ Failed to create admin user: " . $conn->error . "</p>";
    }
} else {
    echo "<p>✅ Admin user already exists in signup table</p>";
}

// Step 7: Verify the migration
echo "<h3>Step 7: Verifying Migration</h3>";

$sql = "SELECT COUNT(*) as count FROM signup";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
echo "<p>📊 Total users in signup table: " . $row['count'] . "</p>";

$sql = "SELECT COUNT(*) as count FROM users";
try {
    $result = $conn->query($sql);
    if ($result !== false && $conn->errno == 0) {
        $row = $result->fetch_assoc();
        echo "<p>📊 Users table still exists with " . $row['count'] . " records (should be 0)</p>";
    } elseif ($conn->errno == 1146) { // Table doesn't exist error
        echo "<p>✅ Users table successfully dropped</p>";
    } else {
        echo "<p>⚠️ Could not verify users table status: " . $conn->error . "</p>";
    }
} catch (Exception $e) {
    echo "<p>✅ Users table successfully dropped (query failed as expected)</p>";
}

echo "<h3>✅ Migration Complete!</h3>";
echo "<p>The system now uses only the signup table for user authentication.</p>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ul>";
echo "<li><a href='login.php'>Test Login</a> - Try logging in with existing accounts</li>";
echo "<li><a href='signup.php'>Test Signup</a> - Create a new account to verify it works</li>";
echo "<li><a href='repair_database.php'>Run Repair Utility</a> - Check for any remaining issues</li>";
echo "</ul>";

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
h2 { color: #1e293b; border-bottom: 3px solid #ff9fb0; padding-bottom: 10px; }
h3 { color: #ff9fb0; margin-top: 30px; }
p { padding: 10px; margin: 5px 0; border-radius: 5px; background: white; }
ul { background: white; padding: 15px; border-radius: 5px; }
a { color: #ff9fb0; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
