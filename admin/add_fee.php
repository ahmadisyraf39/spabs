<?php
include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}

include_once ("fee_crud.php");
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Add New Fee</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">


    <!-- <link href="css/bootstrap.css" rel="stylesheet"> -->
    <!-- <link href="../css/main.css" rel="stylesheet"> -->

    <style>
        .card,
        .accordion,
        .form-container {
            box-shadow: 0px 0px 0px 0px;
            /* horizontal offset, vertical offset, blur radius, shadow color */
        }
    </style>

</head>


<body>






    <div class="form-container">
        <form action="fee_crud.php" method="post">
            <div class="row mb-3">
                <div class="col">
                    <div class="form-group">
                        <label for="fee_name" class="mb-2">Fee Name</label>
                        <input type="text" class="form-control" id="fee_name" name="fee_name"
                            placeholder="Enter Fee Name">
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-7">
                    <div class="form-group">
                        <label for="category" class="mb-2">Category</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="U8">U8</option>
                            <option value="U10">U10</option>
                            <option value="U12">U12</option>
                            <option value="ALL">ALL</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="form-group">
                        <label for="fee_price" class="mb-2">Fee Price</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text">RM</div>
                            </div>
                            <input type="number" class="form-control" id="fee_price" name="fee_price" min="1" required>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="yuranID" id="yuranID"
                value="<?php echo $newFeeID = incrementID($latestFeeID); ?>">


            <div class="row">
                <div class="text-center">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create" class="btn btn-success">Confirm</button>
                </div>
            </div>

        </form>


    </div>












</body>

</html>