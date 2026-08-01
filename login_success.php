<?php
include_once 'db.php';
include_once 'session.php';

if (isset($_SESSION["userID"])) {
    echo '<script type="text/javascript">';
    if ($role === "Admin") {
        echo 'alert("Welcome to SPABS !");';
        echo 'window.location.href = "admin/index.php";';
    } else if ($role === "Coach") {
        echo 'alert("Welcome to SPABS !");';
        echo 'window.location.href = "coach/index.php";';
    } else if ($role === "Parent") {
        echo 'alert("Welcome to SPABS !");';
        echo 'window.location.href = "parent/index.php";';
    }

    echo '</script>';
} else {
    echo '<script type="text/javascript">';
    echo 'alert("Please Login First!");';
    echo 'window.location.href = "login.php";';
    echo '</script>';
}
?>