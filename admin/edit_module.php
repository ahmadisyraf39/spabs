<?php
include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}

include_once ("module_crud.php");

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


    <div class="form-container">
        <form action="module_crud.php" method="post">
            <div class="row mb-3">
                <div class="col">
                    <div class="form-group">
                        <label for="module">Module:</label>
                        <input type="text" class="form-control" id="module" name="module"
                            placeholder="Enter Module Name" required value="<?php if (isset($_GET['edit']))
                                echo $editrow['nama_modul']; ?>">
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <div class="form-group">
                        <label for="progress1">Progress 1 (25%):</label>
                        <textarea class="form-control" id="progress1" name="progress1" rows="3"><?php if (isset($_GET['edit']))
                            echo $editrow['kemajuan_satu']; ?></textarea>
                    </div>
                </div>

            </div>

            <div class="row mb-3">
                <div class="col">
                    <div class="form-group">
                        <label for="progress2">Progress 2 (50%):</label>
                        <textarea class="form-control" id="progress2" name="progress2" rows="3"><?php if (isset($_GET['edit']))
                            echo $editrow['kemajuan_dua']; ?></textarea>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <div class="form-group">
                        <label for="progress3">Progress 3 (75%):</label>
                        <textarea class="form-control" id="progress3" name="progress3" rows="3"><?php if (isset($_GET['edit']))
                            echo $editrow['kemajuan_tiga']; ?></textarea>
                    </div>
                </div>

            </div>

            <div class="row mb-3">
                <div class="col">
                    <div class="form-group">
                        <label for="progress4">Progress 4 (100%):</label>
                        <textarea class="form-control" id="progress4" name="progress4" rows="3"><?php if (isset($_GET['edit']))
                            echo $editrow['kemajuan_empat']; ?></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="text-center">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="update" class="btn btn-success">Update</button>
                </div>
            </div>

            <input type="hidden" name="modulID" id="modulID" value="<?php echo $editrow['modulID']; ?>">
            <input type="hidden" id="kemahiranID" name="kemahiranID" value="<?php echo $editrow['kemahiranID']; ?>">
        </form>
    </div>


</body>

</html>