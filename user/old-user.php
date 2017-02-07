<?php
session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
$user = new User ();

if ($user->isLoggedIn ()) {
    header ( "Location: /user/profile.php" );
    exit ();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once "../header.php"; ?>

</head>

<body>

    <?php require_once "../nav.php"; ?>

    <!-- Page Content -->
	<div class="page-content container">

		<!-- Page Heading/Breadcrumbs -->
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header text-center">Login</h1>
				<ol class="breadcrumb">
					<li><a href="/">Home</a></li>
					<li class="active">Login</li>
				</ol>
			</div>
		</div>
		<!-- /.row -->

		<!-- Services Section -->
		<div class="row">
			<div class="form-group has-feedback">
				<div class="col-md-2">
					<label for="login-username">Username:</label>
				</div>
				<div class="col-md-10">
					<input type="text" class="form-control" id="login-username"
						required placeholder="Username" /> <span
						class="glyphicon form-control-feedback"></span>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="form-group has-feedback">
				<div class="col-md-2">
					<label for="login-password">Password:</label>
				</div>
				<div class="col-md-10">
					<input type="password" class="form-control" id="login-password"
						required placeholder="Password" /> <span
						class="glyphicon form-control-feedback"></span>
				</div>
			</div>
		</div>

		<div class="row">
			<div id="login-message" class="col-md-12"></div>
		</div>
		<div class="row">
			<div class="form-group">
				<div class="col-md-2">
					<button id="login" type="submit"
						class="btn btn-primary btn-success alert">
						<em class="fa fa-sign-in"></em> Login
					</button>
				</div>
			</div>
		</div>
		<!-- /.row -->

        <?php require_once "../footer.php"; ?>

    </div>
	<!-- /.container -->

	<script src="/js/old-user.js"></script>

</body>

</html>