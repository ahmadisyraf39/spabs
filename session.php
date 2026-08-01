<?php
include_once 'database.php';

session_start();

$conn = new PDO("mysql:host=$servername;port=3306;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$userID = $_SESSION['userID'];

$stmt = $conn->prepare("SELECT * FROM tbl_spabs_akaun WHERE userID = '$userID'");

$stmt->execute();

$readrow = $stmt->fetch(PDO::FETCH_ASSOC);

$userid = $readrow['userID'];
$email = $readrow['email'];
$pass = $readrow['password'];
$role = $readrow['user_role'];

if ($email == '') {
    header("location:../login.php");
}

