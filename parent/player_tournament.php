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

$stmt = $conn->prepare("SELECT * FROM tbl_spabs_ibubapa WHERE ibubapaID = :ibubapaid");

$stmt->bindParam(':ibubapaid', $parentID, PDO::PARAM_STR);

$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$ibubapaid = $row['ibubapaID'];

// Fetch selected tournaments with 'Unnotified' status
try {
    $stmt = $conn->prepare("SELECT a.aktivitiID, a.nama_aktiviti,  m.nama_pemain FROM tbl_spabs_aktiviti a
        JOIN tbl_spabs_pemilihan p ON a.aktivitiID = p.aktivitiID
        JOIN tbl_spabs_pemain m ON p.pemainID = m.pemainID
        WHERE m.ibubapaID = :ibubapaID
        AND p.status_notifikasi = 'Unnotified'
        AND p.status_pemilihan = 'Selected'
        AND a.tarikh_aktiviti > CURRENT_DATE");
    $stmt->bindParam(':ibubapaID', $ibubapaid, PDO::PARAM_STR);
    $stmt->execute();
    $selectedTournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Update the status_notifikasi to 'Notified'
if (!empty($selectedTournaments)) {
    try {
        $stmt = $conn->prepare("UPDATE tbl_spabs_pemilihan p
            JOIN tbl_spabs_pemain m ON p.pemainID = m.pemainID
            SET p.status_notifikasi = 'Notified'
            WHERE m.ibubapaID = :ibubapaID
            AND p.status_notifikasi = 'Unnotified'");
        $stmt->bindParam(':ibubapaID', $ibubapaid, PDO::PARAM_STR);
        $stmt->execute();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Update the status_pemilihan to 'Unavailable'
if (isset($_POST['update'])) {
    $pid = $_POST['pemainID'];
    $aid = $_POST['aktivitiID'];
    try {
        $stmt = $conn->prepare("UPDATE tbl_spabs_pemilihan 
            SET status_pemilihan = 'Unavailable',
            status_notifikasi = 'Unnotified'
            WHERE pemainID = :pemainID
            AND aktivitiID = :aktivitiID");
        $stmt->bindParam(':pemainID', $pid, PDO::PARAM_STR);
        $stmt->bindParam(':aktivitiID', $aid, PDO::PARAM_STR);
        $stmt->execute();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Tournament</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">

    <link href="../css/main.css" rel="stylesheet">
    <style>
        .emoji-text {
            display: inline-block;
            margin-right: 5px;
        }

        .month {
            font-size: 18px;
            font-weight: bold;
        }

        .day {
            font-size: 48px;
            font-weight: bold;
        }

        .card,
        .accordion {
            box-shadow: 10px 10px 5px #888888;
            /* horizontal offset, vertical offset, blur radius, shadow color */
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include_once 'sidebar.php'; ?>
        <div class="main">
            <header>
                <span class="ms-2">Tournament Selection Record</span>
                <span class="user-role">Parent</span>
            </header>
            <div class="container p-5 ">
                <h4 class="text-center mb-3">Upcoming Tournament</h4>
                <?php
                try {

                    $stmt = $conn->prepare("SELECT *, 
       DATE_FORMAT(a.tarikh_aktiviti, '%e') AS day, 
       CASE DATE_FORMAT(a.tarikh_aktiviti, '%m')
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
       END AS month
FROM tbl_spabs_aktiviti a
JOIN tbl_spabs_pemilihan p ON a.aktivitiID = p.aktivitiID
JOIN tbl_spabs_pemain m ON p.pemainID = m.pemainID
WHERE m.ibubapaID = :ibubapaID
 AND a.tarikh_aktiviti > CURRENT_DATE
 AND p.status_pemilihan = 'Selected'
             ORDER BY a.tarikh_aktiviti ASC, a.masa_mula ASC;
             
             ");
                    $stmt->bindParam(':ibubapaID', $ibubapaid, PDO::PARAM_STR);
                    $stmt->execute();
                    $result = $stmt->fetchAll();
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }
                if (count($result) >= 1) {
                    foreach ($result as $readrow) {
                        $day = $readrow['day'];
                        $month = $readrow['month'];
                        $start_time = $readrow['masa_mula'];
                        $formatted_start_time = date("h:i A", strtotime($start_time));
                        $end_time = $readrow['masa_tamat'];
                        $formatted_end_time = date("h:i A", strtotime($end_time));

                        $percentage = $readrow['kadar_kemajuan'];
                        $classProgress = 'bg-success';

                        if ($percentage <= 25) {
                            $classProgress = 'bg-danger';
                        } elseif ($percentage <= 50) {
                            $classProgress = 'bg-warning';
                        } elseif ($percentage <= 75) {
                            $classProgress = 'bg-primary';
                        } else {
                            $classProgress = 'bg-success';
                        }

                        $percentagee = $readrow['kadar_kehadiran'];
                        $classAttend = 'bg-success';

                        if ($percentagee <= 25) {
                            $classAttend = 'bg-danger';
                        } elseif ($percentagee <= 50) {
                            $classAttend = 'bg-warning';
                        } elseif ($percentagee <= 75) {
                            $classAttend = 'bg-primary';
                        } else {
                            $classAttend = 'bg-success';
                        }

                        ?>
                        <div class="row mb-3 justify-content-center align-items-center">
                            <div class="card mb-3" style="width: 100%; border: 2px solid black; padding: 0;">
                                <div class="row g-0">
                                    <div class="col-md-2 d-flex flex-column justify-content-center align-items-center"
                                        style="border-right: 1px solid black;">
                                        <div class="month"><?php echo $month; ?></div>
                                        <div class="day"><?php echo $day; ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card-body">
                                            <h5 class="card-title mb-4"><?php echo $readrow['nama_aktiviti']; ?></h5>
                                            <div class="no-margin">
                                                <p class="card-text"><span
                                                        class="emoji-text">🕒</span><?php echo $formatted_start_time; ?> -
                                                    <?php echo $formatted_end_time; ?>
                                                </p>
                                            </div>
                                            <div class="no-padding">
                                                <p class="card-text"><span
                                                        class="emoji-text">📍</span><?php echo $readrow['lokasi']; ?></p>
                                            </div>
                                            <div class="no-padding">
                                                <p class="card-text"><span
                                                        class="emoji-text">👤</span><?php echo $readrow['nama_pemain']; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 d-flex flex-column justify-content-center px-3">
                                        <div class="text-center">Progress Rate:</div>
                                        <div class="progress">
                                            <div class="progress-bar <?php echo $classProgress; ?>" role="progressbar"
                                                style="width: <?php echo $readrow['kadar_kemajuan']; ?>%"
                                                aria-valuenow="<?php echo $readrow['kadar_kemajuan']; ?>" aria-valuemin="0"
                                                aria-valuemax="100">
                                                <?php echo $readrow['kadar_kemajuan']; ?>%
                                            </div>
                                        </div>

                                        <br>

                                        <div class="text-center">Attendance Rate:</div>

                                        <div class="progress">
                                            <div class="progress-bar <?php echo $classAttend; ?>" role="progressbar"
                                                style="width: <?php echo $readrow['kadar_kehadiran']; ?>%"
                                                aria-valuenow="<?php echo $readrow['kadar_kehadiran']; ?>" aria-valuemin="0"
                                                aria-valuemax="100">
                                                <?php echo $readrow['kadar_kehadiran']; ?>%
                                            </div>
                                        </div>

                                    </div>



                                    <div class="col-md-2 d-flex flex-column justify-content-center ">
                                        <div class="mt-2 text-center">
                                            <!-- Details Button -->
                                            <button data-href="activity_details.php?aid=<?php echo $readrow['aktivitiID']; ?>"
                                                class="btn btn-outline-success mb-2" role="button" data-toggle="modal"
                                                data-target="#activityModal">Details</button>
                                            <br>
                                            <button type="button" class="btn btn-outline-danger" role="button"
                                                data-toggle="modal" data-target="#absentModal"
                                                data-pemainid="<?php echo $readrow['pemainID']; ?>"
                                                data-aktivitiid="<?php echo $readrow['aktivitiID']; ?>" name="absent">Send
                                                Leave</button>
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
                                        No Upcoming Tournament Selected
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <h4 class="text-center mb-3">Past Tournament</h4>
                <?php
                try {

                    $stmt = $conn->prepare("SELECT *, 
       DATE_FORMAT(a.tarikh_aktiviti, '%e') AS day, 
       CASE DATE_FORMAT(a.tarikh_aktiviti, '%m')
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
       END AS month
FROM tbl_spabs_aktiviti a
JOIN tbl_spabs_pemilihan p ON a.aktivitiID = p.aktivitiID
JOIN tbl_spabs_pemain m ON p.pemainID = m.pemainID
WHERE m.ibubapaID = :ibubapaID
 AND a.tarikh_aktiviti <= CURRENT_DATE
  AND (p.status_pemilihan = 'Selected' OR p.status_pemilihan = 'Unavailable')
             ORDER BY a.tarikh_aktiviti DESC, a.masa_mula ASC;
             ");
                    $stmt->bindParam(':ibubapaID', $ibubapaid, PDO::PARAM_STR);
                    $stmt->execute();
                    $result = $stmt->fetchAll();
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }
                if (count($result) >= 1) {
                    foreach ($result as $readrow) {
                        $day = $readrow['day'];
                        $month = $readrow['month'];
                        $start_time = $readrow['masa_mula'];
                        $formatted_start_time = date("h:i A", strtotime($start_time));
                        $end_time = $readrow['masa_tamat'];
                        $formatted_end_time = date("h:i A", strtotime($end_time));

                        $percentage = $readrow['kadar_kemajuan'];
                        $classProgress = 'bg-success';

                        if ($percentage <= 25) {
                            $classProgress = 'bg-danger';
                        } elseif ($percentage <= 50) {
                            $classProgress = 'bg-warning';
                        } elseif ($percentage <= 75) {
                            $classProgress = 'bg-primary';
                        } else {
                            $classProgress = 'bg-success';
                        }

                        $percentagee = $readrow['kadar_kehadiran'];
                        $classAttend = 'bg-success';

                        if ($percentagee <= 25) {
                            $classAttend = 'bg-danger';
                        } elseif ($percentagee <= 50) {
                            $classAttend = 'bg-warning';
                        } elseif ($percentagee <= 75) {
                            $classAttend = 'bg-primary';
                        } else {
                            $classAttend = 'bg-success';
                        }

                        $selection = $readrow['status_pemilihan'];
                        $classSelection = 'text=success';

                        if ($selection == 'Unavailable') {
                            $classSelection = 'text-danger';
                        } else {
                            $classSelection = 'text-success';
                        }

                        ?>
                        <div class="row mb-3 justify-content-center align-items-center">
                            <div class="card mb-3" style="width: 100%; border: 2px solid black; padding: 0;">
                                <div class="row g-0">
                                    <div class="col-md-2 d-flex flex-column justify-content-center align-items-center"
                                        style="border-right: 1px solid black;">
                                        <div class="month"><?php echo $month; ?></div>
                                        <div class="day"><?php echo $day; ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card-body">
                                            <h5 class="card-title mb-4"><?php echo $readrow['nama_aktiviti']; ?></h5>
                                            <div class="no-margin">
                                                <p class="card-text"><span
                                                        class="emoji-text">🕒</span><?php echo $formatted_start_time; ?> -
                                                    <?php echo $formatted_end_time; ?>
                                                </p>
                                            </div>
                                            <div class="no-padding">
                                                <p class="card-text"><span
                                                        class="emoji-text">📍</span><?php echo $readrow['lokasi']; ?></p>
                                            </div>
                                            <div class="no-padding">
                                                <p class="card-text"><span
                                                        class="emoji-text">👤</span><?php echo $readrow['nama_pemain']; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 d-flex flex-column justify-content-center px-3">
                                        <div class="text-center">Progress Rate:</div>
                                        <div class="progress">
                                            <div class="progress-bar <?php echo $classProgress; ?>" role="progressbar"
                                                style="width: <?php echo $readrow['kadar_kemajuan']; ?>%"
                                                aria-valuenow="<?php echo $readrow['kadar_kemajuan']; ?>" aria-valuemin="0"
                                                aria-valuemax="100">
                                                <?php echo $readrow['kadar_kemajuan']; ?>%
                                            </div>
                                        </div>

                                        <br>

                                        <div class="text-center">Attendance Rate:</div>

                                        <div class="progress">
                                            <div class="progress-bar <?php echo $classAttend; ?>" role="progressbar"
                                                style="width: <?php echo $readrow['kadar_kehadiran']; ?>%"
                                                aria-valuenow="<?php echo $readrow['kadar_kehadiran']; ?>" aria-valuemin="0"
                                                aria-valuemax="100">
                                                <?php echo $readrow['kadar_kehadiran']; ?>%
                                            </div>
                                        </div>

                                    </div>

                                    <div class="col-md-2 d-flex flex-column justify-content-center px-3">
                                        <div class="text-center <?php echo $classSelection; ?>">
                                            <?php echo $readrow['status_pemilihan']; ?>
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
                                        No Tournament Selected
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>

                <!-- Modal -->
                <div class="modal fade" id="absentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="absentModal">Absent</h5>
                                <button type="button" class="custom-close-button" onclick="closeModal()"
                                    data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="" method="POST" id="absentForm">
                                    <input type="hidden" name="pemainID" id="modalPemainID">
                                    <input type="hidden" name="aktivitiID" id="modalAktivitiID">
                                    <div class="row mb-5 text-center">
                                        Are you sure the player can't join this tournament?
                                    </div>

                            </div>
                            <div class="modal-footer">
                                <div class="text-center">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                                    <button type="submit" name="update" class="btn btn-success">Confirm</button>
                                </div>
                            </div>

                            </form>
                        </div>

                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="activityModal" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">

                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="activityModal">Activity Details</h5>
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

                <!-- Modal -->
                <div class="modal fade" id="congratsModal" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">

                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="congratsModalLabel">Congratulations!</h5>
                                <button type="button" class="custom-close-button" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <?php foreach ($selectedTournaments as $tournament) { ?>
                                    <p>Congratulations! <strong><?php echo $tournament['nama_pemain']; ?></strong> has been
                                        selected for the following tournament:
                                        <strong><?php echo $tournament['nama_aktiviti']; ?></strong>
                                    </p>
                                <?php } ?>
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
        $(document).ready(function () {
            <?php if (!empty($selectedTournaments)) { ?>
                $('#congratsModal').modal('show');
            <?php } ?>
        });

        function closeModal() {
            $('#congratsModal').modal('hide'); // Hide the modal when the close button is clicked
        }


        $('#absentModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var pemainID = button.data('pemainid'); // Extract info from data-* attributes
            var aktivitiID = button.data('aktivitiid'); // Extract info from data-* attributes
            var modal = $(this);

            // Populate the hidden input fields
            modal.find('#modalPemainID').val(pemainID);
            modal.find('#modalAktivitiID').val(aktivitiID);
        });

        $('#absenteeModal').on('show.bs.modal', function (event) {
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

        $('#activityModal').on('show.bs.modal', function (event) {
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


    </script>

</body>

</html>