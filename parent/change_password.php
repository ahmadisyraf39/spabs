<?php
include_once ("../session.php");
include_once ("user_crud.php");
include_once ("../db.php");

if (isset($_POST["pass"])) {

    try {

        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        // $user_id = $_SESSION['userID']; // Assume the user is logged in and user ID is stored in session
        $user_id = $_POST['uid'];
        // Fetch the current password from the database
        $stmt = $conn->prepare("SELECT password FROM tbl_spabs_akaun WHERE userID = :uid");
        $stmt->bindParam("uid", $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $current_password = $row['password'];

        // Verify the old password
        if ($old_password === $current_password) {
            // Check if the new password and confirm password match
            if ($new_password === $confirm_password) {
                // Update the new password in the database
                $stmt = $conn->prepare("UPDATE tbl_spabs_akaun SET password = :pass WHERE userID = :uid");
                $stmt->bindParam("pass", $new_password);
                $stmt->bindParam("uid", $user_id);
                if ($stmt->execute()) {
                    echo "Password changed successfully.";
                    header("Location: parent_profile.php");

                } else {
                    echo "Error updating password.";
                    header("Location: parent_profile.php");
                }

            } else {
                $message = '<label>New password and confirm password do not match.</label>';

            }
        } else {
            $message = '<label>Old password is incorrect. </label>';

        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">


</head>

<body>
    <?php

    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // if (isset($_GET['uid'])) {
    // $uid = $_GET['uid'];
    $uid = 'PAR01';
    try {
        $stmt = $conn->prepare("SELECT * FROM tbl_spabs_akaun WHERE userID = :uid");
        $stmt->bindParam(':uid', $uid, PDO::PARAM_STR);
        $stmt->execute();
        $readrow = $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    ?>

    <div class="form-container">
        <form method="post">
            <div class="row mb-2">
                <div class="col">
                    <div class="form-group">
                        <label for="old_password">Old Password:</label>
                        <input type="password" id="old_password" name="old_password" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col">
                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col">
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                            required>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="text-center">
                    <input type="hidden" name="uid" value="<?php echo $uid; ?>">
                    <button type="button" class="cancel-btn" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="pass" class="buttons">Send</button>
                </div>
            </div>


            <div class="row">
                <div class="text-center fs-6">
                    <?php
                    if (isset($message)) {
                        echo '<label class="text-danger">' . $message . '</label>';
                    }
                    ?>
                </div>
            </div>


        </form>
    </div>

    <?php
    // }
    ?>
</body>

</html>