<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$user = User::fromSystem();
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <?php
    require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/header.php";
    if ($user->isAdmin()) {
        ?>
        <link href="/css/uploadfile.css" rel="stylesheet">
        <?php
    }
    ?>

</head>

<body>

<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/nav.php";

// get our gallery images
$sql = new Sql ();
$images = $sql->getRows("SELECT gallery_images.* FROM `gallery_images` JOIN `galleries` ON gallery_images.gallery = galleries.id WHERE galleries.title = 'Leigh Ann';");
$sql->disconnect();
?>

<!-- Page Content -->
<div class="page-content container">

    <!-- Page Heading/Breadcrumbs -->
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header text-center">Meet Leigh Ann</h1>
            <ol class="breadcrumb">
                <li><a href="/">Home</a></li>
                <li class="active">Information</li>
                <li class="active">Leigh Ann</li>
            </ol>
        </div>
    </div>
    <!-- /.row -->

    <!-- About Leigh Ann Section -->
    <div class="row">
        <div class="col-md-12">
            <img src='img/leigh-ann.jpg' alt="Leigh Ann" align='right'
                 style='margin: 0px 0px 20px 20px;'/>
            <p>Hey! My name is Leigh Ann and I'm the Wham Bam Boss Lady at Saperstone Studios! We've recently
                moved to the East Valley of Arizona and I need friends! When you reach out, be sure to tell me:
                <ul>
                    <li>Your favorite taco joint (the food here is so amazong!)</li>
                    <li>Favorite hike when it's not a bah gillian degrees out - that's the technical term</li>
                </ul>
            </p>
            <h4>The short list</h4>
            <ul>
                <li>Addicted to HomeGoods (my second home)</li>
                <li>Just got a Cricut and I'm overwhelmed</li>
                <li>Will sabotage myself in any board game if it means I can beat my husband</li>
                <li>You can never travel enough</li>
                <li>I'm probably the only person in the world who doesn't drink coffee</li>
            </ul>
            <h4>The long list</h4>
            <p>My first son made me a mother but my second son made me a #boymom. I've officially
                joined the hashtag ranks as a mother of wild ones and I wouldn't trade my crazy goobers
                for the world <3 When I grow up, I want to be just like them and view the world with wonder.
                every. day. If you follow me on <a href='https://www.facebook.com/SaperstoneStudios'
                                                   target='_blank'>Facebook</a> and <a
                        href='https://instagram.com/saperstonestudios'
                        target='_blank'>Instagram</a> you'll see their adorable faces pop up frequently. (Sorry, not
                sorry) ;)</p>
            <p>Pre children you'd find me and my husband traveling the world, playing Settlers of Catan (we're
                only competitive with each other) and cracking poop jokes. While we do less of the former and
                more of the latter (soooo many poop jokes with 3 boys in the house) we do it all together and
                I love him like a unicorn loves prancing on rainbows :D</p>
            <h4>A little about my photography</h4>
            <p>I'll ask you to play and I'll ask you to be silly and I do my best to make you laugh (often at
                my own expense). I want my time with you to show a vibrancy, to be creative and fun! I know I've
                done a job well done when you refer me to your friends and family and it is oh-so-appreciated!</p>
            <p>Not only do I have a degree in photography, but back in my corporate days, I worked for Ringling
                Bros. circus! My life was filled with clowns, elephants and acrobats (oh my?!) I was the lead on
                color management and retouch in their photo services department for many years making sure all
                their billboards, program books and marketing needs were in tip top shape. That's the kind of
                experience I pass on to my clients. It's a little known fact that a lot of hours go on behind the
                scenes to make your images what they are. Don't believe a single image you see in magazines.</p>
            <p>I accept a limited number of weddings and portrait sessions each
                year so <a href='/contact.php'>contact me</a> today to reserve a
                date!</p>
        </div>
    </div>
    <!-- /.row -->

    <!-- Main Content -->
    <div class="row" style="margin-bottom: 30px;">
        <!-- Content Column -->
        <div class="col-md-12">
            <!-- Carousel -->
            <div id="leighAnnCarousel"
                 class="carousel slide carousel-three-by-two">
                <!-- Indicators -->
                <ol class="carousel-indicators">
                    <?php
                    foreach ($images as $num => $image) {
                        $class = "";
                        if ($num == 0) {
                            $class = " class='active'";
                        }
                        echo "<li data-target='#leighAnnCarousel' data-slide-to='$num'$class></li>";
                    }
                    ?>
                </ol>

                <!-- Wrapper for slides -->
                <div class="carousel-inner">
                    <?php
                    foreach ($images as $num => $image) {
                        $active_class = "";
                        if ($num == 0) {
                            $active_class = " active";
                        }
                        echo "<div class='item$active_class'>";
                        echo "    <div class='contain'";
                        echo "        style=\"background-image: url('" . $image ['location'] . "');\"></div>";
                        echo "    <div class='carousel-caption'>";
                        echo "        <h2>" . $image ['caption'] . "</h2>";
                        echo "    </div>";
                        echo "</div>";
                    }
                    ?>
                </div>

                <!-- Controls -->
                <a class="left carousel-control" href="#leighAnnCarousel"
                   data-slide="prev"> <span class="icon-prev"></span>
                </a> <a class="right carousel-control" href="#leighAnnCarousel"
                        data-slide="next"> <span class="icon-next"></span>
                </a>
                <?php if ($user->isAdmin()) { ?>
                    <span
                            style="position: absolute; bottom: 0px; right: 0px; padding: 5px;">
                        <button class="ajax-file-upload"
                                onclick="location.href='/portrait/galleries.php?w=0'"
                                style="position: relative; overflow: hidden; cursor: pointer;">
                            <i class="fa fa-pencil-square-o"></i> Edit These Images
                        </button>
                    </span>
                <?php } ?>
            </div>
        </div>
    </div>

    <?php
    require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/footer.php";
    ?>

</div>
<!-- /.container -->

</body>

</html>