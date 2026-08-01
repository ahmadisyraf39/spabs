<?php
include_once '../database.php';
include_once '../session.php';




$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Generate ID
try {



    // Increment the IDs and generate new IDs
    function incrementID($id)
    {


        // Check if the ID is empty (no records in the database)
        if (empty($id)) {
            return 'ATD001'; // Start with PLA001 if no records exist
        }

        $numericPart = substr($id, 3);
        $incrementedNumericPart = intval($numericPart) + 1;
        $newID = substr($id, 0, 3) . str_pad($incrementedNumericPart, strlen($numericPart), '0', STR_PAD_LEFT);
        return $newID;
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