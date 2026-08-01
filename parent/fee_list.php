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

$stmt = $conn->prepare("SELECT * FROM tbl_spabs_pemain WHERE ibubapaID = :pid");
$stmt->bindParam(':pid', $parentID, PDO::PARAM_STR);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = array_column($rows, 'kategori');

if (!in_array('ALL', $categories)) {
    array_unshift($categories, 'ALL');
}


//Generate ID
try {

    // Query to get the latest Payment ID
    $stmtPayment = $conn->prepare("SELECT MAX(bayaranID) AS latestPaymentID FROM tbl_spabs_bayaran");
    $stmtPayment->execute();
    $rowPayment = $stmtPayment->fetch(PDO::FETCH_ASSOC);
    $latestPaymentID = $rowPayment['latestPaymentID'];

    // Increment the IDs and generate new IDs
    function incrementID($id)
    {

        // Check if the ID is empty (no records in the database)
        if (empty($id)) {
            return 'PAY001'; // Start with ACT001 if no records exist
        } else {
            $numericPart = substr($id, 3);
            $incrementedNumericPart = intval($numericPart) + 1;
            $newID = substr($id, 0, 3) . str_pad($incrementedNumericPart, strlen($numericPart), '0', STR_PAD_LEFT);
            return $newID;
        }


    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
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
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery-bundle.min.css">

    <style>
        .price {
            font-size: 18px;
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
                <span class="ms-2">Fee</span>
                <span class="user-role">Parent</span>
            </header>

            <div class="container p-5">
                <h4 class="text-center mb-3">Fee List</h4>
                <?php
                try {

                    // Fetch all players' IDs
                    $playerIDs = array_column($rows, 'pemainID');

                    // Prepare the SQL query
                    $query = "SELECT y.*, p.pemainID, p.nama_pemain
                              FROM tbl_spabs_yuran y
                              LEFT JOIN tbl_spabs_pemain p ON p.kategori = y.kategori OR y.kategori = 'ALL'
                              LEFT JOIN tbl_spabs_bayaran b ON y.yuranID = b.yuranID AND b.pemainID = p.pemainID
                              WHERE (b.yuranID IS NULL AND p.ibubapaID = :pid AND b.pemainID is NULL  AND y.tarikh > p.tarikh_daftar)
                              OR (b.status_bayaran = 'Unpaid' AND p.ibubapaID = :pid)";

                    // Initialize the query parameters array
                    $query_params = [
                        ':pid' => $parentID
                    ];

                    // Prepare the statement
                    $stmt = $conn->prepare($query);

                    // Bind the parameters
                    foreach ($query_params as $key => $value) {
                        $stmt->bindValue($key, $value, PDO::PARAM_STR);
                    }

                    // Execute the statement
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }
                if (count($result) >= 1) {
                    foreach ($result as $readrow) {

                        ?>

                        <div class="row mb-3 justify-content-center align-items-center">
                            <div class="card p-2 mb-3" style="width: 80%; border: 1px solid black; padding: 0;">
                                <div class="card-body">
                                    <form method="post" action="payment.php">

                                        <div class="row">

                                            <div class="col-md-8 d-flex flex-column justify-content-center">
                                                <div class="price">
                                                    <?php echo $readrow['nama_yuran'] ?>
                                                </div>
                                                <div>

                                                    for <?php echo $readrow['nama_pemain'] ?>
                                                </div>
                                            </div>



                                            <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                                <div class="price"> RM <?php echo $readrow['jumlah_yuran'] ?></div>
                                            </div>
                                            <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                                <div class="text-center"></div>

                                                <button class="btn btn-outline-primary btn-xs mb-2" type="submit"
                                                    name="pay">Pay</button>
                                            </div>
                                        </div>

                                        <!-- Hidden inputs -->
                                        <input type="hidden" name="nama_yuran" value="<?php echo $readrow['nama_yuran']; ?>">
                                        <input type="hidden" name="yuranID" value="<?php echo $readrow['yuranID']; ?>">
                                        <input type="hidden" name="jumlah_yuran"
                                            value="<?php echo $readrow['jumlah_yuran']; ?>">
                                        <input type="hidden" name="bayaranID" id="bayaranID"
                                            value="<?php echo $newPaymentID = incrementID($latestPaymentID); ?>">
                                        <input type="hidden" name="pemainID" value="<?php echo $readrow['pemainID']; ?>">
                                        <input type="hidden" name="nama_pemain" value="<?php echo $readrow['nama_pemain']; ?>">


                                    </form>
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
                                        No Fee need to be Pay
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>

                <h4 class="text-center mb-3">Payment History</h4>
                <?php
                try {

                    // Prepare the SQL statement with IN clause for multiple categories
                    $stmt = $conn->prepare("SELECT y.*, b.bayaranID, p.*
                                                FROM tbl_spabs_yuran y
                                                LEFT JOIN tbl_spabs_bayaran b ON b.yuranID = y.yuranid
                                                LEFT JOIN tbl_spabs_pemain p ON p.pemainID = b.pemainID
                                                WHERE b.ibubapaID = :pid AND b.status_bayaran = 'Paid'
                                            ");
                    $stmt->bindParam(':pid', $parentID, PDO::PARAM_STR);
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
                                        <div class="col-md-8 d-flex flex-column justify-content-center">
                                            <div class="price">
                                                <?php echo $readrow['nama_yuran'] ?>
                                            </div>
                                            <div>

                                                for <?php echo $readrow['nama_pemain'] ?>
                                            </div>
                                        </div>

                                        <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                            <div class="price"> RM <?php echo $readrow['jumlah_yuran'] ?></div>
                                        </div>
                                        <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                            <div class="text-center"></div>
                                            <!-- Details Button -->
                                            <a href="invoice.php?bid=<?php echo $readrow['bayaranID'] ?>"
                                                class="btn btn-outline-success" role="button">Invoice</a>
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
                                        No Fee has been paid
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

    <!-- jQuery, Popper.js, and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/lightgallery.min.js"></script>

    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>





</body>

</html>