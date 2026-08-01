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

    if (isset($_GET['thid'])) {
        $thid = $_GET['thid'];
        try {
            $stmt = $conn->prepare("SELECT * FROM tbl_spabs_ketidakhadiran WHERE ketidakhadiranID = :thid");
            $stmt->bindParam(':thid', $thid, PDO::PARAM_STR);
            $stmt->execute();
            $readrow = $stmt->fetch(PDO::FETCH_ASSOC);

            $send_time = $readrow['masa_hantar']; // Assuming $readrow['start_time'] contains a time value in 24-hour format (e.g., 13:30:00)
    
            // Convert 24-hour format to 12-hour format with AM/PM
            $formatted_send_time = date("h:i A", strtotime($send_time));

            $date = $readrow['tarikh_hantar'];
            $formatted_date = date("d/m/Y", strtotime($date));

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        ?>

        <table class="table table-bordered">
            <tr>
                <td><strong>Reason</strong></td>
                <td><?php echo $readrow['jenis_sebab']; ?></td>
            </tr>
            <tr>
                <td><strong>Description</strong></td>
                <td><?php echo $readrow['keterangan']; ?></td>
            </tr>
            <tr>
                <td><strong>Send Date</strong></td>
                <td><?php echo $formatted_date; ?></td>
            </tr>
            <tr>
                <td><strong>Send Time</strong></td>
                <td><?php echo $formatted_send_time; ?></td>
            </tr>
        </table>

        <?php
    }
    ?>
</body>

</html>