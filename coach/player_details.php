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
    <title>Player Details</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">




    <style>
        .circular-frame {
            /* width: 30%;
            height: 30%;
            border-radius: 20px; */
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            object-position: center;
            display: block;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 10px;
        }
    </style>

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

            $parentid = $readrow['ibubapaID'];

            $stmt2 = $conn->prepare("SELECT * FROM tbl_spabs_ibubapa WHERE ibubapaID = :parentid");
            $stmt2->bindParam(':parentid', $parentid, PDO::PARAM_STR);
            $stmt2->execute();
            $readrow2 = $stmt2->fetch(PDO::FETCH_ASSOC);




        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        ?>

        <!-- Circular Image Frame -->
        <img src="../pictures/players/<?php echo $readrow['gambar']; ?>" alt="Player Image" class="circular-frame">

        <table class="table table-bordered">
            <tr>
                <td><strong>Player Name</strong></td>
                <td><?php echo $readrow['nama_pemain']; ?></td>
            </tr>
            <tr>
                <td><strong>IC Number</strong></td>
                <td><?php echo $readrow['ic_pemain']; ?></td>
            </tr>
            <tr>
                <td><strong>Age</strong></td>
                <td><?php echo $readrow['umur']; ?></td>
            </tr>
            <tr>
                <td><strong>Birthdate </strong></td>
                <td><?php echo $readrow['tarikh_lahir']; ?></td>
            </tr>
            <tr>
                <td><strong>Category</strong></td>
                <td><?php echo $readrow['kategori']; ?></td>
            </tr>
            <tr>
                <td><strong>Gender</strong></td>
                <td><?php echo $readrow['jantina']; ?></td>
            </tr>
            <tr>
                <td><strong>Registered Date</strong></td>
                <td><?php echo date('d/m/Y', strtotime($readrow['tarikh_daftar'])); ?></td>
            </tr>


        </table>

        <table class="table table-bordered">
            <tr>
                <td><strong>Parent Name</strong></td>
                <td><?php echo $readrow2['nama_ibubapa']; ?></td>
            </tr>
            <tr>
                <td><strong>Phone Number</strong></td>
                <td><?php echo $readrow2['tel_ibubapa']; ?></td>
            </tr>
            <tr>
                <td><strong>2 <sup>nd</sup> Phone Number</strong></td>
                <td><?php echo $readrow2['tel_ibubapa2']; ?></td>
            </tr>
            <tr>
                <td><strong>Address</strong></td>
                <td><?php echo $readrow2['alamat']; ?></td>
            </tr>
        </table>

        <?php
    }
    ?>
</body>

</html>