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

    if (isset($_GET['pid'])) {
        $pid = $_GET['pid'];
        try {
            $stmt = $conn->prepare("SELECT
                                        SUM(CASE WHEN k.status_kehadiran = 'Absent' THEN 1 ELSE 0 END) AS count_absent,
                                        SUM(CASE WHEN k.status_kehadiran = 'Attend' THEN 1 ELSE 0 END) AS count_attend,
                                        SUM(CASE WHEN a.jenis_aktiviti = 'Training' THEN 1 ELSE 0 END) AS count_activity_training,
                                        SUM(CASE WHEN a.jenis_aktiviti = 'League Match' THEN 1 ELSE 0 END) AS count_activity_league,
                                        SUM(CASE WHEN a.jenis_aktiviti = 'Tournament' THEN 1 ELSE 0 END) AS count_activity_tourney,
                                        SUM(CASE WHEN a.jenis_aktiviti = 'Other' THEN 1 ELSE 0 END) AS count_activity_other,
                                        SUM(CASE WHEN a.jenis_aktiviti = 'Friendly Match' THEN 1 ELSE 0 END) AS count_activity_friendly,
                                        SUM(
                                            CASE WHEN a.jenis_aktiviti = 'Training' THEN 1 ELSE 0 END +
                                            CASE WHEN a.jenis_aktiviti = 'League Match' THEN 1 ELSE 0 END +
                                            CASE WHEN a.jenis_aktiviti = 'Tournament' THEN 1 ELSE 0 END +
                                            CASE WHEN a.jenis_aktiviti = 'Other' THEN 1 ELSE 0 END +
                                            CASE WHEN a.jenis_aktiviti = 'Friendly Match' THEN 1 ELSE 0 END
                                        ) AS total_activity_count,
                                        SUM(CASE WHEN k.status_kehadiran = 'Attend' AND a.jenis_aktiviti = 'Training' THEN 1 ELSE 0 END) AS count_attend_training,
                                        SUM(CASE WHEN k.status_kehadiran = 'Absent' AND a.jenis_aktiviti = 'Training' THEN 1 ELSE 0 END) AS count_absent_training,
                                        SUM(CASE WHEN k.status_kehadiran = 'Attend' AND a.jenis_aktiviti = 'League Match' THEN 1 ELSE 0 END) AS count_attend_league,
                                        SUM(CASE WHEN k.status_kehadiran = 'Attend' AND a.jenis_aktiviti = 'Tournament' THEN 1 ELSE 0 END) AS count_attend_tourney,
                                        SUM(CASE WHEN k.status_kehadiran = 'Attend' AND a.jenis_aktiviti = 'Other' THEN 1 ELSE 0 END) AS count_attend_other,
                                        SUM(CASE WHEN k.status_kehadiran = 'Attend' AND a.jenis_aktiviti = 'Friendly Match' THEN 1 ELSE 0 END) AS count_attend_friendly,
                                        SUM(CASE WHEN k.status_kehadiran = 'Absent' AND kt.jenis_sebab = 'Family Outing' THEN 1 ELSE 0 END) AS count_absent_family,
                                        SUM(CASE WHEN k.status_kehadiran = 'Absent' AND kt.jenis_sebab = 'School Activity' THEN 1 ELSE 0 END) AS count_absent_school,
                                        SUM(CASE WHEN k.status_kehadiran = 'Absent' AND kt.jenis_sebab = 'Sick/Injury' THEN 1 ELSE 0 END) AS count_absent_sick,
                                        SUM(CASE WHEN k.status_kehadiran = 'Absent' AND kt.jenis_sebab = 'Other' THEN 1 ELSE 0 END) AS count_absent_other,
                                        SUM(CASE WHEN k.status_kehadiran = 'Absent' AND kt.jenis_sebab IS NULL THEN 1 ELSE 0 END) AS count_absent_skip
                                        
                                    FROM
                                        tbl_spabs_kehadiran k
                                    JOIN
                                        tbl_spabs_aktiviti a ON k.aktivitiID = a.aktivitiID
                                    LEFT JOIN
                                        tbl_spabs_ketidakhadiran kt ON k.kehadiranID = kt.kehadiranID
                                    WHERE
                                        k.pemainID = :pid ");

            $stmt->bindParam(':pid', $pid, PDO::PARAM_STR);
            $stmt->execute();
            $readrow = $stmt->fetch(PDO::FETCH_ASSOC);


        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        ?>

        <h5><strong>Attend Record</strong></h5>

        <table class="table table-bordered">
            <tr>
                <th>Type of Activity</th>
                <th>No. of Activity</th>
                <th>No. of Attend</th>
            </tr>
            <tr>
                <td>Training</td>
                <td><?php echo $readrow['count_activity_training']; ?></td>
                <td><?php echo $readrow['count_attend_training']; ?></td>
            </tr>
            <tr>
                <td>Friendly Match</td>
                <td><?php echo $readrow['count_activity_friendly']; ?></td>
                <td><?php echo $readrow['count_attend_friendly']; ?></td>
            </tr>
            <tr>
                <td>League Match</td>
                <td><?php echo $readrow['count_activity_league']; ?></td>
                <td><?php echo $readrow['count_attend_league']; ?></td>
            </tr>
            <tr>
                <td>Tournament</td>
                <td><?php echo $readrow['count_activity_tourney']; ?></td>
                <td><?php echo $readrow['count_attend_tourney']; ?></td>
            </tr>
            <tr>
                <td>Other</td>
                <td><?php echo $readrow['count_activity_other']; ?></td>
                <td><?php echo $readrow['count_attend_other']; ?></td>
            </tr>
            <tfoot>
                <tr>
                    <td><strong>Total</strong></td>
                    <td><strong><?php echo $readrow['total_activity_count']; ?></strong></td>
                    <td><strong><?php echo $readrow['count_attend']; ?></strong></td>
                </tr>
            </tfoot>
        </table>

        <h5><strong>Absent Record</strong></h5>

        <table class="table table-bordered">
            <tr>
                <th>Absence Reason</th>
                <th>No. of Absent</th>
            </tr>
            <tr>
                <td>Family Outing</td>
                <td><?php echo $readrow['count_absent_family']; ?></td>
            </tr>
            <tr>
                <td>School Activity</td>
                <td><?php echo $readrow['count_absent_school']; ?></td>
            </tr>
            <tr>
                <td>Sick/Injury</td>
                <td><?php echo $readrow['count_absent_sick']; ?></td>
            </tr>
            <tr>
                <td>Other</td>
                <td><?php echo $readrow['count_absent_other']; ?></td>
            </tr>
            <tr>
                <td>No Reason</td>
                <td><?php echo $readrow['count_absent_skip']; ?></td>
            </tr>
            <tfoot>
                <tr>
                    <td><strong>Total</strong></td>
                    <td><strong><?php echo $readrow['count_absent']; ?></strong></td>
                </tr>
            </tfoot>
        </table>


        <?php
    }
    ?>
</body>

</html>