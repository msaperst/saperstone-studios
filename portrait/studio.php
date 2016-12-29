<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once "../header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

</head>

<body>

    <?php $nav = "portrait"; require_once "../nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Home Studio</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Portraits</a></li>
                    <li><a href="details.php">Details</a></li>
                    <li class="active">Studio</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-lg-12">
                <h2 class="page-header">Welcome to the studio!</h2>
            </div>
            <div class="col-md-12">
                <p>
                    We're located in Fairfax, VA over by Fair Lakes Shopping Center.
                    While most of my sessions are photographed on location, I do
                    accomodate <a class='error' href='#'>business headshot's</a> and <a
                        href='newborn-faq.php'>newborn sessions</a> at my home studio
                    location.
                </p>
            </div>
        </div>
        <!-- /.row -->

        <?php require_once "../footer.php"; ?>

    </div>
    <!-- /.container -->

</body>

</html>