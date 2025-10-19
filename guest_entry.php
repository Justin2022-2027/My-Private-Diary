<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write Entry - My Private Diary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ff9fb0;
            --primary-dark: #ff7a93;
            --primary-light: #ffe5e9;
            --dark: #1e293b;
            --gray: #64748b;
            --white: #ffffff;
            --success: #10b981;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background-color: #f8fafc;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .entry-form {
            background: var(--white);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .back-link {
            color: var(--gray);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-link:hover {
            color: var(--primary);
        }

        textarea {
            width: 100%;
            min-height: 300px;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-family: inherit;
            resize: vertical;
        }

        .sign-up-prompt {
            text-align: center;
            margin-top: 2rem;
            padding: 1rem;
            background: var(--primary-light);
            border-radius: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
            border: none;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        /* Add these new styles */
        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn-done {
            background: var(--primary);
            color: var(--white);
            border: none;
            cursor: pointer;
        }
        
        .btn-done:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--white);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            text-align: center;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .success-icon {
            color: var(--success, #10b981);
            font-size: 2rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="entry-form">
            <div class="form-header">
                <a href="index.html" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Back 
                </a>
            </div>

            <textarea id="entry-content" placeholder="Start writing your thoughts here..."></textarea>

            <div class="button-group">
                <button onclick="handleDone()" class="btn btn-done">
                    <i class="fas fa-check"></i>
                    Done
                </button>
            </div>

            <div class="sign-up-prompt">
                <p>Want to save your entries and access more features?</p>
                <a href="signup.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i>
                    Sign Up Now
                </a>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="successModal" class="modal">
        <i class="fas fa-check-circle success-icon"></i>
        <h3>Entry Successful!</h3>
        <p>Your entry has been completed.</p>
        <button onclick="closeModal()" class="btn btn-primary">
            Close
        </button>
    </div>
    <div id="modalOverlay" class="modal-overlay"></div>

    <script>
        function handleDone() {
            const content = document.getElementById('entry-content').value;
            
            if (content.trim() === '') {
                alert('Please write something before clicking Done.');
                return;
            }

            document.getElementById('successModal').style.display = 'block';
            document.getElementById('modalOverlay').style.display = 'block';
            
            // Clear the textarea
            document.getElementById('entry-content').value = '';
        }

        function closeModal() {
            document.getElementById('successModal').style.display = 'none';
            document.getElementById('modalOverlay').style.display = 'none';
        }

        // Close modal when clicking outside
        document.getElementById('modalOverlay').addEventListener('click', closeModal);
    </script>
</body>
</html>