<?php

include_once '../database.php';

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Create
if (isset($_POST['create'])) {

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

            $image = "default.jpg";
        }

        $stmt = $conn->prepare("INSERT INTO tbl_spabs_pemain(pemainID,
        ibubapaID, nama_pemain, ic_pemain, umur,
        tarikh_lahir, kategori, jantina, gambar, tarikh_daftar) VALUES(:pid, :ibubapaid, :name, :ic,
        :age, :dob, :category, :gender, :image, :registeredDate)");

        $stmt->bindParam(':pid', $pid, PDO::PARAM_STR);
        $stmt->bindParam(':ibubapaid', $ibubapaid, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':ic', $ic, PDO::PARAM_STR);
        $stmt->bindParam(':age', $age, PDO::PARAM_STR);
        $stmt->bindParam(':dob', $dob, PDO::PARAM_STR);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindParam(':gender', $gender, PDO::PARAM_STR);
        $stmt->bindParam(':image', $image, PDO::PARAM_STR);
        $stmt->bindParam(':registeredDate', $registeredDate, PDO::PARAM_STR);

        $pid = $_POST['pid'];
        $ibubapaid = $_POST['ibubapaID'];
        $name = $_POST['name'];
        $ic = $_POST['icnumber'];
        $category = $_POST['category'];
        $age = $_POST['age'];
        $dob = $_POST['birthdate'];
        $gender = $_POST['gender'];
        // $pic = $_POST['image'];
        $registeredDate = date('Y-m-d');  // Current date in YYYY-MM-DD format


        $stmt->execute();

        header("Location: player_list.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

//Update
if (isset($_POST['update'])) {

    try {

        $pid = $_POST['pid'];
        $category = $_POST['category'];
        $age = $_POST['age'];
        $name = $_POST['name'];
        $icnumber = $_POST['icnumber'];

        $stmt = $conn->prepare("UPDATE tbl_spabs_pemain SET umur = :age,
         kategori = :category, ic_pemain = :icnumber, nama_pemain = :name WHERE pemainID = :pid");

        $stmt->bindParam(':pid', $pid, PDO::PARAM_STR);
        $stmt->bindParam(':age', $age, PDO::PARAM_STR);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindParam(':icnumber', $icnumber, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);

        $stmt->execute();

        header("Location: player_list.php");
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

//Delete
if (isset($_GET['delete'])) {

    try {

        $stmt = $conn->prepare("DELETE FROM tbl_spabs_pemain WHERE pemainID = :pid");

        $stmt->bindParam(':pid', $pid, PDO::PARAM_STR);

        $pid = $_GET['delete'];

        $stmt->execute();

        header("Location: player_list.php");
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

//Edit
if (isset($_GET['edit'])) {

    try {

        $stmt = $conn->prepare("SELECT * 
                                FROM tbl_spabs_pemain 
                                JOIN tbl_spabs_ibubapa 
                                ON tbl_spabs_pemain.ibubapaID = tbl_spabs_ibubapa.ibubapaID 
                                WHERE tbl_spabs_pemain.pemainID = :pid");

        $stmt->bindParam(':pid', $pid, PDO::PARAM_STR);

        $pid = $_GET['edit'];

        $stmt->execute();

        $editrow = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

$conn = null;
?>