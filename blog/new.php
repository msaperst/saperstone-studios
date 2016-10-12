<?php
session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
$user = new user ();

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    include "../errors/401.php";
    exit ();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once "../header.php"; ?>
    <link
	href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/css/bootstrap-dialog.min.css"
	rel="stylesheet">

</head>

<body>

    <?php require_once "../nav.php"; ?>

    <!-- Page Content -->
	<div class="page-content container">

		<!-- Page Heading/Breadcrumbs -->
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header text-center">Write A New Blog Post</h1>
				<ol class="breadcrumb">
					<li><a href="/">Home</a></li>
					<li><a href="/blog/">Blog</a></li>
					<li class="active">New Post</li>
				</ol>
			</div>
		</div>
		<!-- /.row -->

		<!-- Post Section -->
		<div class="row">
			<div class="col-lg-12">
				<input id='post-title-input'
					class='form-control input-lg text-center' type='text'
					placeholder='Blog Post Title' />
			</div>
		</div>
		<div class="row">
			<div id="post-tags" class="col-md-4 text-left">
				<select id='post-tags-select' class='form-control input-sm'
					style='width: auto;'>
					<option></option>
					<option value='0' style='color: red;'>New Category</option>
            	<?php
            $conn = new sql ();
            $conn->connect ();
            $sql = "SELECT * FROM `tags`;";
            $result = mysqli_query ( $conn->db, $sql );
            while ( $row = mysqli_fetch_assoc ( $result ) ) {
                echo "<option value='" . $row ['id'] . "'>" . $row ['tag'] . "</option>";
            }
            $conn->disconnect ();
            ?>
            	</select>
			</div>
			<div class="col-md-4 text-center">
				<strong id="post-date"> <input id='post-date-input'
					class='form-control input-sm' type='date'
					style='width: auto; display: initial;'
					value='<?php echo date("Y-m-d"); ?>' />
				</strong>
			</div>
			<div id="post-likes" class="col-md-4 text-right"></div>
		</div>
		<!-- /.row -->

		<!-- Post Content -->
		<div id="post-content"></div>
		<!-- /.row -->

        <?php
        require_once "../footer.php";
        ?>

    </div>
	<!-- /.container -->

	<script src="/js/post-admin.js"></script>

</body>

</html>