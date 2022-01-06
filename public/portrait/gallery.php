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

$children = array();
foreach( $sql->getRows( "SELECT * FROM `galleries` WHERE parent = '{$gallery->getId()}' AND title != 'Product';" ) as $child) {
    $children[] = Gallery::withId($child['id']);
}
if (sizeof ( $children ) == 0) {
    $errors->throw404();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

    <?php
    $rand = "";
    if ($user->isAdmin ()) {
        $rand = "?" . Strings::randomString ();
        ?>
        ?>
    <link href="/css/uploadfile.css" rel="stylesheet">
    <?php
    }
    ?>
    
</head>

<body>

    <?php $nav = $gallery->getNav(); require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>
    
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
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Services Section -->
        <div class="row">
            <?php
            for($i = 0; $i < count ( $children ); $i ++) {
                $child = $children [$i];
                $grandchildren = $sql->getRows( "SELECT * FROM `galleries` WHERE parent = '{$child->getId()}';" );
                $padding = "";
                if (count ( $children ) % 3 == 1 && $i == (count ( $children ) - 1)) {
                    $padding = "col-sm-offset-4 ";
                }
                if (count ( $children ) % 3 == 2 && $i == (count ( $children ) - 2)) {
                    $padding = "col-sm-offset-2 ";
                }
                ?>
            <div
                class="<?php echo $padding; ?>col-md-4 col-sm-6 col-xs-12">
                <div section="<?php echo $child->getTitle(); ?>"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <span class='preview-title'><?php echo $child->getTitle(); ?></span> <img
                        class="img-responsive" alt="<?php echo $child->getTitle(); ?>"
                        src="img/<?php echo $child->getImage(); echo $rand; ?>" />
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info"
                            <?php
                if (sizeof ( $grandchildren ) == 0) {
                    ?>
                            href="galleries.php?w=<?php echo $child->getId(); ?>">See More</a>
                        <?php
                } else {
                    ?>
                            href="gallery.php?w=<?php echo $child->getId(); ?>">See More</a>
                        <?php
                }
                ?>                        
                    </div>
                </div>
            </div>
    <?php
            }
            $sql->disconnect();
            ?>
        </div>
        <!-- /.row -->

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->
    
    <?php
    if ($user->isAdmin ()) {
        ?>
    <script src="/js/edit-image.js"></script>
    <script src="/js/jquery.uploadfile.js"></script>
    <?php
    }
    ?>

</body>

</html>
