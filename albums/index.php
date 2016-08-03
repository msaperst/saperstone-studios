<?php
session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
$user = new user ();

if (! $user->isLoggedIn ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    include "../errors/401.php";
    exit ();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once "../header.php"; ?>
    <link
    href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css"
    rel="stylesheet">
<link
    href="http://hayageek.github.io/jQuery-Upload-File/4.0.10/uploadfile.css"
    rel="stylesheet">

</head>

<body>

    <?php require_once "../nav.php"; ?>

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
                    <li class="active">Manage Albums</li>
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
                if ($user->getRole () != "admin") {
                    ?>
                    <div id="add-album-div"
                    class="form-group form-inline text-center">
                    <label for="album-code">Add Album:</label> <input type="text"
                        class="form-control" id="album-code" placeholder="Album Code" />
                    <button class="btn btn-success" id="album-code-add">
                        <em class="fa fa-plus-circle"></em>
                
                </div>
                <div id="add-album-error" class="error"></div>
                <?php
                }
                ?>
                <table id="albums" class="display" cellspacing="0"
                    width="100%">
                    <thead>
                        <tr>
                            <?php
                            if ($user->isAdmin () || $user->getRole () == "uploader") {
                                ?>
                            <th>
                                <button id="add-album-btn" type="button"
                                    class="btn btn-xs btn-success">
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

        <?php require_once "../footer.php"; ?>

    </div>
    <!-- /.container -->

    <script
        src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
    <script src="/js/jquery.uploadfile.js"></script>
    <?php
    if ($user->isAdmin ()) {
        ?>
    <script src="/js/albums-admin.js"></script>
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