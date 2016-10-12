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
	href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.2/summernote.css"
	rel="stylesheet">
<link
	href="http://hayageek.github.io/jQuery-Upload-File/4.0.10/uploadfile.css"
	rel="stylesheet">

</head>

<body>

    <?php require_once "../nav.php"; ?>
    
	<!-- Post Control Bar -->
	<div data-spy="affix"
		style="margin-top: 35px; margin-left: 5px; max-width: 300px; z-index:100;"
		class="text-center">
		<div id='post-button-holder'>
			<br />
			<button id="add-text-button" type="button" class="btn btn-info">
				<em class="fa fa-file-text-o"></em> Add Text Area
			</button>
			<button id="add-image-button" type="button" class="btn btn-info">
				<em class="fa fa-image"></em> Add Image Area
			</button>
			<br />
			<button id="save-post" type="button" class="btn btn-warning">
				<em class="fa fa-save"></em> Save Post
			</button>
			<br />
			<button id="schedule-post" type="button" class="btn btn-success">
				<em class="fa fa-clock-o"></em> Schedule Post
			</button>
			<button id="publish-post" type="button" class="btn btn-success">
				<em class="fa fa-send"></em> Publish Post
			</button>
		</div>

		<div id='post-image-holder' style='z-index:100;'></div>
	</div>

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
		<ul id="post-content" class="ui-sortable"></ul>

	</div>
	<!-- /.row -->

        <?php
        require_once "../footer.php";
        ?>

    </div>
	<!-- /.container -->


	<script src="/js/post-admin.js"></script>
	<script src="/js/dragndrop.js"></script>
	<script src="/js/jquery.uploadfile.js"></script><script
		src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.2/summernote.min.js"></script>
	<script
		src="https://cdnjs.cloudflare.com/ajax/libs/jquery-sortable/0.9.13/jquery-sortable-min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

</body>

</html>