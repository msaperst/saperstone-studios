<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = User::fromSystem();
$products = $sql->getRows( "SELECT * FROM `galleries` WHERE parent = 38;" );
$sql->disconnect();
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
    <link href="/css/uploadfile.css" rel="stylesheet">
    <?php
    }
    ?>
    

</head>

<body>

    <?php $nav = "wedding"; require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Products & Investment</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Weddings</a></li>
                    <li><a href="details.php">Details</a></li>
                    <li class="active">Products & Investment</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-lg-12">
                <h2>Investing in Custom Photography</h2>
            </div>
            <div class="col-md-12">
                <p>
                    <a href='/contact.php'>Drop me a line</a> for more of the details
                    or head on over to see what the <a href='experience.php'>wedding
                        experience</a> is all about.
                </p>
                <p>Saperstone Studios is a full-service studio, which means that I
                    go above and beyond simply providing you a USB of images that is
                    destined to get thrown into your junk drawer. My goal is to provide
                    you with options for custom artwork of you and your loved ones to
                    cherish for generations to come. I offer a variety of high quality,
                    professional products for you to choose from, including canvas,
                    metal prints, heirloom albums, and yes – all digital files do come
                    on a USB for engagements and weddings.</p>
                <p>In today’s digital world, I know it is important for most people
                    to share and preserve images digitally via social media but I want
                    your images to have more longevity than the few days of 'likes' and
                    comments on your facebook page. Hanging your favorite images on the
                    walls of your home ensures that you, and generations after, can
                    enjoy them.</p>
                <p>All available products can be seen at your initial consultation
                    and/or image review session. You can also get a sampling of what I
                    offer by viewing the links below.</p>
            </div>
        </div>
        <!-- /.row -->

        <hr />

        <!-- Products Section -->
        <div class="row">
            <div class="col-lg-12">
                <h2>Available Products</h2>
            </div>

            <div class="row">
            <?php
            
            for($i = 0; $i < count ( $products ); $i ++) {
                $product = $products [$i];

                $sql = new Sql ();
                $subProducts = $sql->getRows( "SELECT * FROM `galleries` WHERE parent = '" . $product ['id'] . "';" );
                $sql->disconnect();

                $padding = "";
                if (count ( $products ) % 3 == 1 && $i == (count ( $products ) - 1)) {
                    $padding = "col-sm-offset-4 ";
                }
                if (count ( $products ) % 3 == 2 && $i == (count ( $products ) - 2)) {
                    $padding = "col-sm-offset-2 ";
                }
                ?>
            <div
                    class="<?php echo $padding; ?>col-md-4 col-sm-6 col-xs-12">
                    <div section="<?php echo $product['title']; ?>"
                        class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                        <span class='preview-title'><?php echo $product['title']; ?></span>
                        <img class="img-responsive" alt="<?php echo $product['title']; ?>"
                            src="img/<?php echo $product['image']; echo $rand; ?>"
                            width="100%" />
                        <div class="overlay">
                            <br /> <br /> <br /> <a class="info"
                                <?php
                                if (sizeof ( $subProducts ) == 0) {
                                ?>
                                                    href="galleries.php?w=<?php echo $product['id']; ?>">See More</a>
                            <?php
                            } else {
                                ?>
                                href="gallery.php?w=<?php echo $product['id']; ?>">See More</a>
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