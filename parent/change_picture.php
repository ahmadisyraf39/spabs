<?php
include_once ("../session.php");

$playerID = $_POST['playerID'];
$target_dir = "../pictures/players/";

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // Handle file upload
    if (isset($_FILES['playerPicture']) && $_FILES['playerPicture']['error'] === UPLOAD_ERR_OK) {
        // File type validation
        $allowedTypes = ['image/jpeg', 'image/png'];

        if (!in_array($_FILES['playerPicture']['type'], $allowedTypes)) {
            echo "Invalid file type. Only JPEG and PNG files are allowed.";
            exit;
        }

        // File size limit (2MB)
        $maxFileSize = 4 * 1024 * 1024; // 2 MB

        if ($_FILES['playerPicture']['size'] > $maxFileSize) {
            echo "File size exceeds the maximum limit (4MB).";
            exit;
        }

        // Unique file names to avoid overwriting
        $image = uniqid() . '_' . $_FILES['playerPicture']['name'];
        $targetFile = $target_dir . basename($image);

        if (!move_uploaded_file($_FILES["playerPicture"]["tmp_name"], $targetFile)) {
            echo "Sorry, there was an error uploading your file.";
            exit; // Exit the script if file upload fails
        }
    } else {


    }

    // Get old file name to delete
    $stmt = $conn->prepare("SELECT gambar FROM tbl_spabs_pemain WHERE pemainID = :pid");
    $stmt->bindParam(':pid', $playerID, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $oldFileName = $row['gambar'];

    // Update the database with the new file name
    $stmt = $conn->prepare("UPDATE tbl_spabs_pemain SET gambar = :image WHERE pemainID = :pid");
    $stmt->bindParam(':pid', $playerID, PDO::PARAM_STR);
    $stmt->bindParam(':image', $image, PDO::PARAM_STR);

    if ($stmt->execute()) {
        // Delete the old file if it's not default.jpg
        if (!empty($oldFileName) && $oldFileName !== 'default.jpg' && file_exists($target_dir . $oldFileName)) {
            unlink($target_dir . $oldFileName);
        }
        header("Location: profile.php");
    } else {
        header("Location: profile.php");
    }
    exit();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>