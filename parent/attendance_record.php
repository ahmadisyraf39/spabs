<?php
include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Admin") {
    header("location:../admin/index.php");
    exit;
}

$playerID = $_GET['pid'];

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $conn->prepare("SELECT nama_pemain FROM tbl_spabs_pemain WHERE pemainID = :pid");

$stmt->bindParam(':pid', $playerID, PDO::PARAM_STR);

$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$player_name = $row['nama_pemain'];

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Player's Attendance</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">


    <!-- <link href="css/bootstrap.css" rel="stylesheet"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.5/css/dataTables.bootstrap5.css">
    <link href="../css/main.css" rel="stylesheet">



    <style>
        select.form-select {
            display: inline;
            width: 110px;
            margin-left: 10px;
            margin-right: 10px;
        }

        select.form-select:focus {
            box-shadow: 0 0 20px #148634;
        }

        #attendanceTable th:nth-child(4),
        #attendanceTable td:nth-child(4) {
            text-align: center;
        }

        #attendanceTable tbody tr:nth-of-type(odd) {
            background-color: #FFFFFF;
        }

        #attendanceTable tbody tr:last-of-type {
            border-bottom: 1px solid #165227;
        }

        #attendanceTable thead tr th {
            /* background-color: #148634; */
            background-color: #165227;
            color: white;
            text-align: center;
            font-weight: bold;
        }

        #attendanceTable {
            border-collapse: collapse;
            font-size: 0.9em;
            min-width: 400px;
            border-radius: 5px 5px 0 0;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
            border-color: #165227;
            text-align: center;
        }

        #attendanceTable th,
        #attendanceTable td {
            padding: 15px;
            text-align: center;
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

        <?php
        include_once 'sidebar.php'
            ?>

        <div class="main">
            <header>
                <span class="ms-2">Player's Attendance Record - <?php echo $player_name ?></span>
                <span class="user-role">Parent</span>
            </header>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb ms-5 mt-2">
                    <li class="breadcrumb-item"><a href="player_attendance.php">Attendance Record</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Player's Attendance Record</li>
                </ol>
            </nav>
            <div class="container p-5">

                <div class="card mb-5" style="height: 150px;">
                    <div class="card-body text-center">
                        <!-- User profile section -->
                        <h4 class="text-center mb-4">Attendance Statistics</h4>
                        <section class="wow fadeIn animated" style="visibility: visible; animation-name: fadeIn; ">



                            <div class="row text-center">

                                <?php

                                try {
                                    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                                    $stmt = $conn->prepare("SELECT
    COALESCE(SUM(CASE WHEN k.status_kehadiran = 'Absent' THEN 1 ELSE 0 END), 0) AS count_absent,
    COALESCE(SUM(CASE WHEN k.status_kehadiran = 'Attend' THEN 1 ELSE 0 END), 0) AS count_attend,
    COALESCE(SUM(CASE WHEN a.jenis_aktiviti = 'Training' THEN 1 ELSE 0 END), 0) AS count_activity_training,
    COALESCE(SUM(CASE WHEN a.jenis_aktiviti = 'League Match' THEN 1 ELSE 0 END), 0) AS count_activity_league,
    COALESCE(SUM(CASE WHEN a.jenis_aktiviti = 'Tournament' THEN 1 ELSE 0 END), 0) AS count_activity_tourney,
    COALESCE(SUM(CASE WHEN a.jenis_aktiviti = 'Other' THEN 1 ELSE 0 END), 0) AS count_activity_other,
    COALESCE(SUM(CASE WHEN a.jenis_aktiviti = 'Friendly Match' THEN 1 ELSE 0 END), 0) AS count_activity_friendly,
    COALESCE(SUM(
        CASE WHEN a.jenis_aktiviti = 'Training' THEN 1 ELSE 0 END +
        CASE WHEN a.jenis_aktiviti = 'League Match' THEN 1 ELSE 0 END +
        CASE WHEN a.jenis_aktiviti = 'Tournament' THEN 1 ELSE 0 END +
        CASE WHEN a.jenis_aktiviti = 'Other' THEN 1 ELSE 0 END +
        CASE WHEN a.jenis_aktiviti = 'Friendly Match' THEN 1 ELSE 0 END
    ), 0) AS total_activity_count,
    COALESCE(SUM(CASE WHEN k.status_kehadiran = 'Attend' AND a.jenis_aktiviti = 'Training' THEN 1 ELSE 0 END), 0) AS count_attend_training,
    COALESCE(SUM(CASE WHEN k.status_kehadiran = 'Absent' AND a.jenis_aktiviti = 'Training' THEN 1 ELSE 0 END), 0) AS count_absent_training,
    COALESCE(SUM(CASE WHEN k.status_kehadiran = 'Attend' AND a.jenis_aktiviti = 'League Match' THEN 1 ELSE 0 END), 0) AS count_attend_league,
    COALESCE(SUM(CASE WHEN k.status_kehadiran = 'Attend' AND a.jenis_aktiviti = 'Tournament' THEN 1 ELSE 0 END), 0) AS count_attend_tourney,
    COALESCE(SUM(CASE WHEN k.status_kehadiran = 'Attend' AND a.jenis_aktiviti = 'Other' THEN 1 ELSE 0 END), 0) AS count_attend_other,
    COALESCE(SUM(CASE WHEN k.status_kehadiran = 'Attend' AND a.jenis_aktiviti = 'Friendly Match' THEN 1 ELSE 0 END), 0) AS count_attend_friendly,
    COALESCE(SUM(CASE WHEN k.status_kehadiran = 'Absent' AND kt.jenis_sebab = 'Family Outing' THEN 1 ELSE 0 END), 0) AS count_absent_family,
    COALESCE(SUM(CASE WHEN k.status_kehadiran = 'Absent' AND kt.jenis_sebab = 'School Activity' THEN 1 ELSE 0 END), 0) AS count_absent_school,
    COALESCE(SUM(CASE WHEN k.status_kehadiran = 'Absent' AND kt.jenis_sebab = 'Sick/Injury' THEN 1 ELSE 0 END), 0) AS count_absent_sick,
    COALESCE(SUM(CASE WHEN k.status_kehadiran = 'Absent' AND kt.jenis_sebab = 'Other' THEN 1 ELSE 0 END), 0) AS count_absent_other,
    COALESCE(SUM(CASE WHEN k.status_kehadiran = 'Absent' AND kt.jenis_sebab IS NULL THEN 1 ELSE 0 END), 0) AS count_absent_skip,
    ROUND(
        (
            COALESCE(SUM(CASE WHEN k.status_kehadiran = 'Attend' THEN 1 ELSE 0 END), 0) /
            NULLIF(COALESCE(SUM(
                CASE WHEN a.jenis_aktiviti = 'Training' THEN 1 ELSE 0 END +
                CASE WHEN a.jenis_aktiviti = 'League Match' THEN 1 ELSE 0 END +
                CASE WHEN a.jenis_aktiviti = 'Tournament' THEN 1 ELSE 0 END +
                CASE WHEN a.jenis_aktiviti = 'Other' THEN 1 ELSE 0 END +
                CASE WHEN a.jenis_aktiviti = 'Friendly Match' THEN 1 ELSE 0 END
            ), 0), 0)
        ) * 100
    ) AS avg_attend_percentage
                                        
                                    FROM
                                        tbl_spabs_kehadiran k
                                    JOIN
                                        tbl_spabs_aktiviti a ON k.aktivitiID = a.aktivitiID
                                    LEFT JOIN
                                        tbl_spabs_ketidakhadiran kt ON k.kehadiranID = kt.kehadiranID
                                    WHERE
                                        k.pemainID = :pid ");

                                    $stmt->bindParam(':pid', $playerID, PDO::PARAM_STR);
                                    $stmt->execute();
                                    $readrow = $stmt->fetch(PDO::FETCH_ASSOC);


                                    $totalActivity = $readrow['total_activity_count'];
                                    $totalAttend = $readrow['count_attend'];
                                    $totalAbsent = $readrow['count_absent'];
                                    $totalSkip = $readrow['count_absent_skip'];
                                    $totalTrainingMiss = $readrow['count_absent_training'];
                                    $attendanceRate = $readrow['avg_attend_percentage'];


                                } catch (PDOException $e) {
                                    echo "Connection failed: " . $e->getMessage();
                                } finally {
                                    // Close your database connection here
                                    $conn = null;
                                }


                                ?>




                                <!-- counter -->
                                <div class="col-md-2 col-sm-6 bottom-margin text-center counter-section wow fadeInUp sm-margin-bottom-ten animated"
                                    data-wow-duration="300ms"
                                    style="visibility: visible; animation-duration: 900ms; animation-name: fadeInUp;">
                                    <span id="anim-number-pizza" class="counter-number"></span>
                                    <span class="timer counter alt-font appear" data-to="980" data-speed="7000"
                                        style="font-weight: bold;">Total Activity</span>
                                    <p class="counter-title"><?php echo $totalActivity; ?></p>
                                </div>
                                <!-- end counter -->
                                <!-- counter -->
                                <div class="col-md-2 col-sm-6 bottom-margin text-center counter-section wow fadeInUp sm-margin-bottom-ten animated"
                                    data-wow-duration="600ms"
                                    style="visibility: visible; animation-duration: 1200ms; animation-name: fadeInUp;">
                                    <span class="timer counter alt-font appear" data-to="980" data-speed="7000"
                                        style="font-weight: bold;">Total Attend</span>
                                    <p class="counter-title"><?php echo $totalAttend; ?></p>
                                </div>
                                <!-- end counter -->
                                <!-- counter -->
                                <div class="col-md-2 col-sm-6 bottom-margin-small text-center counter-section wow fadeInUp xs-margin-bottom-ten animated"
                                    data-wow-duration="900ms"
                                    style="visibility: visible; animation-duration: 1500ms; animation-name: fadeInUp;">
                                    <span class="timer counter alt-font appear" data-to="810" data-speed="7000"
                                        style="font-weight: bold;">Total Absent</span>
                                    <p class="counter-title"><?php echo $totalAbsent; ?></p>
                                </div>
                                <!-- end counter -->
                                <!-- counter -->
                                <div class="col-md-2 col-sm-6 text-center counter-section wow fadeInUp animated"
                                    data-wow-duration="1200ms"
                                    style="visibility: visible; animation-duration: 1800ms; animation-name: fadeInUp;">
                                    <span class="timer counter alt-font appear" data-to="600" data-speed="7000"
                                        style="font-weight: bold;">Total Skip</span>
                                    <p class="counter-title"><?php echo $totalSkip; ?></p>
                                </div>
                                <!-- end counter -->
                                <!-- counter -->
                                <div class="col-md-2 col-sm-6 text-center counter-section wow fadeInUp animated"
                                    data-wow-duration="1200ms"
                                    style="visibility: visible; animation-duration: 1800ms; animation-name: fadeInUp;">
                                    <span class="timer counter alt-font appear" data-to="600" data-speed="7000"
                                        style="font-weight: bold;">Training Miss</span>
                                    <p class="counter-title"><?php echo $totalTrainingMiss; ?></p>
                                </div>
                                <!-- end counter -->
                                <!-- counter -->
                                <div class="col-md-2 col-sm-6 text-center counter-section wow fadeInUp animated"
                                    data-wow-duration="1200ms"
                                    style="visibility: visible; animation-duration: 1800ms; animation-name: fadeInUp;">
                                    <span class="timer counter alt-font appear" data-to="600" data-speed="7000"
                                        style="font-weight: bold;">Attendance Rate</span>
                                    <p class="counter-title"><?php echo $attendanceRate; ?> %</p>
                                </div>
                                <!-- end counter -->


                            </div>

                        </section>
                    </div>
                </div>

                <!-- Create the drop down filter -->
                <div class="month-filter">
                    <select id="monthFilter" class="form-select form-select-sm">
                        <option value="">Show All</option>
                        <option value="01">January</option>
                        <option value="02">February</option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06">June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                    <span class="caret"></span> <!-- Bootstrap dropdown symbol -->
                </div>

                <table id="attendanceTable" class="table table-striped table-bordered text-center" style="width:100%">
                    <thead>
                        <tr>

                            <th>Date</th>
                            <th>Time</th>
                            <th>Activity Name</th>
                            <th>Activity Type</th>
                            <th>Attendance Status</th>
                            <th>Absence Reason</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceTableBody">
                        <?php

                        try {
                            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            $stmt = $conn->prepare("SELECT * 
                                                    FROM tbl_spabs_kehadiran AS k 
                                                    LEFT JOIN tbl_spabs_ketidakhadiran AS t 
                                                    ON k.kehadiranID = t.kehadiranID
                                                    JOIN tbl_spabs_aktiviti AS a 
                                                    ON k.aktivitiID = a.aktivitiID
                                                    WHERE k.pemainID = :playerID");
                            $stmt->bindParam(':playerID', $playerID, PDO::PARAM_STR);
                            $stmt->execute();
                            $result = $stmt->fetchAll();
                        } catch (PDOException $e) {
                            echo "Error: " . $e->getMessage();
                        }
                        foreach ($result as $readrow) {

                            $start_time = $readrow['masa_mula']; // Assuming $readrow['start_time'] contains a time value in 24-hour format (e.g., 13:30:00)
                        
                            // Convert 24-hour format to 12-hour format with AM/PM
                            $formatted_start_time = date("h:i A", strtotime($start_time));

                            $end_time = $readrow['masa_tamat']; // Assuming $readrow['start_time'] contains a time value in 24-hour format (e.g., 13:30:00)
                        
                            // Convert 24-hour format to 12-hour format with AM/PM
                            $formatted_end_time = date("h:i A", strtotime($end_time));

                            $date = $readrow['tarikh_aktiviti'];
                            $formatted_date = date("d/m/Y", strtotime($date));

                            ?>
                            <tr>
                                <td style="text-align:center;"><?php echo $formatted_date; ?></td>
                                <td style="text-align:center;"><?php echo $formatted_start_time; ?> -
                                    <?php echo $formatted_end_time; ?>
                                </td>
                                <td><?php echo $readrow['nama_aktiviti']; ?></td>
                                <td><?php echo $readrow['jenis_aktiviti']; ?></td>
                                <td><?php echo $readrow['status_kehadiran']; ?></td>
                                <td><?php echo $readrow['status_kehadiran'] == 'Attend' ? '-' : $readrow['jenis_sebab']; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <!-- <div class="buttons-container">
            <a href="register_player.php" class="buttons">Register New Player</a>
        </div> -->

                <!-- Modal -->
                <div class="modal fade" id="playerModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="playerModal">Player Details</h5>
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
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <!-- <script defer
src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script> -->
    <script defer src="https://cdn.datatables.net/2.0.5/js/dataTables.js"></script>
    <script defer src="https://cdn.datatables.net/2.0.5/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


    <script>
        $("document").ready(function () {
            var attendanceTable = $('#attendanceTable').DataTable({
                "columnDefs": [
                    { "orderable": false, "targets": [2, 3, 4] }, // Disable sorting for columns 0, 1, 2, 3 (Name, Email, Phone Number, UserType)
                    { "searchable": false, "targets": [2, 3, 4] }
                ],
                "lengthMenu": [8, 15, 25], // Dropdown options for page length
                "pageLength": 8, // Default number of records per page
                "lengthChange": false, // Enable length change dropdown
                "initComplete": function () {
                    var dtSearchInput = $('#dt-search-0');
                    dtSearchInput.after($("#monthFilter")); // Append the category filter dropdown after the search input
                }
            });

            // Filter function based on dropdown selection
            $('#monthFilter').on('change', function () {
                var selectedMonth = $(this).val();
                if (selectedMonth) {
                    // Filter rows based on the selected month
                    var regexPattern = '/' + selectedMonth + '/';
                    attendanceTable.columns(0).search(regexPattern, true, false).draw();
                } else {
                    // Clear filter if "Show All" is selected
                    attendanceTable.columns(0).search('').draw();
                }

                // Update the display information after filtering
                var filteredRows = attendanceTable.rows({ search: 'applied' }).nodes().length;
                var totalRows = attendanceTable.rows().nodes().length;
                var infoText = 'Showing ' + filteredRows + ' of ' + totalRows + ' entries';
                $('#attendanceTable_info').html(infoText);
            });


            $('#playerModal').on('show.bs.modal', function (event) {
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
            })

        });
    </script>



</body>

</html>