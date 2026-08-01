<?php
include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Admin") {
    header("location:../admin/index.php");
    exit;
}

$ibubapaID = $_SESSION['userID'];

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $conn->prepare("SELECT * FROM tbl_spabs_ibubapa WHERE ibubapaID = :ibubapaid");

$stmt->bindParam(':ibubapaid', $ibubapaID, PDO::PARAM_STR);

$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$ibubapaid = $row['ibubapaID'];
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Player Progress</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">


    <!-- <link href="css/bootstrap.css" rel="stylesheet"> -->
    <link href="../css/main.css" rel="stylesheet">

    <style>
        .card,
        .accordion {
            box-shadow: 10px 10px 5px #888888;
            /* horizontal offset, vertical offset, blur radius, shadow color */
        }
    </style>



</head>


<body>

    <div class="wrapper">

        <?php
        include_once 'sidebar.php'
            ?>

        <div class="main">

            <header>
                <span class="ms-2">Player Progress</span>
                <span class="user-role">Coach</span>
            </header>

            <div class="container p-5">

                <?php

                try {
                    // Prepare the SQL statement with placeholders and subqueries
                    $sql = "
                    SELECT 
                        pemainID,
                        nama_pemain,
                        COALESCE(SUM(total_progress_status), 0) AS total_progress_status,
                        COALESCE(CEIL(AVG(avg_progress_status)), 0) AS avg_of_avg_progress_status,
                        COALESCE(MAX(latest_evaluation_date), '-') AS latest_evaluation_date
                    FROM (
                        SELECT 
                            p.pemainID,
                            p.nama_pemain,
                            k.kemahiranID, 
                            k.jenis_kemahiran, 
                            COALESCE(SUM(COALESCE(pr.status_capai, 0)), 0) AS total_progress_status,
                            COALESCE(
                                CEIL(SUM(COALESCE(pr.status_capai, 0)) / (
                                    SELECT COUNT(DISTINCT m.modulID)
                                    FROM tbl_spabs_modul m
                                    WHERE m.kemahiranID = k.kemahiranID
                                )), 
                                0
                            ) AS avg_progress_status,
                            COALESCE(MAX(pr.tarikh_penilaian), '-') AS latest_evaluation_date
                        FROM 
                            tbl_spabs_pemain p
                            LEFT JOIN tbl_spabs_kemahiran k ON p.kategori = k.kategori
                            LEFT JOIN tbl_spabs_penilaian pr ON p.pemainID = pr.pemainID AND k.kemahiranID = pr.kemahiranID
                        WHERE 
                            p.ibubapaID = :ibubapaID
                        GROUP BY 
                            p.pemainID, p.nama_pemain, k.kemahiranID, k.jenis_kemahiran
                    ) AS subquery
                    GROUP BY 
                        pemainID, nama_pemain;
                    ";

                    // Prepare the statement
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':ibubapaID', $ibubapaid, PDO::PARAM_STR);
                    $stmt->execute();
                    $result = $stmt->fetchAll();

                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                } ?>

                <?php
                if (count($result) > 0) {
                    ?>

                    <div class="row mb-1">
                        <div class="col-md-4">
                            <b>Player Name:</b>
                        </div>
                        <div class="col-md-3 text-center">
                            <b>Updated Date:</b>
                        </div>
                        <div class="col-md-3 text-center">
                            <b>Progress:</b>
                        </div>
                        <div class="col-md-2 text-center">
                            <b>Actions:</b>
                        </div>
                    </div>

                    <?php

                    foreach ($result as $readrow) {

                        $date = $readrow['latest_evaluation_date'];

                        if ($date != '-') {
                            $formatted_date = date("d/m/Y", strtotime($date));
                        } else {
                            $formatted_date = '-';
                        }

                        $percentage = $readrow['avg_of_avg_progress_status'];
                        $class = 'bg-success';

                        if ($percentage <= 25) {
                            $class = 'bg-danger';
                        } elseif ($percentage <= 50) {
                            $class = 'bg-warning';
                        } elseif ($percentage <= 75) {
                            $class = 'bg-primary';
                        } else {
                            $class = 'bg-success';
                        }
                        ?>

                        <div class="row mb-3">
                            <div class="card  m-0">
                                <div class="card-body m-0">
                                    <div class="row">
                                        <div class="col-md-4 d-flex align-items-center ">
                                            <?php echo $readrow['nama_pemain'] ?>
                                        </div>
                                        <div class="col-md-3 text-center d-flex align-items-center justify-content-center">
                                            <?php echo $formatted_date ?>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-center">
                                            <div class="progress  w-100">
                                                <div class="progress-bar <?php echo $class; ?>" role="progressbar"
                                                    style="width: <?php echo $readrow['avg_of_avg_progress_status'] ?>%"
                                                    aria-valuenow=" <?php echo $readrow['avg_of_avg_progress_status'] ?>"
                                                    aria-valuemin="0" aria-valuemax="100">
                                                    <?php echo $readrow['avg_of_avg_progress_status'] ?>%
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-center justify-content-center">
                                            <div class="text-center ms-4">
                                                <!-- Details Button -->
                                                <a href="skill_progress.php?pid=<?php echo $readrow['pemainID']; ?>"
                                                    class="btn btn-outline-success btn-xs mb-2" role="button">Details</a>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php
                    }
                } else {
                    ?>
                    <div class="row mb-3 justify-content-center align-items-center">
                        <div class="card mb-3" style="width: 100%; border: 2px solid black; padding: 0;">
                            <div class="row g-0">
                                <div class="col-md-12 d-flex flex-column justify-content-center align-items-center">
                                    <div class="card-body">
                                        No Registered Player
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>


            </div>

        </div>
    </div>



</body>

</html>