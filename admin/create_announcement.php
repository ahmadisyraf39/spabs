<?php
include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}

include_once ("announcement_crud.php");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">

    <style>
        .form-container {
            box-shadow: 0px 0px 0px 0px;
        }
    </style>


</head>

<body>
    <?php

    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    ?>

    <div class="form-container">
        <form action="announcement_crud.php" method="post">
            <div class="row mb-3">
                <div class="col">

                    <div class="form-group">
                        <label for="name">Tittle:</label>
                        <input type="text" class="form-control" id="announcement" name="announcement"
                            placeholder="Enter Announcement Tittle" required>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <div class="form-group">
                        <label for="category">Category:</label>
                        <select class="form-select" style="width: 100%;" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="U8">U8</option>
                            <option value="U10">U10</option>
                            <option value="U12">U12</option>
                            <option value="ALL">ALL</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <div class="form-group">
                        <label for="desc">Description:</label>
                        <textarea class="form-control" id="desc" name="desc" rows="4"></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="text-center">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create" class="btn btn-success">Confirm</button>
                </div>
            </div>

            <input type="hidden" name="pengumumanID" id="pengumumanID"
                value="<?php echo $newAnnouncementID = incrementID($latestAnnouncementID); ?>">


        </form>
    </div>


</body>

</html>