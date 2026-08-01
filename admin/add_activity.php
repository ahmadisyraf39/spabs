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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Add New Activity</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">


    <!-- <link href="css/bootstrap.css" rel="stylesheet"> -->
    <link href="../css/main.css" rel="stylesheet">

    <style>
        .form-label {
            font-weight: bold;
        }

        label {
            font-weight: bold;
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
                <span class="ms-2">Add New Activity</span>
                <span class="user-role">Admin</span>
            </header>

            <div class="container">
                <div class="form-container p-5">
                    <!-- This is the register new user pstartTime -->
                    <h1 class="mb-4">Please fill the information below.</h1>
                    <form action="activity_crud.php" method="post" autocomplete="off">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="mb-2">Activity Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="Enter Name" required>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="type" id="typeLabel" class="mb-2">Activity Type</label>
                                    <select class="form-select" style="width: 100%;" id="type" name="type" required>
                                        <option value="">Select type</option>
                                        <option value="Training">Training</option>
                                        <option value="Friendly Match">Friendly Match</option>
                                        <option value="Tournament">Tournament</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="category" id="categoryLabel" class="mb-2">Category</label>
                                    <select class="form-select" style="width: 100%;" id="category" name="category"
                                        required>
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

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date" class="mb-2">Date</label>
                                    <input type="date" class="form-control" id="date" name="date" required>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label for="startTime" class="mb-2">Start Time</label>
                                <input type="time" class="form-control" id="startTime" name="startTime">
                            </div>

                            <div class="col-md-3">
                                <label for="endTime" class="mb-2">End Time</label>
                                <input type="time" class="form-control" id="endTime" name="endTime">
                                <small id="endTimeError" class="text-danger"></small>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="recurring" class="mb-2">Recurring Activity</label>
                                    <select class="form-select" style="width: 100%;" id="recurring" name="recurring">
                                        <option value="">None</option>
                                        <option value="1">Repeat For 1 Week</option>
                                        <option value="2">Repeat For 2 Weeks</option>
                                        <option value="3">Repeat For 3 Weeks</option>
                                        <option value="4">Repeat For 4 Weeks</option>
                                        <option value="5">Repeat For 5 Weeks</option>
                                        <option value="6">Repeat For 6 Weeks</option>
                                        <option value="7">Repeat For 7 Weeks</option>
                                        <option value="8">Repeat For 8 Weeks</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="location" class="mb-2">Location</label>
                                    <textarea class="form-control" id="location" name="location"
                                        placeholder="Enter Location" required rows="4"></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="desc" class="mb-2">Description (Optional)</label>
                                    <textarea class="form-control" id="desc" name="desc" placeholder="Enter Description"
                                        rows="4"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3 tournament-field">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="gender-category" class="mb-2">Gender Category</label>
                                    <select class="form-select" id="gender-category" name="gender-category">
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="max-player-number" class="mb-2">Maximum Player Number</label>
                                    <input class="form-control" type="number" id="max-player-number"
                                        name="max-player-number" min="1" step="1">
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-12 text-end">
                                <div class="form-group">
                                    <input type="hidden" name="aid" id="aid"
                                        value="<?php echo $newActivityID = incrementID($latestActivityID); ?>">
                                    <button type="button" class="btn btn-danger"
                                        onclick="confirmCancel()">Cancel</button>
                                    <button type="submit" name="create" class="btn btn-success"
                                        onclick="confirmAdd()">Add</button>
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
            // $('#date').attr('min', today);

            document.getElementById('endTime').addEventListener('change', function () {
                var startTime = document.getElementById('startTime').value;
                var endTime = this.value;

                if (startTime >= endTime) {
                    document.getElementById('endTimeError').textContent = 'End time cannot be earlier than or equal to start time.';
                    this.value = ''; // Clear the end time input
                } else {
                    document.getElementById('endTimeError').textContent = '';
                }
            });

            $('.tournament-field').hide();

            // Show/Hide gender-category and max-player-number based on the selected activity type
            $('#type').change(function () {
                if ($(this).val() == 'Tournament') {
                    $('.tournament-field').show();
                    $('#gender-category').prop('required', true);
                    $('#max-player-number').prop('required', true);
                } else {
                    $('.tournament-field').hide();
                    $('#gender-category').prop('required', false);
                    $('#max-player-number').prop('required', false);
                }
            });
        });

        function confirmCancel() {
            var confirmed = window.confirm('Are you sure you want to cancel?'); // Display confirmation dialog
            if (confirmed) {
                window.location.href = 'player_list.php'; // Redirect if user confirms
            }
        }

        function confirmAdd() {
            var confirmed = window.confirm('Are you sure you want to add this activity?'); // Display confirmation dialog
            if (!confirmed) {
                event.preventDefault(); // Prevent form submission if not confirmed
            }
        }
    </script>
</body>

</html>