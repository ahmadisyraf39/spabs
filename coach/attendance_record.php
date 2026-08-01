<?php
include_once ("../session.php");

if ($role == "Admin") {
    header("location:../admin/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}


$coachID = $_SESSION['userID'];

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $conn->prepare("SELECT * FROM tbl_spabs_jurulatih WHERE jurulatihID = :cid");

$stmt->bindParam(':cid', $coachID, PDO::PARAM_STR);

$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$category = $row['kategori'];
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Attendance Record</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">


    <!-- <link href="css/bootstrap.css" rel="stylesheet"> -->
    <link href="../css/main.css" rel="stylesheet">

    <style>
        .card,
        .accordion,
        .form-container {
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
                <span class="ms-2">Attendance Record</span>
                <span class="user-role">Coach</span>
            </header>

            <div class="container p-5">
                <div class="row mb-1">
                    <div class="col-md-4 d-flex flex-column justify-content-center">
                        <b>Player Name:</b>
                    </div>
                    <div class="col-md-1 text-center">
                        <b>Total Activity:</b>
                    </div>
                    <div class="col-md-1 text-center">
                        <b>Total Attend:</b>
                    </div>
                    <div class="col-md-1 text-center">
                        <b>Total Absent:</b>
                    </div>
                    <div class="col-md-3 text-center d-flex flex-column justify-content-center">
                        <b>Attendance Rate:</b>
                    </div>
                    <div class="col-md-2 text-center d-flex flex-column justify-content-center">
                        <b>Action:</b>
                    </div>
                </div>

                <?php

                try {
                    // Prepare the SQL statement with placeholders and subqueries
                    $sql = "
                  SELECT 
    p.pemainID,
    p.nama_pemain,
    SUM(CASE WHEN a.kategori = :kategori OR a.kategori = 'ALL' THEN 1 ELSE 0 END) AS total_aktiviti,
    SUM(CASE WHEN (a.kategori = :kategori OR a.kategori = 'ALL') AND k.status_kehadiran = 'attend' THEN 1 ELSE 0 END) AS total_attend,
    SUM(CASE WHEN (a.kategori = :kategori OR a.kategori = 'ALL') AND k.status_kehadiran = 'absent' THEN 1 ELSE 0 END) AS total_absent,
    CASE 
        WHEN SUM(CASE WHEN a.kategori = :kategori OR a.kategori = 'ALL' THEN 1 ELSE 0 END) > 0 THEN 
            ROUND((SUM(CASE WHEN (a.kategori = :kategori OR a.kategori = 'ALL') AND k.status_kehadiran = 'attend' THEN 1 ELSE 0 END) * 100.0) / 
            SUM(CASE WHEN a.kategori = :kategori OR a.kategori = 'ALL' THEN 1 ELSE 0 END))
        ELSE 0
    END AS avg_attend_percentage
FROM 
    tbl_spabs_pemain p
LEFT JOIN 
    tbl_spabs_kehadiran k ON p.pemainID = k.pemainID
JOIN 
    tbl_spabs_aktiviti a ON k.aktivitiID = a.aktivitiID
WHERE 
    (a.kategori = :kategori OR a.kategori = 'ALL') AND p.kategori = :kategori
GROUP BY 
    p.pemainID, p.nama_pemain
UNION
SELECT 
    p.pemainID,
    p.nama_pemain,
    0 AS total_aktiviti,
    0 AS total_attend,
    0 AS total_absent,
    0 AS avg_attend_percentage
FROM 
    tbl_spabs_pemain p
WHERE 
    p.pemainID NOT IN (SELECT DISTINCT pemainID FROM tbl_spabs_kehadiran)
    AND p.kategori = :kategori


                    ";

                    // Prepare the statement
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':kategori', $category, PDO::PARAM_STR);
                    $stmt->execute();
                    $result = $stmt->fetchAll();

                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                } ?>

                <?php
                foreach ($result as $readrow) {

                    $percentage = $readrow['avg_attend_percentage'];
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
                                    <div class="col-md-4 d-flex flex-column justify-content-center ">
                                        <?php echo $readrow['nama_pemain'] ?>
                                    </div>
                                    <div class="col-md-1 d-flex flex-column justify-content-center align-items-center">
                                        <?php echo $readrow['total_aktiviti'] ?>
                                    </div>
                                    <div class="col-md-1 d-flex flex-column justify-content-center align-items-center">
                                        <?php echo $readrow['total_attend'] ?>
                                    </div>
                                    <div class="col-md-1 d-flex flex-column justify-content-center align-items-center">
                                        <?php echo $readrow['total_absent'] ?>
                                    </div>

                                    <div class="col-md-3 d-flex align-items-center">
                                        <div class="progress w-100">
                                            <div class="progress-bar <?php echo $class; ?>" role="progressbar"
                                                style="width: <?php echo $readrow['avg_attend_percentage'] ?>%"
                                                aria-valuenow=" <?php echo $readrow['avg_attend_percentage'] ?>"
                                                aria-valuemin="0" aria-valuemax="100">
                                                <?php echo $readrow['avg_attend_percentage'] ?>%
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-2 ">
                                        <div class="d-flex flex-column justify-content-center align-items-center">
                                            <!-- Details Button -->
                                            <a href="player_attendance_record.php?pid=<?php echo $readrow['pemainID']; ?>"
                                                class="btn btn-outline-success btn-xs mb-2" role="button">View Record</a>


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

    <!-- Modal -->
    <div class="modal fade" id="attendanceModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attendanceModal">Attendance Record</h5>
                    <button type="button" class="custom-close-button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Activity details will be loaded here -->
                </div>
                <!-- <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div> -->
            </div>

        </div>
    </div>

    <!-- jQuery, Popper.js, and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $('#attendanceModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var url = button.data('href'); // Extract info from data-* attributes
            var modal = $(this);

            // Clear previous content before making the AJAX request
            modal.find('.modal-body').html('<div class="text-center"><img src="../pictures/icons/loading.gif" alt="Loading..."></div>');


            // Use jQuery to load the content of the URL into the modal body
            $.ajax({
                url: url,
                success: function (data) {
                    modal.find('.modal-body').html(data);
                }
            });
        });

        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        });
    </script>



</body>

</html>