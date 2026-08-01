<?php
include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}

include_once ("activity_crud.php");

if (!isset($_GET['edit'])) {
    header("Location: activity_list.php");
    exit();
}



?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Edit Activity</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">

    <link href="../css/main.css" rel="stylesheet">

    <style>
        .form-label {
            font-weight: bold;
        }

        label {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include_once 'sidebar.php'; ?>

        <div class="main">
            <header>
                <span class="ms-2">Edit Activity</span>
                <span class="user-role">Admin</span>
            </header>

            <div class="container">
                <div class="form-container p-5">
                    <h1 class="mb-4">Please fill the information below.</h1>
                    <form action="activity_crud.php" method="post">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="mb-2">Activity Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="Enter Name" required value="<?php if (isset($_GET['edit']))
                                            echo $editrow['nama_aktiviti']; ?>">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="type" id="typeLabel" class="mb-2">Activity Type</label>
                                    <input type="text" class="form-control" id="type" name="type" readonly required
                                        value="<?php if (isset($_GET['edit']))
                                            echo $editrow['jenis_aktiviti']; ?>">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="category" id="categoryLabel" class="mb-2">Category</label>
                                    <input type="text" class="form-control" id="category" name="category" readonly
                                        required value="<?php if (isset($_GET['edit']))
                                            echo $editrow['kategori']; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date" class="mb-2">Date</label>
                                    <input type="date" class="form-control" id="date" name="date" required value="<?php if (isset($_GET['edit']))
                                        echo $editrow['tarikh_aktiviti']; ?>">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label for="startTime" class="mb-2">Start Time</label>
                                <input type="time" class="form-control" id="startTime" name="startTime" value="<?php if (isset($_GET['edit']))
                                    echo $editrow['masa_mula']; ?>">
                            </div>

                            <div class="col-md-3">
                                <label for="endTime" class="mb-2">End Time</label>
                                <input type="time" class="form-control" id="endTime" name="endTime" value="<?php if (isset($_GET['edit']))
                                    echo $editrow['masa_tamat']; ?>">
                                <small id="endTimeError" class="text-danger"></small>
                            </div>

                            <?php if ($editrow['jenis_aktiviti'] == 'Tournament') {
                                ?>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="gender-category" class="mb-2">Gender Category</label>
                                        <input type="text" class="form-control" id="gender-category" name="gender-category"
                                            readonly required value="<?php if (isset($_GET['edit']))
                                                echo $editrow['jantina']; ?>">
                                    </div>
                                </div>

                                <?php
                            } ?>


                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="location" class="mb-2">Location</label>
                                    <textarea class="form-control" id="location" name="location"
                                        placeholder="Enter Location" required rows="4"><?php if (isset($_GET['edit']))
                                            echo $editrow['lokasi']; ?></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="desc" class="mb-2">Description (Optional)</label>
                                    <textarea class="form-control" id="desc" name="desc" placeholder="Enter Description"
                                        rows="4"><?php if (isset($_GET['edit']))
                                            echo $editrow['penerangan']; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <?php if ($editrow['jenis_aktiviti'] == 'Tournament') {
                            ?>

                            <!-- Additional Fields for Tournament -->
                            <div class="row mb-3 tournament-field">

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="max-player-number" class="mb-2">Maximum Player Number</label>
                                        <input class="form-control" type="number" id="max-player-number"
                                            name="max-player-number" min="1" step="1" value="<?php if (isset($_GET['edit']))
                                                echo $editrow['maksimum_pemain']; ?>">
                                    </div>
                                </div>


                            </div>

                            <?php
                        } ?>

                        <div class="row mt-2">
                            <div class="col-md-12 text-end">
                                <div class="form-group">
                                    <input type="hidden" name="aid" id="aid"
                                        value="<?php echo $editrow['aktivitiID']; ?>">
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
        $(document).ready(function () {
            // Get today's date in the format YYYY-MM-DD
            const today = new Date().toISOString().split('T')[0];
            // Set the min attribute of the date input to today's date using jQuery
            $('#date').attr('min', today);


            // Validate end time
            $('#endTime').on('change', function () {
                var startTime = $('#startTime').val();
                var endTime = $(this).val();

                if (startTime >= endTime) {
                    $('#endTimeError').text('End time cannot be earlier than or equal to start time.');
                    $(this).val(''); // Clear the end time input
                } else {
                    $('#endTimeError').text('');
                }
            });
        });

        function confirmCancel() {
            var confirmed = window.confirm('Are you sure you want to cancel?'); // Display confirmation dialog
            if (confirmed) {
                window.location.href = 'activity_list.php'; // Redirect if user confirms
            }
        }

        function confirmUpdate() {
            var confirmed = window.confirm('Are you sure you want to update this activity?'); // Display confirmation dialog
            if (!confirmed) {
                event.preventDefault(); // Prevent form submission if not confirmed
            }
        }
    </script>
</body>

</html>