<?php


include_once '../database.php';

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Delete
if (isset($_GET['delete'])) {

    try {
        $mid = $_GET['delete'];
        $albumID = $_GET['albumID'];

        $stmt = $conn->prepare("SELECT * FROM tbl_spabs_media WHERE mediaID = :mid");
        $stmt->bindParam(':mid', $mid, PDO::PARAM_STR);

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $filenameToDelete = $row['nama_media'];

            // Delete the file from the directory
            $fileToDelete = "../pictures/gallery/$albumID/$filenameToDelete"; // Assuming the directory structure
            if (file_exists($fileToDelete)) {
                unlink($fileToDelete); // Delete the file
            }
        }

        // Delete the record from the database
        $stmtDelete = $conn->prepare("DELETE FROM tbl_spabs_media WHERE mediaID = :mid");
        $stmtDelete->bindParam(':mid', $mid, PDO::PARAM_STR);
        $stmtDelete->execute();

        // Redirect to gallery_media.php with albumID
        header("Location: gallery_media.php?albumID=" . urlencode($albumID));
        exit(); // Ensure no further code is executed after the redirect
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>