<?php
session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$user = new User ();

if (! $user->isLoggedIn ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    include dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/errors/401.php";
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
                <h1 class="page-header text-center">Manage Profile</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li class="active">Profile</li>
                    <li class="active">Manage</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Services Section -->
        <div class="form-horizontal">
            <div class="row">
                <div class="form-group">
                    <div class="col-md-2">
                        <label for="profile-username">Username:</label>
                    </div>
                    <div class="col-md-10">
                        <input type="text" class="form-control" id="profile-username"
                            placeholder="Username" value="<?php echo $user->getUser(); ?>"
                            required disabled />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group has-feedback">
                    <div class="col-md-2">
                        <label for="profile-current-password">Current Password:</label>
                    </div>
                    <div class="col-md-10">
                        <input type="password" class="form-control"
                            id="profile-current-password" placeholder="Current Password" /> <span
                            class="glyphicon form-control-feedback"></span>
                        <div class="error" id="update-profile-current-password-message"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group has-feedback">
                    <div class="col-md-2">
                        <label for="profile-password">New Password:</label>
                    </div>
                    <div class="col-md-10">
                        <input type="password" class="form-control" id="profile-password"
                            placeholder="New Password" /> <span
                            class="glyphicon form-control-feedback"></span>
                        <div id="update-profile-password-message">
                            <div id="update-profile-password-strength"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group has-feedback">
                    <div class="col-md-2">
                        <label for="profile-confirm-password">Confirm New Password:</label>
                    </div>
                    <div class="col-md-10">
                        <input type="password" class="form-control"
                            id="profile-confirm-password" placeholder="Confirm New Password" />
                        <span class="glyphicon form-control-feedback"></span>
                        <div class="error" id="update-profile-confirm-password-message"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group has-success has-feedback">
                    <div class="col-md-2">
                        <label for="profile-firstname">First Name:</label>
                    </div>
                    <div class="col-md-10 ">
                        <input type="text" class="form-control" id="profile-firstname"
                            placeholder="First Name" required
                            value="<?php echo $user->getFirstName(); ?>" /> <span
                            class="glyphicon glyphicon-ok form-control-feedback"></span>
                        <div class="error" id="update-profile-firstname-message"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group has-success has-feedback">
                    <div class="col-md-2">
                        <label for="profile-lastname">Last Name:</label>
                    </div>
                    <div class="col-md-10">
                        <input type="text" class="form-control" id="profile-lastname"
                            placeholder="Last Name" required
                            value="<?php echo $user->getLastName(); ?>" /> <span
                            class="glyphicon glyphicon-ok form-control-feedback"></span>
                        <div class="error" id="update-profile-lastname-message"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group has-success has-feedback">
                    <div class="col-md-2">
                        <label for="profile-email">Email:</label>
                    </div>
                    <div class="col-md-10">
                        <input type="email" class="form-control" id="profile-email"
                            placeholder="Email" required
                            value="<?php echo $user->getEmail(); ?>" /> <span
                            class="glyphicon glyphicon-ok form-control-feedback"></span>
                        <div class="error" id="update-profile-email-message"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div id="update-profile-message" class="col-md-12"></div>
            </div>
            <div class="row">
                <div class="form-group">
                    <div class="col-md-2">
                        <button id="update-profile" type="submit"
                            class="btn btn-primary btn-success alert">
                            <em class="fa fa-floppy-o"></em> Update
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