<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once "../header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

    <?php
    $rand = "";
    if ($user->isAdmin ()) {
        require_once '../php/strings.php';
        $string = new Strings ();
        $rand = "?" . $string->randomString ();
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

    <?php $nav = "wedding"; require_once "../nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Wedding Session Details</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Weddings</a></li>
                    <li class="active">Details</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-lg-12">
                <h2 class="page-header">Congrats on your engagement!</h2>
            </div>
            <div class="col-md-12">
                <p>
                    I love, love, love engagement sessions and getting to know my
                    clients without the rush and hurry on your big day. That's why I
                    offer a complimentary engagement session in all of my wedding
                    packages. It offers the opportunity for you to be comfortable in
                    front of the lens and to see how I work. I get the chance to see
                    you two as a couple and what makes you smile, laugh and get some
                    wonderful natural reactions that are candid and <em>you.</em>
                </p>
                <p>Interested in just the engagement session without the wedding
                    photography? That's alright too! <a href='/contact.php'>Contact me</a> today for more
                    information.</p>
            </div>
            <div class="col-lg-12">
                <h2 class="page-header">You said Yes!</h2>
            </div>
            <div class="col-md-12">
                <p>Congrats on stepping into this next chapter of your lives as a
                    force to be reckoned with. Nothing can take you guys down,
                    including the wedding planning process!</p>
                <p>
                    2016 wedding packages start at <span class='error'>??$2,700??</span>
                    but are customizable to fit your needs for your day. All of my
                    packages come with photography time on your wedding day, digital
                    files on a USB with personal print release as well as an engagement
                    session. I love being able to spend time with my couples at an
                    engagement shoot before the rush of the big day. Who do you spend
                    the most time with on your wedding day? Most likely it's your
                    photographer so spending some time together to see how I work and
                    become comfortable in front of the lens can make a world of
                    difference :)
                </p>
            </div>
            <div class="col-lg-12">
                <h2 class="page-header">The Wedding Experience</h2>
            </div>
            <div class="col-md-12">
                <p>Im a self proclaimed photo ninja. And when it comes down to it,
                    haven't you always wanted to have a ninja at your wedding? ;) I
                    have a photo journalistic style which means for the most part I
                    stay out of the way and capture pure emotion as it happens. That
                    being said, there are instances when I do step up and give
                    direction to make sure your photos tell a story in the best way
                    possible.</p>
                <p>Often when a couple is in front of the lens having their
                    portraits taken they need a bit of direction on what to do. No
                    fear, that's why you've hired me. It's my job to pose you both in a
                    way that's natural as well as flattering. To ensure that your posed
                    moments are still ones that are candid, fun and personable, I'll
                    have you interact with each other...simple, right? But it's just oh
                    so effective in making you both smile. :)</p>
                <p>There are other times when the light and/or composition is better
                    in a certain room, I'll be on the look out for harsh shadows and
                    and cluttered backgrounds and be sure to direct you when needed.</p>
            </div>
            <div class="col-lg-12">
                <h2 class="page-header">Night Photography</h2>
            </div>
            <div class="col-md-12">
                <p>Oh.my. how I adore night photography. It's such a quiet time as
                    you step away from the hustle of the party for a moment just for
                    the two of you. I highly suggest working this into the timeline for
                    your day if possible. It can take as little as 20 minutes but with
                    more time allowed, you'll get more variations and angles.</p>
            </div>
            <div class="col-lg-12">
                <h2 class="page-header">Why Choose Us?</h2>
            </div>
            <div class="col-md-12">
                <p>
                    Curious why you should choose Saperstone Studios over everyone else
                    out there? Check out more about <a href="/about.php">how we
                        differentiate ourselves.</a>
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Surprise Proposal"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Bellies and Babies</span> <img
                        class="img-responsive" src="img/surprise.jpg<?php echo $rand; ?>" alt="">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="surprise.php">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Engagement"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Newborn FAQ</span> <img
                        class="img-responsive" src="img/engagement.jpg<?php echo $rand; ?>" alt="">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="engagement.php">See
                            More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Weddings"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Home Studio</span> <img
                        class="img-responsive" src="img/wedding.jpg<?php echo $rand; ?>" alt="">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="weddings.php">See More</a>
                    </div>
                </div>
            </div>
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