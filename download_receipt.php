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
                        // Payment not found - show specific error
                        $error_message = "Payment with ID $payment_id not found or does not belong to your account. Please check if you are logged in with the correct account.";
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
        $error_message = "No payment ID provided.";
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
    error_log("Download receipt user data query failed: " . $e->getMessage());
}

// Ensure we have at least some user data
if (!$user_name || $user_name === 'N/A') {
    $user_name = 'Valued Customer';
}
if (!$user_email || $user_email === 'N/A') {
    $user_email = 'customer@example.com';
}

$conn->close();

// Clear the receipt data from session after displaying
unset($_SESSION['payment_receipt_data']);

// Generate PDF receipt using TCPDF (if available) or simple HTML to PDF
require_once('vendor/autoload.php'); // If using Composer

// For now, let's use a simple approach - generate HTML and convert to PDF using Dompdf
try {
    // Check if Dompdf is available
    if (file_exists('vendor/dompdf/dompdf/autoload.inc.php')) {
        require_once 'vendor/dompdf/dompdf/autoload.inc.php';
        $dompdf = new Dompdf\Dompdf();

        // Create HTML content for PDF
        $html = generateReceiptHTML($user, $receipt_data);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Set headers for PDF download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="receipt_' . $receipt_data['payment_id'] . '.pdf"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $dompdf->output();
        exit();

    } elseif (class_exists('TCPDF')) {
        // Use TCPDF if available
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('My Private Diary');
        $pdf->SetAuthor($user['full_name']);
        $pdf->SetTitle('Payment Receipt');
        $pdf->SetSubject('Payment Receipt - #' . $receipt_data['payment_id']);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 12);

        // Create HTML content for PDF
        $html = generateReceiptHTML($user, $receipt_data);

        // Write HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        // Output PDF
        $pdf->Output('receipt_' . $receipt_data['payment_id'] . '.pdf', 'D');
        exit();

    } else {
        // Fallback: Simple HTML download or print
        header('Content-Type: text/html');
        header('Content-Disposition: attachment; filename="receipt_' . $receipt_data['payment_id'] . '.html"');
        echo generateReceiptHTML($user, $receipt_data);
        exit();
    }

} catch (Exception $e) {
    // Fallback to simple print page if PDF libraries are not available
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Receipt - My Private Diary</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            body {
                font-family: 'Arial', sans-serif;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                background: #f8f9fa;
            }

            .receipt-container {
                background: white;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            }

            .header {
                text-align: center;
                margin-bottom: 30px;
                padding-bottom: 20px;
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
                margin: 30px 0;
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
                padding: 20px;
                border-radius: 8px;
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
                padding-top: 20px;
                border-top: 1px solid #eee;
                color: #666;
                font-size: 14px;
            }

            .download-buttons {
                text-align: center;
                margin: 30px 0;
            }

            .btn {
                display: inline-block;
                padding: 12px 24px;
                margin: 0 10px;
                background: #ff9fb0;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                font-weight: bold;
                transition: background 0.3s;
                border: none;
                cursor: pointer;
            }

            .btn:hover {
                background: #ff7a93;
            }

            .btn-secondary {
                background: #6c757d;
            }

            .btn-secondary:hover {
                background: #545b62;
            }

            @media (max-width: 768px) {
                .receipt-info {
                    grid-template-columns: 1fr;
                }

                body {
                    padding: 10px;
                }

                .receipt-container {
                    padding: 20px;
                }
            }

            @media print {
                body {
                    background: white;
                    padding: 0;
                }

                .download-buttons {
                    display: none;
                }

                .receipt-container {
                    box-shadow: none;
                    border: 1px solid #ddd;
                }
            }
        </style>
    </head>
    <body>
        <div class="receipt-container">
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
                <button onclick="window.print()" class="btn">
                    🖨️ Print Receipt
                </button>
                <a href="premium.php" class="btn btn-secondary">
                    ← Back to Premium
                </a>
            </div>
        </div>

        <script>
            // Auto-print when page loads
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 500);
            };
        </script>
    </body>
    </html>
    <?php
}

function generateReceiptHTML($user, $receipt_data) {
    $user_id = $_SESSION['user_id'];

    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Payment Receipt</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 600px;
                margin: 0 auto;
                padding: 40px 20px;
                color: #333;
                line-height: 1.6;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 3px solid #ff9fb0;
            }
            .logo {
                font-size: 28px;
                font-weight: bold;
                color: #ff9fb0;
                margin-bottom: 10px;
            }
            .receipt-title {
                font-size: 24px;
                color: #333;
                margin: 0;
            }
            .receipt-info {
                display: table;
                width: 100%;
                margin: 20px 0;
            }
            .info-row {
                display: table-row;
            }
            .info-cell {
                display: table-cell;
                padding: 8px;
                border-bottom: 1px solid #eee;
            }
            .info-label {
                font-weight: bold;
                color: #666;
                width: 40%;
            }
            .info-value {
                color: #333;
                width: 60%;
            }
            .payment-details {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                text-align: center;
            }
            .amount {
                font-size: 36px;
                font-weight: bold;
                color: #10b981;
                margin: 15px 0;
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
                padding-top: 20px;
                border-top: 1px solid #eee;
                color: #666;
                font-size: 14px;
            }
            .success-badge {
                color: #10b981;
                font-weight: bold;
                font-size: 16px;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="logo">📔 My Private Diary</div>
            <h1 class="receipt-title">Payment Receipt</h1>
        </div>

        <div class="receipt-info">
            <div class="info-row">
                <div class="info-cell info-label">Customer Name:</div>
                <div class="info-cell info-value">' . htmlspecialchars($user_name) . '</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Email:</div>
                <div class="info-cell info-value">' . htmlspecialchars($user_email) . '</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Customer ID:</div>
                <div class="info-cell info-value">' . $user_id . '</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Receipt ID:</div>
                <div class="info-cell info-value">#' . htmlspecialchars($receipt_data['payment_id'] ?? 'N/A') . '</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Transaction ID:</div>
                <div class="info-cell info-value">' . htmlspecialchars($receipt_data['transaction_id'] ?? 'N/A') . '</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Date & Time:</div>
                <div class="info-cell info-value">' . (isset($receipt_data['payment_date']) ? date('M d, Y h:i A', strtotime($receipt_data['payment_date'])) : 'N/A') . '</div>
            </div>
        </div>

        <div class="payment-details">
            <h3 style="color: #ff9fb0; margin-bottom: 15px;">Payment Information</h3>
            <div style="margin-bottom: 10px;">
                <strong>Plan:</strong> <span class="plan-badge">' . htmlspecialchars(ucfirst($receipt_data['plan_type'] ?? 'N/A')) . '</span>
            </div>
            <div style="margin-bottom: 10px;">
                <strong>Payment Method:</strong> ' . htmlspecialchars(ucfirst($receipt_data['payment_method'] ?? 'N/A')) . '
            </div>
            <div class="amount">₹' . (isset($receipt_data['amount']) ? number_format($receipt_data['amount'], 2) : '0.00') . '</div>
            <div class="success-badge">✓ Payment Successful</div>
        </div>

        <div class="footer">
            <p><strong>Thank you for choosing My Private Diary!</strong></p>
            <p>This receipt confirms your payment and subscription upgrade.</p>
            <p>For any queries, please contact our support team.</p>
            <p style="margin-top: 15px; font-size: 12px;">
                Generated on ' . date('M d, Y \a\t h:i A') . ' | Receipt ID: #' . htmlspecialchars($receipt_data['payment_id'] ?? 'N/A') . '
            </p>
        </div>
    </body>
    </html>';
}
?>

