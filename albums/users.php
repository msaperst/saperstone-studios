<?php
session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php"; $user = new user();

if ($user->getRole () != "admin") {
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
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/css/bootstrap-dialog.min.css"
    rel="stylesheet">

</head>

<body>

    <?php require_once "../nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Manage Users</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li class="active">Administration</li>
                    <li class="active">Manage Users</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Services Section -->
        <div class="row">
            <div class="col-lg-12">
                <table id="users" class="display" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>
                                <button id="add-user-btn" type="button"
                                    class="btn btn-xs btn-success">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </th>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Active</th>
                            <th>Last Logged In</th>
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
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/js/bootstrap-dialog.min.js"></script>
    <script src="/js/user.js"></script>

</body>

</html>