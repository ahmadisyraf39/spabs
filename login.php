<?php

include_once 'db.php';
session_start();
if (isset($_SESSION["email"])) {
    if ($role === "Admin") {
        header("location: admin/index.php");
    } else if ($role === "Coach") {
        header("location: coach/index.php");
    } else if ($role === "Parent") {
        header("location: parent/index.php");
    }
}

try {
    // $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if (isset($_POST["login"])) {
        if (empty($_POST["email"]) || empty($_POST["password"])) {
            $message = '<label>All fields are required</label>';
        } else {
            $query = "SELECT * FROM tbl_spabs_akaun WHERE email = :email AND password = :password";
            $stmt = $conn->prepare($query);
            $stmt->execute(
                array(
                    'email' => $_POST["email"],
                    'password' => $_POST["password"]
                )
            );
            $count = $stmt->rowCount();
            if ($count > 0) {

                //$_SESSION["email"] = $_POST["email"];

                // Fetch the ID from the database
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $userid = $row['userID'];

                $_SESSION["userID"] = $userid;

                header("location:login_success.php");

            } else {
                $message = '<label>Wrong Username/Password</label>';
            }
        }
    }
} catch (PDOException $error) {
    $message = $error->getMessage();
}
?>



<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SPABS</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" rel="stylesheet">
    <style type="text/css">
        /* Importing fonts from Google */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');

        /* Reseting */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            /* background-color: #148634;
            background-color: #165227; */
            background-image: url('pictures/background.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .wrapper {
            max-width: 600px;
            min-height: 500px;
            margin: 130px auto;
            padding: 40px 30px 30px 30px;
            background-color: #ecf0f3;
            border-radius: 30px;
            box-shadow: 1px 1px 20px #cbced1, -1px -1px 20px #fff;
        }

        .logo {
            /* width: 400px; */
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto;
        }

        .logo img {
            width: 50%;
            height: 50%;
            /* Center the image within its parent container */
            object-fit: contain;
        }

        .wrapper .name {
            font-weight: 600;
            font-size: 1.4rem;
            letter-spacing: 1.3px;
            padding-left: 10px;
            color: #148634;
        }

        .wrapper .form-field input {
            display: flex;
            border: none;
            outline: none;
            background: none;
            font-size: 1.2rem;
            color: #666;
            padding: 10px 15px 10px 10px;
            /* border: 1px solid red; */
        }

        .wrapper .form-field {
            width: 80%;
            margin: 0 auto;
            padding-left: 10px;
            margin-bottom: 20px;
            border-radius: 15px;
            box-shadow: inset 6px 6px 6px #cbced1, inset -6px -6px 6px #fff;
        }

        .wrapper .form-field .fas {
            color: #555;
        }

        .btn {
            width: 40%;
            height: 40px;
            margin-top: 10px;
            margin-left: 150px;
            background-color: #148634;
            /* background-color: #165227; */
            color: #fff;
            border-radius: 15px;
            box-shadow: 3px 3px 3px #b1b1b1,
                -3px -3px 3px #fff;
            letter-spacing: 1.3px;
        }

        .btn:hover {
            background-color: #1cc74c;
        }


        @media(max-width: 380px) {
            .wrapper {
                margin: 30px 20px;
                padding: 40px 15px 15px 15px;
            }
        }
    </style>

</head>

<body>

    <div class="wrapper">
        <div class="logo">
            <img src="pictures/logo.png" alt="">
        </div>
        <div class="text-center mt-4 name">
            Sistem Pengurusan Akademi Bola Sepak
        </div>
        <form method="post" class="p-3 mt-3">
            <div class="form-field d-flex align-items-center">
                <span class="far fa-user"></span>
                <input type="text" name="email" id="email" placeholder="Email" class="form-control">
            </div>
            <div class="form-field d-flex align-items-center">
                <span class="fas fa-key"></span>
                <input type="password" name="password" id="password" placeholder="Password" class="form-control">
            </div>
            <input type="submit" name="login" class="btn" value="Login" />
        </form>

        <div class="text-center fs-6">
            <?php
            if (isset($message)) {
                echo '<label class="text-danger">' . $message . '</label>';
            }
            ?>
        </div>
    </div>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://use.fontawesome.com/releases/v5.7.2/css/all.css"></script>
</body>

</html>