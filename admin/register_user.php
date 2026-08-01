<?php
include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to get the latest parent ID
    $stmtParent = $conn->prepare("SELECT MAX(ibubapaID) AS latestParentID FROM tbl_spabs_ibubapa");
    $stmtParent->execute();
    $rowParent = $stmtParent->fetch(PDO::FETCH_ASSOC);
    $latestParentID = $rowParent['latestParentID'];

    // Query to get the latest coach ID
    $stmtCoach = $conn->prepare("SELECT MAX(jurulatihID) AS latestCoachID FROM tbl_spabs_jurulatih");
    $stmtCoach->execute();
    $rowCoach = $stmtCoach->fetch(PDO::FETCH_ASSOC);
    $latestCoachID = $rowCoach['latestCoachID'];

    // Query to get the latest Admin ID
    $stmtAdmin = $conn->prepare("SELECT MAX(pentadbirID) AS latestAdminID FROM tbl_spabs_pentadbir");
    $stmtAdmin->execute();
    $rowAdmin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);
    $latestAdminID = $rowAdmin['latestAdminID'];

    // Function to increment the IDs and generate new IDs
    function incrementID($id, $prefix)
    {
        // Check if the ID is empty (no records in the database)
        if (empty($id)) {
            return $prefix . '001'; // Start with 001 if no records exist
        }

        // Extract the numeric part of the ID
        $numericPart = substr($id, strlen($prefix)); // Remove the prefix
        $incrementedNumericPart = intval($numericPart) + 1;
        $newID = $prefix . str_pad($incrementedNumericPart, strlen($numericPart), '0', STR_PAD_LEFT);
        return $newID;
    }

    // Generate new user IDs based on the retrieved latest IDs
    $newParentID = incrementID($latestParentID, 'PAR');
    $newCoachID = incrementID($latestCoachID, 'COA');
    $newAdminID = incrementID($latestAdminID, 'ADM');

    // echo "Latest Parent ID: $latestParentID, New Parent ID: $newParentID<br>";
    // echo "Latest Coach ID: $latestCoachID, New Coach ID: $newCoachID<br>";

    // Fetch all emails from tbl_spabs_user
    $stmtEmails = $conn->prepare("SELECT email FROM tbl_spabs_akaun");
    $stmtEmails->execute();
    $emails = $stmtEmails->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Register New User</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <?php include_once 'sidebar.php'; ?>
        <div class="main">
            <header>
                <span class="ms-2">Register New User</span>
                <span class="user-role">Admin</span>
            </header>
            <div class="container">
                <div class="form-container p-5">
                    <!-- This is the register new user page -->
                    <h1 class="mb-4">Please fill all the information below.</h1>
                    <form id="registerForm" action="user_crud.php" method="post" autocomplete="off">
                        <div class="row mb-5">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="userType" class="mb-2">User Type</label>
                                    <select class="form-select" style="width: 100%;" id="userType" name="userType"
                                        required>
                                        <option value="">Select User Type</option>
                                        <option value="Parent">Parent</option>
                                        <option value="Coach">Coach</option>
                                        <option value="Admin">Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="mb-2">Name</label>
                                    <input type="text" class="form-control" name="name" id="name"
                                        placeholder="Enter Name" required>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-5">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="email" class="mb-2">Email address</label>
                                    <input type="email" class="form-control" name="email" id="email"
                                        placeholder="Enter Email" required>

                                    <div id="email-error" class="text-danger"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="password" class="mb-2">Password</label>
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="Enter Password" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="phone" class="mb-2">Phone Number</label>
                                    <input type="text" class="form-control" name="phone" id="phone"
                                        placeholder="Enter Phone Number" required pattern="01[0-9]-?\d{7,8}">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-5">

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="phone2" id="phone2Label" class="mb-2">2 <sup>nd</sup> Phone
                                        Number</label>
                                    <input type="text" class="form-control" id="phone2" name="phone2"
                                        placeholder="Enter 2nd Phone Number" required pattern="01[0-9]-?\d{7,8}">
                                    <label for="category" id="categoryLabel" class="mb-2">Category</label>
                                    <select class="form-select" style="width: 100%;" name="category" id="category"
                                        required>
                                        <option value="">Select Category</option>
                                        <option value="U8">U8</option>
                                        <option value="U10">U10</option>
                                        <option value="U12">U12</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="address" id="addressLabel" class="mb-2">Address</label>
                                    <textarea class="form-control" id="address" name="address"
                                        placeholder="Enter Address" required rows="4"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-end">
                                <div class="form-group">
                                    <input type="hidden" name="userid" id="userid"
                                        value="<?php echo ($userType === 'parent') ? $newParentID : $newCoachID; ?>">
                                    <button type="button" class="btn btn-danger"
                                        onclick="confirmCancel()">Cancel</button>
                                    <button type="submit" name="create" class="btn btn-success"
                                        onclick="confirmRegister()">Register</button>
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
            // Initially hide the secondary phone number and category inputs
            $('#phone2').hide();
            $('#category').hide();
            $('#phone2Label').hide();
            $('#categoryLabel').hide();
            $('#addressLabel').hide();
            $('#address').hide();

            // Show/hide inputs based on the selected option
            $('#userType').change(function () {
                var selectedOption = $(this).val();
                if (selectedOption === 'Parent') {
                    $('#phone2').show();
                    $('#category').hide();
                    $('#phone2Label').show();
                    $('#categoryLabel').hide();
                    $('#phone2').prop('required', true); // Set "required" for phone2
                    $('#category').prop('required', false); // Remove "required" for category
                    $('#addressLabel').show();
                    $('#address').show();
                    $('#address').prop('required', true);
                    var newUserID = '<?php echo $newParentID; ?>'; // Set new user ID for parent
                    $('#userid').val(newUserID); // Update the hidden field value

                } else if (selectedOption === 'Coach') {
                    $('#phone2').hide();
                    $('#category').show();
                    $('#phone2Label').hide();
                    $('#categoryLabel').show();
                    $('#addressLabel').hide();
                    $('#address').hide();
                    $('#address').prop('required', false);
                    $('#phone2').prop('required', false); // Remove "required" for phone2
                    $('#category').prop('required', true); // Set "required" for category
                    var newUserID = '<?php echo $newCoachID; ?>'; // Set new user ID for coach
                    $('#userid').val(newUserID); // Update the hidden field value
                } else if (selectedOption === 'Admin') {
                    $('#phone2').hide();
                    $('#category').hide();
                    $('#phone2Label').hide();
                    $('#categoryLabel').hide();
                    $('#addressLabel').hide();
                    $('#address').hide();
                    $('#address').prop('required', false);
                    $('#phone2').prop('required', false); // Remove "required" for phone2
                    $('#category').prop('required', false); // Set "required" for category
                    var newUserID = '<?php echo $newAdminID; ?>'; // Set new user ID for admin
                    $('#userid').val(newUserID); // Update the hidden field value
                } else {
                    $('#phone2').hide();
                    $('#category').hide();
                    $('#phone2Label').hide();
                    $('#categoryLabel').hide();
                    $('#addressLabel').hide();
                    $('#address').hide();
                }
                // $('#userid').val(selectedOption);
            });


        });

        document.addEventListener("DOMContentLoaded", function () {
            const emails = <?php echo json_encode($emails); ?>; // Convert PHP array to JavaScript array

            const emailInput = document.getElementById('email');

            const form = document.getElementById('registerForm');

            emailInput.addEventListener('input', function () {
                const enteredEmail = emailInput.value.trim();

                // Check if the entered email already exists in the emails array
                const emailExists = emails.includes(enteredEmail);

                const errorDiv = document.getElementById('email-error');

                if (emailExists) {
                    errorDiv.textContent = 'User with this email already exists. Please choose a different email.';
                    emailInput.classList.add('is-invalid');
                } else {
                    errorDiv.textContent = '';
                    emailInput.classList.remove('is-invalid');
                }
            });

            form.addEventListener('submit', function (event) {
                const invalidInputs = form.querySelectorAll('.is-invalid');
                if (invalidInputs.length > 0) {
                    event.preventDefault(); // Prevent form submission
                    alert('Please fix the errors in the form before submitting.');
                }
            });
        });

        function confirmCancel() {
            var confirmed = window.confirm('Are you sure you want to cancel?'); // Display confirmation dialog
            if (confirmed) {
                window.location.href = 'player_list.php'; // Redirect if user confirms
            }
        }

        function confirmRegister() {
            var confirmed = window.confirm('Are you sure you want to register this user?'); // Display confirmation dialog
            if (!confirmed) {
                event.preventDefault(); // Prevent form submission if not confirmed
            }
        }
    </script>
</body>

</html>