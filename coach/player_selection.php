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
   SELECT 
    ps.pemainID,
    ps.nama_pemain,
    ps.umur,
    ps.avg_of_avg_progress_status,
    ats.avg_attend_percentage,
    COALESCE(pem.status_pemilihan, 'Not Selected') AS status_pemilihan
FROM 
    (
        SELECT 
            sub.pemainID,
            sub.nama_pemain,
            sub.umur,
            COALESCE(CEIL(AVG(sub.avg_progress_status)), 0) AS avg_of_avg_progress_status
        FROM 
            (
                SELECT 
                    p.pemainID,
                    p.nama_pemain,
                    p.umur,
                    k.kemahiranID,
                    COALESCE(
                        CEIL(SUM(COALESCE(pr.status_capai, 0)) / (
                            SELECT COUNT(DISTINCT m.modulID)
                            FROM tbl_spabs_modul m
                            WHERE m.kemahiranID = k.kemahiranID
                        )), 
                        0
                    ) AS avg_progress_status
                FROM 
                    tbl_spabs_pemain p
                    LEFT JOIN tbl_spabs_kemahiran k ON p.kategori = k.kategori
                    LEFT JOIN tbl_spabs_penilaian pr ON p.pemainID = pr.pemainID AND k.kemahiranID = pr.kemahiranID
                WHERE 
                    p.kategori = :kategori
                GROUP BY 
                    p.pemainID, p.nama_pemain, k.kemahiranID
            ) AS sub
        GROUP BY 
            sub.pemainID, sub.nama_pemain
    ) AS ps
JOIN 
    (
        SELECT 
            p.pemainID,
            p.nama_pemain,
            p.umur,
            CASE 
                WHEN COUNT(a.aktivitiID) > 0 THEN 
                    CAST((SUM(CASE WHEN k.status_kehadiran = 'attend' THEN 1 ELSE 0 END) * 100.0) / 
                    COUNT(a.aktivitiID) AS UNSIGNED)
                ELSE 0
            END AS avg_attend_percentage
        FROM 
            tbl_spabs_pemain p
        JOIN 
            tbl_spabs_kehadiran k ON p.pemainID = k.pemainID
        JOIN 
            tbl_spabs_aktiviti a ON k.aktivitiID = a.aktivitiID
        WHERE 
            (a.kategori = :kategori OR a.kategori = 'ALL')
        GROUP BY 
            p.pemainID, p.nama_pemain
    ) AS ats
ON ps.pemainID = ats.pemainID
LEFT JOIN 
    tbl_spabs_pemilihan pem ON ps.pemainID = pem.pemainID AND pem.aktivitiID = :aid
    ORDER BY ps.avg_of_avg_progress_status DESC, ats.avg_attend_percentage DESC
