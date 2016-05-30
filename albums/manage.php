<?php
session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

require_once "../php/user.php";

if (getRole () != "admin") {
    header ( "HTTP/1.0 403 Forbidden" );
    exit ();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

	<?php require_once "../header.php"; ?>
	<link
	href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css"
	rel="stylesheet">
<link
	href="http://hayageek.github.io/jQuery-Upload-File/4.0.10/uploadfile.css"
	rel="stylesheet">


</head>

<body>

    <?php require_once "../nav.php"; ?>

    <!-- Page Content -->
	<div class="page-content container">

		<!-- Page Heading/Breadcrumbs -->
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header text-center">Manage Albums</h1>
				<ol class="breadcrumb">
					<li><a href="/">Home</a></li>
					<li class="active">Administration</li>
					<li class="active">Manage Albums</li>
				</ol>
			</div>
		</div>
		<!-- /.row -->

		<!-- Services Section -->
		<div class="row">
			<div class="col-lg-12">
				<table id="albums" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th>
								<button id="add-album-btn" type="button"
									class="btn btn-xs btn-success">
									<i class="fa fa-plus"></i>
								</button>
							</th>
							<th>ID</th>
							<th>Album Name</th>
							<th>Album Description</th>
							<th>Album Date</th>
							<th>Images</th>
							<th>Last Accessed</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
		<!-- /.row -->

        <?php require_once "../footer.php"; ?>

    </div>
	<!-- /.container -->

	<script
		src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
	<script src="/js/jquery.uploadfile.js"></script>
	<script src="/js/admin.js"></script>

</body>

</html>