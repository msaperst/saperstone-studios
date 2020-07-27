<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();
$user = new User ($sql);
$sql->disconnect();

if ($user->isLoggedIn ()) {
    header ( "Location: /user/profile.php" );
    exit ();
}

?>

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
                <h1 class="page-header text-center">Register</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li class="active">Profile</li>
                    <li class="active">Register</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Services Section -->
        <div class="form-horizontal">
            <div class="row">
                <div class="form-group has-error has-feedback">
                    <div class="col-md-2">
                        <label for="profile-username">Username:</label>
                    </div>
                    <div class="col-md-10">
                        <input type="text" class="form-control" id="profile-username"
                            placeholder="Username" required /> <span
                            class="glyphicon glyphicon-remove form-control-feedback"></span>
                        <div class="error" id="update-profile-username-message"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group has-error has-feedback">
                    <div class="col-md-2">
                        <label for="profile-password">Password:</label>
                    </div>
                    <div class="col-md-10">
                        <input type="password" class="form-control" id="profile-password"
                            placeholder="Password" required /> <span
                            class="glyphicon glyphicon-remove form-control-feedback"></span>
                        <div id="update-profile-password-message">
                            <div id="update-profile-password-strength"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group has-error has-feedback">
                    <div class="col-md-2">
                        <label for="profile-confirm-password">Confirm Password:</label>
                    </div>
                    <div class="col-md-10">
                        <input type="password" class="form-control"
                            id="profile-confirm-password" placeholder="Confirm Password"
                            required /> <span
                            class="glyphicon glyphicon-remove form-control-feedback"></span>
                        <div class="error" id="update-profile-confirm-password-message"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group has-error has-feedback">
                    <div class="col-md-2">
                        <label for="profile-firstname">First Name:</label>
                    </div>
                    <div class="col-md-10 ">
                        <input type="text" class="form-control" id="profile-firstname"
                            placeholder="First Name" required /> <span
                            class="glyphicon glyphicon-remove form-control-feedback"></span>
                        <div class="error" id="update-profile-firstname-message"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group has-error has-feedback">
                    <div class="col-md-2">
                        <label for="profile-lastname">Last Name:</label>
                    </div>
                    <div class="col-md-10">
                        <input type="text" class="form-control" id="profile-lastname"
                            placeholder="Last Name" required /> <span
                            class="glyphicon glyphicon-remove form-control-feedback"></span>
                        <div class="error" id="update-profile-lastname-message"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group has-error has-feedback">
                    <div class="col-md-2">
                        <label for="profile-email">Email:</label>
                    </div>
                    <div class="col-md-10">
                        <input type="email" class="form-control" id="profile-email"
                            placeholder="Email" required /> <span
                            class="glyphicon glyphicon-remove form-control-feedback"></span>
                        <div class="error" id="update-profile-email-message"></div>
                    </div>
                </div>
            </div>
            <span>
                <div class="checkbox">
                    <label id="profile-remember-span" ><input id="profile-remember" type="checkbox"> Remember me</label>
                </div>
            </span>
            <div class="row">
                <div id="update-profile-message" class="col-md-12"></div>
            </div>
            <div class="row">
                <div class="form-group">
                    <div class="col-md-2">
                        <button id="update-profile" type="submit"
                            class="btn btn-primary btn-success alert">
                            <em class="fa fa-floppy-o"></em> Register
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

    <script src="/js/profile.js"></script>

</body>

</html>