<?php
include_once '../database.php';

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Generate ID
try {

    // Query to get the latest Leave ID
    $stmtLeave = $conn->prepare("SELECT MAX(ketidakhadiranID) AS latestLeaveID FROM tbl_spabs_ketidakhadiran");
    $stmtLeave->execute();
    $rowLeave = $stmtLeave->fetch(PDO::FETCH_ASSOC);
    $latestLeaveID = $rowLeave['latestLeaveID'];

    // Query to get the latest Attendance ID
    $stmtAttendance = $conn->prepare("SELECT MAX(kehadiranID) AS latestAttendanceID FROM tbl_spabs_kehadiran");
    $stmtAttendance->execute();
    $rowAttendance = $stmtAttendance->fetch(PDO::FETCH_ASSOC);
    $latestAttendanceID = $rowAttendance['latestAttendanceID'];

    // Increment the IDs and generate new IDs
    function incrementLeaveID($id)
    {

        // Check if the ID is empty (no records in the database)
        if (empty($id)) {
            return 'ABS0001'; // Start with ACT001 if no records exist
        }

        $numericPart = substr($id, 3);
        $incrementedNumericPart = intval($numericPart) + 1;
        $newID = substr($id, 0, 3) . str_pad($incrementedNumericPart, strlen($numericPart), '0', STR_PAD_LEFT);
        return $newID;
    }

    function incrementAttendanceID($id)
    {

        // Check if the ID is empty (no records in the database)
        if (empty($id)) {
            return 'ATD0001'; // Start with ACT001 if no records exist
        }

        $numericPart = substr($id, 3);
        $incrementedNumericPart = intval($numericPart) + 1;
        $newID = substr($id, 0, 3) . str_pad($incrementedNumericPart, strlen($numericPart), '0', STR_PAD_LEFT);
        return $newID;
    }

    // Generate new user IDs based on the retrieved latest IDs
    // $newLeaveID = incrementID($latestLeaveID);

    //echo "Latest Parent ID: $latestLeaveID, New Leave ID: $newLeaveID";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}


//Create
if (isset($_POST['create'])) {
    try {
        // Set the timezone to Kuala Lumpur
        date_default_timezone_set('Asia/Kuala_Lumpur');

        // Retrieve form data
        $leaveid = $_POST['ketidakhadiranID'];
        $attendanceid = $_POST['kehadiranID'];
        $aktivitiid = $_POST['activityName'];
        $playerid = $_POST['playerName'];
        $leaveReason = $_POST['leaveReason'];
        $keterangan = $_POST['description'];
        $attendanceStatus = 'Absent';
        $createdDate = date('Y-m-d');  // Current date in YYYY-MM-DD format
        $createdTime = date('H:i:s');  // Current time in HH:MM:SS format

        // Prepare the main INSERT statement
        $stmt = $conn->prepare("INSERT INTO tbl_spabs_kehadiran(kehadiranID, aktivitiID, pemainID, status_kehadiran) VALUES(:attendanceid, :aktivitiid, :playerid, :attendanceStatus)");
        $stmt->bindParam(':attendanceid', $attendanceid, PDO::PARAM_STR);
        $stmt->bindParam(':aktivitiid', $aktivitiid, PDO::PARAM_STR);
        $stmt->bindParam(':playerid', $playerid, PDO::PARAM_STR);
        $stmt->bindParam(':attendanceStatus', $attendanceStatus, PDO::PARAM_STR);

        $stmt->execute();

        $stmt2 = $conn->prepare("INSERT INTO tbl_spabs_ketidakhadiran (ketidakhadiranID, kehadiranID, jenis_sebab, keterangan, tarikh_hantar, masa_hantar) VALUES (:leaveid, :attendanceid, :leaveReason, :keterangan, :createdDate, :createdTime)");
        $stmt2->bindParam(':leaveid', $leaveid, PDO::PARAM_STR);
        $stmt2->bindParam(':attendanceid', $attendanceid, PDO::PARAM_STR);
        $stmt2->bindParam(':leaveReason', $leaveReason, PDO::PARAM_STR);
        $stmt2->bindParam(':keterangan', $keterangan, PDO::PARAM_STR);
        $stmt2->bindParam(':createdDate', $createdDate, PDO::PARAM_STR);
        $stmt2->bindParam(':createdTime', $createdTime, PDO::PARAM_STR);

        $stmt2->execute();



        // Redirect to Leave list after successful insertion
        header("Location: player_attendance.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

}

//Delete
if (isset($_GET['delete'])) {

    try {

        $stmt = $conn->prepare("DELETE FROM tbl_spabs_kehadiran WHERE kehadiranID = :kehadiranid");

        $stmt->bindParam(':kehadiranid', $kehadiranid, PDO::PARAM_STR);

        $kehadiranid = $_GET['delete'];

        $stmt->execute();

        header("Location: player_attendance.php");
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

if (isset($_POST['category'])) {
    $category = $_POST['category'];
    $playerID = $_POST['playerID'];

    try {
        $stmt = $conn->prepare("
            SELECT * FROM tbl_spabs_aktiviti 
            WHERE (kategori = :category OR kategori = 'ALL')
            AND tarikh_aktiviti >= CURDATE()
            AND aktivitiID NOT IN (
                SELECT aktivitiID FROM tbl_spabs_kehadiran 
                WHERE pemainID = :playerID
            )
        ");
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindParam(':playerID', $playerID, PDO::PARAM_STR);
        $stmt->execute();
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo '<option value="">Select Activity</option>';

        foreach ($activities as $activity) {
            $date = $activity['tarikh_aktiviti'];
            $formatted_date = date("d/m/Y", strtotime($date));

            echo '<option value="' . $activity['aktivitiID'] . '">' . $activity['nama_aktiviti'] . ' (' . $formatted_date . ')</option>';
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

if (isset($_POST['activityID'])) {
    $activityID = $_POST['activityID'];

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM tbl_spabs_aktiviti WHERE aktivitiID = :activityID");
        $stmt->bindParam(':activityID', $activityID, PDO::PARAM_STR);
        $stmt->execute();
        $activity = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($activity) {
            $date = date("d/m/Y", strtotime($activity['tarikh_aktiviti']));
            $start_time = date("h:i A", strtotime($activity['masa_mula']));
            $end_time = date("h:i A", strtotime($activity['masa_tamat']));
            $time = $start_time . ' - ' . $end_time;

            $response = array('date' => $date, 'time' => $time);
            echo json_encode($response);
        } else {
            // echo json_encode(array('error' => 'Activity not found.'));
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

?>