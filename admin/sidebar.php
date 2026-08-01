<?php
$currentPage = basename($_SERVER['PHP_SELF']);
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
                <a href="user_list.php" class="sidebar-link <?php if ($currentPage == 'user_list.php')
                    echo 'active'; ?>">
                    <i class="fa-solid fa-user"></i>
                    <span>User</span>
                </a>
            </li>

            <li class="sidebar-item">
                <a href="player_list.php" class="sidebar-link <?php if ($currentPage == 'player_list.php')
                    echo 'active'; ?>">
                    <i class="fa-solid fa-users"></i>
                    <span>Player</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="activity_list.php" class="sidebar-link <?php if ($currentPage == 'activity_list.php')
                    echo 'active'; ?>">
                    <i class="fa-solid fa-list"></i>
                    <span>Activity</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="fee_list.php" class="sidebar-link <?php if ($currentPage == 'fee_list.php')
                    echo 'active'; ?>">
                    <i class="fa-solid fa-money-bill"></i>
                    <span>Fee</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="module.php" class="sidebar-link <?php if ($currentPage == 'module.php')
                    echo 'active'; ?>">
                    <i class="fa-solid fa-book"></i>
                    <span>Module</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="tournament.php" class="sidebar-link  <?php if ($currentPage == 'tournament.php')
                    echo 'active'; ?>">
                    <i class="fa-solid fa-trophy"></i>
                    <span>Tournament</span>
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
                <div class="sidebar-footer">
                    <a href="#" onclick="confirmLogout()" class="sidebar-link">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Logout</span>
                    </a>
                </div>
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