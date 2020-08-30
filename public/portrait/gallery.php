<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$errors = new Errors();

$what;
// if no gallery is set, throw a 404 error - TODO - use new gallery object
if (! isset ( $_GET ['w'] )) {
    $errors->throw404();
} else {
    $what = (int) $_GET ['w'];
}

$session = new Session();
$session->initialize();
$sql = new Sql ();
$details = $sql->getRow( "SELECT * FROM `galleries` WHERE id = '$what';" );
if (! $details ['id']) {
    $errors->throw404();
}

$children = $sql->getRows( "SELECT * FROM `galleries` WHERE parent = '$what' AND title != 'Product';" );
if (sizeof ( $children ) == 0) {
    $errors->throw404();
}

$user = User::fromSystem();

$parent = $details ['title'];
if ($details ['parent'] != NULL) {
    $parent = $sql->getRow( "SELECT `title` FROM `galleries` WHERE id = " . $details ['parent'] . ";" ) ['title'];
}
if ($parent == 'Product') {
    $grandparent = $parent;
    $parent = $sql->getRow( "SELECT `parent` FROM `galleries` WHERE id = " . $details ['parent'] . ";" ) ['parent'];
    $parent = $sql->getRow( "SELECT `title` FROM `galleries` WHERE id = $parent;" ) ['title'];
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

    <?php $nav = strtolower($parent); require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>
    
    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center"><?php echo $details['title']; ?> Gallery</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php"><?php echo $parent; ?></a></li>
                    <?php
                    if ($details ['parent'] != NULL && $details ['title'] != 'Product' && (! isset ( $grandparent ) || $grandparent != 'Product')) {
                        ?>
                        <li><a
                        href='gallery.php?w=<?php echo $details['parent']; ?>'>Gallery</a></li>
                    <li class='active'><?php echo $details['title']; ?></li>
                        <?php
                    } elseif (isset ( $grandparent ) && $grandparent == 'Product') {
                        ?>
                    <li><a href='services.php'>Services</a></li>
                    <li><a href='products.php'>Products</a></li>
                    <li><a href='gallery.php?w=<?php echo $details['parent']; ?>'>Gallery</a></li>
                    <li class='active'><?php echo $details['title']; ?></li>
                        <?php
                    } elseif ($details ['parent'] != NULL && $details ['title'] == 'Product') {
                        ?>
                    <li><a href='services.php'>Services</a></li>
                    <li><a href='products.php'>Products</a></li>
                    <li class='active'>Gallery</li>
                        <?php
                    } else {
                        ?>
                        <li class='active'>Gallery</li>
                        <?php
                    }
                    ?>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Services Section -->
        <div class="row">
            <?php
            for($i = 0; $i < count ( $children ); $i ++) {
                $child = $children [$i];
                $grandchildren = $sql->getRows( "SELECT * FROM `galleries` WHERE parent = '" . $child ['id'] . "';" );
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
                <div section="<?php echo $child['title']; ?>"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <span class='preview-title'><?php echo $child['title']; ?></span> <img
                        class="img-responsive" alt="<?php echo $child['title']; ?>"
                        src="img/<?php echo $child['image']; echo $rand; ?>" />
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info"
                            <?php
                if (sizeof ( $grandchildren ) == 0) {
                    ?>
                            href="galleries.php?w=<?php echo $child['id']; ?>">See More</a>
                        <?php
                } else {
                    ?>
                            href="gallery.php?w=<?php echo $child['id']; ?>">See More</a>
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
