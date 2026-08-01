<?php
include_once ("../session.php");

if ($role == "Admin") {
	header("location:../admin/index.php");
	exit;
} elseif ($role == "Parent") {
	header("location:../parent/index.php");
	exit;
}

$coachID = $_SESSION['userID'];

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $conn->prepare("SELECT * FROM tbl_spabs_jurulatih WHERE jurulatihID = :cid");

$stmt->bindParam(':cid', $coachID, PDO::PARAM_STR);

$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$category = $row['kategori'];

$kemahiranID = $_GET['kid'];
$pemainID = $_GET['pid'];

$stmt2 = $conn->prepare("SELECT * FROM tbl_spabs_kemahiran WHERE kemahiranID = :kid");

$stmt2->bindParam(':kid', $kemahiranID, PDO::PARAM_STR);

$stmt2->execute();

$row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>SPABS: Evaluate Progress</title>
	<link rel="icon" href="../pictures/icons/logo.png" type="image/png">

	<!-- <link href="css/bootstrap.css" rel="stylesheet"> -->
	<link href="../css/main.css" rel="stylesheet">

	<style>
		.circle {
			display: inline-block;
			width: 10px;
			/* Adjust the size of the circle as needed */
			height: 10px;
			/* Adjust the size of the circle as needed */
			border-radius: 50%;
			/* Make the element a circle */
			/* Set the background color of the circle */
			margin-right: 5px;
			/* Adjust spacing between the circle and text */
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
		<?php include_once 'sidebar.php'; ?>
		<div class="main">
			<header>
				<span class="ms-2">Evaluate Progress - <?php echo $row2['jenis_kemahiran']; ?></span>
				<span class="user-role">Coach</span>
			</header>
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb ms-5 mt-2">
					<li class="breadcrumb-item"><a href="player_progress.php">Player Progress</a></li>
					<li class="breadcrumb-item"><a href="skill_progress.php?pid=<?php echo $pemainID ?>">Skill
							Progress</a></li>
					<li class="breadcrumb-item active" aria-current="page">Evaluate Progress</li>
				</ol>
			</nav>
			<div class="container p-5">

				<?php
				try {
					$pid = $_GET['pid'];
					$kemahiranID = $_GET['kid'];

					// Check if any modules exist for the given kemahiranID
					$stmt_check = $conn->prepare("SELECT COUNT(*) FROM tbl_spabs_modul WHERE kemahiranID = :kemahiranID");
					$stmt_check->bindParam(':kemahiranID', $kemahiranID, PDO::PARAM_STR);
					$stmt_check->execute();
					$module_count = $stmt_check->fetchColumn();


					$stmt = $conn->prepare("SELECT 
                            p.pemainID,
                            k.kemahiranID, 
                            k.jenis_kemahiran, 
                            pr.penilaianID,
                            m.modulID,
                            m.nama_modul,
                            pr.status_capai
                        FROM 
                            tbl_spabs_pemain p
                            LEFT JOIN tbl_spabs_kemahiran k ON p.kategori = k.kategori
                            LEFT JOIN tbl_spabs_modul m ON k.kemahiranID = m.kemahiranID
                            LEFT JOIN tbl_spabs_penilaian pr ON p.pemainID = pr.pemainID AND k.kemahiranID = pr.kemahiranID AND m.modulID = pr.modulID
                        WHERE 
                            p.pemainID = :pemainID
                            AND k.kemahiranID = :kemahiranID
                        GROUP BY 
                            p.pemainID, k.kemahiranID, k.jenis_kemahiran, pr.penilaianID, m.modulID");
					$stmt->bindParam(':kemahiranID', $kemahiranID, PDO::PARAM_STR);
					$stmt->bindParam(':pemainID', $pid, PDO::PARAM_STR);
					$stmt->execute();
					$result = $stmt->fetchAll();

				} catch (PDOException $e) {
					echo "Error: " . $e->getMessage();
				}
				?>
				<?php if ($module_count > 0) { ?>
					<div class="form-container p-5 mb-3">

						<h3 class="mb-5 text-center">Skill Development - <?php echo $row2['jenis_kemahiran']; ?></h3>
						<div class="row dflex justify-content-center">
							<?php foreach ($result as $readrow2) { ?>
								<div class="col-md-3 mb-5 ">

									<?php if ($readrow2['status_capai'] == '25') { ?>
										<div class="progress">
											<div class="progress-bar bg-danger" role="progressbar" style="width: 25%"
												aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
										</div>
										<div><span class="circle bg-danger"> </span><?php echo $readrow2['nama_modul'] ?></div>
									<?php } elseif ($readrow2['status_capai'] == '50') { ?>
										<div class="progress">
											<div class="progress-bar bg-warning" role="progressbar" style="width: 50%"
												aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
										</div>
										<div><span class="circle bg-warning"> </span><?php echo $readrow2['nama_modul'] ?></div>
									<?php } elseif ($readrow2['status_capai'] == '75') { ?>
										<div class="progress">
											<div class="progress-bar bg-primary" role="progressbar" style="width: 75%"
												aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
										</div>
										<div>
											<span class="circle bg-primary"> </span>
											<?php echo $readrow2['nama_modul'] ?>
										</div>

									<?php } elseif ($readrow2['status_capai'] == '100') { ?>
										<div class="progress">
											<div class="progress-bar bg-success" role="progressbar" style="width: 100%"
												aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
										</div>
										<div>
											<span class="circle bg-success"> </span>
											<?php echo $readrow2['nama_modul'] ?>
										</div>
									<?php } else { ?>
										<div class="progress">
											<div class="progress-bar bg-secondary" role="progressbar" style="width: 0%"
												aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
										</div>
										<div><span class="circle bg-secondary"> </span><?php echo $readrow2['nama_modul'] ?></div>
									<?php } ?>


								</div>
							<?php } ?>

						</div>

						<div class="text-center">
							<span class="circle bg-secondary"></span><span class="me-3">Not Started</span>
							<span class="circle bg-danger"></span><span class="me-3">Just Start</span>
							<span class="circle bg-warning"></span><span class="me-3">In Progress</span>
							<span class="circle bg-primary"></span><span class="me-3">Almost Complete</span>
							<span class="circle bg-success"></span><span class="me-3">Completed</span>
						</div>


					</div>

					<div class="form-container p-5 mb-5">
						<!-- <h3 class="mb-2">Skill Development</h3> -->

						<form method="POST" action="progress_crud.php">
							<div class="row mb-2">
								<div class="col-md-6">
									Module:
								</div>
								<div class="col-md-3 text-center">
									Action:
								</div>
								<div class="col-md-3 text-center">
									Progress:
								</div>
							</div>
							<?php foreach ($result as $readrow2) { ?>
								<div class="row mb-3">
									<div class="col-md-6">
										<div class="form-group">
											<input type="text" class="form-control" disabled
												value="<?php echo $readrow2['nama_modul']; ?>">
										</div>
									</div>

									<div class="col-md-3 d-flex align-items-center justify-content-center">
										<div class="form-group">
											<button type="button"
												data-href="module_details.php?mid=<?php echo $readrow2['modulID']; ?>"
												class="btn btn-outline-success btn-xs mb-2" role="button" data-toggle="modal"
												data-target="#moduleModal">Details</button>
										</div>
									</div>


									<div class="col-md-3">
										<div class="form-group">
											<select class="form-select" style="width: 100%;"
												name="target[<?php echo $readrow2['modulID']; ?>]">
												<option value="">0%</option>
												<option value="25" <?php if ($readrow2['status_capai'] == '25')
													echo 'selected'; ?>>25%</option>
												<option value="50" <?php if ($readrow2['status_capai'] == '50')
													echo 'selected'; ?>>50%</option>
												<option value="75" <?php if ($readrow2['status_capai'] == '75')
													echo 'selected'; ?>>75%</option>
												<option value="100" <?php if ($readrow2['status_capai'] == '100')
													echo 'selected'; ?>>100%</option>
											</select>
										</div>
									</div>

									<div>
										<input type="hidden" name="mid[<?php echo $readrow2['modulID']; ?>]"
											value="<?php echo $readrow2['modulID']; ?>">
										<input type="hidden" name="penid[<?php echo $readrow2['modulID']; ?>]"
											value="<?php echo $readrow2['penilaianID']; ?>">
									</div>


								</div>
							<?php } ?>

							<div class="row mt-2 text-end">
								<div class="col-md-12">
									<input type="hidden" name="kid" id="kid" value="<?php echo $kemahiranID ?>">
									<input type="hidden" name="pid" id="pid" value="<?php echo $pid ?>">
									<button type="submit" name="update" class="btn btn-primary">Update Progress</button>
								</div>
							</div>
						</form>
					</div>
				</div>

			<?php } else { ?>

				<div class="form-container p-4 d-flex justify-content-center align-items-center">No Module Available</div>

			<?php } ?>

			<!-- Modal -->
			<div class="modal fade" id="moduleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
				aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="moduleModal">Module Details</h5>
							<button type="button" class="custom-close-button" data-dismiss="modal" aria-label="Close">
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
	<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/lightgallery.min.js"></script>

	<script src="https://unpkg.com/@popperjs/core@2"></script>
	<script src="https://unpkg.com/tippy.js@6"></script>

	<script>
		$('#moduleModal').on('show.bs.modal', function (event) {
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