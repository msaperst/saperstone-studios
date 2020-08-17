<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = User::fromSystem();
$user->forceAdmin();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
    <link
    href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css"
    rel="stylesheet">
<link href="/css/uploadfile.css" rel="stylesheet">

</head>

<body>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <?php
                if ($user->isAdmin ()) {
                    ?>
                <h1 class="page-header text-center">Manage Albums</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li class="active">Administration</li>
                    <li class="active">Albums</li>
                </ol>
                <?php
                } else {
                    ?>
                <h1 class="page-header text-center">View Albums</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li class="active">Profile</li>
                    <li class="active">Albums</li>
                </ol>
                <?php
                }
                ?>
            </div>
        </div>
        <!-- /.row -->

        <!-- Services Section -->
        <div class="row">
            <div class="col-lg-12">
                <?php
                if (! $user->isAdmin ()) {
                    ?>
                    <div id="add-album-div"
                    class="form-group form-inline text-center">
                    <label for="album-code">Add Album:</label> <input type="text"
                        class="form-control" id="album-code" placeholder="Album Code" />
                    <button class="btn btn-success" id="album-code-add">
                        <em class="fa fa-plus-circle"></em>
                    </button>
                </div>
                <div id="add-album-error" class="error"></div>
                <?php
                }
                ?>
                <table id="albums" class="display"
                    style="width: 100%; border-spacing: 0px;">
                    <thead>
                        <tr>
                            <?php
                            if ($user->isAdmin () || $user->getRole () == "uploader") {
                                ?>
                            <th style="width: 55px;">
                                <button id="add-album-btn" type="button"
                                    class="btn btn-xs btn-success" data-toggle="tooltip"
                                    data-placement="right" title="Add New Album">
                                    <em class="fa fa-plus"></em>
                                </button>
                            </th>
                            <?php
                            }
                            ?>
                            <th>Album Name</th>
                            <th>Album Description</th>
                            <th>Album Date</th>
                            <th>Images</th>
                            <?php
                            if ($user->isAdmin ()) {
                                ?>
                            <th>Last Accessed</th>
                            <th>Access Code</th>
                            <?php
                            }
                            ?>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <!-- /.row -->

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

    <script
        src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
    <script src="/js/jquery.uploadfile.js"></script>
    <?php
    if ($user->isAdmin ()) {
        ?>
    <script src="/js/albums-admin.js"></script>
    <script src="/js/album-admin.js"></script>
    <?php
    } elseif ($user->getRole () == "uploader") {
        ?>
    <script src="/js/albums-uploader.js"></script>
    <?php
    } else {
        ?>
    <script src="/js/albums.js"></script>
    <?php
    }
    ?>

</body>

</html>