";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':kategori', $category, PDO::PARAM_STR);
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
    <title>SPABS: Player Selection</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">

    <link href="../css/main.css" rel="stylesheet">
    <style>
        .emoji-text {
            display: inline-block;
            margin-right: 5px;
        }

        .debug-container {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }

        .custom-checkbox-red {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 80%;
            background-color: red;
            border-radius: 0.25rem;
        }

        .custom-checkbox-red::after {
            content: 'X';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
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

        <?php include_once 'sidebar.php' ?>

        <div class="main">

            <header>
                <span class="ms-2">Player Selection - <?php echo $tourney['nama_aktiviti'] ?></span>
                <span class="user-role">Coach</span>
            </header>

            <div class="container p-5">
                <div class="row mb-5 justify-content-center"> <!-- Added justify-content-center -->
                    <div class="card d-flex align-items-center justify-content-center text-center" style="width: 50%;">
                        <div class="card-body">Please select <?php echo $maxPlayer ?> players</div>
                    </div>
                </div>


                <form method="POST" action="selection_crud.php" id="player-selection-form">

                    <div class="row mb-1">
                        <div class="col-md-4">
                            <b>Player Name:</b>
                        </div>
                        <div class="col-md-1 text-center">
                            <b>Age:</b>
                        </div>
                        <div class="col-md-3 text-center">
                            <b>Progress Rate:</b>
                        </div>
                        <div class="col-md-3 text-center">
                            <b>Attendance Rate:</b>
                        </div>
                        <div class="col-md-1 text-end">
                            <b>Select:</b>
                        </div>
                    </div>

                    <?php foreach ($result as $readrow) {

                        $percentage = $readrow['avg_of_avg_progress_status'];
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

                        $percentagee = $readrow['avg_attend_percentage'];
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
                        <div class="row mb-3">
                            <div class="card m-0">
                                <div class="card-body m-0">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <?php echo htmlspecialchars($readrow['nama_pemain']); ?>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="text-center">
                                                <?php echo htmlspecialchars($readrow['umur']); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="progress">
                                                <div class="progress-bar <?php echo $classProgress; ?>" role="progressbar"
                                                    style="width: <?php echo htmlspecialchars($readrow['avg_of_avg_progress_status']); ?>%"
                                                    aria-valuenow="<?php echo htmlspecialchars($readrow['avg_of_avg_progress_status']); ?>"
                                                    aria-valuemin="0" aria-valuemax="100">
                                                    <?php echo htmlspecialchars($readrow['avg_of_avg_progress_status']); ?>%
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="progress">
                                                <div class="progress-bar <?php echo $classAttend; ?>" role="progressbar"
                                                    style="width: <?php echo htmlspecialchars($readrow['avg_attend_percentage']); ?>%"
                                                    aria-valuenow="<?php echo htmlspecialchars($readrow['avg_attend_percentage']); ?>"
                                                    aria-valuemin="0" aria-valuemax="100">
                                                    <?php echo htmlspecialchars($readrow['avg_attend_percentage']); ?>%
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="d-flex align-items-center justify-content-end">
                                                <?php if ($readrow['status_pemilihan'] == 'Unavailable') { ?>
                                                    <div class="position-relative">
                                                        <input type="checkbox" class="form-check-input border-black"
                                                            name="select_player[]"
                                                            value="<?php echo htmlspecialchars($readrow['pemainID']); ?>"
                                                            disabled>
                                                        <span class="custom-checkbox-red"></span>
                                                    </div>
                                                <?php } else { ?>
                                                    <input type="checkbox" class="form-check-input border-black"
                                                        name="select_player[]"
                                                        value="<?php echo htmlspecialchars($readrow['pemainID']); ?>" <?php if ($readrow['status_pemilihan'] == 'Selected')
                                                               echo 'checked disabled'; ?>
                                                        onchange="updateHiddenFields(this, '<?php echo htmlspecialchars($readrow['pemainID']); ?>', '<?php echo htmlspecialchars($readrow['avg_of_avg_progress_status']); ?>', '<?php echo htmlspecialchars($readrow['avg_attend_percentage']); ?>')">
                                                <?php } ?>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="row mt-3 text-end">
                        <div class="col-md-12">
                            <input type="hidden" name="tournament" value="<?php echo $aid ?>">
                            <button type="submit" name="create" class="btn btn-success"
                                onclick="return checkMaxPlayers()">Confirm</button>
                        </div>
                    </div>
                </form>

                <!-- <div id="debug-container" class="debug-container"></div> -->
            </div>
        </div>
    </div>

    <script>
        const maxPlayer = <?php echo $maxPlayer; ?>;

        function updateHiddenFields(checkbox, playerID, progress, attendance) {
            let debugContainer = document.getElementById('debug-container');
            if (checkbox.checked) {
                // Create hidden input fields for progress rate and attendance rate
                let progressInput = document.createElement('input');
                progressInput.type = 'hidden';
                progressInput.name = 'avg_of_avg_progress_status[' + playerID + ']';
                progressInput.value = progress;

                let attendanceInput = document.createElement('input');
                attendanceInput.type = 'hidden';
                attendanceInput.name = 'avg_attend_percentage[' + playerID + ']';
                attendanceInput.value = attendance;

                // Append hidden input fields to the form
                checkbox.form.appendChild(progressInput);
                checkbox.form.appendChild(attendanceInput);

                // Debug: Show the hidden inputs
                let debugProgress = document.createElement('div');
                debugProgress.textContent = `Hidden Progress Input: ${progressInput.outerHTML}`;
                let debugAttendance = document.createElement('div');
                debugAttendance.textContent = `Hidden Attendance Input: ${attendanceInput.outerHTML}`;
                debugContainer.appendChild(debugProgress);
                debugContainer.appendChild(debugAttendance);

                console.log(`Added hidden fields for Player ID: ${playerID}, Progress: ${progress}, Attendance: ${attendance}`);
            } else {
                // Remove hidden input fields when checkbox is unchecked
                let progressInput = document.querySelector('input[name="avg_of_avg_progress_status[' + playerID + ']"]');
                let attendanceInput = document.querySelector('input[name="avg_attend_percentage[' + playerID + ']"]');
                if (progressInput) progressInput.remove();
                if (attendanceInput) attendanceInput.remove();

                // Debug: Remove the debug info
                let debugElements = debugContainer.querySelectorAll('div');
                debugElements.forEach(element => {
                    if (element.textContent.includes(`Progress Input: name="avg_of_avg_progress_status[${playerID}]`)) {
                        element.remove();
                    }
                    if (element.textContent.includes(`Attendance Input: name="avg_attend_percentage[${playerID}]`)) {
                        element.remove();
                    }
                });

                console.log(`Removed hidden fields for Player ID: ${playerID}`);
            }
        }

        function checkMaxPlayers() {
            const checkboxes = document.querySelectorAll('input[name="select_player[]"]:checked');
            if (checkboxes.length > maxPlayer) {
                alert(`You can only select a maximum of ${maxPlayer} players.`);
                return false; // Prevent form submission
            }
            return true; // Allow form submission
        }
    </script>
</body>

</html>