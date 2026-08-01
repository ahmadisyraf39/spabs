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


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Gallery</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">

    <link href="../css/main.css" rel="stylesheet">
    <style>
        * {
            transition: all .2s linear;
        }

        .img-fixed {
            width: 100%;
            height: 250px;
            object-fit: cover;
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
        }

        .card-img-top:hover {
            transform: scale(1.1);
        }

        .card,
        .accordion {
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
                <span class="ms-2">Gallery</span>
                <span class="user-role">Parent</span>
            </header>
            <div class="container">
                <?php
                try {
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

                    $stmt = $conn->prepare("SELECT * FROM tbl_spabs_pemain WHERE ibubapaID = :pid");
                    $stmt->bindParam(':pid', $parentID, PDO::PARAM_STR);
                    $stmt->execute();
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($rows)) { ?>
                        <div class="row mb-3 justify-content-center align-items-center">
                            <div class="card mb-3" style="width: 100%; border: 2px solid black; padding: 0;">
                                <div class="row g-0">
                                    <div class="col-md-12 d-flex flex-column justify-content-center align-items-center">
                                        <div class="card-body">
                                            No Registered Player
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <?php
                        exit;
                    }

                    $categories = array_column($rows, 'kategori');

                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }

                $type_filter = isset($_GET['type']) ? $_GET['type'] : 'all';

                try {
                    $query = "SELECT * FROM tbl_spabs_album 
                              INNER JOIN tbl_spabs_aktiviti 
                              ON tbl_spabs_album.aktivitiID = tbl_spabs_aktiviti.aktivitiID";

                    $query_params = [];

                    if ($type_filter !== 'all') {
                        $query .= " WHERE tbl_spabs_aktiviti.jenis_aktiviti = :type";
                        $query_params[':type'] = $type_filter;
                    }

                    if (!empty($categories)) {
                        $placeholders = implode(',', array_map(fn($i) => ":category_$i", array_keys($categories)));
                        $query .= $type_filter === 'all' ? " WHERE" : " AND";
                        $query .= " tbl_spabs_aktiviti.kategori IN ($placeholders)";

                        foreach ($categories as $index => $category) {
                            $query_params[":category_$index"] = $category;
                        }
                    }

                    $query .= " ORDER BY tarikh_aktiviti DESC";
                    $stmt = $conn->prepare($query);

                    foreach ($query_params as $key => $value) {
                        $stmt->bindValue($key, $value, PDO::PARAM_STR);
                    }

                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }
                if (count($result) >= 1) {
                    ?>
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
                        <?php if (!empty($result)) {
                            foreach ($result as $readrow) { ?>
                                <div class="col">
                                    <div class="card h-100 border-success" style="overflow:hidden;">
                                        <div class="gallery">
                                            <a href="gallery_media.php?albumID=<?php echo $readrow['albumID']; ?>"
                                                style="color:black;">
                                                <?php
                                                $albumID = $readrow['albumID'];
                                                $firstImage = getFirstImage($albumID);
                                                ?>
                                                <img src="<?php echo $firstImage; ?>" class="card-img-top img-fixed" alt="...">
                                            </a>
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
                                                <div class="col text-end">
                                                    <small class="text-muted"><?php echo $readrow['jenis_aktiviti']; ?></small><br>
                                                    <small class="text-muted"><?php echo $readrow['kategori']; ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                        } else { ?>
                            <p>No results found for the selected filter.</p>
                        <?php } ?>
                    </div>

                    <?php

                } else {
                    ?>
                    <div class="row mb-3 justify-content-center align-items-center">
                        <div class="card mb-3" style="width: 100%; border: 2px solid black; padding: 0;">
                            <div class="row g-0">
                                <div class="col-md-12 d-flex flex-column justify-content-center align-items-center">
                                    <div class="card-body">
                                        No Album Created
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>

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
                                <div class="modal-body">
                                    <!-- Content loaded via AJAX -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>