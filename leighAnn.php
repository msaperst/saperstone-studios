<!DOCTYPE html>
<html lang="en">

<head>

    <?php 
    require_once "header.php"; 
    if ($user->isAdmin ()) {
    ?>
    <link
    href="http://hayageek.github.io/jQuery-Upload-File/4.0.10/uploadfile.css"
    rel="stylesheet">
    <?php
    }
    ?>
    
</head>

<body>

    <?php
    require_once "nav.php";
    
    // get our gallery images
    require_once "php/sql.php";
    $conn = new Sql ();
    $conn->connect ();
    $sql = "SELECT gallery_images.* FROM `gallery_images` JOIN `galleries` ON gallery_images.gallery = galleries.id WHERE galleries.name = 'leigh-ann';";
    $result = mysqli_query ( $conn->db, $sql );
    $images = array ();
    while ( $row = mysqli_fetch_assoc ( $result ) ) {
        $images [] = $row;
    }
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
                <img src='img/leigh-ann.jpg' align='right'
                    style='margin: 0px 0px 20px 20px;' />
                <p>I am a lifestyle photographer based in Northern Virginia and specialize in wedding and family photography as well as retouch. I am based in Fairfax, Virginia but available for travel anywhere.</p>
                <p>Travel is a personal favorite of mine and my photo albums include images of me and my husband Max being complete goofballs together. Our photos show us scuba-diving, white water rafting, rolling down hills in hamster balls and cuddling koala's. This sense of adventure carries over into my photography and I strive to provide more than a photo session but an experience. Unique, out-of-the-box creative thinking to create memories and a custom experience for every client is what it's all about. I'm not your `old-school` photographer. I'll ask you to play and I'll ask you to be silly and I do my best to make you laugh (often at my own expense) <em class='fa fa-smile-o'></em>. I'm always looking for something new and completely different... the more quirky, the better. I want my time with you to show a vibrancy, to be creative and fun! I know I've done a job well done when you refer me to your friends and family and it is oh-so-appreciated!</p>
                <p>Not only do I have a degree in photography, but back in my corporate days, I worked for Ringling Bros. circus! My life was filled with clowns, elephants and acrobats (oh my?!) I was the lead on color management and retouch in their photo services department for many years making sure all their billboards, program books and marketing needs were in tip top shape. That's the kind of experience I pass on to my clients. It's a little known fact that a lot of hours go on behind the scenes to make your images what they are. Don't believe a single image you see in magazines <em class='fa fa-smile-o'></em></p>
                <p>I accept a limited number of weddings and portrait sessions each year so <a href='/contact.php'>contact me</a> today to reserve a date!</p>
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
                        foreach ( $images as $num => $image ) {
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
                        foreach ( $images as $num => $image ) {
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
                    <?php if ($user->isAdmin ()) { ?>
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
        require_once "footer.php";
        $conn->disconnect ();
        ?>

    </div>
    <!-- /.container -->
    
</body>

</html>