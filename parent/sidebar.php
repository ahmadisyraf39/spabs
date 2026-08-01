<?php
$currentPage = basename($_SERVER['PHP_SELF']);

$showBadge = false;

// Check if the user is selected for a tournament
// include_once ("../session.php");

$parentID = $_SESSION['userID'];
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tbl_spabs_aktiviti a
        JOIN tbl_spabs_pemilihan p ON a.aktivitiID = p.aktivitiID
        JOIN tbl_spabs_pemain m ON p.pemainID = m.pemainID
        WHERE m.ibubapaID = :ibubapaID
        AND p.status_pemilihan = 'Selected'
        AND p.status_notifikasi = 'Unnotified'
        AND a.tarikh_aktiviti > CURRENT_DATE");
    $stmt->bindParam(':ibubapaID', $parentID, PDO::PARAM_STR);
    $stmt->execute();
    $unnotifiedCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .sidebar-link {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sidebar-link .badge {
            margin-left: auto;
        }
    </style>
</head>

<body>

    <aside id="sidebar" class="expand">
        <div class="d-flex">
            <button class="toggle-btn " type="button">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="sidebar-logo">
                <a href="index.php">SPABS</a>
            </div>
        </div>
        <ul class="sidebar-nav">
            <li class="sidebar-item">
                <a href="index.php" class="sidebar-link <?php if ($currentPage == 'index.php')
                    echo 'active'; ?>">
                    <i class="fa-solid fa-house"></i>
                    <span>Home</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="activity.php" class="sidebar-link <?php if ($currentPage == 'activity.php')
                    echo 'active'; ?>">
                    <i class="fa-solid fa-list"></i>
                    <span>Activity</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="profile.php" class="sidebar-link <?php if ($currentPage == 'profile.php')
                    echo 'active'; ?>">
                    <i class="fa-solid fa-user"></i>
                    <span>Profiles</span>
                </a>

            </li>


            <li class="sidebar-item">
                <a href="fee_list.php" class="sidebar-link <?php if ($currentPage == 'fee_list.php')
                    echo 'active'; ?>"><i class=" fa-solid fa-money-bill"></i>
                    <span>Fee</span></a>
            </li>

            <li class="sidebar-item">
                <a href="player_attendance.php" class="sidebar-link <?php if ($currentPage == 'player_attendance.php' or $currentPage == 'attendance_record')
                    echo 'active'; ?>">
                    <i class="fa-solid fa-user-check"></i>
                    <span>Attendance</span>
                </a>
            </li>

            <li class="sidebar-item">
                <a href="player_progress.php" class="sidebar-link <?php if ($currentPage == 'player_progress.php')
                    echo 'active'; ?>">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Progress</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="player_tournament.php" class="sidebar-link  <?php if ($currentPage == 'player_tournament.php')
                    echo 'active'; ?>">
                    <i class="fa-solid fa-trophy"></i>
                    <span>Tournament</span>
                    <?php if ($unnotifiedCount > 0) { ?>
                        <span class="badge badge-warning bg-danger ml-auto"><?php echo $unnotifiedCount; ?></span>
                    <?php } ?>
                </a>
            </li>

            <li class="sidebar-item">
                <a href="gallery.php" class="sidebar-link <?php if ($currentPage == 'gallery.php')
                    echo 'active'; ?>">
                    <i class="fa-solid fa-photo-film"></i>
                    <span>Gallery</span>
                </a>
            </li>

            <li class="sidebar-item">
                <a href="#" onclick="confirmLogout()" class="sidebar-link">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
        <!-- <div class="sidebar-footer">
            <a href="#" onclick="confirmLogout()" class="sidebar-link">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div> -->
    </aside>




    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="../js/script.js"></script>
    <script src="../js/jquery.js"></script>
    <script src="https://kit.fontawesome.com/9561e45a86.js" crossorigin="anonymous"></script>

    <script>

        function confirmLogout() {
            var confirmLogout = confirm("Are you sure you want to log out?");
            if (confirmLogout) {
                window.location.href = "../logout.php"; // Redirect to the logout page
            } else {
                // Do nothing or add additional logic as needed
            }
        }

    </script>

</body>

</html>