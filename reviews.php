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
                Testimonials</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li class="active">Information</li>
                    <?php
                    if (isset ( $category )) {
                        echo "<li><a href='/reviews.php'>Reviews</a></li>";
                        echo "<li class='active'>" . ucfirst ( $details ['name'] ) . "</li>";
                    } else {
                        echo "<li class='active'>Reviews</li>";
                    }
                    ?>
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
            <div class="col-lg-12">
                <blockquote>
                    <img
                        src='<?php echo $r['image'] . "' " . $style; ?> />
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

        <!-- Links to external sites -->
        <div class="row">
            <div class="col-lg-12">
                <p>Think these reviews are great? See where else people are saying great things about Saperstone Studios.
                    <ul>
                        <li><a target="_blank" href="http://www.weddingwire.com/reviews/saperstone-studios-reston/cdbd87c3e3540e8e.html">Wedding Wire</a></li>
                        <li><a target="_blank" href="http://www.yelp.com/biz/saperstone-studios-fairfax">Yelp</a></li>
                        <li><a target="_blank" href="https://plus.google.com/104470541535522234804/about?hl=en">Google</a></li>
                    </ul>
                </p>
            </div>
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
