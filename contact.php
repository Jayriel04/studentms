<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
?>
<!doctype html>
<html>

<head>
	<title>Student Management System || Contact Us Page</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all">
	<link href="css/style.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="css/default.css" />
	<link rel="stylesheet" type="text/css" href="css/component.css" />
	<link rel="stylesheet" type="text/css"
		href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
	<link
		href='//fonts.googleapis.com/css?family=Open+Sans:300,300italic,400italic,400,600,600italic,700,700italic,800,800italic'
		rel='stylesheet' type='text/css'>
	<script src="js/jquery-1.11.0.min.js"></script>
	<script src="js/bootstrap.js"></script>
	<script type="text/javascript" src="js/move-top.js"></script>
	<script type="text/javascript" src="js/easing.js"></script>
</head>

<body>
	<?php include_once('includes/header.php'); ?>
	<div class="banner banner5">
		<div class="container">
			<h2 class="modern-section-title">Contact</h2>
		</div>
	</div>
	<div class="modern-section">
		<div class="modern-section-container">
			<div class="modern-card"
				style="max-width:480px;padding-top:2.3em;padding-bottom:2em;align-items: flex-start;">
				<h3 class="modern-section-title" style="font-size:1.45em;margin-bottom:1em;">Contact Information</h3>
				<?php
				$sql = "SELECT * from tblpage where PageType='contactus'";
				$query = $dbh->prepare($sql);
				$query->execute();
				$results = $query->fetchAll(PDO::FETCH_OBJ);
				if ($query->rowCount() > 0) {
					foreach ($results as $row) { ?>
						<div style="margin-bottom:1.2em;">
							<div class="modern-footer-contact-detail"><b>Address:</b> <?php echo $row->PageDescription; ?></div>
							<div class="modern-footer-contact-detail"><b>Phone:</b>
								<?php echo htmlentities($row->MobileNumber); ?></div>
							<div class="modern-footer-contact-detail"><b>Email:</b> <?php echo htmlentities($row->Email); ?>
							</div>
						</div>
					<?php }
				} ?>
			</div>
		</div>
	</div>
	<?php include_once('includes/footer.php'); ?>
</body>

</html>
