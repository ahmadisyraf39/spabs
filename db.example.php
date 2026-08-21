<?php

$servername = "your-db-host";
$username = "your-db-username";
$password = "your-db-password";
$dbname = "your-db-name";

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

?>
