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
            return 'SEL001'; // Start with PLA001 if no records exist
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
if (isset($_POST['create'])) {
    try {
        $tournament = $_POST['tournament'];
        $selectedPlayers = $_POST['select_player'] ?? [];
        $progressRates = $_POST['avg_of_avg_progress_status'] ?? [];
        $attendanceRates = $_POST['avg_attend_percentage'] ?? [];

        foreach ($selectedPlayers as $playerID) {
            $progressRate = $progressRates[$playerID];
            $attendanceRate = $attendanceRates[$playerID];

            // Query to get the latest Attendance ID
            $stmtSelection = $conn->prepare("SELECT MAX(pemilihanID) AS latestSelectionID FROM tbl_spabs_pemilihan");
            $stmtSelection->execute();
            $rowSelection = $stmtSelection->fetch(PDO::FETCH_ASSOC);
            $latestSelectionID = $rowSelection['latestSelectionID'];

            $pemilihanID = incrementID($latestSelectionID);

            // Process the data as needed, e.g., insert into the database
            $stmt = $conn->prepare("INSERT INTO tbl_spabs_pemilihan (pemilihanID, aktivitiID, pemainID, kadar_kemajuan, kadar_kehadiran, status_pemilihan, status_notifikasi) VALUES (:pemilihanID, :aktivitiID, :pemainID, :progress, :attendance, 'Selected', 'Unnotified')");
            $stmt->bindParam(':pemilihanID', $pemilihanID, PDO::PARAM_STR);
            $stmt->bindParam(':aktivitiID', $tournament, PDO::PARAM_STR);
            $stmt->bindParam(':pemainID', $playerID, PDO::PARAM_STR);
            $stmt->bindParam(':progress', $progressRate, PDO::PARAM_INT);
            $stmt->bindParam(':attendance', $attendanceRate, PDO::PARAM_INT);
            $stmt->execute();
        }


        header("Location: tournament.php");

        exit; // Ensure that code execution stops after the redirect
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>