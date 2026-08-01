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

    if (isset($_GET['aid'])) {
        $aid = $_GET['aid'];
        try {
            $stmt = $conn->prepare("SELECT * FROM tbl_spabs_aktiviti WHERE aktivitiID = :aid");
            $stmt->bindParam(':aid', $aid, PDO::PARAM_STR);
            $stmt->execute();
            $readrow = $stmt->fetch(PDO::FETCH_ASSOC);

            $start_time = $readrow['masa_mula']; // Assuming $readrow['start_time'] contains a time value in 24-hour format (e.g., 13:30:00)
    
            // Convert 24-hour format to 12-hour format with AM/PM
            $formatted_start_time = date("h:i A", strtotime($start_time));

            $end_time = $readrow['masa_tamat']; // Assuming $readrow['start_time'] contains a time value in 24-hour format (e.g., 13:30:00)
    
            // Convert 24-hour format to 12-hour format with AM/PM
            $formatted_end_time = date("h:i A", strtotime($end_time));

            $date = $readrow['tarikh_aktiviti'];
            $formatted_date = date("d/m/Y", strtotime($date));

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        ?>

        <table class="table table-bordered">
            <tr>
                <td><strong>Activity Name</strong></td>
                <td><?php echo $readrow['nama_aktiviti']; ?></td>
            </tr>
            <tr>
                <td><strong>Activity Type</strong></td>
                <td><?php echo $readrow['jenis_aktiviti']; ?></td>
            </tr>
            <tr>
                <td><strong>Date</strong></td>
                <td><?php echo $formatted_date; ?></td>
            </tr>
            <tr>
                <td><strong>Time</strong></td>
                <td><?php echo $formatted_start_time; ?> - <?php echo $formatted_end_time; ?></td>
            </tr>
            <tr>
                <td><strong>Location</strong></td>
                <td><?php echo $readrow['lokasi']; ?></td>
            </tr>
            <tr>
                <td><strong>Category</strong></td>
                <td><?php echo $readrow['kategori']; ?></td>
            </tr>
            <tr>
                <td><strong>Description</strong></td>
                <td><?php echo $readrow['penerangan']; ?></td>
            </tr>
        </table>

        <?php
    }
    ?>
</body>

</html>