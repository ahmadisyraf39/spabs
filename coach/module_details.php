<?php
include_once ("../session.php");

if ($role == "Admin") {
    header("location:../admin/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}
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

    if (isset($_GET['mid'])) {
        $mid = $_GET['mid'];
        try {
            $stmt = $conn->prepare("SELECT * FROM tbl_spabs_modul WHERE modulID = :mid");
            $stmt->bindParam(':mid', $mid, PDO::PARAM_STR);
            $stmt->execute();
            $readrow = $stmt->fetch(PDO::FETCH_ASSOC);


        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        ?>

        <table class="table table-bordered">
            <tr>
                <td><strong>Module Name</strong></td>
                <td><?php echo $readrow['nama_modul']; ?></td>
            </tr>
            <tr>
                <td><strong>Progress 1 (25%)</strong></td>
                <td><?php echo $readrow['kemajuan_satu']; ?></td>
            </tr>

            <tr>
                <td><strong>Progress 2 (50%)</strong></td>
                <td><?php echo $readrow['kemajuan_dua']; ?></td>
            </tr>
            <tr>
                <td><strong>Progress 3 (75%)</strong></td>
                <td><?php echo $readrow['kemajuan_tiga']; ?></td>
            </tr>
            <tr>
                <td><strong>Progress 4 (100%)</strong></td>
                <td><?php echo $readrow['kemajuan_empat']; ?></td>
            </tr>
        </table>

        <?php
    }
    ?>
</body>

</html>