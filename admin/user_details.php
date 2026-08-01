<?php
include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}

include_once ("player_crud.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Details</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">


</head>

<body>
    <?php

    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['uid'])) {
        $uid = $_GET['uid'];
        try {

            $stmt = $conn->prepare("SELECT * FROM tbl_spabs_akaun 
                                      WHERE userID = :uid;");
            $stmt->bindParam(':uid', $uid, PDO::PARAM_STR);
            $stmt->execute();
            $readrow = $stmt->fetch(PDO::FETCH_ASSOC);

            $userType = $readrow['user_role'];

            if ($userType == 'Parent') {
                $stmt2 = $conn->prepare("SELECT * FROM tbl_spabs_akaun AS sa
                JOIN tbl_spabs_ibubapa AS sib ON sa.userID = sib.ibubapaID
                WHERE sa.userID = :uid;");
                $stmt2->bindParam(':uid', $uid, PDO::PARAM_STR);
                $stmt2->execute();
                $readrow2 = $stmt2->fetch(PDO::FETCH_ASSOC);

                $stmt3 = $conn->prepare("SELECT * FROM tbl_spabs_pemain WHERE ibubapaID = :uid");
                $stmt3->bindParam(':uid', $uid, PDO::PARAM_STR);
                $stmt3->execute();
                $players = $stmt3->fetchAll(PDO::FETCH_ASSOC);
            } elseif ($userType == 'Coach') {
                $stmt2 = $conn->prepare("SELECT * FROM tbl_spabs_akaun AS sa
                JOIN tbl_spabs_jurulatih AS sj ON sa.userID = sj.jurulatihID
                WHERE sa.userID = :uid;");
                $stmt2->bindParam(':uid', $uid, PDO::PARAM_STR);
                $stmt2->execute();
                $readrow2 = $stmt2->fetch(PDO::FETCH_ASSOC);
            }

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        ?>

        <?php if ($userType == 'Parent') { ?>


            <table class="table table-bordered">
                <tr>
                    <td><strong>User Name</strong></td>
                    <td><?php echo $readrow2['nama_ibubapa']; ?></td>
                </tr>
                <tr>
                    <td><strong>User Role</strong></td>
                    <td><?php echo $readrow2['user_role']; ?></td>
                </tr>
                <tr>
                    <td><strong>Email</strong></td>
                    <td><?php echo $readrow2['email']; ?></td>
                </tr>
                <tr>
                    <td><strong>Phone Number</strong></td>
                    <td><?php echo $readrow2['tel_ibubapa']; ?></td>
                </tr>
                <tr>
                    <td><strong>2 <sup>nd</sup> Phone Number</strong></td>
                    <td><?php echo $readrow2['tel_ibubapa2']; ?></td>
                </tr>
                <tr>
                    <td><strong>Address</strong></td>
                    <td><?php echo $readrow2['alamat']; ?></td>
                </tr>
            </table>

            <table class="table table-bordered">


                <?php if (count($players) >= 1) { ?>

                    <tr>
                        <th><strong>Player Name</strong></th>
                        <th><strong>Age</strong></th>
                        <th><strong>Category</strong></th>
                    </tr>

                    <?php
                    foreach ($players as $player): ?>
                        <tr>
                            <td><?php echo $player['nama_pemain']; ?></td>
                            <td><?php echo $player['umur']; ?></td>
                            <td><?php echo $player['kategori']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php } else { ?>
                    <tr>
                        <td>No Registered Player</td>
                    </tr>
                <?php } ?>
            </table>
        <?php } elseif ($userType == 'Coach') {
            ?>
            <table class="table table-bordered">
                <tr>
                    <td><strong>User Name</strong></td>
                    <td><?php echo $readrow2['nama_jurulatih']; ?></td>
                </tr>
                <tr>
                    <td><strong>User Role</strong></td>
                    <td><?php echo $readrow2['user_role']; ?></td>
                </tr>
                <tr>
                    <td><strong>Email</strong></td>
                    <td><?php echo $readrow2['email']; ?></td>
                </tr>
                <tr>
                    <td><strong>Phone Number</strong></td>
                    <td><?php echo $readrow2['tel_jurulatih']; ?></td>
                </tr>
                <tr>
                    <td><strong>Category</strong></td>
                    <td><?php echo $readrow2['kategori']; ?></td>
                </tr>
            </table>


            <?php
        }
        ?>

        <?php
    }
    ?>
</body>

</html>