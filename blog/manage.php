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
	href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css"
	rel="stylesheet">

</head>

<body>

    <?php require_once "../nav.php"; ?>
    
	<!-- Page Content -->
	<div class="page-content container">

		<!-- Page Heading/Breadcrumbs -->
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header text-center">Manage Blog Posts</h1>
				<ol class="breadcrumb">
					<li><a href="/">Home</a></li>
					<li><a href="/blog/">Blog</a></li>
					<li class="active">Manage</li>
				</ol>
			</div>
		</div>
		<!-- /.row -->

		<!-- Post Section -->
		<div class="row">
			<div class="col-lg-12">
				<table id="posts" class="display"
					style="width: 100%; border-spacing: 0px;">
					<thead>
						<tr>
							<th></th>
							<th>Title</th>
							<th>Date</th>
							<th>Active</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
		<!-- /.row -->

        <?php
        require_once "../footer.php";
        ?>

    </div>
	<!-- /.container -->

	<!-- Slideshow Modal -->
	<div id="post" class="modal fade modal-carousel" role="dialog">
		<div class="modal-dialog modal-lg">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title"></h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-lg-7">
							<input id='post-title-input' class='form-control' type='text'
								placeholder='Blog Post Title' /> <input id='post-date-input'
								class='form-control' type='date' /> <input
								id='post-active-input' type='checkbox' /> Active <br /> <br />
							<div id="post-tags">
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
						</div>
						<div class="col-lg-5">
							<div id='post-preview-holder' class='text-center'
								style='width: 300px; height: 176px; background-color: red; overflow: hidden;'>
								<select id='post-preview-image'
									style='top: 50%; position: absolute; opacity: 0.65; filter: alpha(opacity = 65); z-index: 99; left: 20px;'><option></option></select>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>



		<script src="/js/post-admin.js"></script>
		<script src="/js/posts-manage.js"></script>
		<script
			src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
		<script
			src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

</body>

</html>