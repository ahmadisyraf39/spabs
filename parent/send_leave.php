<?php
include_once ("../session.php");
include_once ("leave_crud.php");

$parentID = $_SESSION['userID']; // assuming parentID is stored in session

// Fetch players
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT * FROM tbl_spabs_pemain WHERE ibubapaID = :parentID");
    $stmt->bindParam(':parentID', $parentID, PDO::PARAM_STR);
    $stmt->execute();
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>

    <div class="form-container">
        <form action="leave_crud.php" method="post">
            <div class="row mb-2">
                <div class="col">
                    <div class="form-group">
                        <label for="playerName">Player Name:</label>
                        <select id="playerName" name="playerName" class="form-select" style="width: 100%;" required>
                            <option value="">Select Player</option>
                            <?php foreach ($players as $player): ?>
                                <option value="<?= $player['pemainID']; ?>" data-category="<?= $player['kategori']; ?>">
                                    <?= $player['nama_pemain']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col">
                    <div class="form-group">
                        <label for="activityName">Activity:</label>
                        <select id="activityName" name="activityName" class="form-select" style="width: 100%;" required>
                            <option value="">Select Activity</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="activityDate">Activity Date:</label>
                        <input type="text" id="activityDate" name="activityDate" class="form-control"
                            style="width: 100%;" readonly>
                        </input>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="form-group">
                        <label for="activityTime">Activity Time:</label>
                        <input type="text" id="activityTime" name="activityTyoe" class="form-control"
                            style="width: 100%;" readonly>
                        </i>
                    </div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col">
                    <div class="form-group">
                        <label for="leaveReason">Reason of Absence:</label>
                        <select id="leaveReason" name="leaveReason" class="form-select" style="width: 100%;" required>
                            <option value="">Select Reason</option>
                            <option value="Sickness">Sickness/Injury</option>
                            <option value="School Activity">School Activity</option>
                            <option value="Family Outing">Family Outing</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea name="description" class="form-control" id="description" rows="4" required></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="text-center">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create" class="btn btn-success">Send</button>
                </div>
            </div>

            <input type="hidden" name="ketidakhadiranID" id="ketidakhadiranID"
                value="<?php echo $newLeaveID = incrementLeaveID($latestLeaveID); ?>">
            <input type="hidden" name="kehadiranID" id="kehadiranID"
                value="<?php echo $newAttendanceID = incrementAttendanceID($latestAttendanceID); ?>">

        </form>
    </div>



    <script>
        $(document).ready(function () {
            $('#playerName').change(function () {
                var category = $(this).find(':selected').data('category');
                var playerID = $(this).val(); // Get the playerID from the selected option

                $.ajax({
                    url: 'leave_crud.php',
                    type: 'POST',
                    data: { category: category, playerID: playerID },
                    success: function (data) {
                        $('#activityName').html(data);
                    }
                });
            });

            $('#activityName').change(function () {
                var activityID = $(this).val();
                $.ajax({
                    url: 'leave_crud.php',
                    type: 'POST',
                    data: { activityID: activityID },
                    success: function (response) {
                        var data = JSON.parse(response);
                        if (data.error) {
                            alert(data.error);
                        } else {
                            $('#activityDate').val(data.date);
                            $('#activityTime').val(data.time);
                        }
                    }
                });
            });

        });
    </script>

</body>

</html>