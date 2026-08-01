<?php
include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}

// Set the default category to U8 if none is provided
$filter_category = isset($_GET['category']) ? $_GET['category'] : 'U8';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Module</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">

    <link href="../css/main.css" rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery-bundle.min.css">

    <style>
        .accordion-item {
            margin-bottom: 10px;
            /* Adjust this value as needed */
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

        .accordion-button {
            display: flex;
            align-items: center;
            justify-content: start;
            /* Align content to the start */
        }

        .accordion-button::after {
            margin-left: auto;
            /* Push the icon to the start */
        }

        .card,
        .accordion-item {
            box-shadow: 10px 10px 5px #888888;
            /* horizontal offset, vertical offset, blur radius, shadow color */
        }

        .accordion {
            box-shadow: 0px 0px 0px 0px;
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
                <span class="ms-2">Module - <?php echo $filter_category ?></span>
                <span class="user-role">Admin</span>
            </header>
            <div class="container p-5">

                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div class="filter-buttons d-inline-block text-center mx-auto">
                        Filter by Category:
                        <a href="module.php?category=U8"
                            class="btn btn-outline-success btn-md mx-2 <?php echo $filter_category == 'U8' ? 'active' : ''; ?>">U8</a>
                        <a href="module.php?category=U10"
                            class="btn btn-outline-success btn-md mx-2 <?php echo $filter_category == 'U10' ? 'active' : ''; ?>">U10</a>
                        <a href="module.php?category=U12"
                            class="btn btn-outline-success btn-md mx-2 <?php echo $filter_category == 'U12' ? 'active' : ''; ?>">U12</a>
                    </div>
                    <button class="btn btn-success mrb50" data-iframe="true" id="open-pdf"
                        data-src="../rujukanModulKemahiran.pdf">
                        Module Guide
                    </button>
                </div>

                <?php
                try {
                    $stmt = $conn->prepare("SELECT * FROM tbl_spabs_kemahiran 
                                                WHERE kategori = :category");
                    $stmt->bindParam(':category', $filter_category, PDO::PARAM_STR);
                    $stmt->execute();
                    $result = $stmt->fetchAll();
                    $counter = 1; // Initialize the counter
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }

                if (count($result) > 0) { ?>

                    <div class="accordion accordion-flush" id="accordionExample">

                        <?php

                        foreach ($result as $readrow) {
                            $headingID = "heading" . $counter;
                            $collapseID = "collapse" . $counter;
                            ?>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="<?php echo $headingID; ?>">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#<?php echo $collapseID; ?>" aria-expanded="false"
                                        aria-controls="<?php echo $collapseID; ?>">
                                        <?php echo $readrow['jenis_kemahiran']; ?>
                                    </button>
                                </h2>
                                <div id="<?php echo $collapseID; ?>" class="accordion-collapse collapse"
                                    aria-labelledby="<?php echo $headingID; ?>" data-bs-parent="#accordionExample">
                                    <!-- Your accordion content here -->
                                    <div class="accordion-body m-0">
                                        <div class="form-container">
                                            <?php

                                            try {
                                                $kemahiranID = $readrow['kemahiranID'];

                                                $stmt = $conn->prepare("SELECT * FROM tbl_spabs_modul 
                                            WHERE kemahiranID = :kemahiranID");
                                                $stmt->bindParam(':kemahiranID', $kemahiranID, PDO::PARAM_STR);
                                                $stmt->execute();
                                                $result = $stmt->fetchAll();

                                            } catch (PDOException $e) {
                                                echo "Error: " . $e->getMessage();
                                            } ?>

                                            <?php
                                            // Check if there are any results
                                            if (count($result) > 0) { ?>

                                                <div class="row mb-2">
                                                    <div class="col-md-7">
                                                        Module:
                                                    </div>
                                                    <div class="col-md-5 text-center">
                                                        Actions:
                                                    </div>
                                                </div>

                                                <?php
                                                foreach ($result as $readrow2) {
                                                    ?>
                                                    <div class="row mb-2">

                                                        <div class="col-md-7">

                                                            <div class="form-group">
                                                                <input type="text" class="form-control" disabled
                                                                    value="<?php echo $readrow2['nama_modul']; ?>">
                                                            </div>
                                                        </div>



                                                        <div class="col-md-5 d-flex flex-column justify-content-center ">
                                                            <div class="text-center">
                                                                <button
                                                                    data-href="module_details.php?mid=<?php echo $readrow2['modulID']; ?>"
                                                                    class="btn btn-outline-success btn-xs mb-2" role="button"
                                                                    data-toggle="modal" data-target="#moduleModal">Details</button>
                                                                <button
                                                                    data-href="edit_module.php?edit=<?php echo $readrow2['modulID']; ?>"
                                                                    class="btn btn-outline-primary btn-xs mb-2" role="button"
                                                                    data-toggle="modal" data-target="#editModuleModal">Edit</button>
                                                                <a href="module_crud.php?delete=<?php echo $readrow2['modulID']; ?>"
                                                                    onclick="return confirm('Are you sure you want to delete this module?');"
                                                                    class="btn btn-outline-danger btn-xs mb-2" role="button">Delete</a>
                                                            </div>

                                                        </div>

                                                    </div>

                                                <?php }
                                            } else {
                                                ?>

                                                <div class="row mb-2 text-center">
                                                    <div class="col-md-12">
                                                        No Module Available
                                                    </div>
                                                </div>

                                                <?php
                                            }


                                            ?>

                                            <div class="row mt-3">
                                                <div class="col-md-12 text-center">
                                                    <button id="addModule"
                                                        data-href="add_module.php?kid=<?php echo $readrow['kemahiranID']; ?>"
                                                        class="btn btn-success rounded-circle" data-toggle="modal"
                                                        data-target="#addModuleModal" data-tippy-content="Add Module">
                                                        <i class="fa-solid fa-plus text-white"></i>
                                                    </button>
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                </div>

                            </div>


                            <div class="mb-4 text-end">
                                <a href="skill_attrib_crud.php?delete=<?php echo $readrow['kemahiranID']; ?>"
                                    onclick="return confirm('Are you sure you want to delete this <?php echo $readrow['jenis_kemahiran']; ?> skill/attribute for <?php echo $readrow['kategori']; ?> category?');"
                                    class="btn btn-danger btn-xs mb-2 mt-1" role="button">Delete</a>
                            </div>



                            <?php
                            $counter++; // Increment the counter
                        }
                        ?>
                    </div>
                    <?php

                } else {
                    ?>

                    <div class="row mt-3 mb-3 justify-content-center align-items-center">
                        <div class="card mb-3" style="width: 100%; border: 2px solid black; padding: 0;">
                            <div class="row g-0">
                                <div class="col-md-12 d-flex flex-column justify-content-center align-items-center">
                                    <div class="card-body">
                                        No Module Available
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <?php
                }


                ?>

                <button id="addSkillAttrib" data-href="add_skill_attrib.php" class="floating-button" role="button"
                    data-toggle="modal" data-target="#addSkillAttribModal"
                    data-tippy-content="Add Skill/Attribute">+</button>

                <!-- Modal -->
                <div class="modal fade" id="addSkillAttribModal" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">

                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addSkillAttribModal">Add Skill/Attribute</h5>
                                <button type="button" class="custom-close-button" data-dismiss="modal"
                                    aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">

                            </div>
                            <!-- <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div> -->
                        </div>

                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="addModuleModal" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">

                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addModuleModal">Add Module</h5>
                                <button type="button" class="custom-close-button" data-dismiss="modal"
                                    aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">


                            </div>
                            <!-- <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div> -->
                        </div>

                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="editModuleModal" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModuleModal">Edit Module</h5>
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

                <!-- Modal -->
                <div class="modal fade" id="moduleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="moduleModal">Module Details</h5>
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

            </div>
        </div>
    </div>



    <!-- jQuery, Popper.js, and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/lightgallery.min.js"></script>

    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>

    <script>
        // Initialize Tippy.js tooltips
        tippy('#addModule', {
            placement: 'left'
        });

        tippy('#addSkillAttrib', {
            placement: 'left'
        });

        lightGallery(document.getElementById('open-pdf'), {
            selector: 'this',
            licenseKey: 'D3F14FC6-13D3-4BD2-9C0A-2115881DEC51'
        });



        $('#addSkillAttribModal').on('show.bs.modal', function (event) {
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

        $('#addModuleModal').on('show.bs.modal', function (event) {
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

        $('#editModuleModal').on('show.bs.modal', function (event) {
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

        $('#moduleModal').on('show.bs.modal', function (event) {
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
    </script>
</body>

</html>