<?php

include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}

include_once ("player_crud.php");

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to get the latest parent ID
    $stmtPlayer = $conn->prepare("SELECT MAX(pemainID) AS latestPlayerID FROM tbl_spabs_pemain");
    $stmtPlayer->execute();
    $rowPlayer = $stmtPlayer->fetch(PDO::FETCH_ASSOC);
    $latestPlayerID = $rowPlayer['latestPlayerID'];

    // Increment the IDs and generate new IDs
    function incrementID($id)
    {
        // Check if the ID is empty (no records in the database)
        if (empty($id)) {
            return 'PLA001'; // Start with PLA001 if no records exist
        }

        $numericPart = substr($id, 3);
        $incrementedNumericPart = intval($numericPart) + 1;
        $newID = substr($id, 0, 3) . str_pad($incrementedNumericPart, strlen($numericPart), '0', STR_PAD_LEFT);
        return $newID;
    }

    // Generate new user IDs based on the retrieved latest IDs
    $newPlayerID = incrementID($latestPlayerID);

    //echo "Latest Parent ID: $latestPlayerID, New Player ID: $newPlayerID";

    // Fetch all ic from tbl_spabs_user
    $stmtIC = $conn->prepare("SELECT ic_pemain FROM tbl_spabs_pemain");
    $stmtIC->execute();
    $ic = $stmtIC->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Register New Player</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">


    <!-- <link href="css/bootstrap.css" rel="stylesheet"> -->
    <link href="../css/main.css" rel="stylesheet">

    <style>
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
                <span class="ms-2">Register New Player</span>
                <span class="user-role">Admin</span>
            </header>

            <div class="container">
                <div class="form-container p-5">
                    <!-- This is the register new user page -->
                    <h1 class="mb-4">Please fill the information below.</h1>
                    <form id="registerForm" action="player_crud.php" method="post" enctype="multipart/form-data"
                        autocomplete="off">
                        <div class="row mb-5">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name" class="mb-2">Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="Enter Name" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="icnumber" class="mb-2">IC Number</label>
                                    <input type="text" class="form-control" id="icnumber" name="icnumber"
                                        placeholder="Enter IC Number" required pattern="^[0-9]{12}$"
                                        title="Invalid IC Number format. Please enter a 12-digit number without -">
                                    <!-- pattern="[0-9]{6}-[0-9]{2}-[0-9]{4}"
                                        title="Please enter the IC Number in the format ######-##-####" -->
                                    <div id="ic-error" class="text-danger"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="parent" id="parentLabel" class="mb-2">Parent</label>
                                    <select class="form-select" style="width: 100%;" id="parent" required>
                                        <option value="">Select Parent</option>
                                        <?php
                                        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                                        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                                        $stmt = $conn->prepare("SELECT ibubapaID, nama_ibubapa FROM tbl_spabs_ibubapa");
                                        $stmt->execute();
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            // Generate option tags dynamically with data-ibubapaID attribute
                                            echo '<option value="' . htmlspecialchars($row['nama_ibubapa']) . '" data-ibubapaID="' . htmlspecialchars($row['ibubapaID']) . '">' . htmlspecialchars($row['nama_ibubapa']) . '</option>';
                                        }
                                        ?>

                                    </select>
                                    <input type="hidden" id="ibubapaID" name="ibubapaID">
                                    <!-- Debugging display -->
                                    <div id="debugOutput"></div>
                                </div>
                            </div>

                        </div>
                        <div class="row mb-5">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="category" id="categoryLabel" class="mb-2">Category</label>
                                    <select class="form-select" style="width: 100%;" id="category" name="category"
                                        required>
                                        <option value="">Select Category</option>
                                        <option value="U8">U8</option>
                                        <option value="U10">U10</option>
                                        <option value="U12">U12</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="age" class="mb-2">Age</label>
                                <input type="number" class="form-control" id="age" name="age" placeholder="Age" min="5"
                                    max="12">
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="birthdate" class="mb-2">Date of Birth</label>
                                    <input type="text" class="form-control" id="birthdate" name="birthdate"
                                        placeholder="Birthdate" required readonly>
                                </div>
                            </div>

                        </div>


                        <div class="row mb-5">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="gender" class="mb-2">Gender</label>
                                    <!-- <select class="form-select" style="width: 100%;" id="gender" required readonly>
                                        <option value="">Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select> -->
                                    <input type="text" class="form-control" id="gender" name="gender"
                                        placeholder="Gender" required readonly>
                                </div>
                            </div>

                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="image" class="mb-2">Picture (Optional)</label>
                                    <input type="file" class="form-control-file" id="image" name="image"
                                        accept=".jpg, .jpeg, .png">
                                </div>
                            </div>
                        </div>


                        <div class="row mt-2">
                            <div class="col-md-12 text-end">
                                <div class="form-group">
                                    <input type="hidden" name="pid" id="pid" value="<?php echo $newPlayerID; ?>">
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
            // Get the select element
            const parentSelect = document.getElementById('parent');

            // Add an event listener to the select element
            parentSelect.addEventListener('change', function () {
                // Get the selected option
                const selectedOption = parentSelect.options[parentSelect.selectedIndex];

                // Get the data-ibubapaID attribute value
                const ibubapaID = selectedOption.getAttribute('data-ibubapaID');

                // Set the value to the hidden input field
                document.getElementById('ibubapaID').value = ibubapaID;

                // Output debugging information
                // const debugOutputDiv = document.getElementById('debugOutput');
                // debugOutputDiv.innerHTML = ibubapaID;
            });

            $("#icnumber").on("input", function () {
                var mycard = $(this);

                // Check if the input value is empty
                if (mycard.val().trim() === "") {
                    // Optionally, you can display an error message or perform some action
                    return;
                }

                // Check if the input value is a 12-digit number
                var pattern = /^[0-9]{12}$/;
                var isValid = pattern.test(mycard.val());

                if (isValid) {
                    // Optionally, you can perform further validation or processing logic here
                    var dobYear = mycard.val().slice(0, 2);
                    var dobMonth = mycard.val().slice(2, 4);
                    var dobDate = mycard.val().slice(4, 6);
                    var genderDigit = mycard.val().slice(-1);

                    // Set the date of birth using dd/mm/yy format
                    $("#birthdate").val(dobDate + "/" + dobMonth + "/" + "20" + dobYear);

                    if (isEven(genderDigit)) {
                        //setSelectedValue($("#gender"), "female");
                        $("#gender").val("Female")
                    } else {
                        //setSelectedValue($("#gender"), "male");
                        $("#gender").val("Male")
                    }

                    // Calculate age
                    var currentDate = new Date();
                    var currentYear = currentDate.getFullYear();
                    var age = currentYear - (2000 + parseInt(dobYear)); // Assuming 2000 is the starting year for the MyCard format

                    $("#age").val(age);

                    if (age >= 11) {
                        $("#category").val("U12")
                    }
                    else if (age >= 9) {
                        $("#category").val("U10")
                    }
                    else {
                        $("#category").val("U8")
                    }


                } else {

                }
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
            const ic = <?php echo json_encode($ic); ?>; // Convert PHP array to JavaScript array

            const icInput = document.getElementById('icnumber');
            const form = document.getElementById('registerForm');

            icInput.addEventListener('input', function () {
                const enteredIC = icInput.value.trim();

                // Check if the entered email already exists in the emails array
                const icExists = ic.includes(enteredIC);

                const errorDiv = document.getElementById('ic-error');

                if (icExists) {
                    errorDiv.textContent = 'Player with this ic number already exists.';
                    icInput.classList.add('is-invalid');
                } else {
                    errorDiv.textContent = '';
                    icInput.classList.remove('is-invalid');
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

        function setSelectedValue(selectObj, valueToSet) {
            selectObj.find("option[value='" + valueToSet + "']").prop("selected", true);
        }

        function isEven(value) {
            return value % 2 === 0;
        }

        function confirmCancel() {
            var confirmed = window.confirm('Are you sure you want to cancel?'); // Display confirmation dialog
            if (confirmed) {
                window.location.href = 'player_list.php'; // Redirect if user confirms
            }
        }

        function confirmRegister() {
            var confirmed = window.confirm('Are you sure you want to register this player?'); // Display confirmation dialog
            if (!confirmed) {
                event.preventDefault(); // Prevent form submission if not confirmed
            }
        }

    </script>



</body>

</html>