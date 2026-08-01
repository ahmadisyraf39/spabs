<?php

include_once '../database.php';

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Generate ID
try {

    // Query to get the latest activity ID
    $stmtActivity = $conn->prepare("SELECT MAX(aktivitiID) AS latestActivityID FROM tbl_spabs_aktiviti");
    $stmtActivity->execute();
    $rowActivity = $stmtActivity->fetch(PDO::FETCH_ASSOC);
    $latestActivityID = $rowActivity['latestActivityID'];

    // Increment the IDs and generate new IDs
    function incrementID($id)
    {

        // Check if the ID is empty (no records in the database)
        if (empty($id)) {
            return 'ACT001'; // Start with PLA001 if no records exist
        }

        $numericPart = substr($id, 3);
        $incrementedNumericPart = intval($numericPart) + 1;
        $newID = substr($id, 0, 3) . str_pad($incrementedNumericPart, strlen($numericPart), '0', STR_PAD_LEFT);
        return $newID;
    }

    // Generate new user IDs based on the retrieved latest IDs
    // $newActivityID = incrementID($latestActivityID);

    //echo "Latest Parent ID: $latestActivityID, New Activity ID: $newActivityID";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

//Create
if (isset($_POST['create'])) {
    try {
        // Retrieve form data
        $aid = $_POST['aid'];
        $type = $_POST['type'];
        $name = $_POST['name'];
        $desc = $_POST['desc'];
        $category = $_POST['category'];
        $location = $_POST['location'];
        $date = $_POST['date'];
        $startTime = $_POST['startTime'];
        $endTime = $_POST['endTime'];

        // Prepare the main INSERT statement
        $stmt = $conn->prepare("INSERT INTO tbl_spabs_aktiviti(aktivitiID, jenis_aktiviti, nama_aktiviti, penerangan, kategori, lokasi, tarikh_aktiviti, masa_mula, masa_tamat) VALUES(:aid, :type, :name, :desc, :category, :location, :date, :startTime, :endTime)");
        $stmt->bindParam(':aid', $aid, PDO::PARAM_STR);
        $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':desc', $desc, PDO::PARAM_STR);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindParam(':location', $location, PDO::PARAM_STR);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':startTime', $startTime, PDO::PARAM_STR);
        $stmt->bindParam(':endTime', $endTime, PDO::PARAM_STR);

        // Handle recurring activity if selected
        if (isset($_POST['recurring']) && !empty($_POST['recurring'])) {
            $repeatWeeks = intval($_POST['recurring']);

            // Generate and insert subsequent recurring activities
            for ($i = 1; $i <= $repeatWeeks; $i++) {
                // Query to get the latest activity ID
                $stmtActivity = $conn->prepare("SELECT MAX(aktivitiID) AS latestActivityID FROM tbl_spabs_aktiviti");
                $stmtActivity->execute();
                $rowActivity = $stmtActivity->fetch(PDO::FETCH_ASSOC);
                $latestActivityID = $rowActivity['latestActivityID'];

                // Calculate new date based on recurrence (e.g., add 7 days for each week)
                $newDate = date('Y-m-d', strtotime($date . " + $i weeks"));

                // Generate a new activity ID (ensure this function generates unique IDs)
                $newAid = incrementID($latestActivityID);

                // Bind new values for recurring activity
                $stmt->bindParam(':aid', $newAid, PDO::PARAM_STR);
                $stmt->bindParam(':date', $newDate, PDO::PARAM_STR);

                // Execute the INSERT statement for recurring activity
                $stmt->execute();
            }
        } else {
            // Execute the main INSERT statement for non-recurring activity
            $stmt->execute();
        }

        if ($type == 'Tournament') {
            $stmt2 = $conn->prepare("INSERT INTO tbl_spabs_kejohanan (kejohananID, maksimum_pemain, jantina) VALUES(:aid, :maxPlayer, :gender)");

            $stmt2->bindParam(':aid', $aid, PDO::PARAM_STR);
            $stmt2->bindParam(':maxPlayer', $maxplayer, PDO::PARAM_STR);
            $stmt2->bindParam(':gender', $gendercategory, PDO::PARAM_STR);

            $gendercategory = $_POST['gender-category'];
            $maxplayer = $_POST['max-player-number'];

            $stmt2->execute();
        }

        // Redirect to activity list after successful insertion
        header("Location: activity_list.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

//Update
if (isset($_POST['update'])) {

    try {


        $stmt = $conn->prepare("UPDATE tbl_spabs_aktiviti SET aktivitiID = :aid, jenis_aktiviti = :type, 
        nama_aktiviti = :name, penerangan = :desc, kategori = :category,
        lokasi = :location, tarikh_aktiviti = :date, masa_mula = :startTime, masa_tamat = :endTime
        WHERE aktivitiID = :aid");

        $stmt->bindParam(':aid', $aid, PDO::PARAM_STR);
        $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':desc', $desc, PDO::PARAM_STR);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindParam(':location', $location, PDO::PARAM_STR);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':startTime', $startTime, PDO::PARAM_STR);
        $stmt->bindParam(':endTime', $endTime, PDO::PARAM_STR);

        $aid = $_POST['aid'];
        $type = $_POST['type'];
        $name = $_POST['name'];
        $desc = $_POST['desc'];
        $category = $_POST['category'];
        $location = $_POST['location'];
        $date = $_POST['date'];
        $startTime = $_POST['startTime'];
        $endTime = $_POST['endTime'];

        $stmt->execute();



        if ($type == 'Tournament') {
            $stmt2 = $conn->prepare("UPDATE tbl_spabs_kejohanan
                                        SET maksimum_pemain = :maxPlayer,
                                            jantina = :gender
                                        WHERE kejohananID = :aid");

            $stmt2->bindParam(':aid', $aid, PDO::PARAM_STR);
            $stmt2->bindParam(':maxPlayer', $maxplayer, PDO::PARAM_STR);
            $stmt2->bindParam(':gender', $gendercategory, PDO::PARAM_STR);

            $gendercategory = $_POST['gender-category'];
            $maxplayer = $_POST['max-player-number'];

            $stmt2->execute();
        }

        header("Location: activity_list.php");
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

//Delete
if (isset($_GET['delete'])) {

    try {

        $stmt = $conn->prepare("DELETE FROM tbl_spabs_aktiviti WHERE aktivitiID = :aid");

        $stmt->bindParam(':aid', $aid, PDO::PARAM_STR);

        $aid = $_GET['delete'];

        $stmt->execute();

        $category = isset($_GET['category']) ? $_GET['category'] : 'all';
        header("Location: activity_list.php?category=" . urlencode($category));
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

//Edit
if (isset($_GET['edit'])) {

    try {

        $stmt = $conn->prepare("SELECT * FROM tbl_spabs_aktiviti a
                                LEFT JOIN tbl_spabs_kejohanan k ON k.kejohananID = a.aktivitiID   
                                WHERE a.aktivitiID = :aid");

        $stmt->bindParam(':aid', $aid, PDO::PARAM_STR);

        $aid = $_GET['edit'];

        $stmt->execute();

        $editrow = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

$conn = null;
?>