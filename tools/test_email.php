<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config.php';
require_once '../include/functions_general.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_email = $_POST['email'] ?? '';
    $subject = 'Δοκιμαστικό Email από τον Πρωτέα';
    $body = 'Αυτό είναι ένα δοκιμαστικό email που στάλθηκε από την εφαρμογή Πρωτέας.';

    if (filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
        // Assuming sendEmail function exists in functions_general.php
        $result = sendEmail($recipient_email, $subject, $body, false);
        if ($result) {
            $message = "<p style=\'color: green;\'>Επιτυχής αποστολή μηνύματος στο {$recipient_email}!</p>";
        } else {
            $message = "<p style=\'color: red;\'>Αποτυχία αποστολής δοκιμαστικού email στο {$recipient_email}.</p>";
        }
    } else {
        $message = "<p style=\'color: red;\'>Δόθηκε λανθασμένη διεύθυνση email.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Αποστολή δοκιμαστικού Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 0 auto;
        }
        h1 {
            color: #0056b3;
            text-align: center;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="email"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Αποστολή δοκιμαστικού Email</h1>
        <form method="POST">
            <label for="email">Email παραλήπτη:</label>
            <input type="email" id="email" name="email" required>
            <button type="submit">Αποστολή δοκιμαστικού Email</button>
        </form>
        <?php echo $message; ?>
    </div>
</body>
</html>