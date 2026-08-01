<?php

include_once '../database.php';

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Create
if (isset($_POST['create'])) {

    try {

        $stmt = $conn->prepare("INSERT INTO tbl_spabs_akaun (userID, email, password, user_role) VALUES(:userid, :email, :password, :userType)");

        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':userType', $userType, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);

        $userid = $_POST['userid'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $userType = $_POST['userType'];
        $password = $_POST['password'];
        $phone = $_POST['phone'];
        $phone2 = $_POST['phone2'];
        $category = $_POST['category'];
        $address = $_POST['address'];

        // $retype_password = $_POST['retype_password'];

        // if ($password !== $retype_password) {
        //     echo '<div class="alert alert-danger" role="alert">Password and Retype Password did not match!</div>';
        // } else {   
        $stmt->execute();
        // }
        if ($userType == 'Coach') {
            $stmt2 = $conn->prepare("INSERT INTO tbl_spabs_jurulatih (jurulatihID, nama_jurulatih, tel_jurulatih, kategori) VALUES(:userid, :name, :phone, :category)");

            $stmt2->bindParam(':userid', $userid, PDO::PARAM_STR);
            $stmt2->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt2->bindParam(':category', $category, PDO::PARAM_STR);
            $stmt2->bindParam(':phone', $phone, PDO::PARAM_STR);

            $stmt2->execute();
        } elseif ($userType == 'Parent') {
            $stmt3 = $conn->prepare("INSERT INTO tbl_spabs_ibubapa (ibubapaID, nama_ibubapa, tel_ibubapa, tel_ibubapa2, alamat) VALUES(:userid, :name, :phone, :phone2, :address)");

            $stmt3->bindParam(':userid', $userid, PDO::PARAM_STR);
            $stmt3->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt3->bindParam(':phone', $phone, PDO::PARAM_STR);
            $stmt3->bindParam(':phone2', $phone2, PDO::PARAM_STR);
            $stmt3->bindParam(':address', $address, PDO::PARAM_STR);

            $stmt3->execute();
        } elseif ($userType == 'Admin') {
            $stmt4 = $conn->prepare("INSERT INTO tbl_spabs_pentadbir (pentadbirID, nama_pentadbir, tel_pentadbir) VALUES(:userid, :name, :phone)");

            $stmt4->bindParam(':userid', $userid, PDO::PARAM_STR);
            $stmt4->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt4->bindParam(':phone', $phone, PDO::PARAM_STR);

            $stmt4->execute();
        }

        // Redirect to user_list.php after processing the form
        header("Location: user_list.php");
        exit(); // Ensure that script execution stops after redirection

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

//Update
if (isset($_POST['update'])) {

    try {

        $stmt = $conn->prepare("UPDATE tbl_spabs_akaun  SET
      email = :email, password = :password, 
      user_role = :userType WHERE userID = :userid");

        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':userType', $userType, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);

        $userid = $_POST['userid'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $userType = $_POST['userType'];
        $password = $_POST['password'];
        $phone = $_POST['phone'];
        $phone2 = $_POST['phone2'];
        $category = $_POST['category'];

        $stmt->execute();

        $stmt->execute();

        if ($userType == 'Coach') {
            $stmt2 = $conn->prepare("UPDATE tbl_spabs_jurulatih
                                    SET nama_jurulatih = :name, tel_jurulatih = :phone, kategori = :category
                                    WHERE jurulatihID = :userid");

            $stmt2->bindParam(':userid', $userid, PDO::PARAM_STR);
            $stmt2->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt2->bindParam(':category', $category, PDO::PARAM_STR);
            $stmt2->bindParam(':phone', $phone, PDO::PARAM_STR);

            $stmt2->execute();
        } elseif ($userType == 'Parent') {
            $stmt3 = $conn->prepare("UPDATE tbl_spabs_ibubapa
                                    SET nama_ibubapa = :name, tel_ibubapa = :phone, tel_ibubapa2 = :phone2
                                    WHERE ibubapaID = :userid");

            $stmt3->bindParam(':userid', $userid, PDO::PARAM_STR);
            $stmt3->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt3->bindParam(':phone', $phone, PDO::PARAM_STR);
            $stmt3->bindParam(':phone2', $phone2, PDO::PARAM_STR);

            $stmt3->execute();
        } elseif ($userType == 'Admin') {
            $stmt4 = $conn->prepare("UPDATE tbl_spabs_pentadbir
                                    SET nama_pentadbir = :name, tel_pentadbir = :phone
                                    WHERE pentadbirID = :userid");

            $stmt4->bindParam(':userid', $userid, PDO::PARAM_STR);
            $stmt4->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt4->bindParam(':phone', $phone, PDO::PARAM_STR);

            $stmt4->execute();
        }


        header("Location: user_list.php");
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
                                    JOIN tbl_spabs_jurulatih
                                    ON tbl_spabs_jurulatih.jurulatihID = tbl_spabs_akaun.userID
                                    WHERE userID = :uid");
        } elseif ($user_role === 'Admin') {
            // Perform query for admin
            $stmt2 = $conn->prepare("SELECT * FROM tbl_spabs_akaun 
                                    JOIN tbl_spabs_pentadbir
                                    ON tbl_spabs_pentadbir.pentadbirID = tbl_spabs_akaun.userID
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

        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        $user_id = $_SESSION['userID']; // Assume the user is logged in and user ID is stored in session

        // Fetch the current password from the database
        $stmt = $conn->prepare("SELECT password FROM tbl_spabs_akaun WHERE id = :uid");
        $stmt->bindParam("uid", $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $current_password = $row['password'];

        // Verify the old password
        if ($old_password === $current_password) {
            // Check if the new password and confirm password match
            if ($new_password === $confirm_password) {
                // Update the new password in the database
                $stmt = $conn->prepare("UPDATE tbl_spabs_akaun SET password = :pass WHERE id = :uid");
                $stmt->bindParam("pass", $new_password);
                $stmt->bindParam("uid", $user_id);
                if ($stmt->execute()) {
                    echo "Password changed successfully.";
                } else {
                    echo "Error updating password.";
                }

            } else {
                $message = '<label>New password and confirm password do not match.</label>';

            }
        } else {
            $message = '<label>Old password is incorrect. </label>';

        }
    } catch (PDOException) {
        echo "Error: " . $e->getMessage();
    }

}

$conn = null;

?>