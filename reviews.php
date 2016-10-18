<?php
require_once "php/sql.php";
$conn = new Sql ();
$conn->connect ();
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
				<h1 class="page-header text-center">Testimonials</h1>
				<ol class="breadcrumb">
					<li><a href="/">Home</a></li>
					<li class="active">Information</li>
					<li class="active">Reviews</li>
				</ol>
			</div>
		</div>
		<!-- /.row -->

		<!-- Review Content -->
		<div class="row">
			<div class="col-lg-12">
				<p>The key to a great business is pleasing your customers. This is
					even more important in the photo industry as your clients will be
					using your product for the rest of their lives. I take great care
					with each of my clients' sessions, paying extra detail not just to
					the shots, but also to the entire post production process, ensuring
					that you are left with fantastic images that you will love.</p>
			</div>
		</div>
		<div class="row">
            <?php
            $sql = "SELECT * FROM `reviews`;";
            $result = mysqli_query ( $conn->db, $sql );
            $counter = 0;
            while ( $r = mysqli_fetch_assoc ( $result ) ) {
                $style = " align='right' style='margin: 0px 0px 20px 20px;'";
                if ($counter % 2) {
                    $style = " align='left' style='margin: 0px 20px 20px 0px;'";
                }
                ?>
            <div class="col-lg-12">
				<blockquote>
					<img src='<?php echo $r['image'] . "' " . $style; ?> />
					<p>
						<?php echo $r['quote']; ?>
					</p>
					<footer><?php echo $r['client']; ?><br /> <em><?php echo $r['event']; ?></em>
					</footer>
				</blockquote>
			</div>
            <?php
                $counter ++;
            }
            ?>
        </div>
		<!-- /.row -->

        <?php require_once "footer.php"; ?>

    </div>
	<!-- /.container -->

</body>

</html>
<?php
$conn->disconnect ();
?>