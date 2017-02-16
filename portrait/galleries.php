<?php
$what;
// if no album is set, throw a 404 error
if (! isset ( $_GET ['w'] )) {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include "../errors/404.php";
    exit ();
} else {
    $what = ( int ) $_GET ['w'];
}

require_once "../php/sql.php";
$conn = new Sql ();
$conn->connect ();
$sql = "SELECT * FROM `galleries` WHERE id = '$what';";
$details = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if (! $details ['id']) {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include "../errors/404.php";
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM `galleries` WHERE parent = '$what';";
$children = array ();
$result = mysqli_query ( $conn->db, $sql );
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $children [] = $r;
}

$sql = "SELECT * FROM `galleries` WHERE id = '" . $details ['parent'] . "';";
$parent = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if ($parent ['parent'] != NULL) {
    $sql = "SELECT * FROM `galleries` WHERE id = '" . $parent ['parent'] . "';";
    $grandparent = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
}
if (isset ( $grandparent ) && $grandparent ['title'] == "Product") {
    $sql = "SELECT `title` FROM `galleries` WHERE id = " . $grandparent ['parent'] . ";";
    $greatgrandparent = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) ) ['title'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once "../header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

    <?php
    if ($user->isAdmin ()) {
        ?>
    <link href="/css/uploadfile.css" rel="stylesheet">
    <?php
    }
    ?>
</head>

<body>

    <?php
    $nav = strtolower ( $parent ['title'] );
    if ($parent ['parent'] != NULL && $grandparent ['title'] != "Product") {
        $nav = strtolower ( $grandparent ['title'] );
    } elseif ($parent ['parent'] != NULL && $grandparent ['title'] == "Product") {
        $nav = strtolower ( $greatgrandparent );
    }
    require_once "../nav.php";
    
    // get our gallery images
    $sql = "SELECT * FROM `gallery_images` WHERE gallery = '$what' ORDER BY `sequence`;";
    $result = mysqli_query ( $conn->db, $sql );
    $images = array ();
    while ( $row = mysqli_fetch_assoc ( $result ) ) {
        $images [] = $row;
    }
    $conn->disconnect ();
    ?>
    
    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center"><?php echo $details['title']; ?> Gallery</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php"><?php echo ucfirst($nav); ?>s</a></li>
                    <?php
                    if ($parent ['parent'] != NULL && $parent ['title'] != 'Product' && $grandparent ['title'] != "Product") {
                        ?>
                        <li><a
                        href="gallery.php?w=<?php echo $parent ['parent']; ?>">Gallery</a></li>
                    <li><a href='gallery.php?w=<?php echo $parent ['id']; ?>'><?php echo $parent ['title']; ?></a></li>
                        <?php
                    } elseif ($parent ['parent'] != NULL && $parent ['title'] == 'Product') {
                        ?>
                        <li><a href='details.php'>Details</a></li>
                    <li><a href='products.php'>Products</a></li>
                    <li><a href="gallery.php?w=<?php echo $details ['parent']; ?>">Gallery</a></li>
                        <?php
                    } elseif ($parent ['parent'] != NULL && $grandparent ['title'] == 'Product') {
                        ?>
                        <li><a href='details.php'>Details</a></li>
                    <li><a href='products.php'>Products</a></li>
                    <li><a href="gallery.php?w=<?php echo $parent ['parent']; ?>">Gallery</a></li>
                    <li><a href="gallery.php?w=<?php echo $details ['parent']; ?>"><?php echo $parent['title']; ?></a></li>
                        <?php
                    } else {
                        ?>
                        <li><a
                        href="gallery.php?w=<?php echo $parent ['id']; ?>">Gallery</a></li>
                        <?php
                    }
                    ?>
                    <li class="active"><?php echo $details['title']; ?></li>
                    <?php
                    if ($user->isAdmin ()) {
                        ?>
                    <li class="no-before pull-right"><button
                            type="button" id="edit-gallery-btn"
                            class="btn btn-xs btn-warning" data-toggle="tooltip"
                            data-placement="left" title="Edit Album Details">
                            <i class="fa fa-pencil-square-o"></i>
                        </button></li>
                    <li class="no-before pull-right"
                        style="padding-right: 5px; display: none;"><button type="button"
                            id="save-gallery-btn" class="btn btn-xs btn-success"
                            data-toggle="tooltip" data-placement="left"
                            title="Save Image Order">
                            <i class="fa fa-floppy-o"></i>
                        </button></li>
                    <li class="no-before pull-right" style="padding-right: 5px;"><button
                            type="button" id="sort-gallery-btn" class="btn btn-xs btn-info"
                            data-toggle="tooltip" data-placement="left"
                            title="Rearrange Album Images">
                            <i class="fa fa-random"></i>
                        </button></li>
                    <?php
                    }
                    ?>
                </ol>
            </div>
        </div>
        <!-- /.row -->
        
        <?php
        if ($details ['comment'] != NULL) {
            ?>
        <div class="row">
            <div class="col-lg-12">
                <p><?php echo $details['comment']; ?></p>
            </div>
        </div>
        <?php
        }
        ?>

        <!-- Services Section -->
        <div class="row image-grid">
            <div id="col-0" class="col-md-3 col-sm-6 col-gallery"></div>
            <div id="col-1" class="col-md-3 col-sm-6 col-gallery"></div>
            <div id="col-2" class="col-md-3 col-sm-6 col-gallery"></div>
            <div id="col-3" class="col-md-3 col-sm-6 col-gallery"></div>
        </div>
        <!-- /.row -->

        <?php require_once "../footer.php"; ?>

    </div>
    <!-- /.container -->

    <!-- Slideshow Modal -->
    <div id="<?php echo str_replace(" ","-",$details['title']); ?>"
        class="modal fade modal-carousel" role="dialog">
        <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><?php echo $details['title']; ?> Gallery</h4>
                </div>
                <div class="modal-body">
                    <!-- Carousel -->
                    <div
                        id="<?php echo str_replace(" ","-",$details['title']); ?>-carousel"
                        class="carousel slide carousel-three-by-two">
                        <!-- Indicators -->
                        <ol class="carousel-indicators">
                        <?php
                        foreach ( $images as $num => $image ) {
                            $class = "";
                            if ($num == 0) {
                                $class = " class='active'";
                            }
                            echo "<li data-target='#" . str_replace ( " ", "-", $details ['title'] ) . "-carousel' data-slide-to='$num'$class></li>";
                        }
                        ?>
                    </ol>

                        <!-- Wrapper for slides -->
                        <div class="carousel-inner">
                        <?php
                        foreach ( $images as $num => $image ) {
                            $active_class = "";
                            if ($num == 0) {
                                $active_class = " active";
                            }
                            echo "<div class='item$active_class'>";
                            echo "    <div class='contain' gallery-id='$what' image-id='" . $image ['id'] . "' sequence='" . $image ['sequence'] . "'";
                            echo "        alt='" . $image ['title'] . "' style=\"background-image: url('" . $image ['location'] . "');\"></div>";
                            echo "    <div class='carousel-caption'>";
                            echo "        <h2>" . $image ['caption'] . "</h2>";
                            echo "    </div>";
                            echo "</div>";
                        }
                        ?>
                    </div>

                        <!-- Controls -->
                        <a class="left carousel-control"
                            href="#<?php echo str_replace(" ","-",$details['title']); ?>-carousel"
                            data-slide="prev"> <span class="icon-prev"></span>
                        </a> <a class="right carousel-control"
                            href="#<?php echo str_replace(" ","-",$details['title']); ?>-carousel"
                            data-slide="next"> <span class="icon-next"></span>
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <span class="pull-left">
                        <?php
                        if ($user->isAdmin ()) {
                            ?>
                        <button id="delete-image-btn" type="button"
                            class="btn btn-default btn-danger btn-action">
                            <em class="fa fa-trash"></em> Delete
                        </button>
                        <?php
                        }
                        ?>
                    </span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>
    <!-- End of Modal -->

    <!-- Gallery JavaScript -->
    <script src="/js/gallery.js"></script>
    
    <?php
    if ($user->isAdmin ()) {
        ?>
    <script src="/js/gallery-admin.js"></script>
    <script src="/js/jquery.uploadfile.js"></script>
    <?php
    }
    ?>

    <!-- Script to Activate the Gallery -->
    <script>
        var loaded = 0;
        var total = <?php echo count($images); ?>;
        var gallery = new Gallery( <?php echo $what; ?>, "<?php echo str_replace(" ","-",$details['title']); ?>", total );

        $(window,document).on("scroll resize", function(){
            if( $('footer').isOnScreen() && loaded < total ) {
                loaded = gallery.loadImages();
            }
        });
    </script>

</body>

</html>
