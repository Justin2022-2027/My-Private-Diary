<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => ''];

// Check if this is an AJAX request or regular form submission
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Handle regular form submission from profile.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['section'])) {
    // This is a regular form submission from the edit modal in profile.php
    try {
        // Start transaction
        $conn->begin_transaction();

        $updates = [];
        $params = [];
        $types = '';

        // Handle all the form fields from profile.php edit modal
        $form_fields = [
            'bio' => 'bio',
            'phone' => 'phone',
            'address' => 'address',
            'postal_code' => 'postal_code',
            'hobbies' => 'hobbies',
            'favorite_music' => 'favorite_music',
            'favorite_films' => 'favorite_films',
            'favorite_books' => 'favorite_books',
            'favorite_places' => 'favorite_places'
        ];

        foreach ($form_fields as $post_key => $db_column) {
            if (isset($_POST[$post_key])) {
                $value = trim($_POST[$post_key]);
                $updates[] = "`$db_column` = ?";
                $params[] = $value;
                $types .= 's';
            }
        }

        // Handle country, state, city from dropdowns (now storing names directly)
        if (isset($_POST['country']) && !empty($_POST['country'])) {
            $updates[] = "`country` = ?";
            $params[] = trim($_POST['country']);
            $types .= 's';
        }
        
        if (isset($_POST['state']) && !empty($_POST['state'])) {
            $updates[] = "`state` = ?";
            $params[] = trim($_POST['state']);
            $types .= 's';
        }
        
        if (isset($_POST['city']) && !empty($_POST['city'])) {
            $updates[] = "`city` = ?";
            $params[] = trim($_POST['city']);
            $types .= 's';
        }

        if (!empty($updates)) {
            // Prepare the SQL query
            $sql = "UPDATE signup SET " . implode(', ', $updates) . " WHERE user_id = ?";
            $stmt = $conn->prepare($sql);

            // Add user_id to params
            $params[] = $user_id;
            $types .= 'i';

            // Bind parameters
            $stmt->bind_param($types, ...$params);

            // Execute the query
            $result = $stmt->execute();

            if ($result) {
                $conn->commit();
                $response['success'] = true;
                $response['message'] = 'Profile updated successfully';

                // Update session data if full name was changed (though it's not in this form)
                if (isset($_POST['full_name'])) {
                    $_SESSION['full_name'] = trim($_POST['full_name']);
                }
            } else {
                $conn->rollback();
                $response['message'] = 'Failed to update profile: ' . $stmt->error;
            }

            $stmt->close();
        } else {
            $response['message'] = 'No changes to update';
        }
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'Error: ' . $e->getMessage();
    }
} elseif (isset($_POST['section'])) {
    // Handle AJAX requests with sections (existing logic)
    $section = $_POST['section'];
    $updates = [];
    $params = [];
    $types = '';

    // Validate and prepare updates based on section
    switch ($section) {
        case 'personal':
            if (isset($_POST['full_name'])) {
                $updates[] = 'full_name = ?';
                $params[] = trim($_POST['full_name']);
                $types .= 's';
            }
            if (isset($_POST['birthdate'])) {
                $updates[] = 'birthdate = ?';
                $params[] = !empty($_POST['birthdate']) ? $_POST['birthdate'] : null;
                $types .= 's';
            }
            if (isset($_POST['gender'])) {
                $updates[] = 'gender = ?';
                $params[] = !empty($_POST['gender']) ? $_POST['gender'] : null;
                $types .= 's';
            }
            if (isset($_POST['phone'])) {
                $updates[] = 'phone = ?';
                $params[] = !empty($_POST['phone']) ? trim($_POST['phone']) : null;
                $types .= 's';
            }
            if (isset($_POST['bio'])) {
                $updates[] = 'bio = ?';
                $params[] = !empty($_POST['bio']) ? trim($_POST['bio']) : null;
                $types .= 's';
            }
            break;

        case 'location':
            if (isset($_POST['address'])) {
                $updates[] = 'address = ?';
                $params[] = !empty($_POST['address']) ? trim($_POST['address']) : null;
                $types .= 's';
            }
            if (isset($_POST['city'])) {
                $updates[] = 'city = ?';
                $params[] = !empty($_POST['city']) ? trim($_POST['city']) : null;
                $types .= 's';
            }
            if (isset($_POST['state'])) {
                $updates[] = 'state = ?';
                $params[] = !empty($_POST['state']) ? trim($_POST['state']) : null;
                $types .= 's';
            }
            if (isset($_POST['country'])) {
                $updates[] = 'country = ?';
                $params[] = !empty($_POST['country']) ? trim($_POST['country']) : null;
                $types .= 's';
            }
            if (isset($_POST['postal_code'])) {
                $updates[] = 'postal_code = ?';
                $params[] = !empty($_POST['postal_code']) ? trim($_POST['postal_code']) : null;
                $types .= 's';
            }
            break;

        case 'preferences':
            if (isset($_POST['theme'])) {
                $updates[] = 'theme = ?';
                $params[] = $_POST['theme'];
                $types .= 's';
            }
            if (isset($_POST['email_notifications'])) {
                $updates[] = 'email_notifications = ?';
                $params[] = (int)$_POST['email_notifications'];
                $types .= 'i';
            }
            if (isset($_POST['language'])) {
                $updates[] = 'language = ?';
                $params[] = $_POST['language'];
                $types .= 's';
            }
            if (isset($_POST['profile_public'])) {
                $updates[] = 'profile_public = ?';
                $params[] = (int)$_POST['profile_public'];
                $types .= 'i';
            }
            break;

        case 'interests':
            $interests_fields = ['favorite_music', 'favorite_films', 'favorite_books', 'favorite_places', 'hobbies'];

            foreach ($interests_fields as $field) {
                if (isset($_POST[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = !empty($_POST[$field]) ? trim($_POST[$field]) : null;
                    $types .= 's';
                }
            }
            break;

        default:
            $response['message'] = 'Invalid section';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
    }

    // If we have updates to make
    if (!empty($updates)) {
        try {
            // Start transaction
            $conn->begin_transaction();

            // Prepare the SQL query
            $sql = "UPDATE signup SET " . implode(', ', $updates) . " WHERE user_id = ?";
            $stmt = $conn->prepare($sql);

            // Add user_id to params
            $params[] = $user_id;
            $types .= 'i';

            // Bind parameters
            $stmt->bind_param($types, ...$params);

            // Execute the query
            $result = $stmt->execute();

            if ($result) {
                $conn->commit();
                $response['success'] = true;
                $response['message'] = 'Profile updated successfully';

                // Update session data if full name was changed
                if (isset($_POST['full_name'])) {
                    $_SESSION['full_name'] = trim($_POST['full_name']);
                }
            } else {
                $conn->rollback();
                $response['message'] = 'Failed to update profile: ' . $stmt->error;
            }

            $stmt->close();
        } catch (Exception $e) {
            $conn->rollback();
            $response['message'] = 'Error: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'No changes to update';
    }
} else {
    $response['message'] = 'Invalid request';
}

// Return response
if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // For regular form submission, redirect back to profile with message
    $message = urlencode($response['message']);
    $success = $response['success'] ? 'success' : 'error';
    header("Location: profile.php?update=$success&message=$message");
    exit;
}
?>
