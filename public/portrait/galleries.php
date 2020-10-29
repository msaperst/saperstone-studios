<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$errors = new Errors();

try {
    $gallery = Gallery::withId($_GET ['w']);
} catch (Exception $e) {
    $errors->throw404();
}

$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = User::fromSystem();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
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
    $nav = $gallery->getNav();
    require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php";
    
    // get our gallery images
    $images = $sql->getRows( "SELECT * FROM `gallery_images` WHERE gallery = '{$gallery->getId()}' ORDER BY `sequence`;" );
    $sql->disconnect ();
    ?>
    
    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center"><?php echo $gallery->getTitle(); ?> Gallery</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <?php foreach( $gallery->getBreadcrumbs() as $breadcrumb ) {
                        if( $breadcrumb['link'] != '' ) {
                            echo "<li><a href='{$breadcrumb['link']}'>{$breadcrumb['title']}</a></li>";
                        } else {
                            echo "<li class='active'>{$breadcrumb['title']}</li>";
                        }
                    } ?>
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
        if ($gallery->getComment() != NULL) {
            ?>
        <div class="row">
            <div class="col-lg-12">
                <p id="gallery-comment"><?php echo $gallery->getComment(); ?></p>
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

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

    <!-- Slideshow Modal -->
    <div id="<?php echo str_replace(" ","-",$gallery->getTitle()); ?>"
        class="modal fade modal-carousel" role="dialog">
        <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><?php echo $gallery->getTitle(); ?> Gallery</h4>
                </div>
                <div class="modal-body">
                    <!-- Carousel -->
                    <div
                        id="<?php echo str_replace(" ","-",$gallery->getTitle()); ?>-carousel"
                        class="carousel slide carousel-three-by-two">
                        <!-- Indicators -->
                        <ol class="carousel-indicators">
                        <?php
                        foreach ( $images as $num => $image ) {
                            $class = "";
                            if ($num == 0) {
                                $class = " class='active'";
                            }
                            echo "<li data-target='#" . str_replace ( " ", "-", $gallery->getTitle() ) . "-carousel' data-slide-to='$num'$class></li>";
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
                            echo "    <div class='contain' gallery-id='{$gallery->getId()}' image-id='" . $image ['id'] . "' sequence='" . $image ['sequence'] . "'";
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
                            href="#<?php echo str_replace(" ","-",$gallery->getTitle()); ?>-carousel"
                            data-slide="prev"> <span class="icon-prev"></span>
                        </a> <a class="right carousel-control"
                            href="#<?php echo str_replace(" ","-",$gallery->getTitle()); ?>-carousel"
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
                        <button id="edit-image-btn" type="button"
                            class="btn btn-default btn-info btn-action">
                            <em class="fa fa-pencil"></em> Edit
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
        var gallery = new Gallery( <?php echo $gallery->getId(); ?>, "<?php echo str_replace(" ","-",$gallery->getTitle()); ?>", total );

        $(window,document).on("scroll resize", function(){
            if( $('footer').isOnScreen() && loaded < total ) {
                loaded = gallery.loadImages();
            }
        });
    </script>

</body>

</html>
