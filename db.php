<?php

$servername = "your-db-host";
$username = "your-db-username-or-name";
$password = "your-db-password";
$dbname = "your-db-username-or-name";

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

?>