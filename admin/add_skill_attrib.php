<?php
include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}

include_once ("skill_attrib_crud.php");

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
        <form action="skill_attrib_crud.php" method="post">
            <div class="row mb-3">
                <div class="col">

                    <div class="form-group">
                        <label for="name">Skill/Attribute:</label>
                        <input type="text" class="form-control" id="skill" name="skill"
                            placeholder="Enter Skill/Attribute Type" required>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <div class="form-group">
                        <label for="target">Category:</label>
                        <select class="form-select" style="width: 100%;" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="U8">U8</option>
                            <option value="U10">U10</option>
                            <option value="U12">U12</option>
                        </select>
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="text-center">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create" class="btn btn-success">Confirm</button>
                </div>
            </div>

            <input type="hidden" name="kemahiranID" id="kemahiranID"
                value="<?php echo $newSkillID = incrementID($latestSkillID); ?>">


        </form>
    </div>


</body>

</html>