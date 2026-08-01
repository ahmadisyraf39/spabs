<?php
include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}

include_once ("album_crud.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">


    <link href="../css/main.css" rel="stylesheet">

    <style>
        .form-container {
            min-width: 400px;
            /* Increase the maximum width */
            margin: 0 auto;
            /* Center the modal horizontally */
            box-shadow: 0px 0px 0px 0px;
        }
    </style>
</head>

<body>
    <form action="album_crud.php" method="post">
        <div class="row">
            <div class="col-md-12">
                <div class="form-container">
                    <div class="form-group">
                        <label for="AlbumForAlbum">Choose an option:</label>
                        <select id="AlbumForAlbum" name="AlbumForAlbum" class="form-select" style="width: 100%;"
                            required>
                            <option value="">Select Album</option>
                            <?php
                            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                            $stmt = $conn->prepare("SELECT aktiviti.aktivitiID, aktiviti.nama_aktiviti, aktiviti.tarikh_aktiviti
                    FROM tbl_spabs_aktiviti aktiviti
                    WHERE NOT EXISTS (
                        SELECT 1
                        FROM tbl_spabs_album album
                        WHERE aktiviti.aktivitiID = album.aktivitiID
                    ) AND aktiviti.tarikh_aktiviti <= CURDATE();");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                // Generate option tags dynamically with data-aktivitiID attribute
                                echo '<option value="' . htmlspecialchars($row['nama_aktiviti']) . '" data-aktivitiID="' . htmlspecialchars($row['aktivitiID']) . '">' . htmlspecialchars($row['nama_aktiviti']) . ' (' . date('d/m/Y', strtotime($row['tarikh_aktiviti'])) . ')</option>';
                            }
                            ?>
                        </select>
                        <input type="hidden" name="albumID" id="albumID"
                            value="<?php echo $newAlbumID = incrementID($latestAlbumID); ?>">
                        <input type="hidden" id="aktivitiID" name="aktivitiID">
                        <!-- Debugging display -->
                        <!-- <div id="debugOutput"></div> -->

                        <!-- Debugging output -->
                        <!-- <?php
                        echo "Album ID: $newAlbumID<br>"; ?> -->

                        <div class="text-end">
                            <button type="button" class="btn btn-danger mt-2" data-dismiss="modal">Cancel</button>
                            <button type="submit" name="create" class="btn btn-success mt-2">Create</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </form>
</body>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<script>
    $(document).ready(function () {
        // Get the select element
        const AlbumSelect = document.getElementById('AlbumForAlbum');

        // Add an event listener to the select element
        AlbumSelect.addEventListener('change', function () {
            // Get the selected option
            const selectedOption = AlbumSelect.options[AlbumSelect.selectedIndex];

            // Get the data-aktivitiID attribute value
            const aktivitiID = selectedOption.getAttribute('data-aktivitiID');

            // Set the value to the hidden input field
            document.getElementById('aktivitiID').value = aktivitiID;

            // Output debugging information
            const debugOutputDiv = document.getElementById('debugOutput');
            debugOutputDiv.innerHTML = aktivitiID;
        });
    });
</script>

</html>