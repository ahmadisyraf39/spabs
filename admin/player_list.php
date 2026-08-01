<?php
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
    <title>SPABS: Player List</title>
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
            background-color: #148634;
            /* background-color: #165227; */
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
            background-color: #165227;
            /* background-color: #148634; */
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
                <span class="ms-2">Player List</span>
                <span class="user-role">Admin</span>
            </header>
            <div class="container p-5">

                <!-- Create the drop down filter -->
                <div class="category-filter">
                    <select id="categoryFilter" class="form-select form-select-sm">
                        <option value="">Show All</option>
                        <option value="U8">U8</option>
                        <option value="U10">U10</option>
                        <option value="U12">U12</option>
                    </select>
                    <span class="caret"></span> <!-- Bootstrap dropdown symbol -->
                </div>

                <table id="playerTable" class="table table-striped table-bordered text-center" style="width:100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>IC Number</th>
                            <th>Age</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="playerTableBody">
                        <?php

                        try {
                            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            $stmt = $conn->prepare("select * from tbl_spabs_pemain");
                            $stmt->execute();
                            $result = $stmt->fetchAll();
                        } catch (PDOException $e) {
                            echo "Error: " . $e->getMessage();
                        }
                        foreach ($result as $readrow) {
                            ?>
                            <tr>
                                <td><?php echo $readrow['nama_pemain']; ?></td>
                                <td style="text-align:center;"><?php echo $readrow['ic_pemain']; ?></td>
                                <td style="text-align:center;"><?php echo $readrow['umur']; ?></td>
                                <td><?php echo $readrow['kategori']; ?></td>
                                <td>
                                    <button data-href="player_details.php?pid=<?php echo $readrow['pemainID']; ?>"
                                        class="btn btn-outline-success btn-xs" role="button" data-toggle="modal"
                                        data-target="#playerModal">Details</button>
                                    <a href="edit_player.php?edit=<?php echo $readrow['pemainID']; ?>"
                                        class="btn btn-outline-primary btn-xs" role="button"> Edit </a>
                                    <a href="player_crud.php?delete=<?php echo $readrow['pemainID']; ?>"
                                        onclick="return confirm('Are you sure you want to delete this player?');"
                                        class="btn btn-outline-danger btn-xs" role="button">Delete</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

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

                <a href="register_player.php" class="floating-button" data-toggle="tooltip" data-placement="left"
                    title="Register New Player">+</a>

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
                    { "orderable": false, "targets": [1, 3, 4] }, // Disable sorting for columns 0, 1, 2, 3 (Name, Email, Phone Number, UserType)
                    { "searchable": false, "targets": [1, 2, 4] }
                ],
                "lengthMenu": [8, 15, 25], // Dropdown options for page length
                "pageLength": 8, // Default number of records per page
                "lengthChange": true, // Enable length change dropdown
                "initComplete": function () {
                    var dtSearchInput = $('#dt-search-0');
                    dtSearchInput.after($("#categoryFilter")); // Append the category filter dropdown after the search input
                }
            });

            // Filter function based on dropdown selection
            $('#categoryFilter').on('change', function () {
                var selectedCategory = $(this).val().toLowerCase();
                console.log("Selected Category:", selectedCategory); // Debug statement to check the value of selectedCategory
                playerTable.column(3).search(selectedCategory).draw(); // Filter column 3 (UserType) based on dropdown selection
                // $('#playerTableBody tr').each(function () {
                //     var userType = $(this).find('td:nth-child(4)').text().toLowerCase();
                //     if (selectedCategory === '' || userType === selectedCategory) {
                //         $(this).show();
                //     } else {
                //         $(this).hide();
                //     }
                // });

                // Update the display information after filtering
                var filteredRows = playerTable.rows({ search: 'applied' }).nodes().length;
                var totalRows = playerTable.rows().nodes().length;
                var infoText = 'Showing ' + filteredRows + ' of ' + totalRows + ' entries';
                $('#playerTable_info').html(infoText);
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

        });
    </script>




</body>

</html>