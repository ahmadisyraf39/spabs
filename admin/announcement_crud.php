<?php
include_once '../database.php';

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Generate ID
try {

    // Query to get the latest Announcement ID
    $stmtAnnouncement = $conn->prepare("SELECT MAX(pengumumanID) AS latestAnnouncementID FROM tbl_spabs_pengumuman");
    $stmtAnnouncement->execute();
    $rowAnnouncement = $stmtAnnouncement->fetch(PDO::FETCH_ASSOC);
    $latestAnnouncementID = $rowAnnouncement['latestAnnouncementID'];

    // Increment the IDs and generate new IDs
    function incrementID($id)
    {

        // Check if the ID is empty (no records in the database)
        if (empty($id)) {
            return 'ANN001'; // Start with ACT001 if no records exist
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
        $pengumumanid = $_POST['pengumumanID'];
        $announcement = $_POST['announcement'];
        $kategori = $_POST['category'];
        $desc = $_POST['desc'];

        $createdDate = date('Y-m-d');  // Current date in YYYY-MM-DD format
        $createdTime = date('H:i:s');  // Current time in HH:MM:SS format

        // Prepare the main INSERT statement
        $stmt = $conn->prepare("INSERT INTO tbl_spabs_pengumuman(pengumumanID, tajuk, penerangan, kategori, tarikh, masa) VALUES(:pengumumanid, :announcement, :desc, :kategori, :createdDate, :createdTime)");
        $stmt->bindParam(':pengumumanid', $pengumumanid, PDO::PARAM_STR);
        $stmt->bindParam(':announcement', $announcement, PDO::PARAM_STR);
        $stmt->bindParam(':kategori', $kategori, PDO::PARAM_STR);
        $stmt->bindParam(':desc', $desc, PDO::PARAM_STR);
        $stmt->bindParam(':createdDate', $createdDate, PDO::PARAM_STR);
        $stmt->bindParam(':createdTime', $createdTime, PDO::PARAM_STR);

        $stmt->execute();

        // Redirect to Announcement list after successful insertion
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

}

//Update
if (isset($_POST['update'])) {

    try {
        // Set the timezone to Kuala Lumpur
        date_default_timezone_set('Asia/Kuala_Lumpur');

        // Retrieve form data
        $pengumumanid = $_POST['pengumumanID'];
        $announcement = $_POST['announcement'];
        $kategori = $_POST['category'];
        $desc = $_POST['desc'];

        $editedDate = date('Y-m-d');  // Current date in YYYY-MM-DD format
        $editedTime = date('H:i:s');  // Current time in HH:MM:SS format

        $stmt = $conn->prepare("UPDATE tbl_spabs_pengumuman SET 
                                tajuk = :announcement,
                                penerangan = :desc, 
                                kategori = :kategori,
                                tarikh = :editedDate,
                                masa = :editedTime
                                WHERE pengumumanID = :pengumumanid");

        $stmt->bindParam(':pengumumanid', $pengumumanid, PDO::PARAM_STR);
        $stmt->bindParam(':announcement', $announcement, PDO::PARAM_STR);
        $stmt->bindParam(':kategori', $kategori, PDO::PARAM_STR);
        $stmt->bindParam(':desc', $desc, PDO::PARAM_STR);
        $stmt->bindParam(':editedDate', $editedDate, PDO::PARAM_STR);
        $stmt->bindParam(':editedTime', $editedTime, PDO::PARAM_STR);

        $stmt->execute();



        // Redirect to Announcement list after successful insertion
        header("Location: index.php");
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

//Delete
if (isset($_GET['delete'])) {

    try {

        $stmt = $conn->prepare("DELETE FROM tbl_spabs_pengumuman WHERE pengumumanID = :pid");

        $stmt->bindParam(':pid', $pid, PDO::PARAM_STR);

        $pid = $_GET['delete'];

        $stmt->execute();

        // Redirect to Announcement list after successful insertion
        header("Location: index.php");
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

//Edit
if (isset($_GET['edit'])) {

    try {

        $stmt = $conn->prepare("SELECT * FROM tbl_spabs_pengumuman WHERE pengumumanID = :pid");

        $stmt->bindParam(':pid', $pid, PDO::PARAM_STR);

        $pid = $_GET['edit'];

        $stmt->execute();

        $editrow = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

?>