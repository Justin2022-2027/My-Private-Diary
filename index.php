<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Private Diary - Your Secure Digital Journal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php
    session_start();
    if (isset($_SESSION['user_id'])) {
        include 'includes/user_theme.php';
    }
    ?>
    <style>
        /* Modern CSS Variables */
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

        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: var(--light);
        }

        /* Utility Classes */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: white;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-primary:hover {
            background-color: var(--primary);
            color: var(--white);
            transform: translateY(-1px);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary);
            color: var(--white);
        }

        /* Header */
        .header {
            background-color: var(--white);
            box-shadow: var(--shadow-sm);
            position: fixed;
            width: 100%;
            z-index: 100;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 4rem;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--dark);
            font-weight: 700;
            font-size: 1.25rem;
        }
        
        .logo i {
            color: var(--primary);
            font-size: 1.5rem;
        }
        
        .nav-menu {
            display: flex;
            gap: 2rem;
        }
        
        .nav-link {
            color: var(--gray);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary);
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -0.25rem;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--primary);
            transition: var(--transition);
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        /* Hero Section */
        .hero {
            padding: 8rem 0 4rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--white);
        }
        
        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        
        /* Features Section */
        .features {
            padding: 6rem 0;
            background-color: var(--white);
        }
        
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .section-subtitle {
            text-align: center;
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto 3rem;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .feature-card {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .feature-icon {
            width: 3.5rem;
            height: 3.5rem;
            background-color: var(--primary);
            color: var(--white);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .feature-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .feature-description {
            color: var(--gray);
        }

        .feature-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1rem;
            text-align: center;
            opacity: 0;
            transition: var(--transition);
            border-radius: 1rem;
        }
        
        .feature-overlay:hover {
            opacity: 1;
        }
        
        .feature-overlay i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .feature-overlay p {
            color: var(--dark);
            margin-bottom: 1rem;
        }
        
        .feature-overlay .btn {
            margin-top: 0.5rem;
        }
        
        /* Testimonials */
        .testimonials {
            padding: 6rem 0;
            background-color: var(--light);
        }
        
        .testimonial-slider {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }
        
        .testimonial {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow);
            margin: 0 1rem;
        }
        
        .testimonial-text {
            font-size: 1.125rem;
            font-style: italic;
            margin-bottom: 1.5rem;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .author-avatar {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            background-color: var(--primary);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .author-name {
            font-weight: 600;
        }
        
        .author-title {
            color: var(--gray);
            font-size: 0.875rem;
        }
        
        /* Pricing */
        .pricing {
            padding: 6rem 0;
            background-color: var(--white);
        }
        
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .pricing-card {
            background-color: var(--white);
            border: 2px solid var(--primary);
            padding: 2rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-radius: 0;
            position: relative;
        }
        
        .pricing-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .pricing-card.featured {
            border: 2px solid var(--primary);
            transform: none;
        }
        
        .pricing-card.featured:hover {
            transform: translateY(-5px);
        }
        
        .pricing-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .pricing-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark);
        }
        
        .pricing-price {
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .pricing-header p {
            color: var(--gray);
            font-size: 1.1rem;
        }
        
        .pricing-features {
            margin-bottom: 2rem;
        }
        
        .pricing-feature {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            color: var(--dark);
            font-size: 1.1rem;
        }
        
        .pricing-feature i {
            color: var(--success);
            font-size: 1.2rem;
        }

        .pricing-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1rem;
            text-align: center;
            opacity: 0;
            transition: var(--transition);
        }
        
        .pricing-overlay:hover {
            opacity: 1;
        }
        
        .pricing-overlay i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .pricing-overlay p {
            color: var(--dark);
            margin-bottom: 1rem;
        }
        
        .pricing-overlay .btn {
            margin-top: 0.5rem;
        }
        
        /* Footer */
        .footer {
            background-color: var(--dark);
            color: var(--white);
            padding: 4rem 0 2rem;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 3rem;
            margin-bottom: 2rem;
        }
        
        .footer-column h3 {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.75rem;
        }
        
        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 3rem;
            height: 2px;
            background-color: var(--primary);
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-link {
            margin-bottom: 0.75rem;
        }
        
        .footer-link a {
            color: var(--gray);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .footer-link a:hover {
            color: var(--white);
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
        }
        
        .social-link {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        
        .social-link:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .copyright {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--gray);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-menu {
            display: none;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .pricing-card.featured {
                transform: none;
            }

            .pricing-card.featured:hover {
                transform: translateY(-5px);
            }
        }

        .modal {
            display: none;
                position: fixed;
            top: 0;
                left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: var(--white);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: var(--shadow-lg);
            max-width: 500px;
            width: 90%;
            text-align: center;
            position: relative;
            animation: modalFadeIn 0.3s ease;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
        }
        
        .modal-message {
            color: var(--gray);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        
        .modal-buttons {
            display: flex;
                gap: 1rem;
            justify-content: center;
        }
        
        .modal-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
        }
        
        .modal-btn-primary {
            background-color: var(--primary);
            color: var(--white);
            border: none;
        }
        
        .modal-btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }
        
        .modal-btn-secondary {
            background-color: var(--light);
            color: var(--dark);
            border: 2px solid var(--gray);
        }
        
        .modal-btn-secondary:hover {
            background-color: var(--gray);
            color: var(--white);
            transform: translateY(-1px);
        }
        
        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 1.5rem;
            transition: var(--transition);
        }
        
        .close-modal:hover {
            color: var(--dark);
            transform: rotate(90deg);
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
        <div class="header-content">
            <a href="#" class="logo">
                <i class="fas fa-book-open"></i>
                My Private Diary
            </a>
            
                <nav class="nav-menu">
                <a href="#features" class="nav-link">Features</a>
                <a href="testimonials.php" class="nav-link">Testimonials</a>
                <a href="premium.php" class="nav-link">Pricing</a>
                <a href="contact.php" class="nav-link">Contact</a>
            </nav>
            
            <div class="auth-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="btn btn-outline">Dashboard</a>
                    <a href="logout.php" class="btn btn-primary">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline">Login</a>
                    <a href="signup.php" class="btn btn-primary">Sign Up</a>
                <?php endif; ?>
            </div>
            </div>
        </div>
    </header>
    
    <section class="hero">
        <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Your Private Thoughts, Securely Preserved</h1>
            <p class="hero-subtitle">A personal and secure digital diary that keeps your memories, thoughts, and reflections organized and accessible from anywhere.</p>
                <div class="hero-buttons">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="write_entry.php" class="btn btn-primary">Start Writing Now</a>
                    <?php else: ?>
                        <a href="guest_entry.php" class="btn btn-primary">Start Writing Now</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    
    <section class="features" id="features">
        <div class="container">
        <h2 class="section-title">Why Choose My Private Diary?</h2>
        <p class="section-subtitle">Our digital diary combines the intimacy of traditional journaling with modern convenience and security.</p>
        
            <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h3 class="feature-title">100% Private & Secure</h3>
                <p class="feature-description">Your entries are encrypted and only accessible to you. We prioritize your privacy with industry-leading security standards.</p>
                    <div class="feature-overlay">
                        <i class="fas fa-lock"></i>
                        <p>Sign in to access this feature</p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary">Sign In</a>
                        <?php endif; ?>
                    </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-cloud"></i>
                </div>
                <h3 class="feature-title">Access Anywhere</h3>
                <p class="feature-description">Write and access your journal from any device with an internet connection. Your diary syncs automatically across all your devices.</p>
                    <div class="feature-overlay">
                        <i class="fas fa-lock"></i>
                        <p>Sign in to access this feature</p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary">Sign In</a>
                        <?php endif; ?>
                    </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="feature-title">Easy Organization</h3>
                <p class="feature-description">Tag entries, search through your content, and use the calendar view to find past memories with just a few clicks.</p>
                    <div class="feature-overlay">
                        <i class="fas fa-lock"></i>
                        <p>Sign in to access this feature</p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary">Sign In</a>
                        <?php endif; ?>
                    </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="feature-title">Mood Tracking</h3>
                <p class="feature-description">Add mood ratings to entries and visualize your emotional patterns over time with insightful charts and trends.</p>
                    <div class="feature-overlay">
                        <i class="fas fa-lock"></i>
                        <p>Sign in to access this feature</p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary">Sign In</a>
                        <?php endif; ?>
                    </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h3 class="feature-title">Journal Reminders</h3>
                <p class="feature-description">Set custom reminders to maintain your journaling habit and never miss a day of reflection.</p>
                    <div class="feature-overlay">
                        <i class="fas fa-lock"></i>
                        <p>Sign in to access this feature</p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary">Sign In</a>
                        <?php endif; ?>
                    </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-paint-brush"></i>
                </div>
                <h3 class="feature-title">Customizable Themes</h3>
                <p class="feature-description">Personalize your diary's appearance with various themes and layouts to match your style and preferences.</p>
                    <div class="feature-overlay">
                        <i class="fas fa-lock"></i>
                        <p>Sign in to access this feature</p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary">Sign In</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="pricing" id="pricing">
        <div class="container">
        <h2 class="section-title">Choose Your Plan</h2>
        <p class="section-subtitle">We offer flexible plans to match your journaling needs. Start with our free plan or upgrade for premium features.</p>
        
            <div class="pricing-grid">
            <div class="pricing-card">
                <div class="pricing-header">
                    <h3 class="pricing-title">Basic</h3>
                        <div class="pricing-price">$0</div>
                        <p>Perfect for getting started</p>
                </div>
                <div class="pricing-features">
                        <div class="pricing-feature">
                            <i class="fas fa-check"></i>
                            <span>Unlimited text entries</span>
                        </div>
                        <div class="pricing-feature">
                            <i class="fas fa-check"></i>
                            <span>Basic search functionality</span>
                        </div>
                        <div class="pricing-feature">
                            <i class="fas fa-check"></i>
                            <span>Calendar view</span>
                        </div>
                        <div class="pricing-feature">
                            <i class="fas fa-check"></i>
                            <span>Up to 5 tags</span>
                    </div>
                </div>
                    <div class="pricing-overlay">
                        <i class="fas fa-lock"></i>
                        <p>Sign in to access this plan</p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary">Sign In</a>
                        <?php endif; ?>
                </div>
            </div>
            
            <div class="pricing-card featured">
                <div class="pricing-header">
                    <h3 class="pricing-title">Premium</h3>
                        <div class="pricing-price">$4.99</div>
                        <p>For dedicated journal keepers</p>
                </div>
                <div class="pricing-features">
                        <div class="pricing-feature">
                            <i class="fas fa-check"></i>
                            <span>Everything in Basic</span>
                        </div>
                        <div class="pricing-feature">
                            <i class="fas fa-check"></i>
                            <span>Advanced search</span>
                        </div>
                        <div class="pricing-feature">
                            <i class="fas fa-check"></i>
                            <span>Unlimited tags</span>
                        </div>
                        <div class="pricing-feature">
                            <i class="fas fa-check"></i>
                            <span>Mood tracking</span>
                        </div>
                        <div class="pricing-feature">
                            <i class="fas fa-check"></i>
                            <span>Custom themes</span>
                        </div>
                    </div>
                    <div class="pricing-overlay">
                        <i class="fas fa-lock"></i>
                        <p>Sign in to access premium features</p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary">Sign In</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>About</h3>
                    <p>My Private Diary is a secure digital journaling platform that helps you preserve your thoughts and memories while maintaining complete privacy.</p>
                </div>
                
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li class="footer-link"><a href="#features">Features</a></li>
                        <li class="footer-link"><a href="premium.php">Pricing</a></li>
                        <li class="footer-link"><a href="testimonials.php">Testimonials</a></li>
                        <li class="footer-link"><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Connect</h3>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; 2024 My Private Diary. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Add this modal HTML before the closing body tag -->
    <div id="guestModal" class="modal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeModal()">&times;</button>
            <h2 class="modal-title">Sign In Required</h2>
            <p class="modal-message">To access all features of My Private Diary, please sign in to your account or create a new one.</p>
            <div class="modal-buttons">
                <a href="login.php" class="modal-btn modal-btn-primary">Sign In</a>
                <a href="signup.php" class="modal-btn modal-btn-secondary">Create Account</a>
            </div>
        </div>
    </div>

    <script>
        // ... existing script content ...
        
        // Add these functions for modal handling
        function showModal() {
            const modal = document.getElementById('guestModal');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal() {
            const modal = document.getElementById('guestModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('guestModal');
            if (event.target === modal) {
                closeModal();
            }
        }
        
        // Add hover effect for feature and pricing cards
        document.querySelectorAll('.feature-card, .pricing-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.querySelector('.feature-overlay, .pricing-overlay').style.opacity = '1';
            });
            
            card.addEventListener('mouseleave', function() {
                this.querySelector('.feature-overlay, .pricing-overlay').style.opacity = '0';
            });
        });
    </script>
</body>
</html>