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
            return 'PEN001'; // Start with PLA001 if no records exist
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

        $evaluation_date = date('Y-m-d');  // Current date in YYYY-MM-DD format

        // Loop through each submitted target
        foreach ($_POST['target'] as $modulID => $status_progress) {
            $penilaianID = $_POST['penid'][$modulID];
            $pemainID = $_POST['pid'];
            $coachID = $_SESSION['userID'];
            $kemahiranID = $_POST['kid'];
            $modulID2 = $_POST['mid'][$modulID];

            if ($penilaianID) {
                // Update existing record
                $stmt = $conn->prepare("UPDATE tbl_spabs_penilaian SET status_capai = :status_progress, 
                                        tarikh_penilaian = :evaluation_date
                                        WHERE penilaianID = :penilaianID");

                $stmt->bindParam(':penilaianID', $penilaianID, PDO::PARAM_STR);
                $stmt->bindParam(':status_progress', $status_progress, PDO::PARAM_STR);
                $stmt->bindParam(':evaluation_date', $evaluation_date, PDO::PARAM_STR);

                $stmt->execute();
            } else {
                // Query to get the latest Evaluation ID
                $stmtEvaluation = $conn->prepare("SELECT MAX(penilaianID) AS latestEvaluationID FROM tbl_spabs_penilaian");
                $stmtEvaluation->execute();
                $rowEvaluation = $stmtEvaluation->fetch(PDO::FETCH_ASSOC);
                $latestEvaluationID = $rowEvaluation['latestEvaluationID'];

                $penilaianID = incrementID($latestEvaluationID);

                // Insert new record
                $stmt = $conn->prepare("INSERT INTO tbl_spabs_penilaian (penilaianID, kemahiranID, modulID, pemainID, 
                                        jurulatihID, status_capai, tarikh_penilaian) 
                                        VALUES (:penilaianID, :kemahiranID, :modulID, :pemainID, :jurulatihID, 
                                        :status_progress, :evaluation_date)");

                $stmt->bindParam(':penilaianID', $penilaianID, PDO::PARAM_STR);
                $stmt->bindParam(':pemainID', $pemainID, PDO::PARAM_STR);
                $stmt->bindParam(':kemahiranID', $kemahiranID, PDO::PARAM_STR);
                $stmt->bindParam(':modulID', $modulID2, PDO::PARAM_STR);
                $stmt->bindParam(':jurulatihID', $coachID, PDO::PARAM_STR);
                $stmt->bindParam(':status_progress', $status_progress, PDO::PARAM_STR);
                $stmt->bindParam(':evaluation_date', $evaluation_date, PDO::PARAM_STR);

                $stmt->execute();
            }
        }

        header("Location: skill_progress.php?pid=" . $pemainID);

        exit; // Ensure that code execution stops after the redirect
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>