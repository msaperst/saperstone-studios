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
    $rand = "";
    if ($user->isAdmin ()) {
        require_once 'php/strings.php';
        $string = new Strings ();
        $rand = "?" . $string->randomString ();
        ?>
    <link href="/css/uploadfile.css" rel="stylesheet">
<link
    href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.css"
    rel="stylesheet">
    <?php
    }
    ?>
</head>

<body>

    <?php
    if (isset ( $_GET ['c'] )) {
        $nav = strtolower ( $details ['name'] );
    }
    require_once "nav.php";
    ?>

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
                    <?php
                    if (isset ( $category )) {
                        ?>
                    <li><a href='index.php'><?php  echo ucfirst ( $details ['name'] ); ?>s</a></li>
                    <li class='active'>Raves</li>
                    <?php
                    } else {
                        ?>
                    <li class="active">Information</li>
                    <li class='active'>Raves</li>
                    <?php
                    }
                    ?>
                </ol>
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
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img src="<?php echo $r['image1']; echo $rand; ?>" width="100%"
                        alt="<?php echo $r['image1']; ?>">
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img src="<?php echo $r['image2']; echo $rand; ?>" width="100%"
                        alt="<?php echo $r['image2']; ?>">
                </div>
            </div>
            <div class="col-xs-12" style="padding-top: 20px;">

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
                        class="img-responsive"
                        src="/img/wedding-wire.jpg<?php echo $rand; ?>" width="100%"
                        alt="Wedding Wire">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" target="_blank"
                            href="http://www.weddingwire.com/reviews/saperstone-studios-reston/cdbd87c3e3540e8e.html">See
                            More</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section='Yelp'
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Yelp</span> <img class="img-responsive"
                        src="/img/yelp.jpg<?php echo $rand; ?>" width="100%" alt="Yelp">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" target="_blank"
                            href="http://www.yelp.com/biz/saperstone-studios-fairfax">See
                            More</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section='Google'
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Google</span> <img
                        class="img-responsive" src="/img/google.jpg<?php echo $rand; ?>"
                        width="100%" alt="Google">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" target="_blank"
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