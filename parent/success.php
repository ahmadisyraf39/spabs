<?php
include "../session.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">


    <link rel="stylesheet" href="../css/style.css">

    <style>
        body {
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .success-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        .success-container h2 {
            color: #4CAF50;
            margin-bottom: 20px;
        }

        .success-container p {
            margin-bottom: 20px;
            color: #555;
        }

        .success-container a {
            display: inline-block;
            padding: 10px 20px;
            color: white;
            background-color: #4CAF50;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .success-container a:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <div class="success-container">
        <h2>Payment Successful!</h2>
        <p>Thank you for your payment. Your transaction has been completed successfully.</p>
        <a href="fee_list.php">Return to Fee Page</a>
    </div>
</body>

</html>