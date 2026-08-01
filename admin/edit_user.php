<?php
include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}

include_once ("user_crud.php");

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Edit User Information</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/main.css" rel="stylesheet">
</head>

<body>
    <div class="wrapper">
        <?php include_once 'sidebar.php'; ?>
        <div class="main">
            <header>
                <span class="ms-2">Edit User Information</span>
                <span class="user-role">Admin</span>
            </header>
            <div class="container">
                <div class="form-container p-5">
                    <!-- This is the register new user page -->
                    <h1 class="mb-4">User's Information Edit Form</h1>
                    <form action="user_crud.php" method="post">
                        <div class="row mb-5">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="userType" class="mb-2">User Type</label>
                                    <input type="text" class="form-control" name="userType" id="userType" readonly
                                        value="<?php if (isset($_GET['edit']))
                                            echo $editrow['user_role']; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="mb-2">Name</label>
                                    <input type="text" class="form-control" name="name" id="name" required value="<?php
                                    if (isset($_GET['edit'])) {
                                        if ($editrow['user_role'] == 'Parent') {
                                            echo $editrow['nama_ibubapa'];
                                        } elseif ($editrow['user_role'] == 'Coach') {
                                            echo $editrow['nama_jurulatih'];
                                        } elseif ($editrow['user_role'] == 'Admin') {
                                            echo $editrow['nama_pentadbir'];
                                        }
                                    }
                                    ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-5">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="mb-2">Email address</label>
                                    <input type="email" class="form-control" name="email" id="email" required value="<?php if (isset($_GET['edit']))
                                        echo $editrow['email']; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password" class="mb-2">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" value="<?php if (isset($_GET['edit']))
                                        echo $editrow['password']; ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-5">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone" class="mb-2">Phone Number</label>
                                    <input type="text" class="form-control" name="phone" id="phone" value="<?php
                                    if (isset($_GET['edit'])) {
                                        if ($editrow['user_role'] == 'Parent') {
                                            echo $editrow['tel_ibubapa'];
                                        } elseif ($editrow['user_role'] == 'Coach') {
                                            echo $editrow['tel_jurulatih'];
                                        } elseif ($editrow['user_role'] == 'Admin') {
                                            echo $editrow['tel_pentadbir'];
                                        }
                                    }
                                    ?>" required pattern="01[0-9]-?\d{7,8}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?php
                                    if (isset($_GET['edit']) && isset($editrow)) {
                                        if ($editrow['user_role'] == 'Parent') { ?>
                                            <label for="phone2" id="phone2Label" class="mb-2">Secondary Phone Number</label>
                                            <input type="text" class="form-control" id="phone2" name="phone2" value="<?php if (isset($_GET['edit']))
                                                echo $editrow['tel_ibubapa2']; ?>" required pattern="01[0-9]-?\d{7,8}">
                                            <?php
                                        } elseif ($editrow['user_role'] == 'Coach') { ?>
                                            <label for="category" id="categoryLabel" class="mb-2">Category</label>
                                            <select class="form-select" style="width: 100%;" name="category" id="category"
                                                required>
                                                <option value="">Select Category</option>
                                                <option value="U8" <?php if (isset($_GET['edit']) && $editrow['kategori'] == 'U8')
                                                    echo 'selected'; ?>>U8</option>
                                                <option value="U10" <?php if (isset($_GET['edit']) && $editrow['kategori'] == 'U10')
                                                    echo 'selected'; ?>>U10</option>
                                                <option value="U12" <?php if (isset($_GET['edit']) && $editrow['kategori'] == 'U12')
                                                    echo 'selected'; ?>>U12</option>
                                            </select>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-end">
                                <div class="form-group">
                                    <input type="hidden" name="userid" id="userid"
                                        value="<?php echo $editrow['userID']; ?>">
                                    <button type="button" class="btn btn-danger"
                                        onclick="confirmCancel()">Cancel</button>
                                    <button type="submit" name="update" class="btn btn-success"
                                        onclick="confirmUpdate()">Update</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <script>
        function confirmCancel() {
            var confirmed = window.confirm('Are you sure you want to cancel?'); // Display confirmation dialog
            if (confirmed) {
                window.location.href = 'player_list.php'; // Redirect if user confirms
            }
        }

        function confirmUpdate() {
            var confirmed = window.confirm('Are you sure you want to update this user information?'); // Display confirmation dialog
            if (!confirmed) {
                event.preventDefault(); // Prevent form submission if not confirmed
            }
        }
    </script>


</body>

</html>