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

<?php $nav = "b'nai mitzvah";
require_once dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

<!-- Page Content -->
<div class="page-content container">

    <!-- Page Heading/Breadcrumbs -->
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header text-center">Session Information</h1>
            <ol class="breadcrumb">
                <li><a href="/">Home</a></li>
                <li><a href="index.php">B'nai Mitzvahs</a></li>
                <li><a href="details.php">Details</a></li>
                <li class="active">Sessions</li>
            </ol>
        </div>
    </div>
    <!-- /.row -->

    <!-- Features Section -->
    <div class="row">
        <div class="col-lg-12">
            <p>Saperstone Studios is a professional Arizona mitzvah photography studio
                based in Chandler, Arizona. I specialize in bar mitzvah and bat mitzvah
                photography throughout the East Valley and Scottsdale, capturing each
                celebration with a vibrant, fun, and authentic style that highlights the
                joy, emotion, and energy of your simcha.</p>
            <p>I proudly photograph at many Arizona synagogues and temples, including:</p>
            <ul>
                <li>Temple Beth Sholom of the East Valley – Chandler, AZ</li>
                <li>Temple Emanuel of Tempe – Tempe, AZ</li>
                <li>Congregation Beth Israel – Scottsdale, AZ</li>
                <li>The New Shul – Scottsdale, AZ</li>
                <li>Temple Kol Ami – Scottsdale, AZ</li>
                <li>Har Zion Congregation – Scottsdale, AZ</li>
                <li>Temple Chai – Phoenix (near Scottsdale)</li>
            </ul>
            <p>Whether you’re planning a traditional bar mitzvah service, a modern bat mitzvah party,
                or a family celebration filled with laughter and dancing, I bring years of experience
                photographing mitzvahs across Arizona’s Jewish community.</p>
            <p>As a proud member of Temple Emanuel of Tempe, I understand the traditions, details, and
                family moments that make each mitzvah unique. From portraits and Torah readings to the
                hora and heartfelt speeches, I capture every special memory with care and creativity.</p>

            <p>If you’re searching for an experienced Scottsdale or East Valley bar/bat mitzvah
                photographer, Saperstone Studios offers professional storytelling,
                attention to detail, and images you’ll cherish for a lifetime.</p>
        </div>

        <div class="col-lg-12">
            <h2 class="page-header">Bimah Session</h2>
        </div>
        <div class="col-xs-6">
            <div class="<?php if ($user->isAdmin()) {
                echo " editable horizontal";
            } ?>">
                <img src="img/bimah-1.jpg<?php echo $rand; ?>" width="100%"
                     alt="Bimah">
            </div>
        </div>
        <div class="col-xs-6">
            <div class="<?php if ($user->isAdmin()) {
                echo " editable horizontal";
            } ?>">
                <img src="img/bimah-2.jpg<?php echo $rand; ?>" width="100%"
                     alt="Bimah">
            </div>
        </div>
        <div class="col-xs-12" style="padding-top: 20px;">
            <p>Can't photograph the services during your mitzvah event? That's OK!
                During a Bimah session I bring the studio to your synagogue. Professional
                lighting and the time to casually capture these have to have moments make
                for great memories. This session can include your immediate family or,
                if you happen to have family in from out of town, they're more than welcome
                to join in on the fun. We'll capture mitzvah portraits around the synagogue
                as well as reading from the Torah and family formals.</p>
            <ul>
                <li>Portraits of the mitzvah and family!</li>
                <li>1 hour session at your synagogue with professional lighting</li>
                <li>50+ fully edited images on USB with personal print release</li>
            </ul>
            <p>
                <a href='galleries.php?w=73'>See More</a>
            </p>
        </div>

        <div class="col-lg-12">
            <h2 class="page-header">Pre Mitzvah Session</h2>
        </div>
        <div class="col-xs-6">
            <div class="<?php if ($user->isAdmin()) {
                echo " editable horizontal";
            } ?>">
                <img src="img/pre-mitzvah-1.jpg<?php echo $rand; ?>" width="100%"
                     alt="Pre-Mitzvah">
            </div>
        </div>
        <div class="col-xs-6">
            <div class="<?php if ($user->isAdmin()) {
                echo " editable horizontal";
            } ?>">
                <img src="img/pre-mitzvah-2.jpg<?php echo $rand; ?>" width="100%"
                     alt="Pre-Mitzvah">
            </div>
        </div>
        <div class="col-xs-12" style="padding-top: 20px;">
            <p>Create a time capsule of who your child is here and now with a pre-mitzvah
                session. We'll pick a location that fits their style and incorporate their
                hobbies, loves and ambitions into a photo shoot to showcase who they are.
                Family is welcome to join this session as well!</p>
            <ul>
                <li>Portraits of the mitzvah and family!</li>
                <li>1 hour session on location within the Phoenix metro area</li>
                <li>50+ fully edited images on USB with personal print release</li>
            </ul>
        </div>

        <div class="col-lg-12">
            <h2 class="page-header">Services</h2>
        </div>
        <div class="col-xs-6">
            <div class="<?php if ($user->isAdmin()) {
                echo " editable horizontal";
            } ?>">
                <img src="img/services-1.jpg<?php echo $rand; ?>" width="100%"
                     alt="Services">
            </div>
        </div>
        <div class="col-xs-6">
            <div class="<?php if ($user->isAdmin()) {
                echo " editable horizontal";
            } ?>">
                <img src="img/services-2.jpg<?php echo $rand; ?>" width="100%"
                     alt="Services">
            </div>
        </div>
        <div class="col-xs-12" style="padding-top: 20px;">
            <p>Are you able to photograph during services? That's great! I take
                care to make sure I respect your synagogues photography rules
                while capturing your mitzvah rockin' it. This would be a part
                of your hourly coverage of your mitzvah event and include all edited
                images on USB with personal print release.</p>
            <p>
                <a href='galleries.php?w=74'>See More</a>
            </p>
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
