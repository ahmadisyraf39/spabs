<?php
include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$type_query = $conn->prepare("SELECT DISTINCT jenis_aktiviti 
                            FROM tbl_spabs_album 
              INNER JOIN tbl_spabs_aktiviti 
              ON tbl_spabs_album.aktivitiID = tbl_spabs_aktiviti.aktivitiID");
$type_query->execute();
$type_result = $type_query->fetchAll(PDO::FETCH_ASSOC);

function getFirstImage($albumID)
{
    $directory = "../pictures/gallery/$albumID/";
    $images = glob($directory . "*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF}", GLOB_BRACE);
    return count($images) > 0 ? $images[0] : '../pictures/noimage2.png'; // Fallback to a default image if no images found
}

$type_filter = isset($_GET['type']) ? $_GET['type'] : 'all';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Gallery</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">


    <!-- <link href="css/bootstrap.css" rel="stylesheet"> -->
    <link href="../css/main.css" rel="stylesheet">

    <style>
        * {
            transition: all .2s linear;
        }

        .img-fixed {
            width: 100%;
            height: 250px;
            /* Adjust this height as needed */
            object-fit: cover;
            /* Crop image to cover the specified dimensions */
        }

        .filter-menu {
            display: none;
            position: fixed;
            bottom: 90px;
            right: 20px;
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            z-index: 1000;
        }

        .filter-menu h3 {
            margin-top: 0;
        }

        .filter-menu label {
            display: block;
            margin-bottom: 10px;
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

        .card-img-top:hover {
            transform: scale(1.1);
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
        include_once 'sidebar.php';
        ?>

        <div class="main">
            <header>
                <span class="ms-2">Gallery</span>
                <span class="user-role">Admin</span>
            </header>

            <div class="container">

                <div class="text-center">
                    <div class="filter-buttons mb-5 d-inline-block">
                        Filter by Activity Type:
                        <?php foreach ($type_result as $type) {
                            $activeClass = ($type_filter == $type['jenis_aktiviti']) ? 'active' : '';
                            ?>
                            <a href="gallery.php?type=<?php echo urlencode($type['jenis_aktiviti']); ?>"
                                class="btn btn-outline-success btn-md mx-2 <?php echo $activeClass; ?>">
                                <?php echo $type['jenis_aktiviti']; ?>
                            </a>
                        <?php }
                        $activeClass = ($type_filter == 'all') ? 'active' : '';
                        ?>
                        <a href="gallery.php"
                            class="btn btn-outline-success btn-md mx-2 <?php echo $activeClass; ?>">All</a>
                    </div>
                </div>

                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php

                    try {
                        $query = "SELECT * FROM tbl_spabs_album 
                        INNER JOIN tbl_spabs_aktiviti 
                        ON tbl_spabs_album.aktivitiID = tbl_spabs_aktiviti.aktivitiID";

                        if ($type_filter !== 'all') {
                            $query .= " WHERE tbl_spabs_aktiviti.jenis_aktiviti = :type";
                        }

                        $query .= " ORDER BY tarikh_aktiviti DESC";

                        $stmt = $conn->prepare($query);

                        if ($type_filter !== 'all') {
                            $stmt->bindParam(':type', $type_filter, PDO::PARAM_STR);
                        }

                        $stmt->execute();
                        $result = $stmt->fetchAll();
                    } catch (PDOException $e) {
                        echo "Error: " . $e->getMessage();
                    }

                    foreach ($result as $readrow) {
                        ?>

                        <div class="col">
                            <div class="card h-100 border-success" style="overflow:hidden ;">
                                <div class="gallery">
                                    <a href="gallery_media.php?albumID=<?php echo $readrow['albumID']; ?>"
                                        style="color:black;">
                                        <?php
                                        $albumID = $readrow['albumID'];
                                        $firstImage = getFirstImage($albumID);
                                        ?>
                                        <img src="<?php echo $firstImage; ?>" class="card-img-top img-fixed" alt="..."></a>
                                </div>

                                <div class="card-body border-top border-success">
                                    <div class="row">
                                        <div class="col no-padding">
                                            <h5 class="card-title"><?php echo $readrow['nama_aktiviti']; ?></h5>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer border-success">
                                    <div class="row">
                                        <div class="col no-padding">
                                            <small
                                                class="text-muted"><?php echo date('d-m-Y', strtotime($readrow['tarikh_aktiviti'])); ?></small>
                                        </div>
                                        <div class="col text-center"><a
                                                href="album_crud.php?delete=<?php echo $readrow['albumID']; ?>"
                                                onclick="return confirm('Are you sure you want to delete this album?');"
                                                class="btn btn-outline-danger btn-xs" role="button">Delete</a></div>
                                        <div class="col text-end">
                                            <small class="text-muted"><?php echo $readrow['jenis_aktiviti']; ?></small>
                                            <br>
                                            <small class="text-muted"><?php echo $readrow['kategori']; ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php } ?>

                </div>

                <button id="createAlbum" data-href="create_album.php" class="floating-button" role="button"
                    data-toggle="modal" data-target="#createAlbumModal" data-tippy-content="Create Album">+</button>

                <!-- Modal -->
                <div class="modal fade" id="createAlbumModal" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="createAlbumModal">Create Album</h5>
                                    <button type="button" class="custom-close-button" data-dismiss="modal"
                                        aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body"></div>
                            </div>
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
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>

    <script>
        // Initialize Tippy.js tooltips
        tippy('#createAlbum', {
            placement: 'left'
        });

        $('#createAlbumModal').on('show.bs.modal', function (event) {
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
    </script>

</body>

</html>