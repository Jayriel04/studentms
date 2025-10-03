<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
?>
<!doctype html>
<html>

<head>
	<title>Student Management System || About Us Page</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all">
	<link href="css/style.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="css/default.css" />
	<link rel="stylesheet" type="text/css" href="css/component.css" />
	<link
		href='//fonts.googleapis.com/css?family=Open+Sans:300,300italic,400italic,400,600,600italic,700,700italic,800,800italic'
		rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css"
		href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
	<script src="js/jquery-1.11.0.min.js"></script>
	<script src="js/bootstrap.js"></script>
	<script type="text/javascript" src="js/move-top.js"></script>
	<script type="text/javascript" src="js/easing.js"></script>
</head>

<body>
	<?php include_once('includes/header.php'); ?>
	<div class="banner banner5">
		<div class="container">
			<h2 class="modern-section-title">About</h2>
		</div>
	</div>
	<div class="modern-section">
		<div class="modern-section-container">
			<div class="modern-card" style="max-width:690px;padding-top:2.1em;padding-bottom:2em;align-items:center;">
				<img src="images/abt.jpg" alt="About Us"
					style="max-width:170px;width:40vw;border-radius:10px;box-shadow:0 1px 12px #2793fd09;margin-bottom:1.5em;">
				<?php
				$sql = "SELECT * from tblpage where PageType='aboutus'";
				$query = $dbh->prepare($sql);
				$query->execute();
				$results = $query->fetchAll(PDO::FETCH_OBJ);
				if ($query->rowCount() > 0) {
					foreach ($results as $row) { ?>
						<div class="modern-section-desc" style="margin-bottom:0;margin-top:0;font-size:1.13em;">
							<?php echo ($row->PageDescription); ?>
						</div>
					<?php }
				} ?>
			</div>
		</div>
	</div>
	<?php include_once('includes/footer.php'); ?>
</body>

</html>
