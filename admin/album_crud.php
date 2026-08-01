<?php
include_once '../database.php';

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Generate ID
try {

    // Query to get the latest Album ID
    $stmtAlbum = $conn->prepare("SELECT MAX(albumID) AS latestAlbumID FROM tbl_spabs_album");
    $stmtAlbum->execute();
    $rowAlbum = $stmtAlbum->fetch(PDO::FETCH_ASSOC);
    $latestAlbumID = $rowAlbum['latestAlbumID'];

    // Increment the IDs and generate new IDs
    function incrementID($id)
    {

        // Check if the ID is empty (no records in the database)
        if (empty($id)) {
            return 'ALB001'; // Start with ACT001 if no records exist
        }

        $numericPart = substr($id, 3);
        $incrementedNumericPart = intval($numericPart) + 1;
        $newID = substr($id, 0, 3) . str_pad($incrementedNumericPart, strlen($numericPart), '0', STR_PAD_LEFT);
        return $newID;
    }

    // Generate new user IDs based on the retrieved latest IDs
    // $newAlbumID = incrementID($latestAlbumID);

    //echo "Latest Parent ID: $latestAlbumID, New Album ID: $newAlbumID";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}


//Create
if (isset($_POST['create'])) {
    try {
        // Retrieve form data
        $albumid = $_POST['albumID'];
        $aktivitiid = $_POST['aktivitiID'];

        // Prepare the main INSERT statement
        $stmt = $conn->prepare("INSERT INTO tbl_spabs_album(albumID, aktivitiID) VALUES(:albumid, :aktivitiid)");
        $stmt->bindParam(':albumid', $albumid, PDO::PARAM_STR);
        $stmt->bindParam(':aktivitiid', $aktivitiid, PDO::PARAM_STR);

        $stmt->execute();

        // Redirect to Album list after successful insertion
        header("Location: gallery.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

}

//Delete
if (isset($_GET['delete'])) {

    try {

        $stmt = $conn->prepare("DELETE FROM tbl_spabs_album WHERE albumID = :aid");

        $stmt->bindParam(':aid', $aid, PDO::PARAM_STR);

        $aid = $_GET['delete'];

        $stmt->execute();

        header("Location: gallery.php");
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

?>