<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$user = User::fromSystem();
$user->forceAdmin();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
    <link
    href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css"
    rel="stylesheet"
    integrity="sha384-YcTv91pbdpZ4It88TK5bVHIGTuPqoSi0CpPF9UA9eRicGHEJ3lpQZajpytN4rLkp"
    crossorigin="anonymous">
<link
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/css/bootstrap-dialog.min.css"
    rel="stylesheet"
    integrity="sha384-Je6/yfVqKlz/140LeySLv95dqJvjJkRTekqU15aZ9XJF5hRATXzV0sBgDZ2+lWyK"
    crossorigin="anonymous">

</head>

<body>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Manage Users</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li class="active">Administration</li>
                    <li class="active">Users</li>
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
                                    class="btn btn-xs btn-success" data-toggle="tooltip"
                                    data-placement="right" title="Add New User">
                                    <em class="fa fa-plus"></em>
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

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

    <script
        src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"
        integrity="sha384-89aj/hOsfOyfD0Ll+7f2dobA15hDyiNb8m1dJ+rJuqgrGR+PVqNU8pybx4pbF3Cc"
        crossorigin="anonymous"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/js/bootstrap-dialog.min.js"
        integrity="sha384-A1l7AkamUsoFMYzhowbdCHQanwuyYkuwH5lCZr7HrtF++vpLSuMWRoQwFaC3d3dg"
        crossorigin="anonymous"></script>
    <script src="/js/user.js?v=<?php echo @filemtime($_SERVER['DOCUMENT_ROOT'] . '/js/user.js'); ?>"></script>

</body>

</html>
