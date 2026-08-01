<?php
include_once ("../session.php");

if ($role == "Admin") {
    header("location:../admin/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}


include_once ("user_crud.php");
include_once ("../db.php");



if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['userID'];

if (isset($_POST["pass"])) {
    try {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Fetch the current password hash from the database
        $stmt = $conn->prepare("SELECT password FROM tbl_spabs_akaun WHERE userID = :uid");
        $stmt->bindParam("uid", $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $current_password_hash = $row['password'];

            // Verify the old password
            if (password_verify($old_password, $current_password_hash)) {
                // Check if the new password and confirm password match
                if ($new_password === $confirm_password) {
                    // Hash the new password
                    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

                    // Update the new password hash in the database
                    $stmt = $conn->prepare("UPDATE tbl_spabs_akaun SET password = :pass WHERE userID = :uid");
                    $stmt->bindParam("pass", $new_password_hash, PDO::PARAM_STR);
                    $stmt->bindParam("uid", $user_id, PDO::PARAM_INT);

                    if ($stmt->execute()) {
                        echo "Password changed successfully.";
                        header("Location: parent_profile.php");
                        exit;
                    } else {
                        echo "Error updating password.";
                    }
                } else {
                    $message = '<label>New password and confirm password do not match.</label>';
                }
            } else {
                $message = '<label>Old password is incorrect.</label>';
            }
        } else {
            $message = '<label>User not found.</label>';
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
    <title>Change Password</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">

</head>

<body>
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
</body>

</html>