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


</head>

<body>


    <div class="form-container">
        <form action="announcement_crud.php" method="post">
            <div class="row mb-3">
                <div class="col">

                    <div class="form-group">
                        <label for="name">Module:</label>
                        <input type="text" class="form-control" id="announcement" name="announcement"
                            placeholder="Enter Module Name" required value="<?php if (isset($_GET['edit']))
                                echo $editrow['tajuk']; ?>">
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <div class="form-group">
                        <label for="category">Category:</label>
                        <select class="form-select" style="width: 100%;" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="U8" <?php if (isset($_GET['edit']) && $editrow['kategori'] == 'U8')
                                echo 'selected'; ?>>U8</option>
                            <option value="U10" <?php if (isset($_GET['edit']) && $editrow['kategori'] == 'U10')
                                echo 'selected'; ?>>U10</option>
                            <option value="U12" <?php if (isset($_GET['edit']) && $editrow['kategori'] == 'U12')
                                echo 'selected'; ?>>U12</option>
                            <option value="ALL" <?php if (isset($_GET['edit']) && $editrow['kategori'] == 'ALL')
                                echo 'selected'; ?>>ALL</option>

                        </select>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <div class="form-group">
                        <label for="desc">Description:</label>
                        <textarea class="form-control" id="desc" name="desc" rows="4">
                                <?php if (isset($_GET['edit']))
                                    echo $editrow['penerangan']; ?>
                            </textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="text-center">
                    <button type="button" class="cancel-btn" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="update" class="buttons">Update</button>
                </div>
            </div>

            <input type="hidden" name="pengumumanID" id="pengumumanID" value="<?php echo $editrow['pengumumanID']; ?>">

        </form>
    </div>


</body>

</html>