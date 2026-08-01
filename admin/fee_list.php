<?php
include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}

$filter_category = isset($_GET['category']) ? $_GET['category'] : 'all';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Fee List</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">


    <!-- <link href="css/bootstrap.css" rel="stylesheet"> -->
    <link href="../css/main.css" rel="stylesheet">

    <style>
        .price {
            font-size: 18px;
            font-weight: bold;
            /* color: #148634; */
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

        <?php
        include_once 'sidebar.php'
            ?>

        <div class="main">

            <header>
                <span class="ms-2">Fee List</span>
                <span class="user-role">Admin</span>
            </header>

            <div class="container p-5">

                <div class="text-center">
                    <div class="filter-buttons mb-3 d-inline-block mb-5">
                        Filter by Category:

                        <a href="fee_list.php?category=U8"
                            class="btn btn-outline-success btn-md mx-2 <?php echo $filter_category == 'U8' ? 'active' : ''; ?>">
                            U8
                        </a>
                        <a href="fee_list.php?category=U10"
                            class="btn btn-outline-success btn-md mx-2 <?php echo $filter_category == 'U10' ? 'active' : ''; ?>">
                            U10
                        </a>
                        <a href="fee_list.php?category=U12"
                            class="btn btn-outline-success btn-md mx-2 <?php echo $filter_category == 'U12' ? 'active' : ''; ?>">
                            U12
                        </a>
                        <a href="fee_list.php?category=all"
                            class="btn btn-outline-success btn-md mx-2 <?php echo $filter_category == 'all' ? 'active' : ''; ?>">
                            All
                        </a>

                    </div>
                </div>

                <?php
                try {
                    // Get the current date in the format MySQL expects (YYYY-MM-DD)
                    $currentDate = date('Y-m-d');

                    $stmt = $conn->prepare("  SELECT 
            y.yuranID, 
            y.nama_yuran,
            y.jumlah_yuran,
            y.kategori,
            (
                SELECT COUNT(pemainID) 
                FROM tbl_spabs_pemain p
                WHERE y.tarikh > p.tarikh_daftar 
                    AND (p.kategori = :category OR :category = 'ALL')
            ) AS total_player_exceeding_date,
            (
                SELECT COUNT(pemainID) 
                FROM tbl_spabs_bayaran b
                WHERE y.yuranID = b.yuranID
                AND b.status_bayaran = 'Paid'
            ) AS paid_player,
            (
                (
                    SELECT COUNT(pemainID) 
                    FROM tbl_spabs_pemain P
                    WHERE y.tarikh > p.tarikh_daftar 
                    AND (p.kategori = :category OR :category = 'ALL')
                ) 
                - 
                (
                    SELECT COUNT(pemainID) 
                    FROM tbl_spabs_bayaran b
                    WHERE y.yuranID = b.yuranID
                    AND b.status_bayaran = 'Paid'
                )
            ) AS unpaid_player
        FROM tbl_spabs_yuran y
        WHERE :category = 'ALL' OR y.kategori = :category
        ORDER BY y.tarikh DESC");

                    $stmt->bindParam(':category', $filter_category, PDO::PARAM_STR);
                    $stmt->execute();
                    $result = $stmt->fetchAll();
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }
                if (count($result) >= 1) {
                    foreach ($result as $readrow) {

                        ?>

                        <div class="row mb-3 justify-content-center align-items-center">
                            <div class="card p-2 mb-3" style="width: 80%; border: 1px solid black; padding: 0;">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-5 d-flex flex-column justify-content-center">
                                            <div class="price">
                                                <?php echo $readrow['nama_yuran'] ?>
                                            </div>
                                            <div>
                                                For <?php echo $readrow['kategori'] ?>
                                            </div>
                                        </div>

                                        <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                            <div class="price"> RM <?php echo $readrow['jumlah_yuran'] ?></div>
                                        </div>
                                        <div class="col-md-3 d-flex flex-column justify-content-center align-items-center">
                                            <div><?php echo $readrow['paid_player'] ?><span style="color:green;">
                                                    Completed</span> </div>

                                            <div><?php echo $readrow['unpaid_player'] ?><span style="color:red;"> Pending
                                                    Payment</span></div>
                                        </div>
                                        <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                            <div class="text-center"></div>
                                            <!-- Details Button -->
                                            <a href="fee_payment_details.php?yid=<?php echo $readrow['yuranID'] ?>"
                                                class="btn btn-outline-success btn-xs mb-2" role="button">Details</a>
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
                                        No Fee Created
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>



                <button id="addFee" data-href="add_fee.php" class="floating-button" role="button" data-toggle="modal"
                    data-target="#addFeeModal" data-tippy-content="Add Fee">+</button>

                <!-- Modal -->
                <div class="modal fade" id="addFeeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">

                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addFeeModal">Add New Fee</h5>
                                <button type="button" class="custom-close-button" data-dismiss="modal"
                                    aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">


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


    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>

    <script>
        // Initialize Tippy.js tooltips
        tippy('#addFee', {
            placement: 'left'
        });


        $('#addFeeModal').on('show.bs.modal', function (event) {
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