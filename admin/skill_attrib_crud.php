<?php
include_once '../database.php';

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Generate ID
try {

    // Query to get the latest Skill ID
    $stmtSkill = $conn->prepare("SELECT MAX(kemahiranID) AS latestSkillID FROM tbl_spabs_kemahiran");
    $stmtSkill->execute();
    $rowSkill = $stmtSkill->fetch(PDO::FETCH_ASSOC);
    $latestSkillID = $rowSkill['latestSkillID'];

    // Increment the IDs and generate new IDs
    function incrementID($id)
    {

        // Check if the ID is empty (no records in the database)
        if (empty($id)) {
            return 'KEM001'; // Start with ACT001 if no records exist
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
        $kemahiranid = $_POST['kemahiranID'];
        $skill = $_POST['skill'];
        $category = $_POST['category'];

        // Prepare the main INSERT statement
        $stmt = $conn->prepare("INSERT INTO tbl_spabs_kemahiran(kemahiranID, jenis_kemahiran, kategori)
                                VALUES(:kemahiranid, :skill, :kategori)");

        $stmt->bindParam(':kemahiranid', $kemahiranid, PDO::PARAM_STR);
        $stmt->bindParam(':skill', $skill, PDO::PARAM_STR);
        $stmt->bindParam(':kategori', $category, PDO::PARAM_STR);

        $stmt->execute();

        // Redirect to Skill list after successful insertion
        header("Location: module.php?category=$category");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

}


//Delete
if (isset($_GET['delete'])) {

    try {

        $stmt = $conn->prepare("DELETE FROM tbl_spabs_kemahiran WHERE kemahiranID = :kid");

        $stmt->bindParam(':kid', $kid, PDO::PARAM_STR);

        $kid = $_GET['delete'];



        $stmt2 = $conn->prepare("SELECT *
        FROM tbl_spabs_kemahiran
        WHERE kemahiranID = :kid
        ");
        $stmt2->bindParam(':kid', $kid, PDO::PARAM_STR);
        $stmt2->execute();
        $row = $stmt2->fetch(PDO::FETCH_ASSOC);
        $category = $row['kategori'];

        $stmt->execute();

        // Redirect to Skill list after successful insertion
        header("Location: module.php?category=$category");
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}


?>