<?php
include_once ("../session.php");

if ($role == "Admin") {
    header("location:../admin/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}

if (!isset($_GET['aid'])) {
    header("Location: tournament.php");
    exit();
}

$aid = $_GET['aid'];

$stmt = $conn->prepare("SELECT * FROM tbl_spabs_aktiviti a
                        JOIN tbl_spabs_kejohanan k
                        ON k.kejohananID = a.aktivitiID WHERE a.aktivitiID = :aid");
$stmt->bindParam(':aid', $aid, PDO::PARAM_STR);
$stmt->execute();
$tourney = $stmt->fetch(PDO::FETCH_ASSOC);

$maxPlayer = $tourney['maksimum_pemain'];

try {
    $coachID = $_SESSION['userID'];

    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT * FROM tbl_spabs_jurulatih WHERE jurulatihID = :cid");
    $stmt->bindParam(':cid', $coachID, PDO::PARAM_STR);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $category = $row['kategori'];
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}


try {
    $sql = "
   SELECT * FROM tbl_spabs_pemilihan s
   JOIN tbl_spabs_aktiviti a ON a.aktivitiID = s.aktivitiID
   JOIN tbl_spabs_pemain p ON p.pemainID = s.pemainID
   WHERE s.status_pemilihan = 'Selected'
   AND a.aktivitiID = :aid
";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':aid', $aid, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Selected Player</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">

    <link href="../css/main.css" rel="stylesheet">
    <style>
        .emoji-text {
            display: inline-block;
            margin-right: 5px;
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

        <?php include_once 'sidebar.php' ?>

        <div class="main">

            <header>
                <span class="ms-2">Selected Player - <?php echo $tourney['nama_aktiviti'] ?></span>
                <span class="user-role">Coach</span>
            </header>

            <div class="container p-5">

                <div class="row mb-1">
                    <div class="col-md-5">
                        <b>Player Name:</b>
                    </div>
                    <div class="col-md-1 text-center">
                        <b>Age:</b>
                    </div>
                    <div class="col-md-2 text-center">
                        <b>Progress Rate:</b>
                    </div>
                    <div class="col-md-2 text-center">
                        <b>Attendance Rate:</b>
                    </div>
                    <div class="col-md-2 text-center">
                        <b>Action:</b>
                    </div>
                </div>

                <?php foreach ($result as $readrow) {

                    $percentage = $readrow['kadar_kemajuan'];
                    $classProgress = 'bg-success';

                    if ($percentage <= 25) {
                        $classProgress = 'bg-danger';
                    } elseif ($percentage <= 50) {
                        $classProgress = 'bg-warning';
                    } elseif ($percentage <= 75) {
                        $classProgress = 'bg-primary';
                    } else {
                        $classProgress = 'bg-success';
                    }

                    $percentagee = $readrow['kadar_kehadiran'];
                    $classAttend = 'bg-success';

                    if ($percentagee <= 25) {
                        $classAttend = 'bg-danger';
                    } elseif ($percentagee <= 50) {
                        $classAttend = 'bg-warning';
                    } elseif ($percentagee <= 75) {
                        $classAttend = 'bg-primary';
                    } else {
                        $classAttend = 'bg-success';
                    }

                    ?>
                    <div class="row mb-2">
                        <div class="card m-0">
                            <div class="card-body m-0">
                                <div class="row">
                                    <div class="col-md-5  d-flex flex-column justify-content-center">
                                        <?php echo $readrow['nama_pemain']; ?>
                                    </div>
                                    <div class="col-md-1  d-flex flex-column justify-content-center">
                                        <div class="text-center">
                                            <?php echo $readrow['umur']; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-2  d-flex flex-column justify-content-center px-3">
                                        <div class="progress">
                                            <div class="progress-bar <?php echo $classProgress; ?>" role="progressbar"
                                                style="width: <?php echo $readrow['kadar_kemajuan']; ?>%"
                                                aria-valuenow="<?php echo $readrow['kadar_kemajuan']; ?>" aria-valuemin="0"
                                                aria-valuemax="100">
                                                <?php echo $readrow['kadar_kemajuan']; ?>%
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2  d-flex flex-column justify-content-center px-3">
                                        <div class="progress">
                                            <div class="progress-bar <?php echo $classAttend; ?>" role="progressbar"
                                                style="width: <?php echo $readrow['kadar_kehadiran']; ?>%"
                                                aria-valuenow="<?php echo $readrow['kadar_kehadiran']; ?>" aria-valuemin="0"
                                                aria-valuemax="100">
                                                <?php echo $readrow['kadar_kehadiran']; ?>%
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="d-flex align-items-center justify-content-center ms-4">
                                            <button data-href="player_details.php?pid=<?php echo $readrow['pemainID']; ?>"
                                                class="btn btn-outline-success btn-xs" role="button" data-toggle="modal"
                                                data-target="#playerModal">Details</button>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>


                <!-- <div id="debug-container" class="debug-container"></div> -->

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
    </script>

</body>

</html>