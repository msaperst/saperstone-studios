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
if (sizeof ( $children ) == 0) {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include "../errors/404.php";
    $conn->disconnect ();
    exit ();
}

$parent = $details ['title'];
if ($details ['parent'] != NULL) {
    $sql = "SELECT `title` FROM `galleries` WHERE id = " . $details ['parent'] . ";";
    $parent = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) ) ['title'];
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

    <?php $nav = strtolower($parent); require_once "../nav.php"; ?>
    
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
    if ($details ['parent'] != NULL) {
        ?>
                        <li><a href='gallery.php?w=<?php echo $details['parent']; ?>'>Gallery</a></li>
                    <li class='active'><?php echo $details['title']; ?></li>
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
foreach ( $children as $child ) {
    $sql = "SELECT * FROM `galleries` WHERE parent = '" . $child ['id'] . "';";
    $grandchildren = array ();
    $result = mysqli_query ( $conn->db, $sql );
    while ( $r = mysqli_fetch_assoc ( $result ) ) {
        $grandchildren [] = $r;
    }
    ?>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="<?php echo $child['title']; ?>"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'><?php echo $child['title']; ?></span> <img
                        class="img-responsive" src="img/<?php echo $child['image']; ?>"
                        alt="">
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
?>
        </div>
        <!-- /.row -->

        <?php require_once "../footer.php"; ?>

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