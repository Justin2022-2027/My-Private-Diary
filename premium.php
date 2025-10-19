<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle clearing success message
if (isset($_GET['clear_message'])) {
    unset($_SESSION['success_message']);
    unset($_SESSION['payment_receipt_data']);
    unset($_SESSION['latest_payment_id']); // Clear latest payment highlighting
    header("Location: premium.php");
    exit();
}

require 'db_connect.php';

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Define subscription plans
$plans = [
    'basic' => [
        'name' => 'Basic',
        'price' => 'Free',
        'features' => [
            'Unlimited diary entries',
            'Essential themes',
            'Simple mood tracking',
            'Calendar view',
            'Basic reminders',
        ],
        'button_text' => 'Basic Plan'
    ],
    'premium' => [
        'name' => 'Premium',
        'price' => '₹500',
        'period' => 'month',
        'features' => [
            'All Basic features',
            'Advanced mood analytics',
            'Premium themes',
            'Cloud backup & sync',
            'Export diary entries (PDF/CSV)',
            'Multiple reminders',
            'Priority email support',
            'Ad-free experience',
        ],
        'button_text' => 'Premium Plan'
    ],
    'lifetime' => [
        'name' => 'Lifetime',
        'price' => '₹2000',
        'period' => 'one-time',
        'features' => [
            'All Premium features',
            'Lifetime access (one-time payment)',
            'Early access to new features',
            'Personal diary consultant',
            'Custom theme creation',
            'Exclusive webinars & workshops',
        ],
        'button_text' => 'Lifetime Plan'
    ]
];

