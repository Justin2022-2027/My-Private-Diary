<?php
$conn = new mysqli('localhost', 'root', '', 'mpd');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "<h2>Creating themes table...</h2>";

// Create themes table
$sql = "CREATE TABLE IF NOT EXISTS `themes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `theme_name` VARCHAR(50) DEFAULT 'default',
    `font_choice` VARCHAR(50) DEFAULT 'Inter',
    `background_color` VARCHAR(7) DEFAULT '#ffffff',
    `text_color` VARCHAR(7) DEFAULT '#1e293b',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `signup`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Themes table created successfully</p>";
} else {
    echo "<p>❌ Error creating themes table: " . $conn->error . "</p>";
}

// Check if any users exist and create default theme entries for them
$result = $conn->query("SELECT user_id FROM signup");
if ($result && $result->num_rows > 0) {
    echo "<p>Creating default theme entries for existing users...</p>";

    while ($row = $result->fetch_assoc()) {
        $user_id = $row['user_id'];

        // Check if user already has a theme entry
        $check_result = $conn->query("SELECT id FROM themes WHERE user_id = $user_id");
        if ($check_result && $check_result->num_rows == 0) {
            $insert_sql = "INSERT INTO themes (user_id, theme_name, font_choice, background_color, text_color)
                          VALUES ($user_id, 'default', 'Inter', '#ffffff', '#1e293b')";

            if ($conn->query($insert_sql) === TRUE) {
                echo "<p>✅ Created default theme for user ID: $user_id</p>";
            } else {
                echo "<p>❌ Error creating default theme for user ID $user_id: " . $conn->error . "</p>";
            }
        }
    }
} else {
    echo "<p>No users found to create default themes for.</p>";
}

echo "<h3>Themes table setup completed!</h3>";
$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
h2 { color: #2c3e50; }
h3 { color: #3498db; margin-top: 30px; }
p { margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 4px; }
</style>
