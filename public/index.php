<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$user = User::fromSystem();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

    <?php
    $rand = "";
    if ($user->isAdmin()) {
        $rand = "?" . Strings::randomString();
        ?>
        <link href="/css/uploadfile.css" rel="stylesheet">
        <?php
    }
    ?>

</head>

<body>

<?php require_once dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

<!-- Page Content -->
<div class="page-content container">

    <!-- Services Section -->
    <div class="row">
        <div class="col-lg-12">
            <!--            <h1 class="page-header">Photography Services</h1>-->
            <div style="margin-top: 80px; margin-bottom:30px;">
                <p>If you've heard the news, my family has moved to Phoenix Arizona! We've traded in the humid heat
                    for dry and invested heavily in sunscreen. But don't count me out to photograph your beautiful
                    family in Northern Virginia! I'll still be frequenting Fairfax, Burke, Springfield, McLean,
                    Oakton, Herndon and Washington, DC as I have <a href="/b-nai-mitzvah/index.php">Mitzvahs</a> and
                    <a href="/wedding/index.php">Weddings</a> booked through 2026 and will be back in town to capture
                    those clients' milestones. <a href="https://app.acuityscheduling.com/schedule.php?owner=32987573"
                                                  target="_blank">Here</a> is my updated calendar to see when you can
                    book a Mini photography session or a full family photography session in Virginia.</p>
                <p>If you're here in Arizona I'm beyond excited to be here with you. We're an adventurous family of four
                    that loves the outdoors and hiking. I'm bubbling with excitement to document families in this
                    gorgeous new landscape and have already started scouting out the area.
                    <a href="/contact.php">Contact me</a> today for special portfolio building rates - I can't wait to
                    meet you!</p>
            </div>
        </div>
        <div class="col-md-6 col-xs-12">
            <div section='Mitzvahs'
                 class="hovereffect img-portfolio<?php if ($user->isAdmin()) {
                     echo " editable horizontal";
                 } ?>">
                <span class='preview-title'>B'nai Mitzvahs</span> <img
                        class="img-responsive" src="/img/main/mitzvahs.jpg<?php echo $rand; ?>"
                        width="100%" alt="B'nai Mitzvahs">
                <div class="overlay">
                    <br/> <br/> <br/> <a class="info" href="/b-nai-mitzvah/index.php">See
                        More</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xs-12">
            <div section='Portraits'
                 class="hovereffect img-portfolio<?php if ($user->isAdmin()) {
                     echo " editable horizontal";
                 } ?>">
                <span class='preview-title'>Portraits</span> <img
                        class="img-responsive"
                        src="/img/main/portraits.jpg<?php echo $rand; ?>" width="100%"
                        alt="Portraits">
                <div class="overlay">
                    <br/> <br/> <br/> <a class="info" href="/portrait/index.php">See
                        More</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xs-12">
            <div section='Weddings'
                 class="hovereffect img-portfolio<?php if ($user->isAdmin()) {
                     echo " editable horizontal";
                 } ?>">
                <span class='preview-title'>Weddings</span> <img
                        class="img-responsive" src="/img/main/weddings.jpg<?php echo $rand; ?>"
                        width="100%" alt="Weddings">
                <div class="overlay">
                    <br/> <br/> <br/> <a class="info" href="/wedding/index.php">See
                        More</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xs-12">
            <div section='Commercial'
                 class="hovereffect img-portfolio<?php if ($user->isAdmin()) {
                     echo " editable horizontal";
                 } ?>">
                <span class='preview-title'>Commercial</span> <img
                        class="img-responsive" src="/img/main/commercial.jpg<?php echo $rand; ?>"
                        width="100%" alt="Commercial">
                <div class="overlay">
                    <br/> <br/> <br/> <a class="info" href="/commercial/index.php">See
                        More</a>
                </div>
            </div>
        </div>
    </div>
    <!-- /.row -->

    <!-- Features Section -->
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header">Welcome to Saperstone Studios</h2>
        </div>
        <div class="col-md-12">
            <!--            <img style='max-width: 100%;' alt="Where Saperstone Studios Works"-->
            <!--                 src='img/locationbox.png' align='right'/>-->
            <p>Allow me to introduce myself! I've worked in the photography industry for over 10 years, coordinating
                photo shoots and performing high level color management and retouch for program books and billboards
                for Ringling Bros. Barnum & Bailey Circus [[So many elephants!]] as well as a local DC fashion company
                managing the retouch workflow and consistent quality control.</p>
            <p class="lead">I've started Saperstone Studios with the hope of
                providing not just photography, but also an experience.</p>
            <p>I'm located in Chandler, Arizona and provide professional photography for the Phoenix, Arizona and
                surrounding areas such as Scottsdale, Gilbert, Mesa, Tempe, Queen Creek, San Tan Valley, Chandler,
                Apache Junction, Gold Canyon, Paradise Valley, Fountain Hills and more. Saperstone Studios photographs
                countless moments for everything from Mitzvahs and weddings to family and events. Each time I pull out
                my camera I strive to provide a unique, fun photography experience where you can be, well, you! Many
                clients have let me know they didn't know what to expect but were pleasantly surprised how at ease they
                felt in front of the camera. We'll have fun capturing natural, fun moments that reflect who you are as a
                family or couple.</p>
            <p class="lead">My photography style is vibrant and colorful to reflect how you love life.</p>
            <p>I also provide retouching services that include restoring old photographs, enhancing poor wedding day
                photography, removing people/distractions, opening eyes etc. The possibilities are endless!</p>
            <p>Have a photography or retouch assignment for Saperstone Studios? <a href="/contact.php">Let us tell your
                    story</a>!
            </p>
            <p>* Based in Chandler, AZ serving the Phoenix Metropolitan area as well as Northern VA, Washington, DC,
                Maryland & Beyond</p>
        </div>
    </div>
    <!-- /.row -->

    <?php require_once dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

</div>
<!-- /.container -->

<?php
if ($user->isAdmin()) {
    ?>
    <script src="/js/edit-image.js"></script>
    <script src="/js/jquery.uploadfile.js"></script>
    <?php
}
?>

</body>

</html>
