<?php
include_once ("../session.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (!empty($_GET['albumID'])) {
    $albumID = $_GET['albumID'];
}

// Generate ID
try {
    // Query to get the latest Media ID
    $stmtMedia = $conn->prepare("SELECT MAX(mediaID) AS latestMediaID FROM tbl_spabs_media");
    $stmtMedia->execute();
    $rowMedia = $stmtMedia->fetch(PDO::FETCH_ASSOC);
    $latestMediaID = $rowMedia['latestMediaID'];

    // Increment the IDs and generate new IDs
    function incrementID($id)
    {
        // Check if the ID is empty (no records in the database)
        if (empty($id)) {
            return 'MED0001'; // Start with MED001 if no records exist
        }

        $numericPart = substr($id, 3);
        $incrementedNumericPart = intval($numericPart) + 1;
        $newID = substr($id, 0, 3) . str_pad($incrementedNumericPart, strlen($numericPart), '0', STR_PAD_LEFT);
        return $newID;
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

if (!empty($_FILES['file'])) {
    try {
        $albumID = $_POST['albumID']; // Ensure albumID is taken from POST data
        $targetDir = "../pictures/gallery/$albumID/";
        $filename = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
        $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $targetFilePath = $targetDir . $filename . '.' . $extension;

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true); // Create the directory if it does not exist
        }

        // Check if the file already exists and rename it if necessary
        $counter = 1;
        $newFilename = $filename;
        while (file_exists($targetFilePath)) {
            $newFilename = $filename . '(' . $counter . ')';
            $targetFilePath = $targetDir . $newFilename . '.' . $extension;
            $counter++;
        }

        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFilePath)) {

            // Retrieve the file type (MIME type)
            $filetype = $_FILES['file']['type'];

            // Extract general category (image or video) from the MIME type
            if (strpos($filetype, 'image') !== false) {
                $filetype = 'image';
            } elseif (strpos($filetype, 'video') !== false) {
                $filetype = 'video';
            } else {
                $filetype = 'other'; // Handle other file types as needed
            }

            // Append the file type to the filename
            $filenameWithType = $newFilename . '.' . $extension;

            // Insert file details into database
            $stmt = $conn->prepare("INSERT INTO tbl_spabs_media (mediaID, albumID, jenis_media, nama_media) VALUES (:mediaid, :albumid, :filetype, :filename)");

            // Generate a new media ID
            $mediaID = incrementID($latestMediaID);

            $stmt->bindParam(':mediaid', $mediaID, PDO::PARAM_STR);
            $stmt->bindParam(':albumid', $albumID, PDO::PARAM_STR);
            $stmt->bindParam(':filetype', $filetype, PDO::PARAM_STR);
            $stmt->bindParam(':filename', $filenameWithType, PDO::PARAM_STR);

            // Debugging output
            echo "mediaID: $mediaID<br>";
            echo "albumID: $albumID<br>";
            echo "filetype: $filetype<br>";
            echo "filename: $filenameWithType <br>";

            if ($stmt->execute()) {
                echo 'File Uploaded and saved in database';
            } else {
                // Output the error info
                $errorInfo = $stmt->errorInfo();
                echo "File uploaded but database insertion failed: " . print_r($errorInfo, true);
            }

        } else {
            echo 'File Upload Failed';
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    exit;
}
?>