<?php include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}

include_once ("activity_crud.php");
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$categories_query = $conn->prepare("SELECT DISTINCT kategori FROM tbl_spabs_aktiviti
                                    WHERE kategori != 'ALL'
                                    ORDER BY FIELD(kategori, 'U8', 'U10', 'U12')");
$categories_query->execute();
$categories_result = $categories_query->fetchAll(PDO::FETCH_ASSOC);

$filter_category = isset($_GET['category']) ? $_GET['category'] : 'all';
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

        .modal-header {
            background-color: #165227;
            color: white;

        }

        .custom-close-button {
            border: none;
            /* Removes the border */
            outline: none;
            /* Removes the outline that appears when the button is focused */
            background: transparent;
            /* Ensures the background is transparent */
            padding: 0;
            /* Removes any padding that might create visual space */
            margin: 0;
            /* Removes any margin */
            box-shadow: none;
            /* Removes any box-shadow which might look like a border */
            color: white;
            font-size: 1.5rem;
            /* Adjust the size as needed */
        }

        .custom-close-button:hover,
        .custom-close-button:focus {
            outline: none;
            /* Ensures no outline appears when focused or hovered */
        }

        .floating-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #148634;
            /* background-color: #165227; */
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 24px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .floating-button:hover {
            background-color: #165227;
            /* background-color: #148634; */
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
                <span class="ms-2">Activity List</span>
                <span class="user-role">Admin</span>
            </header>

            <div class="container p-5">
                <!-- d-flex nowrap justify-content-center align-items-center -->
                <div class="text-center">
                    <div class="filter-buttons mb-5 d-inline-block">
                        Filter by Category:
                        <?php foreach ($categories_result as $category) {
                            $activeClass = ($filter_category == $category['kategori']) ? 'active' : '';
                            ?>
                            <a href="activity_list.php?category=<?php echo urlencode($category['kategori']); ?>"
                                class="btn btn-outline-success btn-md mx-2 <?php echo $activeClass; ?>">
                                <?php echo $category['kategori']; ?>
                            </a>
                        <?php }
                        $activeClass = ($filter_category == 'all') ? 'active' : '';
                        ?>
                        <a href="activity_list.php"
                            class="btn btn-outline-success btn-md mx-2 <?php echo $activeClass; ?>">All</a>
                    </div>
                </div>

                <?php
                try {
                    // Get the current date in the format MySQL expects (YYYY-MM-DD)
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
                        END AS month FROM tbl_spabs_aktiviti WHERE tarikh_aktiviti >= :currentDate " . ($filter_category !== 'all' ? "AND kategori = :category" : "") . " ORDER BY tarikh_aktiviti ASC, masa_mula ASC");

                    $stmt->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);
                    if ($filter_category !== 'all') {
                        $stmt->bindParam(':category', $filter_category, PDO::PARAM_STR);
                    }
                    $stmt->execute();
                    $result = $stmt->fetchAll();
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }
                if (count($result) >= 1) {
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
                            <div class="card mb-3" style="width: 80%; border: 1px solid black; padding: 0;">
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
                                                    <?php echo $formatted_start_time; ?> -
                                                    <?php echo $formatted_end_time; ?>
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
                                    <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                        <div class=" mt-2 text-center">
                                            <!-- Details Button -->
                                            <button data-href="activity_details.php?aid=<?php echo $readrow['aktivitiID']; ?>"
                                                class="btn btn-outline-success btn-xs mb-2" role="button" data-toggle="modal"
                                                data-target="#activityModal">Details</button>
                                            <!-- Edit Button -->
                                            <a href="edit_activity.php?edit=<?php echo $readrow['aktivitiID']; ?>"
                                                class="btn btn-outline-primary btn-xs mb-2" role="button">Edit</a>
                                            <!-- Delete Button -->
                                            <a href="activity_crud.php?delete=<?php echo $readrow['aktivitiID']; ?>"
                                                onclick="return confirm('Are you sure you want to delete this activity?');"
                                                class="btn btn-outline-danger btn-xs" role="button">Delete</a>
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
                                        No Upcoming Activity
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>

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



                <a href="add_activity.php" class="floating-button" data-toggle="tooltip" data-placement="left"
                    title="Add New Activity">+</a>



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

        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        });
    </script>



</body>

</html>