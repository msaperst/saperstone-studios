<?php
session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
$user = new User ();

if (! $user->isLoggedIn ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    include "../errors/401.php";
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
				<h1 class="page-header text-center">Manage Profile</h1>
				<ol class="breadcrumb">
					<li><a href="/">Home</a></li>
					<li class="active">Profile</li>
					<li class="active">Manage</li>
				</ol>
			</div>
		</div>
		<!-- /.row -->

		<!-- Services Section -->
		<div class="row">
			<div class="form-group">
				<div class="col-md-2">
					<label for="profile-username">Username:</label>
				</div>
				<div class="col-md-10">
					<input type="text" class="form-control" id="profile-username"
						placeholder="Username" value="<?php echo $user->getUser(); ?>"
						disabled />
				</div>
			</div>
		</div>
		<div class="row">
			<div class="form-group">
				<div class="col-md-2">
					<label for="profile-firstname">First Name:</label>
				</div>
				<div class="col-md-10">
					<input type="text" class="form-control" id="profile-firstname"
						placeholder="First Name"
						value="<?php echo $user->getFirstName(); ?>" />
				</div>
			</div>
		</div>
		<div class="row">
			<div class="form-group">
				<div class="col-md-2">
					<label for="profile-lastname">Last Name:</label>
				</div>
				<div class="col-md-10">
					<input type="text" class="form-control" id="profile-lastname"
						placeholder="Last Name"
						value="<?php echo $user->getLastName(); ?>" />
				</div>
			</div>
		</div>
		<div class="row">
			<div class="form-group">
				<div class="col-md-2">
					<label for="profile-email">Email:</label>
				</div>
				<div class="col-md-10">
					<input type="text" class="form-control" id="profile-email"
						placeholder="Email" value="<?php echo $user->getEmail(); ?>" />
				</div>
			</div>
		</div>
		<div class="row">
			<div class="form-group">
				<div class="col-md-2">
					<button id="update-profile" type="submit"
						class="btn btn-primary btn-success">
						<em class="fa fa-floppy-o"></em> Update
					</button>
				</div>
			</div>
		</div>
		<!-- /.row -->

        <?php require_once "../footer.php"; ?>

    </div>
	<!-- /.container -->
	
	<script src="/js/profile.js"></script>

</body>

</html>