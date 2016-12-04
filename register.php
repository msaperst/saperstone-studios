<?php
session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "php/user.php";
$user = new User ();

if ($user->isLoggedIn ()) {
    header ( "Location: /albums/profile.php" );
    exit ();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once "header.php"; ?>

</head>

<body>

    <?php require_once "nav.php"; ?>

    <!-- Page Content -->
	<div class="page-content container">

		<!-- Page Heading/Breadcrumbs -->
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header text-center">Register</h1>
				<ol class="breadcrumb">
					<li><a href="/">Home</a></li>
					<li class="active">Profile</li>
					<li class="active">Register</li>
				</ol>
			</div>
		</div>
		<!-- /.row -->

		<!-- Services Section -->
		<div class="form-horizontal">
			<div class="row">
				<div class="form-group has-error has-feedback">
					<div class="col-md-2">
						<label for="profile-username">Username:</label>
					</div>
					<div class="col-md-10">
						<input type="text" class="form-control" id="profile-username"
							placeholder="Username" required /> <span
							class="glyphicon glyphicon-remove form-control-feedback"></span>
						<div class="error" id="update-profile-username-message"></div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="form-group has-error has-feedback">
					<div class="col-md-2">
						<label for="profile-password">Password:</label>
					</div>
					<div class="col-md-10">
						<input type="password" class="form-control" id="profile-password"
							placeholder="Password" required /> <span
							class="glyphicon glyphicon-remove form-control-feedback"></span>
						<div id="update-profile-password-message">
							<div id="update-profile-password-strength"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="form-group has-error has-feedback">
					<div class="col-md-2">
						<label for="profile-confirm-password">Confirm Password:</label>
					</div>
					<div class="col-md-10">
						<input type="password" class="form-control"
							id="profile-confirm-password" placeholder="Confirm Password"
							required /> <span
							class="glyphicon glyphicon-remove form-control-feedback"></span>
						<div class="error" id="update-profile-confirm-password-message"></div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="form-group has-error has-feedback">
					<div class="col-md-2">
						<label for="profile-firstname">First Name:</label>
					</div>
					<div class="col-md-10 ">
						<input type="text" class="form-control" id="profile-firstname"
							placeholder="First Name" required /> <span
							class="glyphicon glyphicon-remove form-control-feedback"></span>
						<div class="error" id="update-profile-firstname-message"></div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="form-group has-error has-feedback">
					<div class="col-md-2">
						<label for="profile-lastname">Last Name:</label>
					</div>
					<div class="col-md-10">
						<input type="text" class="form-control" id="profile-lastname"
							placeholder="Last Name" required /> <span
							class="glyphicon glyphicon-remove form-control-feedback"></span>
						<div class="error" id="update-profile-lastname-message"></div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="form-group has-error has-feedback">
					<div class="col-md-2">
						<label for="profile-email">Email:</label>
					</div>
					<div class="col-md-10">
						<input type="email" class="form-control" id="profile-email"
							placeholder="Email" required /> <span
							class="glyphicon glyphicon-remove form-control-feedback"></span>
						<div class="error" id="update-profile-email-message"></div>
					</div>
				</div>
			</div>
			<div class="row">
				<div id="update-profile-message" class="col-md-12"></div>
			</div>
			<div class="row">
				<div class="form-group">
					<div class="col-md-2">
						<button id="update-profile" type="submit"
							class="btn btn-primary btn-success alert">
							<em class="fa fa-floppy-o"></em> Update
						</button>
					</div>
				</div>
			</div>
		</div>
		<!-- /.row -->

        <?php require_once "footer.php"; ?>

    </div>
	<!-- /.container -->

	<script src="/js/profile.js"></script>

</body>

</html>