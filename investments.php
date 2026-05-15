<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investments | Mivonta</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: #ffffff;
            color: #111827;
            font-family: Arial, sans-serif;
        }

        .unavailable-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px;
        }

        .unavailable-card {
            width: min(720px, 100%);
            min-height: 360px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 32px 24px;
        }

        .unavailable-card h1 {
            margin: 0 0 12px;
            font-size: 32px;
            font-weight: 700;
        }

        .unavailable-card p {
            margin: 0;
            font-size: 22px;
            color: #374151;
        }

        .back-link {
            margin-top: 28px;
            color: #2563eb;
            text-decoration: none;
            font-size: 15px;
        }
    </style>
</head>
<body>
    <div class="unavailable-wrap">
        <div class="unavailable-card">
            <h1>Investments</h1>
            <p>this service unavailable right now</p>
            <a href="dashboard.php" class="back-link">Back to dashboard</a>
        </div>
    </div>
</body>
</html>
