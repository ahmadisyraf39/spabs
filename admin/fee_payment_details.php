<?php
include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}

include_once ("payment_crud.php");

if (isset($_GET['yid'])) {
    $yid = $_GET['yid'];


    $filter_category = isset($_GET['category']) ? $_GET['category'] : 'all';

    try {
        // Get the kategori from tbl_spabs_yuran based on yid
        $stmt = $conn->prepare("SELECT kategori, nama_yuran, jumlah_yuran FROM tbl_spabs_yuran WHERE yuranID = :yid");
        $stmt->bindParam(':yid', $yid, PDO::PARAM_STR);
        $stmt->execute();
        $Row = $stmt->fetch(PDO::FETCH_ASSOC);
        $kategori = $Row['kategori'];
        $payment = $Row['jumlah_yuran'];

        if ($kategori === 'ALL') {
            $sql = "SELECT 
    p.pemainID,
    p.nama_pemain,
    p.kategori,
    i.nama_ibubapa,
    i.ibubapaID,
    b.bayaranID,
    CASE 
        WHEN b.status_bayaran IS NULL THEN 'Unpaid'
        ELSE b.status_bayaran
    END AS status_bayaran
FROM tbl_spabs_pemain p
LEFT JOIN tbl_spabs_bayaran b ON p.pemainID = b.pemainID AND b.yuranID = :yid
LEFT JOIN tbl_spabs_ibubapa i ON p.ibubapaID = i.ibubapaID
WHERE p.tarikh_daftar < (SELECT tarikh FROM tbl_spabs_yuran WHERE yuranID = :yid)";

            // Conditionally add category filter
            if ($filter_category !== 'all') {
                $sql .= " AND p.kategori = :kategori";
            }

            $sql .= " ORDER BY 
    CASE 
        WHEN b.bayaranID IS NOT NULL THEN 
            CASE 
                WHEN b.status_bayaran = 'Paid' THEN 1
                WHEN b.status_bayaran = 'Unpaid' THEN 2
                ELSE 3
            END
        ELSE 4 
    END,
    p.nama_pemain";
        } else {
            $sql = "SELECT 
                p.pemainID,
                p.nama_pemain,
                p.kategori,
                i.nama_ibubapa,
                i.ibubapaID,
                b.bayaranID,
                CASE 
                    WHEN b.status_bayaran IS NULL THEN 'Unpaid'
        ELSE b.status_bayaran
                END AS status_bayaran
            FROM tbl_spabs_pemain p
            LEFT JOIN tbl_spabs_bayaran b ON p.pemainID = b.pemainID AND b.yuranID = :yid
            LEFT JOIN tbl_spabs_ibubapa i ON p.ibubapaID = i.ibubapaID
            WHERE  p.tarikh_daftar < (SELECT tarikh FROM tbl_spabs_yuran WHERE yuranID = :yid)
                    AND p.kategori = :kategori
            ORDER BY 
    CASE 
        WHEN b.bayaranID IS NOT NULL THEN 
            CASE 
                WHEN b.status_bayaran = 'Paid' THEN 1
                WHEN b.status_bayaran = 'Unpaid' THEN 2
                ELSE 3
            END
        ELSE 4  -- for NULL values of b.bayaranID
    END,
    p.nama_pemain";
        }

        $stmt = $conn->prepare($sql);

        if ($filter_category !== 'all') {
            $stmt->bindParam(':kategori', $filter_category, PDO::PARAM_STR);
        }

        $stmt->bindParam(':yid', $yid, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);


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
    <title>SPABS: Fee Payment Details</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">


    <!-- <link href="css/bootstrap.css" rel="stylesheet"> -->
    <link href="../css/main.css" rel="stylesheet">

    <style>
        .price {
            font-size: 18px;
            font-weight: bold;
            /* color: #148634; */
        }

        .accordion-item {
            margin-bottom: 20px;
            /* Adjust this value as needed */
        }

        /* Default style for select box */
        .form-select {
            transition: background-color 0.3s !important;
        }

        /* Background color for "Hadir" */
        .hadir-bg {
            background-color: #ccffcc !important;
        }

        /* Background color for "Tidak Hadir" */
        .tidak-hadir-bg {
            background-color: #ffcccb !important;
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
                <span>Fee Payment Details - <?php echo $Row['nama_yuran']; ?></span>
                <span class="user-role">Admin</span>
            </header>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb ms-5 mt-2">
                    <li class="breadcrumb-item"><a href="fee_list.php">Fee List</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Payment Details</li>
                </ol>
            </nav>


            <div class="container p-5">

                <?php if ($kategori === 'ALL'): ?>
                    <div class="row">
                        <div class="col text-center">
                            <div class="filter-buttons mb-3 d-inline-block mb-5">
                                Filter by Category:
                                <a href="fee_payment_details.php?yid=<?php echo $yid; ?>&category=U8"
                                    class="btn btn-outline-success btn-md mx-2 <?php echo $filter_category == 'U8' ? 'active' : ''; ?>">
                                    U8
                                </a>
                                <a href="fee_payment_details.php?yid=<?php echo $yid; ?>&category=U10"
                                    class="btn btn-outline-success btn-md mx-2 <?php echo $filter_category == 'U10' ? 'active' : ''; ?>">
                                    U10
                                </a>
                                <a href="fee_payment_details.php?yid=<?php echo $yid; ?>&category=U12"
                                    class="btn btn-outline-success btn-md mx-2 <?php echo $filter_category == 'U12' ? 'active' : ''; ?>">
                                    U12
                                </a>
                                <a href="fee_payment_details.php?yid=<?php echo $yid; ?>&category=all"
                                    class="btn btn-outline-success btn-md mx-2 <?php echo $filter_category == 'all' ? 'active' : ''; ?>">
                                    All
                                </a>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>

                <div class="row mb-2">
                    <div class="col-md-4">
                        <b>Player Name:</b>
                    </div>
                    <div class="col-md-2 text-center">
                        <b>Parent Name:</b>
                    </div>
                    <div class="col-md-2 text-center">
                        <b>Category:</b>
                    </div>
                    <div class="col-md-2 text-center">
                        <b>Payment Status:</b>
                    </div>
                    <div class="col-md-2 text-center">
                        <b>Invoice:</b>
                    </div>


                </div>
                <form method="post" action="payment_crud.php">
                    <?php foreach ($result as $readrow) {

                        $status = $readrow['status_bayaran'];
                        $classStatus = 'text-success';

                        if ($status == 'Unpaid') {
                            $classStatus = 'text-danger';
                        } else {
                            $classStatus = 'text-success';
                        }


                        ?>
                        <div class="row mb-3">
                            <div class="card m-0">
                                <div class="card-body m-0">
                                    <div class="row">
                                        <div class="col-md-4  d-flex flex-column justify-content-center">
                                            <?php echo $readrow['nama_pemain']; ?>
                                        </div>
                                        <div class="col-md-2  d-flex flex-column justify-content-center ps-3">
                                            <div class="text-center">
                                                <?php echo $readrow['nama_ibubapa']; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-2  d-flex flex-column justify-content-center ps-4">
                                            <div class="text-center">
                                                <?php echo $readrow['kategori']; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-2 d-flex flex-column justify-content-center ps-4">
                                            <div class="form-group">
                                                <?php if ($readrow['status_bayaran'] == 'Paid'): ?>
                                                    <span class="text-success text-center d-block">Paid</span>
                                                    <input type="hidden" name="status[<?php echo $readrow['pemainID']; ?>]"
                                                        value="Paid">
                                                <?php else: ?>
                                                    <select class="form-select attendance-select" style="width: 100%;"
                                                        name="status[<?php echo $readrow['pemainID']; ?>]" required>
                                                        <option value="Paid" <?php if ($readrow['status_bayaran'] == 'Paid')
                                                            echo 'selected'; ?>>Paid</option>
                                                        <option value="Unpaid" <?php if ($readrow['status_bayaran'] == 'Unpaid' || is_null($readrow['status_bayaran']))
                                                            echo 'selected'; ?>>Unpaid
                                                        </option>
                                                    </select>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-2 d-flex flex-column justify-content-center ps-4">
                                            <div class="d-flex align-items-center justify-content-center ">
                                                <?php if ($readrow['status_bayaran'] == 'Paid'): ?>
                                                    <a href="invoice.php?bid=<?php echo $readrow['bayaranID'] ?>"
                                                        class="btn btn-outline-success" role="button">Invoice</a>
                                                <?php else: ?>
                                                    <span>-</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <input type="hidden" name="pid[<?php echo $readrow['pemainID']; ?>]"
                                value="<?php echo $readrow['pemainID']; ?>">
                            <input type="hidden" name="parid[<?php echo $readrow['pemainID']; ?>]"
                                value="<?php echo $readrow['ibubapaID']; ?>">
                            <input type="hidden" name="bid[<?php echo $readrow['pemainID']; ?>]"
                                value="<?php echo $readrow['bayaranID']; ?>">

                        </div>
                    <?php } ?>

                    <input type="hidden" name="yid" id="yid" value="<?php echo $yid ?>">
                    <input type="hidden" name="payment" id="payment" value="<?php echo $payment ?>">

                    <div class="row mb-3 justify-content-center align-items-center">
                        <div class="row g-0" style="width:100%;">
                            <div
                                class="col-md-2 d-flex flex-column justify-content-center align-items-center order-last">
                                <button type="submit" name="update"
                                    class="btn btn-secondary btn-xs mb-2">Update</button>


                            </div>
                            <!-- Other columns here -->
                            <div class="col-md d-flex flex-column justify-content-center align-items-center">
                                <!-- Content for other columns -->
                            </div>
                        </div>
                    </div>

                </form>


            </div>
        </div>

        <!-- jQuery, Popper.js, and Bootstrap JS -->
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


        <script src="https://unpkg.com/@popperjs/core@2"></script>
        <script src="https://unpkg.com/tippy.js@6"></script>

        <script>
            // Define a function to handle class changes
            function handleClassChange(selectBox) {
                var selectedOption = selectBox.value;
                if (selectedOption === 'Paid') {
                    selectBox.classList.remove('tidak-hadir-bg');
                    selectBox.classList.add('hadir-bg');
                } else if (selectedOption === 'Unpaid') {
                    selectBox.classList.remove('hadir-bg');
                    selectBox.classList.add('tidak-hadir-bg');
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                var selectBoxes = document.querySelectorAll('.attendance-select');

                // Loop through select boxes on page load
                selectBoxes.forEach(function (selectBox) {
                    handleClassChange(selectBox);
                });

                // Add event listeners for select boxes
                selectBoxes.forEach(function (selectBox) {
                    selectBox.addEventListener('change', function () {
                        handleClassChange(selectBox);
                    });
                });
            });
        </script>





</body>

</html>