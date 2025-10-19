<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Basic validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    }
    
    // If no errors, process the form
    if (empty($errors)) {
        // In a real application, you would typically:
        // 1. Save to database
        // 2. Send an email notification
        // 3. Process the contact request
        
        // For now, we'll just set a success message
        $_SESSION['message'] = 'Thank you for your message! We will get back to you soon.';
        $_SESSION['message_type'] = 'success';
    } else {
        // Set error messages
        $_SESSION['message'] = implode('<br>', $errors);
        $_SESSION['message_type'] = 'error';
        
        // Store form data to repopulate the form
        $_SESSION['form_data'] = [
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message
        ];
    }
    
    // Redirect back to contact page
    header('Location: contact.php');
    exit();
} else {
    // If someone tries to access this file directly
    header('Location: contact.php');
    exit();
}
