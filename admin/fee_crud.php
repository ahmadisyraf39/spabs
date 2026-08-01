<?php
include_once '../database.php';

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Generate ID
try {

    // Query to get the latest Fee ID
    $stmtFee = $conn->prepare("SELECT MAX(yuranID) AS latestFeeID FROM tbl_spabs_yuran");
    $stmtFee->execute();
    $rowFee = $stmtFee->fetch(PDO::FETCH_ASSOC);
    $latestFeeID = $rowFee['latestFeeID'];

    // Increment the IDs and generate new IDs
    function incrementID($id)
    {

        // Check if the ID is empty (no records in the database)
        if (empty($id)) {
            return 'FEE001'; // Start with ACT001 if no records exist
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


//Create
if (isset($_POST['create'])) {
    try {
        // Set the timezone to Kuala Lumpur
        date_default_timezone_set('Asia/Kuala_Lumpur');


        // Retrieve form data
        $yuranid = $_POST['yuranID'];
        $fee = $_POST['fee_price'];
        $name = $_POST['fee_name'];
        $category = $_POST['category'];

        $createdDate = date('Y-m-d');  // Current date in YYYY-MM-DD format

        // Prepare the main INSERT statement
        $stmt = $conn->prepare("INSERT INTO tbl_spabs_yuran(yuranID, nama_yuran, kategori, jumlah_yuran, tarikh) VALUES(:yuranid, :fee_name, :kategori, :fee_price, :createdDate)");
        $stmt->bindParam(':yuranid', $yuranid, PDO::PARAM_STR);
        $stmt->bindParam(':fee_price', $fee, PDO::PARAM_STR);
        $stmt->bindParam(':kategori', $category, PDO::PARAM_STR);
        $stmt->bindParam(':fee_name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':createdDate', $createdDate, PDO::PARAM_STR);

        $stmt->execute();

        // Redirect to Fee list after successful insertion
        header("Location: fee_list.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

}

// Update
if (isset($_POST['update'])) {
    try {
        // Set the timezone to Kuala Lumpur
        date_default_timezone_set('Asia/Kuala_Lumpur');

        // Loop through each submitted target
        foreach ($_POST['status'] as $pemainID => $status_kehadiran) {
            $kehadiranID = $_POST['kehid'][$pemainID];
            $aktivitiID = $_POST['aid'];
            $pemainID = $_POST['pid'][$pemainID];

            if ($kehadiranID) {
                // Update existing record
                $stmt = $conn->prepare("UPDATE tbl_spabs_kehadiran SET status_kehadiran = :status_kehadiran
                                        WHERE kehadiranID = :kehadiranID");

                $stmt->bindParam(':kehadiranID', $kehadiranID, PDO::PARAM_STR);
                $stmt->bindParam(':status_kehadiran', $status_kehadiran, PDO::PARAM_STR);

                $stmt->execute();
            } else {
                // Query to get the latest Attendance ID
                $stmtAttendance = $conn->prepare("SELECT MAX(kehadiranID) AS latestAttendanceID FROM tbl_spabs_kehadiran");
                $stmtAttendance->execute();
                $rowAttendance = $stmtAttendance->fetch(PDO::FETCH_ASSOC);
                $latestAttendanceID = $rowAttendance['latestAttendanceID'];

                $kehadiranID = incrementID($latestAttendanceID);

                // Insert new record
                $stmt = $conn->prepare("INSERT INTO tbl_spabs_kehadiran (kehadiranID, aktivitiID, pemainID, 
                                        status_kehadiran) 
                                        VALUES (:kehadiranID, :aktivitiID, :pemainID, :status_kehadiran)");

                $stmt->bindParam(':aktivitiID', $aktivitiID, PDO::PARAM_STR);
                $stmt->bindParam(':pemainID', $pemainID, PDO::PARAM_STR);
                $stmt->bindParam(':kehadiranID', $kehadiranID, PDO::PARAM_STR);
                $stmt->bindParam(':status_kehadiran', $status_kehadiran, PDO::PARAM_STR);

                $stmt->execute();
            }
        }

        header("Location: activity.php");

        exit; // Ensure that code execution stops after the redirect
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}



?>