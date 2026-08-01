<?php
include_once ("player_crud.php");

include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}



?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Edit Player Info</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">


    <!-- <link href="css/bootstrap.css" rel="stylesheet"> -->
    <link href="../css/main.css" rel="stylesheet">

    <style>
        img {
            border-color: black;
        }

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
                <span class="ms-2">Edit Player Information - <?php if (isset($_GET['edit']))
                    echo $editrow['nama_pemain']; ?></span>
                <span class="user-role">Admin</span>
            </header>

            <div class="container">
                <div class="form-container p-5">
                    <h1 class="mb-4">Player's Information Edit Form</h1>
                    <form action="player_crud.php" method="POST">
                        <div class="row mb-5">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name" class="mb-2">Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="Enter Name" required value="<?php if (isset($_GET['edit']))
                                            echo $editrow['nama_pemain']; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="parent" id="parentLabel" class="mb-2">Parent</label>
                                    <input type="text" class="form-control" id="parent" name="parent"
                                        placeholder="Enter Name" required disabled value="<?php if (isset($_GET['edit']))
                                            echo $editrow['nama_ibubapa']; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="icnumber" class="mb-2">IC Number</label>
                                    <input type="text" class="form-control" id="icnumber" name="icnumber"
                                        placeholder="Enter IC Number" required pattern="^[0-9]{12}$"
                                        title="Invalid IC Number format. Please enter a 12-digit number without -"
                                        value="<?php if (isset($_GET['edit']))
                                            echo $editrow['ic_pemain']; ?>">
                                    <!-- pattern="[0-9]{6}-[0-9]{2}-[0-9]{4}"
                                        title="Please enter the IC Number in the format ######-##-####" -->
                                </div>
                            </div>

                        </div>
                        <div class="row mb-5">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="birthdate" class="mb-2">Date of Birth</label>
                                    <input type="text" class="form-control" id="birthdate" name="birthdate"
                                        placeholder="Birthdate" required disabled value="<?php if (isset($_GET['edit']))
                                            echo $editrow['tarikh_lahir']; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="age" class="mb-2">Age</label>
                                <input type="number" class="form-control" id="age" name="age" placeholder="Age" min="5"
                                    max="12" value="<?php if (isset($_GET['edit']))
                                        echo $editrow['umur']; ?>">
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="category" id="categoryLabel" class="mb-2">Category</label>
                                    <select class="form-select" style="width: 100%;" id="category" name="category"
                                        required>
                                        <option value="">Select Category</option>
                                        <option value="U8" <?php if (isset($_GET['edit']) && $editrow['kategori'] == 'U8')
                                            echo 'selected'; ?>>U8</option>
                                        <option value="U10" <?php if (isset($_GET['edit']) && $editrow['kategori'] == 'U10')
                                            echo 'selected'; ?>>U10</option>
                                        <option value="U12" <?php if (isset($_GET['edit']) && $editrow['kategori'] == 'U12')
                                            echo 'selected'; ?>>U12</option>
                                    </select>
                                </div>
                            </div>


                        </div>


                        <div class="row mb-5">


                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="gender" class="mb-2">Gender</label>
                                    <input type="text" class="form-control" id="gender" name="gender"
                                        placeholder="Gender" required disabled value="<?php if (isset($_GET['edit']))
                                            echo $editrow['jantina']; ?>">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="registerDate" class="mb-2">Registered Date</label>
                                    <input type="text" class="form-control" id="registerDate" name="registerDate"
                                        required disabled value="<?php if (isset($_GET['edit']))
                                            echo date('d/m/Y', strtotime($editrow['tarikh_daftar'])); ?>">
                                </div>
                            </div>


                        </div>

                        <div class="row mt-2">
                            <div class="col-md-12 text-end">
                                <div class="form-group">
                                    <input type="hidden" name="pid" id="pid" value="<?php if (isset($_GET['edit']))
                                        echo $editrow['pemainID']; ?>">
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

        function confirmUpdate() {
            var confirmed = window.confirm('Are you sure you want to update this player information?'); // Display confirmation dialog
            if (!confirmed) {
                event.preventDefault(); // Prevent form submission if not confirmed
            }
        }
    </script>



</body>

</html>