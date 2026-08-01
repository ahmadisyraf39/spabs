<?php
include_once ("../session.php");

if ($role == "Admin") {
    header("location:../admin/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_GET['aid'])) {
    $aid = $_GET['aid'];

    $stmt = $conn->prepare("SELECT * FROM tbl_spabs_aktiviti WHERE aktivitiID = :aid");

    $stmt->bindParam(':aid', $aid, PDO::PARAM_STR);

    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $category = $row['kategori'];
    $nama_aktiviti = $row['nama_aktiviti'];
    $tarikh_aktiviti = $row['tarikh_aktiviti'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Activity Attendance</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">


    <!-- <link href="css/bootstrap.css" rel="stylesheet"> -->
    <link href="../css/main.css" rel="stylesheet">

    <style>
        /* Default style for select box */
        .form-select {
            transition: background-color 0.3s !important;
        }

        /* Background color for "Hadir" */
        .hadir-bg {
            background-color: #ccffcc !important;
        }

        /* Background color for "Tidak Hadir" */
        .tidak-hadir-bg {
            background-color: #ffcccb !important;
        }

        .card,
        .accordion,
        .form-container {
            box-shadow: 0px 0px 0px 0px;
            /* horizontal offset, vertical offset, blur radius, shadow color */
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include_once 'sidebar.php' ?>

        <div class="main">
            <header>
                <span class="ms-2">Take Attendance - <?php echo $nama_aktiviti ?>
                    <?php echo date('d/m/Y', strtotime($tarikh_aktiviti)); ?> </span>
                <span class="user-role">Coach</span>
            </header>

            <div class="container p-5">

                <div class="row mb-3 justify-content-center align-items-center">
                    <div class="row g-0 p-3 pb-0" style=width:80%;>
                        <div class="col-md-4 d-flex flex-column justify-content-center align-items-center">
                            Name
                        </div>
                        <div class="col-md-3 d-flex flex-column justify-content-center align-items-center">
                            Attendance
                        </div>
                        <div class="col-md-3 d-flex flex-column justify-content-center align-items-center">
                            Reason for Absence
                        </div>
                        <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                            Absence Details
                        </div>

                    </div>
                </div>

                <?php
                try {
                    $stmt = $conn->prepare("SELECT 
                                                p.pemainID,
                                                p.nama_pemain,
                                                a.aktivitiID,
                                                k.kehadiranID,
                                                k.status_kehadiran,
                                                th.ketidakhadiranID,
                                                COALESCE(th.jenis_sebab, '-') AS jenis_sebab
                                            FROM 
                                                tbl_spabs_pemain p
                                            LEFT JOIN 
                                                tbl_spabs_aktiviti a ON (p.kategori = a.kategori OR a.kategori = 'ALL')
                                            LEFT JOIN 
                                                tbl_spabs_kehadiran k ON a.aktivitiID = k.aktivitiID
                                                AND p.pemainID = k.pemainID
                                            LEFT JOIN 
                                                tbl_spabs_ketidakhadiran th ON th.kehadiranID = k.kehadiranID
                                            WHERE 
                                                p.kategori = :kategori
                                                AND 
                                                a.aktivitiID = :aid
                                                AND 
                                                p.tarikh_daftar <= a.tarikh_aktiviti
                                            GROUP BY 
                                                p.pemainID, 
                                                p.nama_pemain, 
                                                a.aktivitiID, 
                                                k.kehadiranID, 
                                                k.status_kehadiran,
                                                th.ketidakhadiranID, 
                                                th.jenis_sebab");
                    $stmt->bindParam(':kategori', $category, PDO::PARAM_STR);
                    $stmt->bindParam(':aid', $aid, PDO::PARAM_STR);
                    $stmt->execute();
                    $result = $stmt->fetchAll();
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                } ?>

                <form method="post" action="attendance_crud.php">
                    <?php
                    foreach ($result as $readrow) {
                        ?>
                        <div class="row mb-1 justify-content-center align-items-center">
                            <div class="card mb-3 p-3" style="width: 80%; border: 2px solid black; ">
                                <div class="row g-0">
                                    <div class="col-md-4 d-flex flex-column justify-content-center align-items-center">
                                        <div><?php echo $readrow['nama_pemain']; ?></div>
                                    </div>
                                    <div class="col-md-3 d-flex flex-column justify-content-center align-items-center">
                                        <div class="form-group">
                                            <select class="form-select attendance-select " style="width: 100%;"
                                                name="status[<?php echo $readrow['pemainID']; ?>]" required>
                                                <option value="Attend" <?php if ($readrow['status_kehadiran'] == 'Attend')
                                                    echo 'selected'; ?>     <?php if ($readrow['ketidakhadiranID'])
                                                               echo 'disabled'; ?>>Attend</option>
                                                <option value="Absent" <?php if ($readrow['status_kehadiran'] == 'Absent')
                                                    echo 'selected'; ?>>Absent</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3 d-flex flex-column justify-content-center align-items-center">
                                        <?php echo $readrow['jenis_sebab']; ?>
                                    </div>
                                    <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                        <div class="text-center">
                                            <?php
                                            if (isset($readrow['jenis_sebab']) && $readrow['jenis_sebab'] !== "-") {
                                                // Details Button
                                                ?>
                                                <button
                                                    data-href="absence_details.php?thid=<?php echo $readrow['ketidakhadiranID']; ?>"
                                                    class="btn btn-outline-success btn-xs" role="button" data-toggle="modal"
                                                    data-target="#absenceModal" type="button">Details</button>
                                                <?php
                                            } else {
                                                echo "-";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <input type="hidden" name="pid[<?php echo $readrow['pemainID']; ?>]"
                                value="<?php echo $readrow['pemainID']; ?>">
                            <input type="hidden" name="kehid[<?php echo $readrow['pemainID']; ?>]"
                                value="<?php echo $readrow['kehadiranID']; ?>">
                        </div>
                    <?php } ?>

                    <div class="row mb-3 justify-content-center align-items-center">
                        <div class="row g-0" style="width:80%;">
                            <div
                                class="col-md-2 d-flex flex-column justify-content-center align-items-center order-last">
                                <button type="submit" name="update"
                                    class="btn btn-secondary btn-xs mb-2">Update</button>


                            </div>
                            <!-- Other columns here -->
                            <div class="col-md d-flex flex-column justify-content-center align-items-center">
                                <!-- Content for other columns -->
                            </div>
                        </div>
                    </div>


                    <input type="hidden" name="aid" id="aid" value="<?php echo $aid ?>">

                </form>

                <!-- Modal -->
                <div class="modal fade" id="absenceModal" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="absenceModal">Absence Details</h5>
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

    <script>
        $('#absenceModal').on('show.bs.modal', function (event) {
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

    <script>
        // Define a function to handle class changes
        function handleClassChange(selectBox) {
            var selectedOption = selectBox.value;
            if (selectedOption === 'Attend') {
                selectBox.classList.remove('tidak-hadir-bg');
                selectBox.classList.add('hadir-bg');
            } else if (selectedOption === 'Absent') {
                selectBox.classList.remove('hadir-bg');
                selectBox.classList.add('tidak-hadir-bg');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            var selectBoxes = document.querySelectorAll('.attendance-select');

            // Loop through select boxes on page load
            selectBoxes.forEach(function (selectBox) {
                handleClassChange(selectBox);
            });

            // Add event listeners for select boxes
            selectBoxes.forEach(function (selectBox) {
                selectBox.addEventListener('change', function () {
                    handleClassChange(selectBox);
                });
            });
        });
    </script>
</body>

</html>