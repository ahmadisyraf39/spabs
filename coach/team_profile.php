<?php
include_once ("../session.php");

if ($role == "Admin") {
    header("location:../admin/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}

$coachID = $_SESSION['userID'];

include_once ("user_crud.php");

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $conn->prepare("SELECT *
                        FROM tbl_spabs_akaun AS sa
                        JOIN tbl_spabs_jurulatih AS sj ON sa.userID = sj.jurulatihID
                        WHERE sa.userID = :cid;");

$stmt->bindParam(':cid', $coachID, PDO::PARAM_STR);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$category = $row['kategori'];
$emel = $row['email'];


$stmt2 = $conn->prepare("SELECT * FROM tbl_spabs_jurulatih 
                        WHERE jurulatihID != :cid
                        AND kategori = :kategori;");

$stmt2->bindParam(':cid', $coachID, PDO::PARAM_STR);
$stmt2->bindParam(':kategori', $category, PDO::PARAM_STR);
$stmt2->execute();
$coaches = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Fetch the current password from the database
$stmt3 = $conn->prepare("SELECT password FROM tbl_spabs_akaun WHERE userID = :uid");
$stmt3->bindParam(":uid", $coachID, PDO::PARAM_STR);
$stmt3->execute();
$pass = $stmt3->fetch(PDO::FETCH_ASSOC);
$currentpass = $pass['password']



    ?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Team Profile</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">

    <!-- <link href="css/bootstrap.css" rel="stylesheet"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.5/css/dataTables.bootstrap5.css">

    <link href="../css/main.css" rel="stylesheet">



    <style>
        select.form-select {
            display: inline;
            width: 110px;
            margin-left: 10px;
            margin-right: 10px;
        }

        select.form-select:focus {
            box-shadow: 0 0 20px #148634;
        }

        .floating-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            /* background-color: #148634; */
            background-color: #165227;
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 24px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .floating-button:hover {
            /* background-color: #165227; */
            background-color: #148634;
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

        <?php
        include_once 'sidebar.php'
            ?>

        <div class="main">
            <header>
                <span class="ms-2">Team Profile - <?php echo $category ?></span>
                <span class="user-role">Admin</span>
            </header>
            <div class="container p-5">

                <div class="row mb-3 justify-content-center">
                    <div class="col-md-4">
                        <div class="form-container p-4">
                            <h1 class="mb-1 text-center">My Profile</h1>
                            <hr>

                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="name" class="mb-2">Name</label>
                                        <input type="text" class="form-control" name="name" id="name" disabled
                                            value="<?php echo $row['nama_jurulatih']; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="email" class="mb-2">Email address</label>
                                        <input type="email" class="form-control" name="email" id="email" disabled
                                            value="<?php echo $emel ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="phone" class="mb-2">Phone Number</label>
                                        <input type="text" class="form-control" name="phone" id="phone" disabled
                                            value="<?php echo $row['tel_jurulatih']; ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="phone2" id="phone2Label" class="mb-2">Category</label>
                                        <input type="text" class="form-control" id="phone2" name="phone2" disabled
                                            value="<?php echo $row['kategori']; ?>">
                                    </div>
                                </div>
                            </div>

                            <br>

                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <button type="button" class="btn btn-outline-primary" role="button"
                                        data-toggle="modal" data-target="#editModal" name="edit">Edit Tel</button>
                                    <!-- <button type="button" class="btn btn-outline-secondary">Change Password</button> -->

                                    <button class="btn btn-outline-secondary btn-xs " role="button" data-toggle="modal"
                                        data-target="#changePassModal">Change Password</button>
                                </div>
                            </div>
                        </div>

                        <div class="form-container p-4 mt-4">
                            <h1 class="text-center">Coach List</h1>
                            <hr>

                            <?php foreach ($coaches as $coach): ?>



                                <div class="row mb-2">
                                    <div class="col-md-12">
                                        <ol>
                                            <li style=" list-style-type: initial;"><?php echo $coach['nama_jurulatih']; ?>
                                            </li>
                                            <ul>
                                                <li style="list-style-type: circle;"> Tel:
                                                    <?php echo $coach['tel_jurulatih']; ?>
                                                </li>
                                            </ul>
                                        </ol>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="col-md-8 mt-4">
                        <div class="text-center">
                            <h1 class="mb-1">Player List</h1>
                            <hr>
                        </div>
                        <table id="playerTable" class="table table-striped table-bordered text-center"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Age</th>
                                    <th>Birthdate</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="playerTableBody">
                                <?php

                                try {
                                    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                    $stmt = $conn->prepare("select * from tbl_spabs_pemain WHERE kategori = :category");
                                    $stmt->bindParam(':category', $category, PDO::PARAM_STR);
                                    $stmt->execute();
                                    $result = $stmt->fetchAll();
                                } catch (PDOException $e) {
                                    echo "Error: " . $e->getMessage();
                                }
                                foreach ($result as $readrow) {
                                    ?>
                                    <tr>
                                        <td><?php echo $readrow['nama_pemain']; ?></td>
                                        <td style="text-align:center;"><?php echo $readrow['umur']; ?></td>
                                        <td style="text-align:center;"><?php echo $readrow['tarikh_lahir']; ?></td>
                                        <td>
                                            <button data-href="player_details.php?pid=<?php echo $readrow['pemainID']; ?>"
                                                class="btn btn-outline-success btn-xs" role="button" data-toggle="modal"
                                                data-target="#playerModal">Details</button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>





                <!-- <div class="buttons-container">
                    <a href="register_player.php" class="buttons">Register New Player</a>
                </div> -->

                <!-- Modal -->
                <div class="modal fade" id="playerModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">

                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="playerModal">Player Details</h5>
                                <button type="button" class="custom-close-button" data-dismiss="modal"
                                    aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <!-- Activity details will be loaded here -->
                            </div>
                            <!-- <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div> -->
                        </div>

                    </div>
                </div>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">

                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModal">Edit Phone Number Information</h5>
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
                                            <input type="text" class="form-control" name="name" id="name" required
                                                readonly value="<?php
                                                echo $row['nama_jurulatih'];
                                                ?>">
                                        </div>
                                    </div>
                                    <div class="row mb-2">

                                        <div class="form-group">
                                            <label for="email" class="mb-2">Email address</label>
                                            <input type="email" class="form-control" name="email" id="email" required
                                                readonly value="<?php
                                                echo $emel ?>">
                                        </div>

                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="phone" class="mb-2">Phone Number</label>
                                                <input type="text" class="form-control" name="phone" id="phone" value="<?php

                                                echo $row['tel_jurulatih'];

                                                ?>" required pattern="01[0-9]-?\d{7,8}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">


                                                <label for="category" id="categoryLabel" class="mb-2">Category</label>
                                                <input type="text" class="form-control" id="category" name="category"
                                                    value="<?php
                                                    echo $row['kategori']; ?>" required readonly>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 text-end">
                                            <div class="form-group">
                                                <input type="hidden" name="userid" id="userid"
                                                    value="<?php echo $userID ?>">
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
                                    <input type="hidden" id="userID" name="userID" value="<?php echo $coachID ?>">

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

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <!-- <script defer
        src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script> -->
    <script defer src="https://cdn.datatables.net/2.0.5/js/dataTables.js"></script>
    <script defer src="https://cdn.datatables.net/2.0.5/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


    <script>
        $("document").ready(function () {
            var playerTable = $('#playerTable').DataTable({
                "columnDefs": [
                    { "orderable": false, "targets": [2, 3] }, // Disable sorting for columns 0, 1, 2, 3 (Name, Email, Phone Number, UserType)
                    { "searchable": false, "targets": [1, 2, 3] }
                ],
                "lengthMenu": [8, 15, 25], // Dropdown options for page length
                "pageLength": 8, // Default number of records per page
                "lengthChange": true, // Enable length change dropdown
                "initComplete": function () {
                    var dtSearchInput = $('#dt-search-0');
                    dtSearchInput.after($("#categoryFilter")); // Append the category filter dropdown after the search input
                }
            });


            $('#playerModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var url = button.data('href'); // Extract info from data-* attributes
                var modal = $(this);

                // Clear previous content before making the AJAX request
                modal.find('.modal-body').html('<div class="text-center"><img src="../pictures/icons/loading.gif" alt="Loading..."></div>');

                // Use jQuery to load the content of the URL into the modal body
                $.ajax({
                    url: url,
                    success: function (data) {
                        modal.find('.modal-body').html(data);
                    }
                });
            });





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




</body>

</html>