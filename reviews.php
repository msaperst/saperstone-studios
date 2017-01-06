<?php
$category;
// if no active category is set, throw a 404 error
require_once "php/sql.php";
$conn = new Sql ();
$conn->connect ();

if (isset ( $_GET ['c'] )) {
    $category = $_GET ['c'];
    $sql = "SELECT * FROM `review_types` WHERE id = '$category';";
    $details = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
    if (! $details ['name']) {
        header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
        include "errors/404.php";
        $conn->disconnect ();
        exit ();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once "header.php"; ?>
	<link href="/css/hover-effect.css" rel="stylesheet">
    
    <?php
    if ($user->isAdmin ()) {
        ?>
    <link
	href="http://hayageek.github.io/jQuery-Upload-File/4.0.10/uploadfile.css"
	rel="stylesheet">
<link
	href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.css"
	rel="stylesheet">
    <?php
    }
    ?>
</head>

<body>

    <?php require_once "nav.php"; ?>

    <!-- Page Content -->
	<div class="page-content container">

		<!-- Page Heading/Breadcrumbs -->
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header text-center">
                <?php
                if (isset ( $category )) {
                    echo ucfirst ( $details ['name'] ) . " ";
                }
                ?>
                Raves</h1>
				<ol class="breadcrumb">
					<li><a href="/">Home</a></li>
					<li class="active">Information</li>
                    <?php
                    if (isset ( $category )) {
                        echo "<li><a href='/reviews.php'>Raves</a></li>";
                        echo "<li class='active'>" . ucfirst ( $details ['name'] ) . "</li>";
                    } else {
                        echo "<li class='active'>Raves</li>";
                    }
                    ?>
                </ol>
			</div>
		</div>
		<!-- /.row -->

		<!-- Review Content -->
		<div class="row">
			<div class="col-lg-12">
				<!-- 				<p>The key to a great business is pleasing your customers. This is -->
				<!-- 					even more important in the photo industry as your clients will be -->
				<!-- 					using your product for the rest of their lives. I take great care -->
				<!-- 					with each of my clients' sessions, paying extra detail not just to -->
				<!-- 					the shots, but also to the entire post production process, ensuring -->
				<!-- 					that you are left with fantastic images that you will love.</p> -->
			</div>
		</div>
		<!-- /.row -->

		<div class="row">
            <?php
            $where = "";
            if (isset ( $category )) {
                $where = " WHERE `category` = $category";
            }
            $sql = "SELECT * FROM `reviews`$where;";
            $result = mysqli_query ( $conn->db, $sql );
            $counter = 0;
            while ( $r = mysqli_fetch_assoc ( $result ) ) {
                $style = " align='right' style='margin: 0px 0px 20px 20px;'";
                if ($counter % 2) {
                    $style = " align='left' style='margin: 0px 20px 20px 0px;'";
                }
                ?>
				<div class="col-md-6">
				<img src='<?php echo $r['image']; ?>' style='width: 100%;' />
			</div>
			<div class="col-md-6">
				<img src='<?php echo $r['image']; ?>' style='width: 100%;' />
			</div>
			<div class="col-md-12" style="padding-top: 20px;">

				<blockquote>
					<p>
                        <?php echo $r['quote']; ?>
                    
                    </p>
					<footer><?php echo $r['client']; ?><br /> <em><?php echo $r['event']; ?></em>
					</footer>

				</blockquote>
				<hr />
			</div>
            <?php
                $counter ++;
            }
            ?>
        </div>
		<!-- /.row -->

		<!-- Links to external sites -->
		<div class="row">
			<div class="col-lg-12">
				<p>Think these reviews are great? See where else people are saying
					fantastic things about Saperstone Studios.</p>
			</div>

			<div class="col-md-4 col-sm-6 col-xs-12">
				<div section='Wedding Wire'
					class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
					<span class='preview-title'>Wedding Wire</span> <img
						class="img-responsive" src="/img/wedding-wire.jpg" width="100%"
						alt="Wedding Wire">
					<div class="overlay">
						<br />
						<br />
						<br /> <a class="info" target="_blank"
							href="http://www.weddingwire.com/reviews/saperstone-studios-reston/cdbd87c3e3540e8e.html">See
							More</a>
					</div>
				</div>
			</div>

			<div class="col-md-4 col-sm-6 col-xs-12">
				<div section='Yelp'
					class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
					<span class='preview-title'>Yelp</span> <img class="img-responsive"
						src="/img/yelp.jpg" width="100%" alt="Yelp">
					<div class="overlay">
						<br />
						<br />
						<br /> <a class="info" target="_blank"
							href="http://www.yelp.com/biz/saperstone-studios-fairfax">See
							More</a>
					</div>
				</div>
			</div>

			<div class="col-md-4 col-sm-6 col-xs-12">
				<div section='Google'
					class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
					<span class='preview-title'>Google</span> <img
						class="img-responsive" src="/img/google.jpg" width="100%"
						alt="Google">
					<div class="overlay">
						<br />
						<br />
						<br /> <a class="info" target="_blank"
							href="https://www.google.com/search?q=saperstone+studios&oq=saperstone+studios&aqs=chrome..69i57j69i60l2j69i65.2255j0j4&sourceid=chrome&ie=UTF-8#lrd=0x89b637e9f071197b:0x4c75c5462bac5863,1,">See
							More</a>
					</div>
				</div>
			</div>
		</div>
		<!-- /.row -->

        <?php require_once "footer.php"; ?>

    </div>
	<!-- /.container -->
	
	<?php
    if ($user->isAdmin ()) {
        ?>
    <script src="/js/edit-image.js"></script>
    <script src="/js/jquery.uploadfile.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>
    <?php
    }
    ?>

</body>

</html>
<?php
$conn->disconnect ();