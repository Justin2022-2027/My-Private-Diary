<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

// Razorpay API credentials
$keyId = 'rzp_test_RRsJPhPnB9WOzv';
$keySecret = 'Kiw5lvwHWhCtG82VHoMurjbg';

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Function to verify Razorpay payment signature
function verifyPaymentSignature($order_id, $payment_id, $signature, $key_secret) {
    $generated_signature = hash_hmac('sha256', $order_id . '|' . $payment_id, $key_secret);
    return hash_equals($generated_signature, $signature);
}

// Function to make API calls to Razorpay
function makeRazorpayApiCall($url, $method, $data = null, $key_id, $key_secret) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode($key_id . ':' . $key_secret),
        'Content-Type: application/json'
    ]);
    
    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code >= 200 && $http_code < 300) {
        return json_decode($response, true);
    } else {
        throw new Exception('API call failed: ' . $response);
    }
}

// Handle payment processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $order_id = $_POST['razorpay_order_id'];
        $payment_id = $_POST['razorpay_payment_id'];
        $signature = $_POST['razorpay_signature'];
        $plan_type = $_POST['plan_type'];

        // Verify payment signature
        if (!verifyPaymentSignature($order_id, $payment_id, $signature, $keySecret)) {
            throw new Exception('Payment signature verification failed');
        }

        // Get payment details from Razorpay API
        $payment_url = "https://api.razorpay.com/v1/payments/" . $payment_id;
        $payment_data = makeRazorpayApiCall($payment_url, 'GET', null, $keyId, $keySecret);
        
        $amount = $payment_data['amount'] / 100; // Convert from paise to rupees

        // Start transaction
        $conn->begin_transaction();

        try {
            // Insert payment record
            $stmt = $conn->prepare("INSERT INTO payments (user_id, amount, payment_method, transaction_id, plan_type, status) VALUES (?, ?, ?, ?, ?, 'completed')");
            $payment_method = $payment_data['method'] ?? 'razorpay';
            $stmt->bind_param("idsss", $user_id, $amount, $payment_method, $payment_id, $plan_type);
            $stmt->execute();
            $stmt->close();

            // Update user's subscription
            $subscription_start = date('Y-m-d H:i:s');
            $subscription_end = ($plan_type === 'lifetime') ? NULL : date('Y-m-d H:i:s', strtotime('+1 month'));

            $stmt = $conn->prepare("UPDATE users SET subscription_plan = ?, subscription_status = 'active', subscription_start_date = ?, subscription_end_date = ? WHERE user_id = ?");
            $stmt->bind_param("sssi", $plan_type, $subscription_start, $subscription_end, $user_id);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $conn->commit();

            $_SESSION['success_message'] = "Payment successful! Your account has been upgraded.";
            $_SESSION['payment_receipt_data'] = [
                'payment_id' => $conn->insert_id,
                'transaction_id' => $payment_id,
                'amount' => $amount,
                'plan_type' => $plan_type,
                'payment_method' => $payment_method,
                'payment_date' => $subscription_start
            ];
            $_SESSION['latest_payment_id'] = $conn->insert_id; // Set latest payment ID for highlighting
            header("Location: premium.php");
            exit();

        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            throw $e;
        }

    } catch (Exception $e) {
        $message = "Payment verification failed: " . $e->getMessage();
        $message_type = "error";
    }
}

// Create order for new payment
if (isset($_GET['plan'])) {
    $plan = $_GET['plan'];
    $amount = ($plan === 'premium') ? 500 : (($plan === 'lifetime') ? 2000 : 0);

    if ($amount <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid plan selected']);
        exit();
    }

    $orderData = [
        'receipt' => 'order_' . time() . '_' . $user_id,
        'amount' => $amount * 100, // Convert to paise
        'currency' => 'INR',
        'notes' => [
            'plan_type' => $plan,
            'user_id' => $user_id
        ]
    ];

    try {
        $order_url = "https://api.razorpay.com/v1/orders";
        $order_response = makeRazorpayApiCall($order_url, 'POST', $orderData, $keyId, $keySecret);
        
        // Return order data for frontend
        header('Content-Type: application/json');
        echo json_encode([
            'order_id' => $order_response['id'],
            'amount' => $amount * 100,
            'currency' => 'INR',
            'plan_type' => $plan
        ]);
        exit();

    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
        exit();
    }
}

// If accessing the page directly (not via GET or POST), redirect to premium page
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: premium.php");
    exit();
}

// If no specific action is being performed, show success message or redirect
if (!isset($_GET['plan']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Check if there's a success message in session
    if (isset($_SESSION['success_message'])) {
        $success_message = $_SESSION['success_message'];
        unset($_SESSION['success_message']);
    } else {
        // Redirect to premium page if no message
        header("Location: premium.php");
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status - My Private Diary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ff9fb0;
            --primary-dark: #ff7a93;
            --success: #10b981;
            --danger: #ef4444;
            --light: #f8fafc;
            --dark: #1e293b;
            --white: #ffffff;
            --shadow: 0 4px 16px rgba(0,0,0,0.07);
            --radius: 1.25rem;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--light);
            color: var(--dark);
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .payment-container {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            text-align: center;
        }
        
        .payment-icon {
            font-size: 4rem;
            color: var(--success);
            margin-bottom: 1.5rem;
        }
        
        .payment-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
        }
        
        .payment-message {
            color: var(--dark);
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
            border: none;
            cursor: pointer;
            margin: 0.5rem;
        }
        
        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn-outline:hover {
            background: var(--primary);
            color: var(--white);
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <?php if (isset($success_message)): ?>
            <div class="payment-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="payment-title">Payment Successful!</h1>
            <p class="payment-message"><?php echo htmlspecialchars($success_message); ?></p>
            <a href="premium.php" class="btn btn-primary">
                <i class="fas fa-crown"></i>
                View Premium Features
            </a>
            <a href="dashboard.php" class="btn btn-outline">
                <i class="fas fa-home"></i>
                Go to Dashboard
            </a>
        <?php elseif (isset($message) && $message_type === 'error'): ?>
            <div class="payment-icon" style="color: var(--danger);">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h1 class="payment-title">Payment Failed</h1>
            <p class="payment-message"><?php echo htmlspecialchars($message); ?></p>
            <a href="premium.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i>
                Try Again
            </a>
            <a href="dashboard.php" class="btn btn-outline">
                <i class="fas fa-home"></i>
                Go to Dashboard
            </a>
        <?php else: ?>
            <div class="payment-icon">
                <i class="fas fa-info-circle"></i>
            </div>
            <h1 class="payment-title">Payment Processing</h1>
            <p class="payment-message">Please wait while we process your payment...</p>
            <script>
                // Auto redirect after 3 seconds
                setTimeout(function() {
                    window.location.href = 'premium.php';
                }, 3000);
            </script>
            <a href="premium.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i>
                Back to Premium Plans
            </a>
        <?php endif; ?>
    </div>
</body>
</html> 