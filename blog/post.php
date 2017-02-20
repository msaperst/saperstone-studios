<?php
session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

$post;
// if no album is set, throw a 404 error
if (! isset ( $_GET ['p'] )) {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include "../errors/404.php";
    exit ();
} else {
    $post = ( int ) $_GET ['p'];
}

require_once "../php/sql.php";
$conn = new Sql ();
$conn->connect ();
$sql = "SELECT * FROM `blog_details` WHERE id = '$post';";
$details = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if (! $details ['title']) {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include "../errors/404.php";
    $conn->disconnect ();
    exit ();
}

include_once "../php/user.php";
$user = new User ();

if (! $user->isAdmin () && ! $details ['active']) {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include "../errors/404.php";
    $conn->disconnect ();
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
				<h1 class="page-header text-center">Recent Blog Posts</h1>
				<ol class="breadcrumb">
					<li><a href="/">Home</a></li>
					<li><a href="/blog/">Blog</a></li>
					<li><a href="/blog/posts.php">Posts</a></li>
					<li class="active" id="breadcrumb-title"></li>
                    <?php
                    if ($user->isAdmin ()) {
                        ?>
                    <li class="no-before pull-right"><button
							type="button" id="edit-post-btn" class="btn btn-xs btn-warning"
							data-toggle="tooltip" data-placement="left"
							title="Edit Blog Post">
							<i class="fa fa-pencil-square-o"></i>
						</button></li>
                    <?php
                    }
                    ?>
                </ol>
			</div>
		</div>
		<!-- /.row -->

		<!-- Post Section -->
		<div id="post-content"></div>
		<!-- /.row -->

		<!-- Comment Section -->
		<div class="row">
			<div class="col-md-12">
				<h2 class='text-center'>Leave A Comment</h2>
			</div>
		</div>
		<div class="row">
			<input type="text" class="hidden" id="post-comment-user"
				value="<?php echo $user->getId(); ?>" />
			<div class="col-md-1">
				<label for="post-comment-name">Name:</label>
			</div>
			<div class="col-md-5">
				<input type="text" class="form-control" id="post-comment-name"
					placeholder="Name" value="<?php echo $user->getName(); ?>" />
				<div class="error" id="post-comment-name-message"></div>
			</div>
			<div class="col-md-1">
				<label for="post-comment-email">Email:</label>
			</div>
			<div class="col-md-5">
				<input type="text" class="form-control" id="post-comment-email"
					placeholder="Email"
					data-validation-required-message="Please enter a valid email address"
					value="<?php echo $user->getEmail(); ?>" />
				<div class="error" id="post-comment-email-message"></div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="controls">
					<textarea rows="5    " cols="100" class="form-control"
						id="post-comment-message" placeholder="Message" required=""
						data-validation-required-message="Please enter your message"
						maxlength="999" style="resize: none" aria-invalid="false"></textarea>
					<div class="error" id="post-comment-message-message"></div>
				</div>
			</div>
		</div>
		<!-- /.row -->

		<!-- View Comments -->
		<div id="post-comments">
			<div class="row">
				<div class="col-md-6">
					<h2 class='text-left'></h2>
				</div>
				<div class="col-md-6 text-right">
					<button post-id="<?php echo $post; ?>" id="post-comment-submit"
						class="btn btn-success disabled" style="margin-top: 20px;"
						disabled>Submit Comment</button>
				</div>
			</div>
		</div>
		<!-- /.row -->

        <?php require_once "../footer.php"; ?>

    </div>
	<!-- /.container -->

	<!-- Gallery JavaScript -->
	<script src="/js/post.js"></script>
	<script src="/js/post-full.js"></script>

	<!-- Script to Activate the Gallery -->
	<script>
        new PostFull( <?php echo $post; ?> );
    </script>

</body>

</html>