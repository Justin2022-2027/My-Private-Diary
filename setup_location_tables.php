<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect.php';

echo "<h2>Setting up Location Tables</h2>";

// Create countries table
$sql = "CREATE TABLE IF NOT EXISTS `countries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `code` VARCHAR(3) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "<p style='color: green;'>✅ Countries table created successfully</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating countries table: " . $conn->error . "</p>";
}

// Create states table
$sql = "CREATE TABLE IF NOT EXISTS `states` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `country_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`country_id`) REFERENCES `countries`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_state` (`country_id`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "<p style='color: green;'>✅ States table created successfully</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating states table: " . $conn->error . "</p>";
}

// Create cities table
$sql = "CREATE TABLE IF NOT EXISTS `cities` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `state_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`state_id`) REFERENCES `states`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_city` (`state_id`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "<p style='color: green;'>✅ Cities table created successfully</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating cities table: " . $conn->error . "</p>";
}

// Check if data already exists
$result = $conn->query("SELECT COUNT(*) as count FROM countries");
$count = $result->fetch_assoc()['count'];

if ($count == 0) {
    echo "<h3>Inserting Sample Data...</h3>";
    
    // Insert sample countries
    $countries = [
        ['India', 'IN'],
        ['United States', 'US'],
        ['United Kingdom', 'GB'],
        ['Canada', 'CA'],
        ['Australia', 'AU']
    ];
    
    foreach ($countries as $country) {
        $stmt = $conn->prepare("INSERT INTO countries (name, code) VALUES (?, ?)");
        $stmt->bind_param("ss", $country[0], $country[1]);
        $stmt->execute();
        $stmt->close();
    }
    echo "<p style='color: green;'>✅ Sample countries inserted</p>";
    
    // Insert sample states for India
    $india_id = $conn->insert_id;
    $states = [
        'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
        'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand',
        'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur',
        'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
        'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura',
        'Uttar Pradesh', 'Uttarakhand', 'West Bengal', 'Delhi', 'Puducherry'
    ];
    
    // Get India ID
    $result = $conn->query("SELECT id FROM countries WHERE code = 'IN'");
    $india = $result->fetch_assoc();
    $india_id = $india['id'];
    
    foreach ($states as $state) {
        $stmt = $conn->prepare("INSERT INTO states (country_id, name) VALUES (?, ?)");
        $stmt->bind_param("is", $india_id, $state);
        $stmt->execute();
        $stmt->close();
    }
    echo "<p style='color: green;'>✅ Sample states for India inserted</p>";
    
    // Insert sample cities for Kerala
    $result = $conn->query("SELECT id FROM states WHERE name = 'Kerala' AND country_id = $india_id");
    $kerala = $result->fetch_assoc();
    if ($kerala) {
        $kerala_id = $kerala['id'];
        $cities = [
            'Thiruvananthapuram', 'Kochi', 'Kozhikode', 'Thrissur', 'Kollam',
            'Palakkad', 'Alappuzha', 'Kannur', 'Kottayam', 'Malappuram',
            'Kasaragod', 'Wayanad', 'Idukki', 'Pathanamthitta'
        ];
        
        foreach ($cities as $city) {
            $stmt = $conn->prepare("INSERT INTO cities (state_id, name) VALUES (?, ?)");
            $stmt->bind_param("is", $kerala_id, $city);
            $stmt->execute();
            $stmt->close();
        }
        echo "<p style='color: green;'>✅ Sample cities for Kerala inserted</p>";
    }
    
    // Insert sample cities for Maharashtra
    $result = $conn->query("SELECT id FROM states WHERE name = 'Maharashtra' AND country_id = $india_id");
    $maharashtra = $result->fetch_assoc();
    if ($maharashtra) {
        $maharashtra_id = $maharashtra['id'];
        $cities = [
            'Mumbai', 'Pune', 'Nagpur', 'Nashik', 'Aurangabad',
            'Solapur', 'Kolhapur', 'Amravati', 'Thane', 'Navi Mumbai'
        ];
        
        foreach ($cities as $city) {
            $stmt = $conn->prepare("INSERT INTO cities (state_id, name) VALUES (?, ?)");
            $stmt->bind_param("is", $maharashtra_id, $city);
            $stmt->execute();
            $stmt->close();
        }
        echo "<p style='color: green;'>✅ Sample cities for Maharashtra inserted</p>";
    }
    
} else {
    echo "<p style='color: blue;'>ℹ️ Location data already exists ($count countries found)</p>";
}

echo "<h3>✅ Setup Complete!</h3>";
echo "<p><a href='profile.php' style='padding: 10px 20px; background: #ff9fb0; color: white; text-decoration: none; border-radius: 5px;'>Go to Profile</a></p>";

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
    h2, h3 {
        color: #1e293b;
        border-bottom: 3px solid #ff9fb0;
        padding-bottom: 10px;
    }
    p {
        padding: 10px;
        margin: 5px 0;
        border-radius: 5px;
        background: white;
    }
</style>
