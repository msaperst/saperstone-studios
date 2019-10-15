<?php require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php"; ?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>

</head>

<body>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">About Saperstone Studios</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li class="active">Information</li>
                    <li class="active">About</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Intro Images -->
        <div class="row">
            <div class="col-md-6">
                <img width="100%" alt="About Saperstone Studios"
                    src="/img/about-1.jpg" />
                <div class="overlay"></div>
            </div>
            <div class="col-md-6">
                <img width="100%" alt="About Saperstone Studios"
                    src="/img/about-2.jpg" />
                <div class="overlay"></div>
            </div>
        </div>

        <!-- Intro Content -->
        <div class="row">
            <div class="col-md-12">
                <h2>What Makes Saperstone Studios Unique</h2>
                <p>
                    It's all in the name, Saperstone Studios Photography and <em><a
                        href='/portrait/retouch.php'>Retouch</a></em>. It's a digital age
                    and as such the art of photography has expanded beyond being able
                    to compose and take beautiful photographs. Much of the work that
                    goes into your photos is what happens after you download the images
                    to the computer.
                </p>
                <p>I've worked in the photography industry for over 10 years, coordinating
                    photo shoots and performing high level color management and retouch for
                    program books and billboards for Ringling Bros. Barnum & Bailey Circus
                    <em>[[So many elephants!]]</em> as well as a local DC fashion company
                    managing the retouch workflow and consistent quality control. <em>That's</em>
                    the expertise and knowledge I pass onto you.
                </p>
                <h2>Why Photography</h2>
                <p>People buy from people. The first page we click on a website is the “about”
                    page, we want to know more about the people behind the business before deciding
                    to work with them. Creating authentic photographs which reflect your personality
                    and brand is so important. I'll work with you to ensure key qualities that define
                    you and your company shine through in your images. And bottom line? We have FUN!
                    Contact me today to get started!
                </p>
            </div>
        </div>
        <!-- /.row -->

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

</body>

</html>