// Get user's current plan
$stmt = $conn->prepare("SELECT subscription_plan FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$current_plan = $user['subscription_plan'] ?? 'basic';
$stmt->close();

// Check for success message from payment
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Get user's payment history
$payments = [];
$payment_query = $conn->prepare("SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
if ($payment_query) {
    $payment_query->bind_param("i", $user_id);
    if ($payment_query->execute()) {
        $result = $payment_query->get_result();
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
    }
    $payment_query->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Features - My Private Diary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Reuse root variables from dashboard.php */
        :root {
            --primary: hsl(351, 63%, 76%);
            --primary-dark: hwb(350 76% 0%);
            --secondary: #f59e0b;
            --success: #10b981;
            --danger: #ef4444;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray: #64748b;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --transition: all 0.3s ease;
        }

        .premium-header {
            background: var(--white);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            text-align: center;
        }

        .premium-header h2 {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .premium-header p {
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto;
        }

        .plans-grid {
            display: flex;
            flex-direction: row;
            gap: 2rem;
            margin-bottom: 2rem;
            align-items: flex-start;
            justify-content: center;
            flex-wrap: wrap;
        }

        .plan-card {
            width: 100%;
            max-width: 370px;
            min-height: 540px;
            background: var(--white);
            padding: 2rem 1.5rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: stretch;
            position: relative;
            border: 2px solid #f3f3f3;
            justify-content: flex-start;
        }

        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .plan-card.popular {
            border: 2px solid var(--primary);
        }

        .plan-card.lifetime {
            border: 2px solid var(--secondary);
        }

        .plan-card.current {
            border: 2px solid var(--success);
            background: #f0fdf4;
        }

        .plan-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .plan-price {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.25rem;
            text-align: center;
        }

        .plan-period {
            color: var(--gray);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .plan-features {
            list-style: none;
            padding: 0;
            margin: 0 0 2rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .plan-features li {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            color: var(--dark);
            background: #f8fafc;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            font-size: 1rem;
        }

        .plan-features i {
            color: var(--success);
            margin-top: 0.15rem;
        }

        .btn {
            margin-top: auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.85rem 1.5rem;
            border-radius: 0.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            border: none;
            cursor: pointer;
            width: 100%;
            box-shadow: 0 2px 8px rgba(237,157,157,0.08);
        }

        .btn-primary {
            background: linear-gradient(90deg, #ff9fb0 0%, #ffb8c6 100%);
            color: #fff;
            box-shadow: 0 4px 16px rgba(255,159,176,0.15);
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #ff7a93 0%, #ffb8c6 100%);
            color: #fff;
            box-shadow: 0 6px 20px rgba(255,159,176,0.22);
        }

        .btn-outline {
            border: 2px solid #ff9fb0;
            color: #ff9fb0;
            background: #fff;
        }

        .btn-outline:hover {
            background: #ff9fb0;
            color: #fff;
            box-shadow: 0 4px 16px rgba(255,159,176,0.15);
        }

        .btn-disabled {
            background: #e5e7eb;
            color: #b0b0b0;
            cursor: not-allowed;
            opacity: 0.7;
            border: none;
        }

        @media (max-width: 900px) {
            .plans-grid {
                flex-direction: column;
                align-items: center;
            }
            .plan-card {
                max-width: 100%;
                min-height: 0;
            }
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 3rem;
        }

        .feature-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            background: var(--light);
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .feature-icon i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .feature-description {
            color: var(--gray);
            font-size: 0.875rem;
        }

        /* Payment History Styles */
        .payment-history-section {
            margin-top: 3rem;
            padding-top: 3rem;
            border-top: 2px solid var(--light);
        }

        .section-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .section-header h2 {
            color: var(--dark);
            margin-bottom: 1rem;
            font-size: 2rem;
        }

        .payment-history {
            display: grid;
            gap: 1.5rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .payment-card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            transition: var(--transition);
            border: 2px solid transparent;
        }

        .payment-card.latest-payment {
            border-color: var(--primary);
            background: linear-gradient(135deg, var(--primary-light), var(--white));
        }

        .payment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .payment-info h4 {
            margin: 0 0 0.5rem 0;
            color: var(--dark);
            font-size: 1.25rem;
        }

        .payment-date {
            color: var(--gray);
            font-size: 0.9rem;
            margin: 0;
        }

        .payment-amount {
            text-align: right;
        }

        .payment-amount .amount {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 0.25rem;
        }

        .status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status.completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status.pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status.failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .payment-details {
            margin: 1rem 0;
            padding: 1rem;
            background: var(--light);
            border-radius: var(--radius-sm);
        }

        .detail-item {
            display: flex;
            margin-bottom: 0.5rem;
        }

        .detail-item:last-child {
            margin-bottom: 0;
        }

        .detail-label {
            font-weight: 600;
            color: var(--dark);
            min-width: 120px;
            margin-right: 1rem;
        }

        .detail-value {
            color: var(--gray);
        }

        .payment-actions {
            margin-top: 1rem;
            text-align: right;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        @media (max-width: 768px) {
            .payment-header {
                flex-direction: column;
                gap: 1rem;
            }

            .payment-amount {
                text-align: left;
            }

            .detail-item {
                flex-direction: column;
            }

            .detail-label {
                margin-bottom: 0.25rem;
                margin-right: 0;
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <?php if (!empty($success_message) && isset($_GET['show_receipt'])): ?>
            <div style="background: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem; text-align: center;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
            
        <h2 style="text-align:center; margin-bottom:1.5rem; color:var(--primary);">Choose Your Plan</h2>

            <div class="plans-grid">
                <?php foreach ($plans as $id => $plan): ?>
                <div class="plan-card <?php echo $id === 'premium' ? 'popular' : ''; ?><?php echo $current_plan === $id ? ' current' : ''; ?>">
                    <?php if ($id === 'premium'): ?>
                    <span class="popular-badge">Most Popular</span>
                    <?php endif; ?>
                        
                    <h3 class="plan-name"><?php echo $plan['name']; ?></h3>
                    <div class="plan-price"><?php echo $plan['price']; ?></div>
                    <?php if (isset($plan['period'])): ?>
                    <div class="plan-period">per <?php echo $plan['period']; ?></div>
                    <?php endif; ?>

                    <ul class="plan-features">
                        <?php foreach ($plan['features'] as $feature): ?>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <?php echo $feature; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if ($current_plan === $id): ?>
                    <button class="btn btn-disabled" disabled>
                        <?php echo $plan['button_text']; ?>
                    </button>
                    <?php else: ?>
                    <button class="btn <?php echo $id === 'premium' ? 'btn-primary' : 'btn-outline'; ?>"
                            onclick="upgradePlan('<?php echo $id; ?>')">
                        <?php echo $plan['button_text']; ?>
                    </button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="feature-title">Advanced Analytics</h3>
                    <p class="feature-description">
                        Get detailed insights into your mood patterns and writing habits with advanced analytics.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-cloud"></i>
                    </div>
                    <h3 class="feature-title">Cloud Backup</h3>
                    <p class="feature-description">
                        Keep your memories safe with automatic cloud backup and sync across devices.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-paint-brush"></i>
                    </div>
                    <h3 class="feature-title">Premium Themes</h3>
                    <p class="feature-description">
                        Access exclusive themes and customize your diary's appearance to match your style.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-export"></i>
                    </div>
                    <h3 class="feature-title">Export Options</h3>
                    <p class="feature-description">
                        Export your diary entries in multiple formats for backup or printing.
                    </p>
                </div>
            </div>

            <!-- Payment History Section -->
            <?php if (!empty($payments)): ?>
            <div class="payment-history-section" style="margin-top: 3rem; padding-top: 3rem; border-top: 2px solid var(--light);">
                <div class="section-header">
                    <h2 style="color: var(--dark); margin-bottom: 1rem;">
                        <i class="fas fa-history" style="color: var(--primary); margin-right: 0.5rem;"></i>
                        Payment History
                    </h2>
                    <p style="color: var(--gray); margin-bottom: 2rem;">View your recent payment transactions</p>
                </div>

                <div class="payment-history">
                    <?php foreach ($payments as $payment): ?>
                    <div class="payment-card <?php echo ($payment['payment_id'] == ($_SESSION['latest_payment_id'] ?? null)) ? 'latest-payment' : ''; ?>">
                        <div class="payment-header">
                            <div class="payment-info">
                                <h4><?php echo ucfirst($payment['plan_type']); ?> Plan</h4>
                                <p class="payment-date"><?php echo date('M d, Y', strtotime($payment['created_at'])); ?></p>
                            </div>
                            <div class="payment-amount">
                                <span class="amount">₹<?php echo number_format($payment['amount'], 2); ?></span>
                                <span class="status <?php echo $payment['status']; ?>"><?php echo ucfirst($payment['status']); ?></span>
                            </div>
                        </div>
                        <div class="payment-details">
                            <div class="detail-item">
                                <span class="detail-label">Transaction ID:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($payment['transaction_id']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Payment Method:</span>
                                <span class="detail-value"><?php echo ucfirst($payment['payment_method']); ?></span>
                            </div>
                        </div>
                        <div class="payment-actions">
                            <a href="receipt.php?payment_id=<?php echo $payment['payment_id']; ?>" class="btn btn-outline btn-sm">
                                <i class="fas fa-receipt"></i> View Receipt
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
            <!-- Debug information when no payments found -->
            <?php if (isset($_SESSION['user_id'])): ?>
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 1rem; border-radius: 0.5rem; margin: 2rem 0; text-align: center;">
                <i class="fas fa-info-circle" style="color: #856404; margin-right: 0.5rem;"></i>
                <strong>Debug Info:</strong> No payments found for user ID <?php echo $_SESSION['user_id']; ?>.
                <?php if (isset($_SESSION['latest_payment_id'])): ?>
                    Latest payment ID in session: <?php echo $_SESSION['latest_payment_id']; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        // Show payment success popup on page load if payment was successful
        <?php if (!empty($success_message) && isset($_SESSION['payment_receipt_data']) && !isset($_GET['show_receipt'])): ?>
        window.addEventListener('DOMContentLoaded', function() {
            // Check if popup is already shown to prevent duplicates
            if (!document.querySelector('.payment-success-popup')) {
                showPaymentSuccess();
            }
            // Scroll to payment history section after popup is closed
            setTimeout(function() {
                const paymentHistory = document.querySelector('.payment-history-section');
                if (paymentHistory) {
                    paymentHistory.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }, 500);
        });
        <?php endif; ?>

        function showPaymentSuccess() {
            // Prevent multiple popups
            if (document.querySelector('.payment-success-popup')) {
                return;
            }

            const popup = document.createElement('div');
            popup.className = 'payment-success-popup';
            popup.style.position = 'fixed';
            popup.style.top = '0';
            popup.style.left = '0';
            popup.style.width = '100vw';
            popup.style.height = '100vh';
            popup.style.background = 'rgba(0,0,0,0.5)';
            popup.style.display = 'flex';
            popup.style.alignItems = 'center';
            popup.style.justifyContent = 'center';
            popup.style.zIndex = '9999';
            popup.innerHTML = `
                <div style="background: #fff; border-radius: 1rem; padding: 2.5rem 2rem; box-shadow: 0 8px 32px rgba(0,0,0,0.3); text-align: center; max-width: 400px;">
                    <div style="font-size: 2.5rem; color: #10b981; margin-bottom: 1rem;"><i class='fas fa-check-circle'></i></div>
                    <h2 style="color: #10b981; margin-bottom: 0.5rem;">Payment Successful!</h2>
                    <p style="color: #333; margin-bottom: 1.5rem;">Thank you for upgrading! Your account has been upgraded successfully.</p>
                    <div style="margin-top: 1.5rem;">
                        <button onclick="window.location.href='receipt.php<?php echo isset($_SESSION['payment_receipt_data']['payment_id']) ? '?payment_id=' . $_SESSION['payment_receipt_data']['payment_id'] : ''; ?>'" style="margin: 0.25rem; padding: 0.7rem 1.5rem; background: #ff9fb0; color: #fff; border: none; border-radius: 0.5rem; font-size: 1rem; font-weight: 600; cursor: pointer;">View Receipt</button>
                        <button onclick="closeSuccessPopup()" style="margin: 0.25rem; padding: 0.7rem 1.5rem; background: #6c757d; color: #fff; border: none; border-radius: 0.5rem; font-size: 1rem; font-weight: 600; cursor: pointer;">Close</button>
                    </div>
                </div>
            `;
            document.body.appendChild(popup);
        }

        function closeSuccessPopup() {
            // Clear the success message from session and reload
            window.location.href = 'premium.php?clear_message=1';
        }

        async function upgradePlan(planId) {
            try {
                // Get order details from server
                const response = await fetch(`process_payment.php?plan=${planId}`);
                const data = await response.json();

                if (data.error) {
                    alert(data.error);
                    return;
                }

                // Configure Razorpay options
                const options = {
                    key: 'rzp_test_RRsJPhPnB9WOzv', // Razorpay Test Key ID
                    amount: data.amount,
                    currency: data.currency,
                    name: 'My Private Diary',
                    description: `${planId.charAt(0).toUpperCase() + planId.slice(1)} Plan Subscription`,
                    order_id: data.order_id,
                    handler: function(response) {
                        // Payment successful, the popup will show on page reload
                        // Submit payment details for verification
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'process_payment.php';

                        const fields = {
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_order_id: response.razorpay_order_id,
                            razorpay_signature: response.razorpay_signature,
                            plan_type: planId
                        };

                        for (const [key, value] of Object.entries(fields)) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = value;
                            form.appendChild(input);
                        }

                        document.body.appendChild(form);
                        form.submit();
                    },
                    prefill: {
                        name: '<?php echo htmlspecialchars($_SESSION['full_name']); ?>',
                        email: '<?php echo htmlspecialchars($_SESSION['email']); ?>'
                    },
                    theme: {
                        color: '#ff9fb0'
                    }
                };

                // Initialize Razorpay
                const razorpay = new Razorpay(options);
                razorpay.open();

            } catch (error) {
                alert('An error occurred. Please try again.');
                console.error(error);
            }
        }
    </script>
</body>
</html>