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
    <title>SPABS: Player Attendance</title>
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
                <span class="ms-2">Player Attendance</span>
                <span class="user-role">Parent</span>
            </header>

            <div class="container p-5">


                <?php

                try {
                    // Prepare the SQL statement with placeholders and subqueries
                    $sql = "
                     SELECT 
                            p.pemainID,
                            p.nama_pemain,
                            p.kategori,
                            SUM(CASE WHEN a.kategori = p.kategori OR a.kategori = 'ALL' THEN 1 ELSE 0 END) AS total_aktiviti,
                            SUM(CASE WHEN (a.kategori = p.kategori OR a.kategori = 'ALL') AND k.status_kehadiran = 'attend' THEN 1 ELSE 0 END) AS total_attend,
                            SUM(CASE WHEN (a.kategori = p.kategori OR a.kategori = 'ALL') AND k.status_kehadiran = 'absent' THEN 1 ELSE 0 END) AS total_absent,
                            CASE 
                                WHEN SUM(CASE WHEN a.kategori = p.kategori OR a.kategori = 'ALL' THEN 1 ELSE 0 END) > 0 THEN 
                                    ROUND((SUM(CASE WHEN (a.kategori = p.kategori OR a.kategori = 'ALL') AND k.status_kehadiran = 'attend' THEN 1 ELSE 0 END) * 100.0) / 
                                    SUM(CASE WHEN a.kategori = p.kategori OR a.kategori = 'ALL' THEN 1 ELSE 0 END))
                                ELSE 0
                            END AS avg_attend_percentage
                        FROM 
                            tbl_spabs_pemain p
                        LEFT JOIN 
                            tbl_spabs_kehadiran k ON p.pemainID = k.pemainID
                        LEFT JOIN 
                            tbl_spabs_aktiviti a ON k.aktivitiID = a.aktivitiID
                        WHERE 
                            p.ibubapaID = :ibubapaID
                        GROUP BY 
                            p.pemainID, p.nama_pemain, p.kategori

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
                                                <a href="attendance_record.php?pid=<?php echo $readrow['pemainID']; ?>"
                                                    class="btn btn-outline-success btn-xs mb-2 ms-3 " role="button">View
                                                    Record</a>


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

                <div class="text-center mt-5 mb-3">
                    <h4>Leave Sent</h4>
                </div>



                <?php

                $stmt = $conn->prepare("SELECT *, 
                DATE_FORMAT(a.tarikh_aktiviti, '%e') AS day, CASE DATE_FORMAT(a.tarikh_aktiviti, '%m')
                            WHEN '01' THEN 'Jan'
                            WHEN '02' THEN 'Feb'
                            WHEN '03' THEN 'Mar'
                            WHEN '04' THEN 'Apr'
                            WHEN '05' THEN 'May'
                            WHEN '06' THEN 'Jun'
                            WHEN '07' THEN 'Jul'
                            WHEN '08' THEN 'Aug'
                            WHEN '09' THEN 'Sep'
                            WHEN '10' THEN 'Oct'
                            WHEN '11' THEN 'Nov'
                            WHEN '12' THEN 'Dec'
                            END AS month  FROM tbl_spabs_kehadiran k
                                            JOIN tbl_spabs_pemain p ON p.pemainID = k.pemainID
                                            JOIN tbl_spabs_aktiviti a ON a.aktivitiID = k.aktivitiID
                                            JOIN tbl_spabs_ketidakhadiran th ON th.kehadiranID = k.kehadiranID
                                            WHERE p.ibubapaID = :ibubapaID
                                            AND a.tarikh_aktiviti >= CURDATE()
                                        ");
                $stmt->bindParam(':ibubapaID', $ibubapaid, PDO::PARAM_STR);
                $stmt->execute();
                $result = $stmt->fetchAll();


                ?>

                <?php
                if (empty($result)) { ?>
                    <div class="row mb-3 justify-content-center align-items-center">
                        <div class="card mb-3" style="width: 100%; border: 2px solid black; padding: 0;">
                            <div class="row g-0">
                                <div class="col-md-12 d-flex flex-column justify-content-center align-items-center">
                                    <div class="card-body">
                                        No Leave Sent
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <?php
                } else {
                    foreach ($result as $readrow) {

                        $day = $readrow['day'];
                        $month = $readrow['month'];

                        $start_time = $readrow['masa_mula']; // Assuming $readrow['start_time'] contains a time value in 24-hour format (e.g., 13:30:00)
                
                        // Convert 24-hour format to 12-hour format with AM/PM
                        $formatted_start_time = date("h:i A", strtotime($start_time));

                        $end_time = $readrow['masa_tamat']; // Assuming $readrow['start_time'] contains a time value in 24-hour format (e.g., 13:30:00)
                
                        // Convert 24-hour format to 12-hour format with AM/PM
                        $formatted_end_time = date("h:i A", strtotime($end_time));
                        ?>


                        <div class="row mb-3 justify-content-center align-items-center">

                            <div class="card mb-3" style="width: 90%; border: 2px solid black; padding: 0;">
                                <div class="row g-0">
                                    <div class="col-md-2 d-flex flex-column justify-content-center align-items-center"
                                        style="border-right: 1px solid black;">
                                        <div class="month"><?php echo $month; ?></div>
                                        <div class="day"><?php echo $day; ?></div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="card-body">
                                            <h5 class="card-title mb-4"> <?php echo $readrow['nama_aktiviti']; ?></h5>

                                            <div class="no-margin">
                                                <p class="card-text"><span class="emoji-text">🕒</span>
                                                    <?php echo $formatted_start_time; ?> - <?php echo $formatted_end_time; ?>
                                                </p>
                                            </div>
                                            <div class="no-padding">
                                                <p class="card-text"><span class="emoji-text">📍</span>
                                                    <?php echo $readrow['lokasi']; ?></p>
                                            </div>
                                            <div class="no-margin">
                                                <p class="card-text"><span class="emoji-text">👤</span>
                                                    <?php echo $readrow['nama_pemain']; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3 d-flex flex-column justify-content-center ">

                                        <div class="text-center">
                                            <button
                                                onclick="return confirmLeaveCancellation('<?php echo $readrow['kehadiranID']; ?>');"
                                                class="btn btn-outline-danger btn-xs mb-2">Cancel Leave</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php
                    }
                }
                ?>

                <div class="text-center">
                    <button data-href="send_leave.php" class="btn btn-primary btn-xs mb-2" role="button"
                        data-toggle="modal" data-target="#sendLeaveModal">Send Leave</button>
                </div>



                <!-- Modal -->
                <div class="modal fade" id="sendLeaveModal" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">

                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="sendLeaveModal">Send Leave</h5>
                                <button type="button" class="custom-close-button" data-dismiss="modal"
                                    aria-label="Close">
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


            </div>

        </div>
    </div>



    <!-- jQuery, Popper.js, and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>

        $('#sendLeaveModal').on('show.bs.modal', function (event) {
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

        function confirmLeaveCancellation(kehadiranID) {
            if (confirm('Are you sure you want to cancel the leave?')) {
                window.location.href = 'leave_crud.php?delete=' + kehadiranID;
                return true;
            }
            return false;
        }


    </script>




</body>

</html>