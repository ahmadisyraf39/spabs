<?php
include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
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
        .accordion,
        .form-container {
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
                <span class="ms-2">Tournament List </span>
                <span class="user-role">Admin</span>
            </header>
            <div class="container p-5 ">
                <h4 class="text-center mb-3">Upcoming Tournament</h4>
                <?php
                try {
                    $currentDate = date('Y-m-d');
                    $stmt = $conn->prepare("SELECT a.*, 
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
       END AS month,
       COALESCE(p.bilangan_pemain, 0) AS bilangan_pemain,
       k.maksimum_pemain
FROM tbl_spabs_aktiviti a
LEFT JOIN (
    SELECT aktivitiID, COUNT(pemainID) AS bilangan_pemain
    FROM tbl_spabs_pemilihan
    WHERE status_pemilihan != 'Unavailable'
    GROUP BY aktivitiID
) p ON a.aktivitiID = p.aktivitiID
LEFT JOIN tbl_spabs_kejohanan k ON a.aktivitiID = k.kejohananID
  WHERE a.jenis_aktiviti = 'Tournament'
  AND a.tarikh_aktiviti >= :currentDate
ORDER BY a.tarikh_aktiviti ASC, a.masa_mula ASC;
");
                    $stmt->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);
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
                        ?>
                        <div class="row mb-3 justify-content-center align-items-center">
                            <div class="card mb-3" style="width: 80%; border: 2px solid black; padding: 0;">
                                <div class="row g-0">
                                    <div class="col-md-2 d-flex flex-column justify-content-center align-items-center"
                                        style="border-right: 1px solid black;">
                                        <div class="month"><?php echo $month; ?></div>
                                        <div class="day"><?php echo $day; ?></div>
                                    </div>
                                    <div class="col-md-5">
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
                                        </div>
                                    </div>

                                    <div class="col-md-2 d-flex flex-column justify-content-center ">
                                        <div class="mt-2 text-center">
                                            <!-- Details Button -->
                                            <button data-href="activity_details.php?aid=<?php echo $readrow['aktivitiID']; ?>"
                                                class="btn btn-outline-success mb-2" role="button" data-toggle="modal"
                                                data-target="#activityModal">Details</button>

                                        </div>
                                    </div>

                                    <div class="col-md-3 d-flex flex-column justify-content-center ">
                                        <div class="mt-2 text-center">
                                            <!-- Details Button -->
                                            <a href="selected_player.php?aid=<?php echo $readrow['aktivitiID']; ?>"
                                                class="btn btn-outline-primary btn-xs mb-2" role="button">Selected Players</a>
                                            <br>
                                            <?php echo $readrow['bilangan_pemain']; ?> /
                                            <?php echo $readrow['maksimum_pemain']; ?> Selected
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
                        <div class="card mb-3" style="width: 80%; border: 2px solid black; padding: 0;">
                            <div class="row g-0">
                                <div class="col-md-12 d-flex flex-column justify-content-center align-items-center">
                                    <div class="card-body">
                                        No Activity Today
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
                    $currentDate = date('Y-m-d');
                    $stmt = $conn->prepare("SELECT *, DATE_FORMAT(tarikh_aktiviti, '%e') AS day, CASE DATE_FORMAT(tarikh_aktiviti, '%m')
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
                            FROM tbl_spabs_aktiviti WHERE jenis_aktiviti = 'Tournament'
                            AND tarikh_aktiviti < :currentDate
                            ORDER BY tarikh_aktiviti DESC, masa_mula ASC;
             
             ");

                    $stmt->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);
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
                        ?>
                        <div class="row mb-3 justify-content-center align-items-center">
                            <div class="card mb-3" style="width: 80%; border: 2px solid black; padding: 0;">
                                <div class="row g-0">
                                    <div class="col-md-2 d-flex flex-column justify-content-center align-items-center"
                                        style="border-right: 1px solid black;">
                                        <div class="month"><?php echo $month; ?></div>
                                        <div class="day"><?php echo $day; ?></div>
                                    </div>
                                    <div class="col-md-7">
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
                                        </div>
                                    </div>
                                    <div class="col-md-3 d-flex flex-column justify-content-center ">
                                        <div class="mt-2 text-center">
                                            <!-- Details Button -->
                                            <a href="selected_player.php?aid=<?php echo $readrow['aktivitiID']; ?>"
                                                class="btn btn-outline-primary btn-xs mb-2" role="button">Selected Players</a>
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
                        <div class="card mb-3" style="width: 80%; border: 2px solid black; padding: 0;">
                            <div class="row g-0">
                                <div class="col-md-12 d-flex flex-column justify-content-center align-items-center">
                                    <div class="card-body">
                                        No Past Tournament
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>

                <!-- Modal -->
                <div class="modal fade" id="unavailableModal" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">

                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="unavailableModalLabel">Unavailable Player</h5>
                                <button type="button" class="custom-close-button" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <?php foreach ($unavailablePlayers as $tournament) { ?>
                                    <p><strong><?php echo $tournament['nama_pemain']; ?></strong> is not available
                                        for the following tournament:
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
            <?php if (!empty($unavailablePlayers)) { ?>
                $('#unavailableModal').modal('show');
            <?php } ?>
        });

        function closeModal() {
            $('#unavailableModal').modal('hide'); // Hide the modal when the close button is clicked
        }

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