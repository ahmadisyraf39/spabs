<?php
include_once '../database.php';

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Generate ID
try {

    // Query to get the latest Module ID
    $stmtModule = $conn->prepare("SELECT MAX(modulID) AS latestModuleID FROM tbl_spabs_modul");
    $stmtModule->execute();
    $rowModule = $stmtModule->fetch(PDO::FETCH_ASSOC);
    $latestModuleID = $rowModule['latestModuleID'];

    // Increment the IDs and generate new IDs
    function incrementID($id)
    {

        // Check if the ID is empty (no records in the database)
        if (empty($id)) {
            return 'MOD001'; // Start with ACT001 if no records exist
        }

        $numericPart = substr($id, 3);
        $incrementedNumericPart = intval($numericPart) + 1;
        $newID = substr($id, 0, 3) . str_pad($incrementedNumericPart, strlen($numericPart), '0', STR_PAD_LEFT);
        return $newID;
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}


//Create
if (isset($_POST['create'])) {
    try {
        // Retrieve form data
        $modulid = $_POST['modulID'];
        $kemahiranid = $_POST['kemahiranID'];
        $modul = $_POST['module'];
        $progress1 = $_POST['progress1'];
        $progress2 = $_POST['progress2'];
        $progress3 = $_POST['progress3'];
        $progress4 = $_POST['progress4'];

        // Prepare the main INSERT statement
        $stmt = $conn->prepare("INSERT INTO tbl_spabs_modul(modulID, kemahiranID, nama_modul, kemajuan_satu, kemajuan_dua, kemajuan_tiga, kemajuan_empat)
                        VALUES(:modulid, :kemahiranid, :modul, :progress1, :progress2, :progress3, :progress4)");
        $stmt->bindParam(':modulid', $modulid, PDO::PARAM_STR);
        $stmt->bindParam(':kemahiranid', $kemahiranid, PDO::PARAM_STR);
        $stmt->bindParam(':modul', $modul, PDO::PARAM_STR);
        $stmt->bindParam(':progress1', $progress1, PDO::PARAM_STR);
        $stmt->bindParam(':progress2', $progress2, PDO::PARAM_STR);
        $stmt->bindParam(':progress3', $progress3, PDO::PARAM_STR);
        $stmt->bindParam(':progress4', $progress4, PDO::PARAM_STR);

        // Execute the query
        $stmt->execute();
        // $stmt->bindParam(':sasaran', $target, PDO::PARAM_STR);



        $stmt2 = $conn->prepare("SELECT * FROM tbl_spabs_kemahiran WHERE kemahiranID = :kemahiranid");
        $stmt2->bindParam(':kemahiranid', $kemahiranid, PDO::PARAM_STR);
        $stmt2->execute();
        $row = $stmt2->fetch(PDO::FETCH_ASSOC);
        $category = $row['kategori'];

        // Redirect to Module list after successful insertion
        header("Location: module.php?category=$category");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

}

//Update
if (isset($_POST['update'])) {

    try {

        // Retrieve form data
        $modulid = $_POST['modulID'];
        $modul = $_POST['module'];
        $kemahiranid = $_POST['kemahiranID'];
        $progress1 = $_POST['progress1'];
        $progress2 = $_POST['progress2'];
        $progress3 = $_POST['progress3'];
        $progress4 = $_POST['progress4'];
        // $target = $_POST['target'];


        $stmt = $conn->prepare("UPDATE tbl_spabs_modul SET 
                                    nama_modul = :modul,
                                    kemahiranID = :kemahiranid,
                                    kemajuan_satu = :progress1,
                                    kemajuan_dua = :progress2,
                                    kemajuan_tiga = :progress3,
                                    kemajuan_empat = :progress4
                                    WHERE modulID = :modulid");

        $stmt->bindParam(':modulid', $modulid, PDO::PARAM_STR);
        $stmt->bindParam(':modul', $modul, PDO::PARAM_STR);
        $stmt->bindParam(':kemahiranid', $kemahiranid, PDO::PARAM_STR);
        $stmt->bindParam(':progress1', $progress1, PDO::PARAM_STR);
        $stmt->bindParam(':progress2', $progress2, PDO::PARAM_STR);
        $stmt->bindParam(':progress3', $progress3, PDO::PARAM_STR);
        $stmt->bindParam(':progress4', $progress4, PDO::PARAM_STR);
        // $stmt->bindParam(':sasaran', $target, PDO::PARAM_STR);

        $stmt->execute();

        $stmt2 = $conn->prepare("SELECT * FROM tbl_spabs_kemahiran WHERE kemahiranID = :kemahiranid");
        $stmt2->bindParam(':kemahiranid', $kemahiranid, PDO::PARAM_STR);
        $stmt2->execute();
        $row = $stmt2->fetch(PDO::FETCH_ASSOC);
        $category = $row['kategori'];

        // Redirect to Module list after successful insertion
        header("Location: module.php?category=$category");
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

//Delete
if (isset($_GET['delete'])) {

    try {

        $stmt = $conn->prepare("DELETE FROM tbl_spabs_modul WHERE modulID = :mid");

        $stmt->bindParam(':mid', $mid, PDO::PARAM_STR);

        $mid = $_GET['delete'];



        $stmt2 = $conn->prepare("SELECT m.*, k.*
        FROM tbl_spabs_modul m
        JOIN tbl_spabs_kemahiran k ON m.kemahiranID = k.kemahiranID
        WHERE m.modulID = :modulid
        ");
        $stmt2->bindParam(':modulid', $mid, PDO::PARAM_STR);
        $stmt2->execute();
        $row = $stmt2->fetch(PDO::FETCH_ASSOC);
        $category = $row['kategori'];

        $stmt->execute();

        // Redirect to Module list after successful insertion
        header("Location: module.php?category=$category");
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

//Edit
if (isset($_GET['edit'])) {

    try {

        $stmt = $conn->prepare("SELECT * FROM tbl_spabs_modul WHERE modulID = :mid");

        $stmt->bindParam(':mid', $mid, PDO::PARAM_STR);

        $mid = $_GET['edit'];

        $stmt->execute();

        $editrow = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

?>