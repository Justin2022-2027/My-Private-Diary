<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

$user_id = $_SESSION['user_id'];

// Check if payment receipt data exists
if (!isset($_SESSION['payment_receipt_data'])) {
    // Check if payment_id is provided via GET parameter
    if (isset($_GET['payment_id']) && is_numeric($_GET['payment_id'])) {
        $payment_id = (int)$_GET['payment_id'];

        // Validate payment_id
        if ($payment_id < 0 || !is_numeric($_GET['payment_id'])) {
            $error_message = "Invalid payment ID provided.";
        } else {
            // Get payment data from database
            $stmt = $conn->prepare("SELECT * FROM payments WHERE payment_id = ? AND user_id = ?");
            if ($stmt) {
                $stmt->bind_param("ii", $payment_id, $user_id);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    if ($result && $result->num_rows > 0) {
                        $payment = $result->fetch_assoc();

                        // Validate payment data
                        if (empty($payment['payment_id'])) {
                            $error_message = "Payment data is incomplete.";
                        } else {
                            // Set receipt data in session
                            $_SESSION['payment_receipt_data'] = [
                                'payment_id' => $payment['payment_id'],
                                'transaction_id' => $payment['transaction_id'],
                                'amount' => $payment['amount'],
                                'plan_type' => $payment['plan_type'],
                                'payment_method' => $payment['payment_method'],
                                'payment_date' => $payment['created_at']
                            ];
                        }
                    } else {
                        // Payment not found - try to find the most recent payment for this user as fallback
                        $fallback_stmt = $conn->prepare("SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
                        if ($fallback_stmt) {
                            $fallback_stmt->bind_param("i", $user_id);
                            if ($fallback_stmt->execute()) {
                                $fallback_result = $fallback_stmt->get_result();
                                if ($fallback_result && $fallback_result->num_rows > 0) {
                                    $fallback_payment = $fallback_result->fetch_assoc();

                                    // Use the most recent payment as fallback
                                    $_SESSION['payment_receipt_data'] = [
                                        'payment_id' => $fallback_payment['payment_id'],
                                        'transaction_id' => $fallback_payment['transaction_id'],
                                        'amount' => $fallback_payment['amount'],
                                        'plan_type' => $fallback_payment['plan_type'],
                                        'payment_method' => $fallback_payment['payment_method'],
                                        'payment_date' => $fallback_payment['created_at']
                                    ];
                                    error_log("Used fallback payment ID: " . $fallback_payment['payment_id'] . " for user: $user_id");
                                } else {
                                    $error_message = "Payment with ID $payment_id not found or does not belong to your account. Please check if you are logged in with the correct account.";
                                    error_log("Payment query failed: SELECT * FROM payments WHERE payment_id = $payment_id AND user_id = $user_id");
                                }
                            }
                            $fallback_stmt->close();
                        } else {
                            $error_message = "Payment with ID $payment_id not found or does not belong to your account. Please check if you are logged in with the correct account.";
                        }
                    }
                } else {
                    // Query execution failed
                    $error_message = "Database query failed. Please try again later.";
                }
                $stmt->close();
            } else {
                // Statement preparation failed
                $error_message = "Database connection error. Please contact support.";
            }
        }
    } else {
        // No payment_id provided
        $error_message = "No payment ID provided. Please access this page through a valid payment link.";
    }

    // If still no receipt data, show error
    if (!isset($_SESSION['payment_receipt_data'])) {
        $error_message = $error_message ?? "Unable to load receipt data. Please try again.";
    }
}

$receipt_data = $_SESSION['payment_receipt_data'] ?? null;

// Check if payment receipt data exists and has required fields
if (!$receipt_data || !is_array($receipt_data)) {
    $error_message = $error_message ?? "Receipt data not found or invalid. Please try again.";
    die("<h2 style='color: #ff7a93; text-align:center; margin-top:3rem;'>$error_message</h2>");
}

// Ensure all required fields exist with fallback values
$receipt_data = array_merge([
    'payment_id' => null,
    'transaction_id' => null,
    'amount' => 0,
    'plan_type' => null,
    'payment_method' => null,
    'payment_date' => null
], $receipt_data);

// Get user details with better error handling
$user = null;
$user_name = $_SESSION['full_name'] ?? 'Valued Customer';
$user_email = $_SESSION['email'] ?? 'N/A';

try {
    $stmt = $conn->prepare("SELECT full_name, email FROM users WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $user_name = $user['full_name'] ?? $user_name;
                $user_email = $user['email'] ?? $user_email;
            }
        }
        $stmt->close();
    }
} catch (Exception $e) {
    // Use session data as fallback if database query fails
    error_log("Receipt user data query failed: " . $e->getMessage());
}

