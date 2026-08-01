<?php
include_once ("../session.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">

</head>

<body>
    <?php

    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['pid'])) {
        $pid = $_GET['pid'];
        try {
            $stmt = $conn->prepare("SELECT * FROM tbl_spabs_pemain WHERE pemainID = :pid");
            $stmt->bindParam(':pid', $pid, PDO::PARAM_STR);
            $stmt->execute();
            $readrow = $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        ?>

        <img src="../pictures/players/<?php echo $readrow['gambar']; ?>">

        <?php
    } else {
        echo "takde";
    }
    ?>
</body>

</html>