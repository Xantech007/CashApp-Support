<?php
// Simple PHP variables for the message
$name = "Chioma";
$from = "Alexander";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I Love You, <?php echo htmlspecialchars($name); ?></title>
    <style>
        /* CSS for styling the page */
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #ff6b6b, #ff8e53, #ffeb3b);
            overflow: hidden;
        }

        .container {
            text-align: center;
            background: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            animation: fadeIn 2s ease-in-out;
        }

        h1 {
            font-size: 3.5em;
            color: #e91e63;
            margin-bottom: 20px;
            font-family: 'Great Vibes', cursive;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        p {
            font-size: 1.5em;
            color: #333;
            margin-top: 20px;
            font-style: italic;
        }

        .heart {
            font-size: 2em;
            color: #e91e63;
            animation: pulse 1.5s infinite;
            display: inline-block;
        }

        /* Animation for fade-in effect */
        @keyframes fadeIn {
            0% { opacity: 0; transform: scale(0.8); }
            100% { opacity: 1; transform: scale(1); }
        }

        /* Animation for pulsing heart */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        /* Responsive design */
        @media (max-width: 600px) {
            .container {
                padding: 20px;
                margin: 10px;
            }
            h1 {
                font-size: 2.5em;
            }
            p {
                font-size: 1.2em;
            }
        }
    </style>
    <!-- Include Google Fonts for elegant typography -->
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>I love you so much, <?php echo htmlspecialchars($name); ?> <span class="heart">♥</span></h1>
        <p>With all my heart, <br> <?php echo htmlspecialchars($from); ?></p>
    </div>
</body>
</html>
