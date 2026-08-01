<?php
include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Admin") {
    header("location:../admin/index.php");
    exit;
}

$parentID = $_SESSION['userID'];

include_once ("user_crud.php");

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fetch Parent Data
$stmt = $conn->prepare("SELECT *
FROM tbl_spabs_akaun AS sa
JOIN tbl_spabs_ibubapa AS sib ON sa.userID = sib.ibubapaID
WHERE sa.userID = :pid;");
$stmt->bindParam(':pid', $parentID, PDO::PARAM_STR);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch Player Data
$stmt2 = $conn->prepare("SELECT * FROM tbl_spabs_pemain WHERE ibubapaID = :pid");
$stmt2->bindParam(':pid', $parentID, PDO::PARAM_STR);
$stmt2->execute();
$players = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$playerCount = count($players);

// Fetch the current password from the database
$stmt3 = $conn->prepare("SELECT password FROM tbl_spabs_akaun WHERE userID = :uid");
$stmt3->bindParam(":uid", $parentID, PDO::PARAM_STR);
$stmt3->execute();
$pass = $stmt3->fetch(PDO::FETCH_ASSOC);
$currentpass = $pass['password']
    ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Profiles</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">

    <link href="../css/main.css" rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery-bundle.min.css">
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lg-autoplay.min.css"> -->
    <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery-bundle.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/lightgallery.min.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/autoplay/lg-autoplay.min.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/thumbnail/lg-thumbnail.min.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/fullscreen/lg-fullscreen.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/video/lg-video.min.js"></script>

    <!-- jQuery, Popper.js, and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


    <style>
        .form-container {
            width: 100%;
            /* background-color: #f8f9fa; */
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        img {
            border-color: black;
        }

        /* img {
            width: 200px;
            height: 200px;
            border: 3px solid #165227;
            border-radius: 20px;
            padding: 5px;
            display: inline-block;
        } */

        label {
            font-weight: bold;
        }

        .circular-frame {
            /* width: 30%;
            height: 30%;
            border-radius: 20px; */
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            object-position: center;
            display: block;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 10px;
            border: 2px solid #dee2e6;
        }

        .form-group input,
        .form-group textarea {
            background-color: #ffffff;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        .card,
        .accordion,
        .form-container {
            box-shadow: 10px 10px 5px #888888;
            /* horizontal offset, vertical offset, blur radius, shadow color */
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include_once 'sidebar.php'; ?>
        <div class="main">
            <header>
                <span class="ms-2">Profiles</span>
                <span class="user-role">Parent</span>
            </header>
            <div class="container">
                <div class="row mb-4 justify-content-center ">
                    <div class="col-md-4">
                        <div class="form-container p-4 mb-4">
                            <h1 class="mb-1 text-center">My Profile</h1>
                            <hr>

                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="name" class="mb-2">Name</label>
                                        <input type="text" class="form-control" name="name" id="name" disabled
                                            value="<?php echo $row['nama_ibubapa']; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="email" class="mb-2">Email address</label>
                                        <input type="email" class="form-control" name="email" id="email" disabled
                                            value="<?php echo $row['email']; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone" class="mb-2">Phone Number</label>
                                        <input type="text" class="form-control" name="phone" id="phone" disabled
                                            value="<?php echo $row['tel_ibubapa']; ?>">
                                    </div>

                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone2" id="phone2Label" class="mb-2">2<sup>nd</sup> Phone
                                            Number</label>
                                        <input type="text" class="form-control" id="phone2" name="phone2" disabled
                                            value="<?php echo $row['tel_ibubapa2']; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="address" class="mb-2">Address</label>
                                        <textarea class="form-control" id="address" name="address"
                                            placeholder="Enter Address" disabled rows="5"><?php
                                            echo $row['alamat']; ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <button type="button" class="btn btn-outline-primary" role="button"
                                        data-toggle="modal" data-target="#editModal" name="edit">Edit</button>
                                    <!-- <button type="button" class="btn btn-outline-secondary">Change Password</button> -->

                                    <button class="btn btn-outline-secondary btn-xs " role="button" data-toggle="modal"
                                        data-target="#changePassModal">Change Password</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="form-container p-4">
                            <h1 class="mb-1 text-center">Player's Profile</h1>
                            <hr>
                            <?php
                            $count = 1;
                            if ($playerCount >= 1) {
                                foreach ($players as $player): ?>

                                    <div class="row mb-4 justify-content-center">

                                        <div class="col-md-4 text-center mt-1">
                                            <div class="gallery" id="gallery<?php echo $count ?>">
                                                <form id="playerForm_<?php echo $player['pemainID']; ?>"
                                                    action="change_picture.php" method="POST" enctype="multipart/form-data">
                                                    <a href="../pictures/players/<?php echo $player['gambar']; ?>">
                                                        <img src="../pictures/players/<?php echo $player['gambar']; ?>"
                                                            alt="Player Image" class="circular-frame"
                                                            id="playerImage_<?php echo $player['pemainID']; ?>"
                                                            style="width: 150px; height: 150px;">
                                                    </a>
                                                    <input type="file" name="playerPicture"
                                                        id="playerPicture_<?php echo $player['pemainID']; ?>"
                                                        accept=".jpg,.jpeg,.png" style="display: none;"
                                                        onchange="submitForm('<?php echo $player['pemainID']; ?>')">
                                                    <input type="hidden" name="playerID"
                                                        value="<?php echo $player['pemainID']; ?>">
                                                    <button type="button" class="btn btn-outline-secondary mt-2"
                                                        onclick="document.getElementById('playerPicture_<?php echo $player['pemainID']; ?>').click();">Change
                                                        Picture</button>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="col-md-8 ">
                                            <div class="row mb-2 d-flex align-items-center justify-content-center">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="name" class="mb-1 font-weight-bold">Name</label>
                                                        <input type="text" class="form-control" name="name" required disabled
                                                            value="<?php echo $player['nama_pemain']; ?>">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mb-2  d-flex align-items-center justify-content-center">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="icnumber" class="mb-1 font-weight-bold">IC Number</label>
                                                        <input type="text" class="form-control" name="icnumber" required
                                                            disabled value="<?php echo $player['ic_pemain']; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="age" class="mb-1 font-weight-bold">Age</label>
                                                    <input type="number" class="form-control" name="age" disabled
                                                        value="<?php echo $player['umur']; ?>">
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="category" id="categoryLabel"
                                                            class="mb-1 font-weight-bold">Category</label>
                                                        <input type="text" class="form-control" name="category" required
                                                            disabled value="<?php echo $player['kategori']; ?>">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mb-2  d-flex align-items-center justify-content-center">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="birthdate" class="mb-1 font-weight-bold">Date of
                                                            Birth</label>
                                                        <input type="text" class="form-control" name="birthdate" required
                                                            disabled value="<?php echo $player['tarikh_lahir']; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="gender" class="mb-1 font-weight-bold">Gender</label>
                                                        <input type="text" class="form-control" name="gender"
                                                            placeholder="Gender" required disabled
                                                            value="<?php echo $player['jantina']; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                    <label for="registerDate" class="mb-2">Registered Date</label>
                                    <input type="text" class="form-control" id="registerDate" name="registerDate"
                                        required disabled  value="<?php  echo date('d/m/Y', strtotime($player['tarikh_daftar'])); ?>">
                                </div>
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                    <?php if ($playerCount > 1): ?>
                                        <hr>
                                    <?php endif; ?>

                                    <?php $count++; ?>
                                <?php endforeach; ?>
                            <?php } else { ?>
                                <div class="text-center">No Registered Player</div>
                                <?php
                            } ?>
                        </div>
                    </div>
                </div>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">

                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModal">Edit Information</h5>
                                <button type="button" class="custom-close-button" data-dismiss="modal"
                                    aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="user_crud.php" method="post" id="editForm">
                                    <div class="row mb-2">
                                        <div class="form-group">
                                            <label for="name" class="mb-2">Name</label>
                                            <input type="text" class="form-control" name="name" id="namee" required
                                                readonly value="<?php
                                                echo $row['nama_ibubapa'];
                                                ?>">
                                        </div>
                                    </div>
                                    <div class="row mb-2">

                                        <div class="form-group">
                                            <label for="email" class="mb-2">Email address</label>
                                            <input type="email" class="form-control" name="email" id="emaill" required
                                                readonly value="<?php
                                                echo $row['email']; ?>">
                                        </div>

                                    </div>
                                    <div class="row mb-2">
                                        <div class="form-group">
                                            <label for="phone" class="mb-2">Phone Number</label>
                                            <input type="text" class="form-control" name="phone" id="phonee" value="<?php

                                            echo $row['tel_ibubapa'];

                                            ?>" required pattern="01[0-9]-?\d{7,8}">
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="form-group">
                                            <label for="phone2" class="mb-2">Secondary Phone Number</label>
                                            <input type="text" class="form-control" name="phone2" id="phonee2" value="<?php

                                            echo $row['tel_ibubapa2'];

                                            ?>" required pattern="01[0-9]-?\d{7,8}">
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="form-group">
                                            <label for="address" class="mb-2">Address</label>
                                            <textarea class="form-control" id="address" name="address"
                                                placeholder="Enter Address" required rows="5"><?php
                                                echo $row['alamat']; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 text-end">
                                            <div class="form-group">
                                                <input type="hidden" name="userid" id="userid"
                                                    value="<?php echo $row['userID']; ?>">
                                                <button type="button" class="btn btn-danger"
                                                    data-dismiss="modal">Cancel</button>
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



                <!-- Change Password Modal -->
                <div class="modal fade" id="changePassModal" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="changePassModalLabel">Change Password</h5>
                                <button type="button" class="custom-close-button" data-dismiss="modal"
                                    aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="changePasswordForm" action="user_crud.php" method="POST">
                                    <div class="form-group">
                                        <label for="currentPassword">Current Password</label>
                                        <input type="password" class="form-control mb-2" id="currentPassword"
                                            name="currentPassword" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="newPassword">New Password</label>
                                        <input type="password" class="form-control mb-2" id="newPassword"
                                            name="newPassword" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirmPassword">Confirm Password</label>
                                        <input type="password" class="form-control mb-2" id="confirmPassword"
                                            name="confirmPassword" required>
                                    </div>
                                    <div class="text-center">
                                        <div id="error-message" style="color: red; display: none;">Passwords do not
                                            match.
                                        </div>
                                        <div id="error-message2" style="color: red; display: none;">Current password
                                            incorrect.
                                        </div>
                                    </div>

                                    <input type="hidden" id="current-pass" value="<?php echo $currentpass ?>">
                                    <input type="hidden" id="userID" name="userID" value="<?php echo $parentID ?>">

                                    <div class="text-end mt-3">
                                        <button type="button" class="btn btn-danger"
                                            data-dismiss="modal">Cancel</button>
                                        <button type="submit" name="pass" class="btn btn-success ">Confirm</button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function submitForm(playerID) {
            // Use jQuery to submit the form
            $('#playerForm_' + playerID).submit();
        }

        $("document").ready(function () {

            function submitForm(playerID) {
                // Use jQuery to submit the form
                $('#playerForm_' + playerID).submit();
            }

            $(function () {
                $('[data-toggle="tooltip"]').tooltip()
            })

            document.getElementById('changePasswordForm').addEventListener('submit', function (event) {
                const currentPassword = document.getElementById('currentPassword').value;
                const currentPass = document.getElementById('current-pass').value;
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                const errorMessage = document.getElementById('error-message');
                const errorMessage2 = document.getElementById('error-message2');

                // Clear old error messages
                errorMessage.style.display = 'none';
                errorMessage2.style.display = 'none';

                if (currentPassword !== currentPass) {
                    errorMessage2.style.display = 'block';
                    event.preventDefault();
                } else if (newPassword !== confirmPassword) {
                    errorMessage.style.display = 'block';
                    event.preventDefault();
                }
                else {
                    errorMessage.style.display = 'none';
                }
            });

            // Add event listener for when the modal is fully hidden
            $('#changePassModal').on('hidden.bs.modal', function () {
                // Clear the form inputs
                document.getElementById('changePasswordForm').reset();
                // Hide the error messages
                document.getElementById('error-message').style.display = 'none';
                document.getElementById('error-message2').style.display = 'none';
            });

            // Add event listener for when the modal is fully hidden
            $('#editModal').on('hidden.bs.modal', function () {
                // Clear the form inputs
                document.getElementById('editForm').reset();

            });

            function confirmUpdate() {
                var confirmed = window.confirm('Are you sure you want to update your phone number?'); // Display confirmation dialog
                if (!confirmed) {
                    event.preventDefault(); // Prevent form submission if not confirmed
                }
            }



        });

    </script>



    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Get all gallery containers
            const galleryContainers = document.querySelectorAll('.gallery');

            // Loop through each gallery container and initialize LightGallery
            galleryContainers.forEach(function (container) {
                const galleryId = container.id; // Get the ID of the current gallery container
                const galleryElement = document.getElementById(galleryId);

                lightGallery(galleryElement, {
                    // Your LightGallery settings and plugins here
                    selector: 'a',
                    counter: 'false',
                    licenseKey: 'D3F14FC6-13D3-4BD2-9C0A-2115881DEC51'
                });
            });
        });
    </script>







</body>



</html>