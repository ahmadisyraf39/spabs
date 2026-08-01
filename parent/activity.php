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


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Activity List</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">


    <!-- <link href="css/bootstrap.css" rel="stylesheet"> -->
    <link href="../css/main.css" rel="stylesheet">

    <style>
        .emoji-text {
            display: inline-block;
            margin-right: 5px;
            /* Adjust the margin as needed */
        }

        .month {
            font-size: 18px;
            font-weight: bold;
            /* color: #148634; */
        }

        .day {
            font-size: 48px;
            font-weight: bold;
            /* color: #148634; */
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
                <span class="ms-2">Activity List</span>
                <span class="user-role">Parent</span>
            </header>

            <div class="container p-5 ">
                <?php
                try {

                    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    $stmt = $conn->prepare("SELECT DISTINCT kategori FROM tbl_spabs_pemain WHERE ibubapaID = :pid");
                    $stmt->bindParam(':pid', $parentID, PDO::PARAM_STR);
                    $stmt->execute();
                    $categories_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $filter_category = isset($_GET['category']) ? $_GET['category'] : 'all';

                    // $category = $row['kategori'];
                
                    //$playerid = $row['pemainID'];
                
                    $stmt = $conn->prepare("SELECT * FROM tbl_spabs_pemain WHERE ibubapaID = :pid");
                    $stmt->bindParam(':pid', $parentID, PDO::PARAM_STR);
                    $stmt->execute();
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($rows)) { ?>
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
                        exit;
                    }

                    $categories = array_column($rows, 'kategori');

                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }



                try {
                    $query = "SELECT *, DATE_FORMAT(tarikh_aktiviti, '%e') AS day, CASE DATE_FORMAT(tarikh_aktiviti, '%m')
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
                          FROM tbl_spabs_aktiviti";

                    $query_params = [];
                    $conditions = [];

                    // Add the condition for tarikh_aktiviti
                    $conditions[] = "tarikh_aktiviti >= CURDATE()
                                    AND jenis_aktiviti != 'Tournament'";

                    if ($filter_category !== 'all') {
                        $conditions[] = "kategori = :type";
                        $query_params[':type'] = $filter_category;

                        if (!empty($categories)) {
                            $placeholders = implode(',', array_map(fn($i) => ":category_$i", array_keys($categories)));
                            $conditions[] = "kategori IN ($placeholders)";

                            foreach ($categories as $index => $category) {
                                $query_params[":category_$index"] = $category;
                            }
                        }
                    } else {
                        if (!empty($categories)) {
                            $placeholders = implode(',', array_map(fn($i) => ":category_$i", array_keys($categories)));
                            $conditions[] = "(kategori IN ($placeholders) OR kategori = 'ALL')";

                            foreach ($categories as $index => $category) {
                                $query_params[":category_$index"] = $category;
                            }
                        } else {
                            $conditions[] = "kategori = 'ALL'";
                        }
                    }

                    // If there are any conditions, add them to the query
                    if (!empty($conditions)) {
                        $query .= " WHERE " . implode(" AND ", $conditions);
                    }

                    $query .= " ORDER BY tarikh_aktiviti ASC";
                    $stmt = $conn->prepare($query);

                    foreach ($query_params as $key => $value) {
                        $stmt->bindValue($key, $value, PDO::PARAM_STR);
                    }

                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }


                ?>
                <!-- d-flex nowrap justify-content-center align-items-center -->
                <div class="text-center">
                    <div class="filter-buttons mb-5 d-inline-block">
                        Filter by Category:
                        <?php foreach ($categories_result as $category) {
                            $activeClass = ($filter_category == $category['kategori']) ? 'active' : '';
                            ?>
                            <a href="activity.php?category=<?php echo urlencode($category['kategori']); ?>"
                                class="btn btn-outline-success btn-md mx-2 <?php echo $activeClass; ?>">
                                <?php echo $category['kategori']; ?>
                            </a>
                        <?php }
                        $activeClass = ($filter_category == 'all') ? 'active' : '';
                        ?>
                        <a href="activity.php"
                            class="btn btn-outline-success btn-md mx-2 <?php echo $activeClass; ?>">All</a>
                    </div>
                </div>

                <?php

                try {



                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }

                foreach ($result as $readrow) {
                    $aktivitiID = $readrow['aktivitiID'];

                    // Query to check if a leave request exists
                    // $leaveStmt = $conn->prepare("SELECT COUNT(*) 
                    // FROM tbl_spabs_kehadiran AS k 
                    // JOIN tbl_spabs_ketidakhadiran AS t 
                    // ON k.kehadiranID = t.kehadiranID 
                    // WHERE k.pemainID = :playerID 
                    // AND k.aktivitiID = :aktivitiID 
                    // AND k.status_kehadiran = 'Absent'");
                    // $leaveStmt->bindParam(':playerID', $playerid, PDO::PARAM_STR);
                    // $leaveStmt->bindParam(':aktivitiID', $aktivitiID, PDO::PARAM_STR);
                    // $leaveStmt->execute();
                    // $leaveExists = $leaveStmt->fetchColumn() > 0;
                
                    // Query to check if a leave request exists and to select kehadiranID
                    $leaveStmt = $conn->prepare("
                                                SELECT k.kehadiranID
                                                FROM tbl_spabs_kehadiran AS k 
                                                JOIN tbl_spabs_ketidakhadiran AS t 
                                                ON k.kehadiranID = t.kehadiranID 
                                                WHERE k.pemainID = :playerID 
                                                AND k.aktivitiID = :aktivitiID 
                                                AND k.status_kehadiran = 'Absent'
                                                ");
                    $leaveStmt->bindParam(':playerID', $playerid, PDO::PARAM_STR);
                    $leaveStmt->bindParam(':aktivitiID', $aktivitiID, PDO::PARAM_STR);
                    $leaveStmt->execute();
                    $leaveResult = $leaveStmt->fetch(PDO::FETCH_ASSOC);

                    // Check if a leave request exists
                    $leaveExists = $leaveResult !== false;

                    if ($leaveExists) {
                        $kehadiranID = $leaveResult['kehadiranID'];
                        // You can now use $kehadiranID for further processing
                    }

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

                        <div class="card mb-3" style="width: 80%; border: 2px solid black; padding: 0;">
                            <div class="row g-0">
                                <div class="col-md-2 d-flex flex-column justify-content-center align-items-center"
                                    style="border-right: 1px solid black;">
                                    <div class="month"><?php echo $month; ?></div>
                                    <div class="day"><?php echo $day; ?></div>
                                </div>
                                <div class="col-md-6">
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
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                    <div class="month"> <?php echo $readrow['kategori']; ?></div>
                                </div>
                                <div class="col-md-2 d-flex flex-column justify-content-center ">
                                    <div class="mt-2 text-center">
                                        <!-- Details Button -->
                                        <button data-href="activity_details.php?aid=<?php echo $readrow['aktivitiID']; ?>"
                                            class="btn btn-outline-success btn-xs mb-2" role="button" data-toggle="modal"
                                            data-target="#activityModal">Details</button>

                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>



                <?php } ?>

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