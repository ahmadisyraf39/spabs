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

    if (isset($_GET['aid'])) {
        $aid = $_GET['aid'];
        try {
            $stmt = $conn->prepare("SELECT *
            FROM tbl_spabs_kehadiran AS k
            JOIN tbl_spabs_aktiviti AS a ON k.aktivitiID = a.aktivitiID
            JOIN tbl_spabs_pemain AS p ON k.pemainID = p.pemainID
            JOIN tbl_spabs_ketidakhadiran AS kh ON k.kehadiranID = kh.kehadiranID
            WHERE k.aktivitiID = :aid;
            ");
            $stmt->bindParam(':aid', $aid, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) >= 1) {

                echo '<table class="table table-bordered">

                    <tr>
                            <th><strong>Player Name</strong></th>
                            <th><strong>Reason</strong></th>
                            <td><strong>Description</strong></td>
                            <td><strong>Send Date</strong></td>
                            <td><strong>Send Time</strong></td>
                        </tr>';


                foreach ($rows as $readrow) {

                    $send_time = $readrow['masa_hantar']; // Assuming $readrow['masa_hantar'] contains a time value in 24-hour format (e.g., 13:30:00)
    
                    // Convert 24-hour format to 12-hour format with AM/PM
                    $formatted_send_time = date("h:i A", strtotime($send_time));

                    $date = $readrow['tarikh_hantar'];
                    $formatted_date = date("d/m/Y", strtotime($date));

                    echo '<tr>
                            
                        <td>' . $readrow['nama_pemain'] . '</td>
                            <td>' . $readrow['jenis_sebab'] . '</td>
                            <td>' . $readrow['keterangan'] . '</td>
                            <td>' . $formatted_date . '</td>
                            <td>' . $formatted_send_time . '</td>
                        </tr>';
                }

                echo '</table>';
            } else { ?>
                <div class="text-center">No Absentee</div>
                <?php
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    ?>
</body>

</html>