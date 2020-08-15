<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new CurrentUser ($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

    <?php
    $rand = "";
    if ($user->isAdmin ()) {
        $string = new Strings ();
        $rand = "?" . $string->randomString ();
        ?>
    <link href="/css/uploadfile.css" rel="stylesheet">
    <?php
    }
    ?>
    
</head>

<body>

    <?php $nav = "commercial"; require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Services</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Commercial</a></li>
                    <li><a href="details.php">Details</a></li>
                    <li class="active">Services</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-lg-12"></div>

            <div class="col-lg-12">
                <h2 class="page-header">Studio Headshots</h2>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/studio-headshots-1.jpg<?php echo $rand; ?>" width="100%"
                        alt="Studio Headshots">
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/studio-headshots-2.jpg<?php echo $rand; ?>" width="100%"
                        alt="Studio Headshots">
                </div>
            </div>
            <div class="col-xs-12" style="padding-top: 20px;">
                <p>Stand out from the crowd with an updated professional studio headshot.
                        Perfect for LinkedIn, Business cards or your website. These sessions
                        allow flexibility to choose which background fits your style.
                        'Say cheese!' is sooo outdated and will never be uttered at Saperstone
                         Studios. We have a relaxed session and strive to achieve natural
                         smiles -- you really can tell the difference!</p>
                <p><span style="color:#980f7f;">DUE TO COVID-19</span> I'll be bringing the
                        studio outside. You'll receive that same classic 'studio look' with
                        the safety of being outdoors and maintaining 6ft+ social distancing at
                        all times. Because of this change, time of day and rain do become a factor
                        but it's worth every bit of extra precaution.</p>
                <p>
                    <a href='galleries.php?w=53'>See More</a>
                </p>
            </div>

            <div class="col-lg-12">
                <h2 class="page-header">On Location Headshots</h2>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/on-location-headshots-1.jpg<?php echo $rand; ?>" width="100%"
                        alt="On Location Headshots">
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/on-location-headshots-2.jpg<?php echo $rand; ?>" width="100%"
                        alt="On Location Headshots">
                </div>
            </div>
            <div class="col-xs-12" style="padding-top: 20px;">
                <p>Bring the session outdoors to allow for more backdrops and posing.
                        Don't have a ton of time or desire to travel? I can come to you.
                        Most of these sessions are photographed in <a
                        href='/blog/post.php?p=413'>parking lots</a>, you'd be surprised
                        what I can do with a small amount of greenery!</p>
                <p>
                    <a href='galleries.php?w=54'>See More</a>
                </p>
            </div>

            <div class="col-lg-12">
                <h2 class="page-header">Company Headshots</h2>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/company-headshots-1.jpg<?php echo $rand; ?>" width="100%"
                        alt="Company Headshots">
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/company-headshots-2.jpg<?php echo $rand; ?>" width="100%"
                        alt="Company Headshots">
                </div>
            </div>
            <div class="col-xs-12" style="padding-top: 20px;">
                <p>Have a business of 3 or 3000? No problem. Get a consistent look
                        throughout all your employees images - even the make up shots.
                        We bring the studio to you and can handle nearly any amount of
                        headshots in any time period.</p>

                <p><a href='/contact.php'>Contact me today</a> for a custom quote based
                        off of the desired number of headshots and group shots needed.</p>
                <p>
                    <a href='galleries.php?w=55'>See More</a>
                </p>
            </div>

            <div class="col-lg-12">
                <h2 class="page-header">Professional Branding</h2>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/professional-branding-1.jpg<?php echo $rand; ?>" width="100%"
                        alt="Professional Branding">
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/professional-branding-2.jpg<?php echo $rand; ?>" width="100%"
                        alt="Professional Branding">
                </div>
            </div>
            <div class="col-xs-12" style="padding-top: 20px;">
                <p>Go beyond the average headshot and tell the story of your brand
                        through photography.</p>
                <p>We'll collaborate to tailor this session to your specific business
                        and showcase everything you have to offer your clients. Whether
                        you own a small, creative or corporate business, this session
                        can accommodate your needs. Together, we'll curate the perfect
                        shot list that captures words to describe who you are and what
                        your business can do for your client.</p>
                <p>
                    <a href='gallery.php?w=56'>See More</a>
                </p>
            </div>

            <div class="col-lg-12">
                <h2 class="page-header">Events</h2>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/events-1.jpg<?php echo $rand; ?>" width="100%"
                        alt="Events">
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/events-2.jpg<?php echo $rand; ?>" width="100%"
                        alt="Events">
                </div>
            </div>
            <div class="col-xs-12" style="padding-top: 20px;">
                <p>Capturing your company's events is important for any marketing
                        strategy. Whether it's an annual company meeting, a trade
                        exhibition or a holiday party, we show the important details
                        you worked so hard to create as well as people having a great
                        time and interacting with your brand.</p>
                <p>
                    <a href='gallery.php?w=57'>See More</a>
                </p>
            </div>

            <div class="col-lg-12">
                <h2 class="page-header">Photo Booth</h2>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/photo-booth-2.jpg<?php echo $rand; ?>" width="100%"
                        alt="Photo Booth">
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/photo-booth-1.jpg<?php echo $rand; ?>" width="100%"
                        alt="Photo Booth">
                </div>
            </div>
            <div class="col-xs-12" style="padding-top: 20px;">
                <p>Perfect for any event, photo booths are a fantastic way to add
                        instant fun to any occasion. Here are the basics, please
                        <a href='/contact.php'>contact us</a> for more details!</p>
                <ul>
                    <li>Open backdrop to ensure large group shots are possible</li>
                    <li>Attendant is included for setup/breakdown and is there the
                            entire time to make sure everything runs smoothly</li>
                    <li>3 photos taken per group to be put into photo strips</li>
                    <li>Strips are printed throughout the photo booth for guests
                            to come back and collect</li>
                    <li>Photo strips are customized for your event! Add a logo, the
                            date or your names for a personal touch</li>
                    <li>All individual images as well as photo strips available for
                            download via web gallery within a week of your event</li>
                    <li>Fun props included!</li>
                </ul>
                <p>
                    <a href='galleries.php?w=58'>See More</a>
                </p>
            </div>
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