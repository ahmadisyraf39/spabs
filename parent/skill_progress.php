<?php
include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Admin") {
    header("location:../admin/index.php");
    exit;
}

$parentID = $_SESSION['userID'];

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $conn->prepare("SELECT * FROM tbl_spabs_pemain WHERE ibubapaID = :pid");

$stmt->bindParam(':pid', $parentID, PDO::PARAM_STR);

$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$pid = $_GET['pid'];

$category = $row['kategori'];

$nama_pemain = $row['nama_pemain'];
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Skill Progress</title>
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
                <span class="ms-2">Skill Progress - <?php echo $nama_pemain ?></span>
                <span class="user-role">Parent</span>
            </header>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb ms-5 mt-2">
                    <li class="breadcrumb-item"><a href="player_progress.php">Player Progress</a></li>

                    <li class="breadcrumb-item active" aria-current="page">Skill Progress</li>
                </ol>
            </nav>




            <div class="container p-5">
                <div class="row mb-1">
                    <div class="col-md-4">
                        <b>Skill:</b>
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



                try {

                    $stmt = $conn->prepare("SELECT 
                                                p.pemainID,
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
                                                p.pemainID = :pemainID
                                            GROUP BY 
                                                p.pemainID, k.kemahiranID, k.jenis_kemahiran;");
                    $stmt->bindParam(':pemainID', $pid, PDO::PARAM_STR);
                    $stmt->execute();
                    $result = $stmt->fetchAll();

                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                } ?>

                <?php
                foreach ($result as $readrow) {

                    $date = $readrow['latest_evaluation_date'];

                    if ($date != '-') {
                        $formatted_date = date("d/m/Y", strtotime($date));
                    } else {
                        $formatted_date = '-';
                    }

                    $percentage = $readrow['avg_progress_status'];
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
                                    <div class="col-md-4 d-flex align-items-center">
                                        <?php echo $readrow['jenis_kemahiran'] ?>
                                    </div>
                                    <div class="col-md-3 text-center d-flex align-items-center justify-content-center">
                                        <?php echo $formatted_date ?>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-center">
                                        <div class="progress w-100">
                                            <div class="progress-bar  <?php echo $class; ?>" role="progressbar"
                                                style="width: <?php echo $readrow['avg_progress_status'] ?>%"
                                                aria-valuenow=" <?php echo $readrow['avg_progress_status'] ?>"
                                                aria-valuemin="0" aria-valuemax="100">
                                                <?php echo $readrow['avg_progress_status'] ?>%
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-center justify-content-center">
                                        <div class="text-center ms-4">
                                            <!-- Details Button -->
                                            <!-- <button data-href="" class="btn btn-outline-success btn-xs mb-2" role="button"
                                                    data-toggle="modal" data-target="#activityModal">Details</button> -->
                                            <a href="module_progress.php?pid=<?php echo $readrow['pemainID']; ?>&kid=<?php echo $readrow['kemahiranID']; ?>"
                                                class="btn btn-outline-success btn-xs mb-2" role="button">Details</a>

                                        </div>
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