// Ensure we have at least some user data
if (!$user_name || $user_name === 'N/A') {
    $user_name = 'Valued Customer';
}
if (!$user_email || $user_email === 'N/A') {
    $user_email = 'customer@example.com';
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - My Private Diary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ff9fb0;
            --primary-dark: #ff7a93;
            --success: #10b981;
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
            padding: 2rem;
        }

        .receipt-container {
            max-width: 600px;
            width: 100%;
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 40px 40px 20px;
            border-bottom: 2px solid #ff9fb0;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #ff9fb0;
            margin-bottom: 10px;
        }

        .receipt-title {
            font-size: 28px;
            color: #333;
            margin: 0;
        }

        .receipt-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 30px 40px;
        }

        .info-section h3 {
            color: #ff9fb0;
            margin-bottom: 15px;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-item {
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
        }

        .info-label {
            font-weight: bold;
            color: #666;
        }

        .info-value {
            color: #333;
        }

        .payment-details {
            background: #f8f9fa;
            padding: 20px 40px;
            margin: 20px 0;
        }

        .payment-details h3 {
            color: #ff9fb0;
            margin-bottom: 15px;
            text-align: center;
        }

        .amount {
            font-size: 32px;
            font-weight: bold;
            color: #10b981;
            text-align: center;
            margin: 20px 0;
        }

        .plan-badge {
            display: inline-block;
            background: #ff9fb0;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px 40px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }

        .download-buttons {
            text-align: center;
            padding: 30px 40px;
            background: #fff3cd;
            border-top: 1px solid #ffeaa7;
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

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        @media (max-width: 768px) {
            .receipt-info {
                grid-template-columns: 1fr;
            }

            body {
                padding: 1rem;
            }

            .receipt-container {
                margin: 0;
            }

            .header, .receipt-info, .payment-details, .footer, .download-buttons {
                padding-left: 20px;
                padding-right: 20px;
            }
        }

        @media print {
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .download-buttons {
                display: none !important;
            }

            .receipt-container {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                max-width: none !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 20px !important;
            }

            .header, .receipt-info, .payment-details, .footer {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            .btn {
                display: none !important;
            }

            .amount {
                font-size: 24px !important;
            }

            .plan-badge {
                border: 1px solid #333 !important;
                color: #333 !important;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <?php if (!$receipt_data || !isset($receipt_data['payment_id']) || $receipt_data['payment_id'] === null || $receipt_data['payment_id'] === ''): ?>
            <div style="text-align: center; padding: 3rem; color: #ff7a93;">
                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <h2>Receipt Error</h2>
                <p>There was an issue loading your receipt. Please try again or contact support if the problem persists.</p>
                <a href="premium.php" class="btn btn-primary" style="margin-top: 1rem;">Back to Premium</a>
                <?php if (isset($_GET['payment_id'])): ?>
                <p style="margin-top: 1rem; font-size: 0.9rem; color: #666;">
                    Debug: Payment ID <?php echo htmlspecialchars($_GET['payment_id']); ?> -
                    <?php if (!$receipt_data): ?>No receipt data found<?php endif; ?>
                    <?php if ($receipt_data && !isset($receipt_data['payment_id'])): ?>Payment ID not set in receipt data<?php endif; ?>
                    <?php if ($receipt_data && isset($receipt_data['payment_id']) && ($receipt_data['payment_id'] === null || $receipt_data['payment_id'] === '')): ?>Payment ID is null or empty<?php endif; ?>
                </p>
                <?php endif; ?>
            </div>
        <?php else: ?>
        <div class="header">
            <div class="logo">📔 My Private Diary</div>
            <h1 class="receipt-title">Payment Receipt</h1>
        </div>

        <div class="receipt-info">
            <div class="info-section">
                <h3>Customer Information</h3>
                <div class="info-item">
                    <span class="info-label">Name:</span>
                    <span class="info-value"><?php echo htmlspecialchars($user_name); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo htmlspecialchars($user_email); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Customer ID:</span>
                    <span class="info-value"><?php echo $user_id; ?></span>
                </div>
            </div>

            <div class="info-section">
                <h3>Transaction Details</h3>
                <div class="info-item">
                    <span class="info-label">Receipt ID:</span>
                    <span class="info-value">#<?php echo htmlspecialchars($receipt_data['payment_id'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Transaction ID:</span>
                    <span class="info-value"><?php echo htmlspecialchars($receipt_data['transaction_id'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Date:</span>
                    <span class="info-value"><?php echo isset($receipt_data['payment_date']) ? date('M d, Y', strtotime($receipt_data['payment_date'])) : 'N/A'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Time:</span>
                    <span class="info-value"><?php echo isset($receipt_data['payment_date']) ? date('h:i A', strtotime($receipt_data['payment_date'])) : 'N/A'; ?></span>
                </div>
            </div>
        </div>

        <div class="payment-details">
            <h3>Payment Information</h3>
            <div class="info-item">
                <span class="info-label">Plan:</span>
                <span class="info-value">
                    <span class="plan-badge"><?php echo htmlspecialchars(ucfirst($receipt_data['plan_type'] ?? 'N/A')); ?></span>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Payment Method:</span>
                <span class="info-value"><?php echo htmlspecialchars(ucfirst($receipt_data['payment_method'] ?? 'N/A')); ?></span>
            </div>
            <div class="amount">₹<?php echo isset($receipt_data['amount']) ? number_format($receipt_data['amount'], 2) : '0.00'; ?></div>
            <div style="text-align: center; color: #10b981; font-weight: bold;">
                <i class="fas fa-check-circle"></i> Payment Successful
            </div>
        </div>

        <div class="footer">
            <p><strong>Thank you for choosing My Private Diary!</strong></p>
            <p>This receipt confirms your payment and subscription upgrade.</p>
            <p>For any queries, please contact our support team.</p>
            <p style="margin-top: 15px; font-size: 12px;">
                Generated on <?php echo date('M d, Y \a\t h:i A'); ?> |
                Receipt ID: #<?php echo htmlspecialchars($receipt_data['payment_id'] ?? 'N/A'); ?>
            </p>
        </div>

        <div class="download-buttons">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print Receipt
            </button>
            <a href="premium.php" class="btn btn-outline">
                Back to Premium
            </a>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>

<?php
// Clear the receipt data from session after displaying
unset($_SESSION['payment_receipt_data']);
?>
