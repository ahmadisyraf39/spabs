<?php
include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}

if (!isset($_GET['bid'])) {
    header("Location: fee_list.php");
}

$bid = $_GET['bid'];

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $conn->prepare("SELECT b.*, y.*, p.*, i.*, a.*
                        FROM tbl_spabs_bayaran b
                        LEFT JOIN tbl_spabs_yuran y ON b.yuranID = y.yuranID
                        LEFT JOIN tbl_spabs_pemain p ON b.pemainID = p.pemainID
                        LEFT JOIN tbl_spabs_ibubapa i ON p.ibubapaID = i.ibubapaID
                        LEFT JOIN tbl_spabs_akaun a ON i.ibubapaID = a.userID
                        WHERE b.bayaranID = :bid");

$stmt->bindParam(':bid', $bid, PDO::PARAM_STR);

$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$date = $row['tarikh_bayaran'];
$formatted_date = date("d/m/Y", strtotime($date));
$yid = $row['yuranID'];

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Invoice</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">

    <link href="../css/main.css" rel="stylesheet">
    <style>
        .invoice-container {
            width: 60%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 10px 10px 5px #888888;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header img {
            width: 150px;
        }

        .invoice-details {
            margin-bottom: 20px;
        }

        .invoice-details h2 {
            margin-bottom: 10px;
        }

        .info {
            display: flex;
            justify-content: space-between;
        }

        .info div {
            width: 48%;
        }

        .info h3 {
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .total {
            text-align: right;
            margin-top: 20px;
        }

        #loader {
            display: none;
            text-align: center;
            margin-top: 20px;
        }

        p {
            margin-bottom: 0;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <?php include_once 'sidebar.php' ?>
        <div class="main">
            <header>
                <span class="ms-2">Payment Invoice</span>
                <span class="user-role">Parent</span>
            </header>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb ms-5 mt-2">
                    <li class="breadcrumb-item"><a href="fee_list.php">Fee List</a></li>
                    <li class="breadcrumb-item"><a href="fee_payment_details.php?yid=<?php echo $yid ?>">Payment
                            Details</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Invoice</li>
                </ol>
            </nav>

            <div class="container p-5">
                <div class="invoice-container">
                    <div class="header">
                        <h1>SPABS</h1>
                        <p>Sistem Pengurusan Akademi Bola Sepak</p>
                    </div>
                    <div class="invoice-details">
                        <h2>Invoice</h2>
                        <p><strong>Payment ID:</strong> <?php echo $row['bayaranID']; ?><br>
                            <strong>Payment Date:</strong> <?php echo $formatted_date ?><br>
                            <strong>Payment Method:</strong> <?php echo $row['cara_bayaran']; ?>
                        </p>
                    </div>
                    <br>
                    <div class="info">
                        <div>
                            <h3>Parent Information</h3>
                            <p>Name: <?php echo $row['nama_ibubapa']; ?><br>
                                Address: <?php echo $row['alamat']; ?><br>
                                Phone: <?php echo $row['tel_ibubapa']; ?><br>
                                Email: <?php echo $row['email']; ?></p>
                        </div>
                        <div class="text-end">
                            <h3>Player Information</h3>
                            <p>Name: <?php echo $row['nama_pemain']; ?><br>
                                Category: <?php echo $row['kategori']; ?></p>
                        </div>
                    </div>
                    <table>
                        <tr>
                            <th>Description</th>
                            <th>Amount</th>
                        </tr>
                        <tr>
                            <td><?php echo $row['nama_yuran']; ?></td>
                            <td>RM<?php echo $row['jumlah_yuran']; ?></td>
                        </tr>
                    </table>
                    <div class="total">
                        <h3>Total: RM<?php echo $row['jumlah_yuran']; ?></h3>
                    </div>
                </div>
                <br>
                <div class="d-flex justify-content-center align-items-center">
                    <button class="btn btn-primary" id="download-pdf">Download PDF</button>
                    <div id="loader">Generating PDF, please wait...</div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script>
        document.getElementById('download-pdf').addEventListener('click', function () {
            const invoiceContainer = document.querySelector('.invoice-container');
            const bayaranID = "<?php echo $row['bayaranID']; ?>"; // PHP variable to JavaScript

            // Show loader
            document.getElementById('loader').style.display = 'block';

            const opt = {
                margin: 0.5,
                filename: 'invoice_' + bayaranID + '.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
            };

            html2pdf().from(invoiceContainer).set(opt).save().finally(function () {
                // Hide loader
                document.getElementById('loader').style.display = 'none';
            });
        });
    </script>
</body>

</html>