<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = 'Contact Us - My Private Diary';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Compromise for NLP -->
    <script src="https://unpkg.com/compromise"></script>

    <!-- Fuse.js for fuzzy matching -->
    <script src="https://cdn.jsdelivr.net/npm/fuse.js/dist/fuse.min.js"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .header {
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            padding: 1.5rem 0 1.5rem 0;
            text-align: center;
        }
        .header .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            font-size: 2rem;
            font-weight: 700;
            color: #f87171;
            text-decoration: none;
        }
        .header .logo img {
            width: 32px;
            height: 32px;
            vertical-align: middle;
        }
        .contact-main {
            max-width: 600px;
            margin: 3rem auto 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07);
            padding: 2.5rem 2rem 2rem 2rem;
            text-align: center;
        }
        .contact-main h1 {
            color:rgb(246, 174, 174);
            margin-bottom: 0.5rem;
            font-size: 2rem;
        }
        .contact-main p {
            color: #64748b;
            margin-bottom: 2rem;
        }
        .contact-info {
            margin-bottom: 2rem;
        }
        .contact-info strong {
            color: #1e293b;
        }
        .contact-info a {
            color:rgb(243, 158, 158);
            text-decoration: none;
        }
        .contact-info a:hover {
            text-decoration: underline;
        }
        .social-section {
            margin-top: 2rem;
        }
        .social-section h3 {
            margin-bottom: 1rem;
            color: #1e293b;
            font-size: 1.1rem;
        }
        .social-icons {
            display: flex;
            justify-content: center;
            gap: 1.2rem;
        }
        .social-icons a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f8fafc;
            color: #1e293b;
            font-size: 1.3rem;
            transition: background 0.2s, color 0.2s;
            border: 1px solid #e5e7eb;
        }
        .social-icons a:hover {
            background: #f87171;
            color: #fff;
            border-color: #f87171;
        }
        @media (max-width: 600px) {
            .contact-main {
                padding: 1.2rem 0.5rem;
            }
        }
        /* Chatbot styles */
        .mpd-chatbot-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 4px 16px rgba(0,0,0,0.13);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1001;
            border: none;
            transition: box-shadow 0.2s;
        }
        .mpd-chatbot-btn img {
            width: 36px;
            height: 36px;
        }
        .mpd-chatbot-btn:hover {
            box-shadow: 0 8px 32px rgba(248,113,113,0.18);
        }
        .mpd-chatbot-window {
            display: none;
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 340px;
            max-width: 95vw;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(248,113,113,0.18);
            z-index: 1002;
            flex-direction: column;
            overflow: hidden;
            animation: fadeIn 0.2s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px);}
            to { opacity: 1; transform: translateY(0);}
        }
        .mpd-chatbot-header {
            background: #f87171;
            color: #fff;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .mpd-chatbot-header img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #fff;
        }
        .mpd-chatbot-header span {
            font-weight: 600;
            font-size: 1.1rem;
        }
        .mpd-chatbot-close {
            margin-left: auto;
            background: none;
            border: none;
            color: #fff;
            font-size: 1.2rem;
            cursor: pointer;
        }
        .mpd-chatbot-body {
            background: #f8fafc;
            padding: 1rem;
            height: 220px;
            overflow-y: auto;
            font-size: 0.98rem;
        }
        .mpd-chatbot-msg {
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }
        .mpd-chatbot-msg.user {
            justify-content: flex-end;
        }
        .mpd-chatbot-msg .msg-bubble {
            background: #fff;
            color: #1e293b;
            padding: 0.7rem 1rem;
            border-radius: 16px;
            max-width: 75%;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }
        .mpd-chatbot-msg.user .msg-bubble {
            background: #f87171;
            color: #fff;
            border-bottom-right-radius: 6px;
        }
        .mpd-chatbot-footer {
            padding: 0.7rem;
            background: #fff;
            border-top: 1px solid #f3f4f6;
            display: flex;
            gap: 0.5rem;
        }
        .mpd-chatbot-footer input {
            flex: 1;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 0.5rem 1rem;
            font-size: 1rem;
        }
        .mpd-chatbot-footer button {
            background: #f87171;
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 0.5rem 1rem;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .mpd-chatbot-footer button:hover {
            background: #dc2626;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container" style="position: relative;">
            <!-- Back Button -->
            <a href="index.html" class="back-button" style="position: absolute; top: 16px; left: 16px; text-decoration: none; font-size: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                
                <span style="font-size: 1rem; font-weight: 500;"><i class="fas fa-arrow-left"></i> Back</span>
            </a>
            <!-- Logo and Title -->
            <a href="index.html" class="logo" style="display: flex; align-items: center; justify-content: center; text-decoration: none;">
                <span style="font-size: 1.5rem; font-weight: bold; color:rgb(246, 162, 162);"><i class="fas fa-book-open"></i> My Private Diary</span>
            </a>
        </div>
    </header>
    <div class="contact-main">
        <h1>Contact Us</h1>
        <p>Get in touch with us anytime</p>
        <div class="contact-info">
            <div><strong>Website:</strong> My Private Diary</div>
            <div><strong>Email:</strong> <a href="mailto:mpd241203@gmail.com">mpd241203@gmail.com</a></div>
            <div><strong>Phone No:</strong> <a href="tel:7306978298">7306978298</a></div>
        </div>
        <div class="social-section">
            <h3>Join us on our social media platforms</h3>
            <div class="social-icons">
                <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>

    <!-- Chatbot Button -->
    <button class="mpd-chatbot-btn" id="mpdChatbotBtn" title="Chat with MPD Assistant">
        <img src="book_icon.png" alt="MPD Assistant" />
    </button>
    <!-- Chatbot Window -->
    <div class="mpd-chatbot-window" id="mpdChatbotWindow">
        <div class="mpd-chatbot-header">
            <img src="book_icon.png" alt="MPD Assistant" />
            <span>MPD Assistant</span>
            <button class="mpd-chatbot-close" id="mpdChatbotClose" title="Close">&times;</button>
        </div>
        <div class="mpd-chatbot-body" id="mpdChatbotBody">
            <div class="mpd-chatbot-msg">
                <div class="msg-bubble">Hello! I am your MPD Assistant. Ask your Queries!</div>
            </div>
        </div>
        <form class="mpd-chatbot-footer" id="mpdChatbotForm" autocomplete="off" onsubmit="return false;">
            <input type="text" id="mpdChatbotInput" placeholder="Type your message..." />
            <button type="submit"><i class="fas fa-paper-plane"></i></button>
        </form>
    </div>
    <script src="https://unpkg.com/compromise"></script>
    <script>
        // Chatbot toggle logic
        const chatbotBtn = document.getElementById('mpdChatbotBtn');
        const chatbotWindow = document.getElementById('mpdChatbotWindow');
        const chatbotClose = document.getElementById('mpdChatbotClose');
        const chatbotBody = document.getElementById('mpdChatbotBody');
        const chatbotForm = document.getElementById('mpdChatbotForm');
        const chatbotInput = document.getElementById('mpdChatbotInput');

        chatbotBtn.onclick = () => {
            chatbotWindow.style.display = 'flex';
            setTimeout(() => chatbotInput.focus(), 200);
        };
        chatbotClose.onclick = () => {
            chatbotWindow.style.display = 'none';
        };

        // Updated chatbot responses
        const responses = {
            'hello': 'Hello! Welcome to My Private Diary. How can I assist you today?',
            'hi': 'Hi there! How can I help you with My Private Diary?',
            'hey': 'Hey! I’m here to support you with your diary experience.',
            'how are you': 'I\'m a helpful diary bot, always ready to assist you!',
            'what can you do': 'I can help you write entries, view past memories, track your mood, set reminders, and explore premium features!',
            'tell me about the features': 'My Private Diary offers diary entry writing, mood tracking, reminders, personalization, and premium upgrades.',
            'what are the features': 'You can write and view diary entries, set reminders, personalize your diary, track your mood, and more!',
            'how to use this site': 'Just log in, choose an option from the sidebar like "Write New Entry" or "Mood Tracker", and start exploring.',
            'how do i create a diary entry': 'Click on "Write New Entry" from the sidebar, fill in your thoughts, and hit save!',
            'how do i view past entries': 'Use the "View Past Entries" option to browse all your saved entries.',
            'can i delete an entry': 'Yes, simply go to "View Past Entries", select the entry, and you’ll see a delete option.',
            'how does the mood tracker work': 'The Mood Tracker lets you log your emotional state each day. It helps you reflect and see patterns over time.',
            'how can i track my mood': 'Click on "Mood Tracker", choose your mood, and optionally leave a note for the day.',
            'what is journal timeline': 'The Journal Timeline gives you a visual history of your entries by date, so you can relive your journey.',
            'how to see journal history': 'Use the "Journal Timeline" from the menu to browse your diary by time.',
            'can i set a reminder': 'Yes! Use the "Reminders" option to set personal reminders for entries, journaling times, or moods.',
            'how to set a daily reminder': 'Go to "Reminders", choose the date and time, and enter a short message for your reminder.',
            'can i customize the diary': 'Absolutely! Go to "Personalize Diary" to change fonts, colors, and themes to match your style.',
            'how to change the theme': 'Visit "Personalize Diary" and pick your preferred theme or color scheme.',
            'what are premium features': 'Premium includes advanced analytics, custom themes, more moods, and priority support.',
            'how to become a premium user': 'Click on "Premium Features" and choose your plan. You can pay securely using our payment system.',
            'how do i pay for premium': 'Go to "Premium Features", and you’ll find the payment options to upgrade your account.',
            'is payment safe': 'Yes, we use secure payment gateways like Razorpay/Stripe in test mode to ensure your payment is safe.',
            'can i try before buying': 'Some features are available in free mode. For full access, try our premium with a 7-day trial in demo mode!',
            'how do i update my profile': 'Click on "Settings" to edit your name, birthdate, or password.',
            'can i change my email': 'For security reasons, please contact support to update your email.',
            'how do i logout': 'Just click "Logout" from the sidebar to securely exit your session.',
            'how to contact support': 'Email us at mpd241203@gmail.com or call 7306978298. We’re here for you!',
            'i need help': 'Sure, tell me what you’re trying to do and I’ll guide you!',
            'i forgot my password': 'You can reset your password from the login page using the "Forgot Password?" option.',
            'thank you': 'You’re most welcome! Let me know if you need anything else.',
            'thanks': 'Glad to help!',
            'bye': 'Goodbye! Take care of yourself and your thoughts!',
            'see you later': 'See you soon! Keep journaling :)',
            'default': 'I\'m not sure how to respond to that. Try asking about diary entries, mood tracker, or reminders!'
        };

        const intentList = [
  { intent: 'greeting', phrases: ['hello', 'hi', 'hey', 'good morning', 'good evening'] },
  { intent: 'features', phrases: ['features', 'what can you do', 'what are your features'] },
  { intent: 'mood', phrases: ['track mood', 'mood tracker', 'how to track emotions', 'emotions'] },
  { intent: 'journal', phrases: ['journal', 'timeline', 'past entries'] },
  { intent: 'reminder', phrases: ['set reminder', 'remind me', 'reminders'] },
  { intent: 'customize', phrases: ['customize', 'personalize', 'themes', 'font'] },
  { intent: 'premium', phrases: ['premium', 'upgrade', 'subscribe'] },
  { intent: 'contact', phrases: ['contact', 'support', 'help'] },
  { intent: 'security', phrases: ['secure', 'encryption', 'safety'] },
  { intent: 'how to use', phrases: ['how to use', 'how do i', 'instructions'] },
  { intent: 'thanks', phrases: ['thank you', 'thanks'] },
  { intent: 'goodbye', phrases: ['bye', 'goodbye', 'see you'] }
];

// Initialize Fuse.js
const fuseOptions = {
  keys: ['phrases'],
  includeScore: true,
  threshold: 0.4
};

const fuse = new Fuse(intentList, fuseOptions);

function getBotResponse() {
  const input = document.getElementById('userInput').value.trim().toLowerCase();
  const doc = nlp(input);
  const verbs = doc.verbs().out('array');
  const nouns = doc.nouns().out('array');

  let matched = fuse.search(input);
  let responseKey = matched.length > 0 ? matched[0].item.intent : 'default';

  document.getElementById('chatResponse').innerHTML += `<div><b>You:</b> ${input}</div>`;
  document.getElementById('chatResponse').innerHTML += `<div><b>Bot:</b> ${responses[responseKey]}</div>`;
  document.getElementById('userInput').value = '';
  document.getElementById('chatResponse').scrollTop = document.getElementById('chatResponse').scrollHeight;
}
        function getBotResponse() {
  const input = document.getElementById('userInput').value.trim().toLowerCase();
  const doc = nlp(input);

  let responseKey = 'unknown';

  // Keywords
  if (/hi|hello|hey/.test(input)) responseKey = 'greeting';
  else if (/feature|what can you do/.test(input)) responseKey = 'features';
  else if (/mood|how.*feel/.test(input)) responseKey = 'mood';
  else if (/journal|timeline/.test(input)) responseKey = 'journal';
  else if (/remind|reminder/.test(input)) responseKey = 'reminder';
  else if (/customize|personalize|theme|font/.test(input)) responseKey = 'customize';
  else if (/premium|pay|subscription/.test(input)) responseKey = 'premium';
  else if (/contact|help|support/.test(input)) responseKey = 'contact';
  else if (/thank/.test(input)) responseKey = 'thanks';
  else if (/bye|goodbye/.test(input)) responseKey = 'goodbye';

  // Fallback using NLP (verbs/nouns for intent detection)
  if (responseKey === 'unknown') {
    const verbs = doc.verbs().out('array').join(' ');
    const nouns = doc.nouns().out('array').join(' ');

    if (verbs.includes('track') && nouns.includes('mood')) responseKey = 'mood';
    else if (verbs.includes('write') && nouns.includes('entry')) responseKey = 'features';
    else if (verbs.includes('set') && nouns.includes('reminder')) responseKey = 'reminder';
  }

  document.getElementById('chatResponse').innerText = responses[responseKey];
}

        chatbotForm.onsubmit = function() {
            const msg = chatbotInput.value.trim();
            if (!msg) return false;

            // Show user message
            chatbotBody.innerHTML += `<div class="mpd-chatbot-msg user"><div class="msg-bubble">${msg}</div></div>`;
            chatbotBody.scrollTop = chatbotBody.scrollHeight;
            chatbotInput.value = '';

            // Bot response
            setTimeout(() => {
                let reply = responses['default'];
                for (const key in responses) {
                    if (msg.toLowerCase().includes(key)) {
                        reply = responses[key];
                        break;
                    }
                }
                chatbotBody.innerHTML += `<div class="mpd-chatbot-msg"><div class="msg-bubble">${reply}</div></div>`;
                chatbotBody.scrollTop = chatbotBody.scrollHeight;
            }, 600);

            return false;
        };
    </script>
</body>
</html>
