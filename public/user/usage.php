<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
new Session();
$sql = new Sql ();
$user = new User ($sql);
$user->forceAdmin();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
    <link
    href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css"
    rel="stylesheet">

</head>

<body>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Site Usage</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li class="active">Administration</li>
                    <li class="active">Usage</li>

                    <li class="no-before pull-right"><label class="checkbox-inline"><input
                            id="ignore-admins-input" type="checkbox" value=""> Ignore Admins</label>
                    </li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Time Controls -->
        <div class="row text-center">
            <p>
                <span id="over-time-usage-prev" class="btn btn-info">Prev</span> <span
                    id="over-time-usage-now" class="btn btn-info">Now</span> <span
                    id="over-time-usage-next" class="btn btn-info">Next</span>
            </p>
            <p>
                <span id="over-time-usage-year" class="btn btn-info">Year</span> <span
                    id="over-time-usage-month" class="btn btn-info">Month</span> <span
                    id="over-time-usage-week" class="btn btn-info">Week</span>
            </p>
        </div>

        <!-- Graphs Section -->
        <div class="row">
            <div class="col-md-12" id="page-usage"></div>
        </div>
        <div class="row">
            <div class="col-md-6" id="hit-usage"></div>
            <div class="col-md-6" id="unique-usage"></div>
        </div>
        <div class="row">
            <div class="col-md-6" id="device-usage"></div>
            <div class="col-md-6" id="os-usage"></div>
        </div>
        <div class="row">
            <div class="col-md-6 text-center">
                <div id="browser-usage"></div>
                <button id="browser-usage-restart" class="btn btn-info">Back</button>
            </div>
            <div class="col-md-6" id="screen-usage"></div>

        </div>
        <!-- /.row -->

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

    <script
        src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/canvasjs/1.7.0/canvasjs.min.js"></script>
    <script src="/js/usage.js"></script>
</body>

</html>
