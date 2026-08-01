<?php
include_once '../database.php';

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

// Update
if (isset($_POST['update'])) {
    try {
        // Set the timezone to Kuala Lumpur
        date_default_timezone_set('Asia/Kuala_Lumpur');

        // Loop through each submitted target
        foreach ($_POST['status'] as $pemainID => $status_bayaran) {
            $bayaranID = $_POST['bid'][$pemainID];
            $yuranID = $_POST['yid'];
            $pemainID = $_POST['pid'][$pemainID];
            $ibubapaID = $_POST['parid'][$pemainID];
            $total_payment = $_POST['payment'];

            if ($bayaranID) {
                // Update existing record
                $stmt = $conn->prepare("UPDATE tbl_spabs_bayaran SET status_bayaran = :status_bayaran
                                        WHERE bayaranID = :bayaranID");

                $stmt->bindParam(':bayaranID', $bayaranID, PDO::PARAM_STR);
                $stmt->bindParam(':status_bayaran', $status_bayaran, PDO::PARAM_STR);

                $stmt->execute();
            } else {
                // Query to get the latest Payment ID
                $stmtPayment = $conn->prepare("SELECT MAX(bayaranID) AS latestPaymentID FROM tbl_spabs_bayaran");
                $stmtPayment->execute();
                $rowPayment = $stmtPayment->fetch(PDO::FETCH_ASSOC);
                $latestPaymentID = $rowPayment['latestPaymentID'];

                $bayaranID = incrementID($latestPaymentID);


                $payment_method = 'Manual';
                $paymentDate = date('Y-m-d');  // Current date in YYYY-MM-DD format

                // Insert into the database
                $stmt = $conn->prepare("INSERT INTO tbl_spabs_bayaran (bayaranID, yuranID, ibubapaID, pemainID, jumlah_bayaran, status_bayaran, tarikh_bayaran, cara_bayaran)
                                        VALUES (:bayaranID, :yuranID, :ibubapaID, :pemainID, :jumlah_bayaran, :status_bayaran, :tarikh_bayaran, :cara_bayaran)");

                $stmt->bindParam(':bayaranID', $bayaranID, PDO::PARAM_STR);
                $stmt->bindParam(':yuranID', $yuranID, PDO::PARAM_STR);
                $stmt->bindParam(':ibubapaID', $ibubapaID, PDO::PARAM_STR);
                $stmt->bindParam(':pemainID', $pemainID, PDO::PARAM_STR);
                $stmt->bindParam(':jumlah_bayaran', $total_payment, PDO::PARAM_INT);
                $stmt->bindParam(':status_bayaran', $status_bayaran, PDO::PARAM_STR);
                $stmt->bindParam(':tarikh_bayaran', $paymentDate, PDO::PARAM_STR);
                $stmt->bindParam(':cara_bayaran', $payment_method, PDO::PARAM_STR);

                $stmt->execute();
            }
        }

        header("Location: fee_list.php");

        exit; // Ensure that code execution stops after the redirect
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} ?>