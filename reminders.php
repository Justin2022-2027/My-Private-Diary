<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Drop and recreate reminders table with consistent column names
$sql = "DROP TABLE IF EXISTS reminders";
if ($conn->query($sql) === FALSE) {
    die("Error dropping reminders table: " . $conn->error);
}

// Create reminders table with consistent column names
$sql = "CREATE TABLE IF NOT EXISTS reminders (
    reminder_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    time TIME NOT NULL,
    days VARCHAR(100) NOT NULL,
    enabled TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES signup(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Execute the query using the correct variable name
if (!$conn->query($sql)) {
    $message = 'Error creating reminders table: ' . $conn->error;
    $message_type = 'error';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'update') {
        // Validate time
        $time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_STRING);
        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
            $message = 'Invalid time format';
            $message_type = 'error';
        } else {
            // Process days
            $days = isset($_POST['days']) ? (array)$_POST['days'] : [];
            $valid_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $days = array_intersect($days, $valid_days);
            $days_str = !empty($days) ? implode(',', $days) : '';
            
            // Sanitize enabled status
            $enabled = isset($_POST['enabled']) ? 1 : 0;
            
            if (empty($days_str)) {
                $message = 'Please select at least one day for the reminder';
                $message_type = 'error';
            } else {
                if ($action === 'add') {
                    // Check if user exists in signup table (only user table now)
                    $user_check_sql = "SELECT user_id FROM signup WHERE user_id = ?";
                    $user_check_stmt = $conn->prepare($user_check_sql);
                    $user_check_stmt->bind_param("i", $user_id);
                    $user_check_stmt->execute();
                    $user_check_result = $user_check_stmt->get_result();

                    if ($user_check_result->num_rows == 0) {
                        $message = 'User account not found. Please log in again.';
                        $message_type = 'error';
                    } else {
                        // Add new reminder
                        $stmt = $conn->prepare("INSERT INTO reminders (user_id, time, days, enabled) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("issi", $_SESSION['user_id'], $time, $days_str, $enabled);

                        if ($stmt->execute()) {
                            $message = 'Reminder added successfully';
                            $message_type = 'success';
                        } else {
                            $message = 'Error adding reminder: ' . $conn->error;
                            $message_type = 'error';
                        }
                        $stmt->close();
                    }
                    $user_check_stmt->close();
                } else {
                    // Update existing reminder
                    $reminder_id = filter_input(INPUT_POST, 'reminder_id', FILTER_VALIDATE_INT);
                    if ($reminder_id) {
                        $stmt = $conn->prepare("UPDATE reminders SET time = ?, days = ?, enabled = ? WHERE reminder_id = ? AND user_id = ?");
                        $stmt->bind_param("ssiii", $time, $days_str, $enabled, $reminder_id, $_SESSION['user_id']);

                        if ($stmt->execute()) {
                            $message = 'Reminder updated successfully';
                            $message_type = 'success';
                        } else {
                            $message = 'Error updating reminder: ' . $conn->error;
                            $message_type = 'error';
                        }
                        $stmt->close();
                    } else {
                        $message = 'Invalid reminder ID';
                        $message_type = 'error';
                    }
                }
            }
        }
    } elseif ($action === 'delete') {
        // Delete reminder
        $reminder_id = filter_input(INPUT_POST, 'reminder_id', FILTER_VALIDATE_INT);
        if ($reminder_id) {
            $stmt = $conn->prepare("DELETE FROM reminders WHERE reminder_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $reminder_id, $_SESSION['user_id']);

            if ($stmt->execute()) {
                $message = 'Reminder deleted successfully';
                $message_type = 'success';
            } else {
                $message = 'Error deleting reminder: ' . $conn->error;
                $message_type = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Invalid reminder ID';
            $message_type = 'error';
        }
    }
}

// Get user's reminders
$reminders = [];
$stmt = $conn->prepare("SELECT * FROM reminders WHERE user_id = ? ORDER BY time");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $reminders[] = $row;
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminders - My Private Diary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #a78bfa;
            --primary-dark: #7c3aed;
            --primary-light: #ede9fe;
            --danger: #ef4444;
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
        .main-content {
            max-width: 900px;
            margin: 0 auto;
            padding: 2.5rem 2rem 2rem 2rem;
            margin-top: 90px;
        }
        .reminders-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        .reminders-title {
            font-size: 2rem;
            font-weight: 700;
            color: rgb(237, 157, 157);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .add-reminder-btn {
            background:rgb(237, 157, 157);
            color: var(--white);
            border: none;
            border-radius: 0.75rem;
            padding: 0.85rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(167,139,250,0.08);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: background 0.2s, transform 0.2s;
        }
        .add-reminder-btn:hover {
            background: rgb(222, 141, 141);
            transform: translateY(-2px);
        }
        .reminders-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        .reminder-card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .reminder-card:hover {
            box-shadow: 0 8px 24px rgba(124,58,237,0.10);
            transform: translateY(-2px);
        }
        .reminder-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .reminder-time {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-dark);
        }
        .reminder-days {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .reminder-day {
            background: var(--primary-light);
            color: var(--primary-dark);
            border-radius: 1rem;
            padding: 0.25rem 0.85rem;
            font-size: 0.95rem;
            font-weight: 500;
        }
        .reminder-actions {
            display: flex;
            gap: 0.5rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.25rem;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .btn-edit {
            background: var(--primary-light);
            color: var(--primary-dark);
        }
        .btn-edit:hover {
            background: var(--primary);
            color: var(--white);
        }
        .btn-delete {
            background: #fee2e2;
            color: var(--danger);
        }
        .btn-delete:hover {
            background: var(--danger);
            color: var(--white);
        }
        .empty-state {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 2.5rem 2rem;
            text-align: center;
            margin-top: 2rem;
        }
        .empty-state-icon {
            font-size: 3rem;
            color: rgb(237, 157, 157);
            margin-bottom: 1rem;
        }
        .empty-state h3 {
            color: rgb(237, 157, 157);
            margin-bottom: 0.5rem;
        }
        .empty-state p {
            color: black;
            margin-bottom: 1.5rem;
        }
        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .message.success {
            background-color: #dcfce7;
            border-left: 4px solid #22c55e;
            color: #166534;
        }

        .message.error {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        .close-message {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
        }

        .close-message:hover {
            opacity: 1;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <main class="main-content">
        <div class="reminders-header">
            <div class="reminders-title">
                <i class="fas fa-bell"></i> Reminders
            </div>
            <button class="add-reminder-btn" id="addReminderBtn"><i class="fas fa-plus"></i> Add Reminder</button>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <span><?php echo htmlspecialchars($message); ?></span>
                <button class="close-message">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Reminder Form (initially hidden) -->
        <div id="reminderFormContainer" style="display: none;">
            <div class="reminder-card" style="margin-bottom: 2rem;">
                <h3 id="formTitle">Add New Reminder</h3>
                <form id="reminderForm" method="POST">
                    <input type="hidden" id="formAction" name="action" value="add">
                    <input type="hidden" id="reminderId" name="reminder_id" value="">

                    <div style="margin-bottom: 1rem;">
                        <label for="time">Time:</label>
                        <input type="time" id="time" name="time" required style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label>Days:</label>
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <?php $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']; ?>
                            <?php foreach ($days as $day): ?>
                                <label style="display: flex; align-items: center; gap: 0.25rem;">
                                    <input type="checkbox" name="days[]" value="<?php echo $day; ?>" class="day-checkbox">
                                    <?php echo $day; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" id="enabled" name="enabled" checked>
                            Enable reminder
                        </label>
                    </div>

                    <div style="display: flex; gap: 0.5rem;">
                        <button type="submit" class="btn btn-edit" style="flex: 1;">Save Reminder</button>
                        <button type="button" id="cancelBtn" class="btn btn-delete" style="flex: 1;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <?php if (empty($reminders)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="far fa-bell-slash"></i></div>
                <h3>No Reminders Yet</h3>
                <p>You haven't set up any reminders. Click the button above to add your first reminder!</p>
            </div>
        <?php else: ?>
            <div class="reminders-list">
                <?php foreach ($reminders as $reminder): 
                    $days = !empty($reminder['days']) ? explode(',', $reminder['days']) : [];
                    $time = new DateTime($reminder['time']);
                ?>
                    <div class="reminder-card">
                        <div class="reminder-info">
                            <div class="reminder-time"><i class="fas fa-clock"></i> <?php echo $time->format('h:i A'); ?></div>
                            <?php if (!empty($days)): ?>
                                <div class="reminder-days">
                                    <?php foreach ($days as $day): ?>
                                        <span class="reminder-day"><?php echo $day; ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="reminder-actions">
                            <button class="btn btn-edit edit-reminder" data-reminder='<?php echo json_encode($reminder); ?>'><i class="fas fa-edit"></i> Edit</button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this reminder?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="reminder_id" value="<?php echo $reminder['reminder_id']; ?>">
                                <button type="submit" class="btn btn-delete"><i class="fas fa-trash"></i> Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // DOM Elements
        const addReminderBtn = document.getElementById('addReminderBtn');
        const reminderFormContainer = document.getElementById('reminderFormContainer');
        const reminderForm = document.getElementById('reminderForm');
        const formTitle = document.getElementById('formTitle');
        const formAction = document.getElementById('formAction');
        const reminderIdInput = document.getElementById('reminderId');
        const cancelBtn = document.getElementById('cancelBtn');
        const timeInput = document.getElementById('time');
        const enabledCheckbox = document.getElementById('enabled');

        // Toggle form visibility
        function toggleForm(show = true) {
            if (show) {
                reminderFormContainer.style.display = 'block';
                // Scroll to form
                reminderFormContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                reminderFormContainer.style.display = 'none';
                // Reset form
                reminderForm.reset();
                formTitle.textContent = 'Add New Reminder';
                formAction.value = 'add';
                reminderIdInput.value = '';
                // Clear all day checkboxes
                document.querySelectorAll('.day-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
                // Reset time to current time
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                timeInput.value = `${hours}:${minutes}`;
                // Enable the enabled checkbox by default
                enabledCheckbox.checked = true;
            }
        }

        // Add New Reminder button click
        if (addReminderBtn) {
            addReminderBtn.addEventListener('click', function() {
                toggleForm(true);
            });
        }

        // Cancel button click
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function(e) {
                e.preventDefault();
                toggleForm(false);
            });
        }

        // Edit Reminder button click
        document.querySelectorAll('.edit-reminder').forEach(button => {
            button.addEventListener('click', function() {
                const reminder = JSON.parse(this.getAttribute('data-reminder'));

                // Set form title and action
                formTitle.textContent = 'Edit Reminder';
                formAction.value = 'update';
                reminderIdInput.value = reminder.reminder_id;

                // Set time
                timeInput.value = reminder.time;

                // Set days
                document.querySelectorAll('.day-checkbox').forEach(checkbox => {
                    checkbox.checked = reminder.days.includes(checkbox.value);
                });

                // Set enabled status
                enabledCheckbox.checked = reminder.enabled;

                // Show form
                toggleForm(true);
            });
        });

        // Form submission
        if (reminderForm) {
            reminderForm.addEventListener('submit', function(e) {
                // Client-side validation
                const daysChecked = document.querySelectorAll('.day-checkbox:checked').length > 0;
                if (!daysChecked) {
                    e.preventDefault();
                    alert('Please select at least one day for the reminder.');
                    return false;
                }

                // If validation passes, the form will submit normally
                return true;
            });
        }

        // Close message when clicking the close button
        const closeMessageBtns = document.querySelectorAll('.close-message');
        closeMessageBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.message').style.display = 'none';
            });
        });

        // Auto-hide success messages after 5 seconds
        const successMessages = document.querySelectorAll('.message.success');
        successMessages.forEach(msg => {
            setTimeout(() => {
                msg.style.opacity = '0';
                setTimeout(() => {
                    msg.style.display = 'none';
                }, 300);
            }, 5000);
        });

        // Initialize time input with current time if empty
        if (timeInput && !timeInput.value) {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            timeInput.value = `${hours}:${minutes}`;
        }
    });
    </script>
</body>
</html>