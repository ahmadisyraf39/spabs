<?php
include_once ("../session.php");
include_once '../database.php';


$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//picture
if (isset($_POST['picture'])) {

    $pid = $_POST['pid'];
    $target_dir = "../pictures/players/";

    try {

        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // File type validation
            $allowedTypes = ['image/jpeg', 'image/png'];

            if (!in_array($_FILES['image']['type'], $allowedTypes)) {
                echo "Invalid file type. Only JPEG and PNG files are allowed.";
                exit;
            }

            // File size limit (2MB)
            $maxFileSize = 2 * 1024 * 1024; // 2 MB

            if ($_FILES['image']['size'] > $maxFileSize) {
                echo "File size exceeds the maximum limit (2MB).";
                exit;
            }

            // Unique file names to avoid overwriting
            $image = uniqid() . '_' . $_FILES['image']['name'];
            $targetDirectory = "../pictures/players/"; // Change this to your upload directory
            $targetFile = $targetDirectory . basename($image);

            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                echo "Sorry, there was an error uploading your file.";
                exit; // Exit the script if file upload fails
            }
        } else {
            echo "File upload failed or no file selected.";
            exit; // Exit the script if no file is selected
        }
        // Get old file name to delete
        $stmt = $conn->prepare("SELECT gambar FROM tbl_spabs_pemain WHERE pemainID = :pid");
        $stmt->bindParam(':pid', $playerID, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $oldFileName = $row['gambar'];

        // Update the database with the new file name
        $stmt = $conn->prepare("UPDATE tbl_spabs_pemain SET gambar = :image
                                WHERE pemainID = :pid)");
        $stmt->bindParam(':pid', $pid, PDO::PARAM_STR);
        $stmt->bindParam(':image', $image, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // Delete the old file
            if (!empty($oldFileName) && file_exists($target_dir . $oldFileName)) {
                unlink($target_dir . $oldFileName);
            }
        }

        header("Location: profile.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

//Update
if (isset($_POST['update'])) {

    try {

        $userid = $_POST['userid'];
        $phone = $_POST['phone'];
        $phone2 = $_POST['phone2'];
        $address = $_POST['address'];

        $stmt2 = $conn->prepare("UPDATE tbl_spabs_ibubapa
                                    SET  tel_ibubapa = :phone,
                                    tel_ibubapa2 = :phone2,
                                    alamat = :address
                                    WHERE ibubapaID = :userid");

        $stmt2->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt2->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stmt2->bindParam(':phone2', $phone2, PDO::PARAM_STR);
        $stmt2->bindParam(':address', $address, PDO::PARAM_STR);

        $stmt2->execute();

        header("Location: profile.php");
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

//Delete
if (isset($_GET['delete'])) {

    try {

        $stmt = $conn->prepare("DELETE FROM tbl_spabs_akaun where userID = :userid");

        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);

        $userid = $_GET['delete'];

        $stmt->execute();

        header("Location: user_list.php");
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

//Edit
if (isset($_GET['edit'])) {

    try {

        $stmt = $conn->prepare("SELECT * FROM tbl_spabs_akaun WHERE userID = :uid");

        $stmt->bindParam(':uid', $uid, PDO::PARAM_STR);

        $uid = $_GET['edit'];

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $user_role = $row['user_role'];


        if ($user_role === 'Parent') {
            // Perform query for parent
            $stmt2 = $conn->prepare("SELECT * FROM tbl_spabs_akaun 
                                    JOIN tbl_spabs_ibubapa
                                    ON tbl_spabs_ibubapa.ibubapaID = tbl_spabs_akaun.userID
                                    WHERE userID = :uid");
        } elseif ($user_role === 'Coach') {
            // Perform query for coach
            $stmt2 = $conn->prepare("SELECT * FROM tbl_spabs_akaun 
                                    JOIN tbl_spabs_ibubapa
                                    ON tbl_spabs_ibubapa.ibubapaID = tbl_spabs_akaun.userID
                                    WHERE userID = :uid");
        }

        $stmt2->bindParam(':uid', $uid, PDO::PARAM_STR);

        $uid = $_GET['edit'];

        $stmt2->execute();

        $editrow = $stmt2->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

if (isset($_POST["pass"])) {
    try {

        $new_password = $_POST['newPassword'];
        $userID = $_POST['userID'];

        // Fetch the current password hash from the database
        $stmt = $conn->prepare("UPDATE tbl_spabs_akaun SET password = :pass WHERE userID = :uid");
        $stmt->bindParam(":uid", $userID, PDO::PARAM_STR);
        $stmt->bindParam(":pass", $new_password, PDO::PARAM_STR);
        $stmt->execute();

        // Set the success message in session
        // $_SESSION['success_message'] = "Password updated successfully.";

        // Display success message and redirect
        echo "<script>
       alert('Password updated successfully.');
       window.location.href = 'profile.php';
     </script>";
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
$conn = null;

?>