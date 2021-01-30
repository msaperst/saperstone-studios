
<!-- Messages -->
<?php
$height_offset = 10;
$DOCUMENT_ROOT = "DOCUMENT_ROOT";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$navSql = new Sql ();
$navUser = User::fromSystem();
$query = "SELECT * FROM `announcements` WHERE NOW() BETWEEN `start` AND `end`;";
if ($navSql->getRowCount ( $query )) {
    ?>
<div id='displayed-alerts'
    style='position: fixed; width: 100%; top: -20px; z-index: 10000; font-size:large; font-weight:bold; text-align:center'>
    <?php
}
foreach ( $navSql->getRows( $query ) as $row ) {
    if (! isset ( $_COOKIE ["announcement-" . $row ['id']] ) && Strings::startsWith($_SERVER['REQUEST_URI'],$row['path'])) {
           $height_offset += 60;
        ?>
<div
        class="alert alert-warning<?php if ($row['dismissible']) { echo " alert-dismissable fade in"; } ?>">
        <?php if ($row['dismissible']) { echo "<a id='announcement-" . $row['id'] . "' href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>"; } ?>
        <strong><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></strong> <?php echo $row['message']; ?>
</div>
<?php
    }
}
if ($navSql->getRowCount ( $query )) {
    ?>
    </div>
<?php
    $navSql->disconnect();
}
?>
<!-- Navigation -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation" style="border-top-width:<?php echo $height_offset; ?>px">
    <div class="container">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse"
                data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span> <span
                    class="icon-bar"></span> <span class="icon-bar"></span> <span
                    class="icon-bar"></span>
            </button>
            <a id="nav-logo-link" class="nav-logo-link navbar-brand" href="/"><img
                class="nav-logo" id="nav-logo" src="/img/2014websitelogo250px.png"
                alt="Saperstone Studios Logo"></a> <a id="nav-logo-link-2"
                class="nav-logo-link navbar-brand" href="/"><img class="nav-logo"
                id="nav-logo-2" src="/img/2014websitelogo250px.png"
                alt="Saperstone Studios Logo"></a>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->

        <?php
        switch ($nav) {
            case "portrait" :
                require_once dirname ( $_SERVER [$DOCUMENT_ROOT] ) . DIRECTORY_SEPARATOR . "templates/nav-portrait.php";
                break;
            case "wedding" :
                require_once dirname ( $_SERVER [$DOCUMENT_ROOT] ) . DIRECTORY_SEPARATOR . "templates/nav-wedding.php";
                break;
            case "commercial" :
                require_once dirname ( $_SERVER [$DOCUMENT_ROOT] ) . DIRECTORY_SEPARATOR . "templates/nav-commercial.php";
                break;
            default :
                require_once dirname ( $_SERVER [$DOCUMENT_ROOT] ) . DIRECTORY_SEPARATOR . "templates/nav-main.php";
        }
        ?>

                  <?php
                if (! $navUser->isLoggedIn ()) {
                    ?>
                <li><a id="login-menu-item" href="javascript:void(0);" data-toggle="modal"
            data-target="#login-modal"><em class="fa fa-sign-in"></em> Login</a></li>
                <?php
                } elseif ($navUser->isAdmin ()) {
                    ?>
                <li class="dropdown"><a href="javascript:void(0);"
            class="dropdown-toggle" data-toggle="dropdown"><?php echo $navUser->getUsername(); ?><strong
                class="caret"></strong></a>
            <ul class="dropdown-menu">
                <li><a href="/user/users.php">Manage Users</a></li>
                <li><a href="/user/index.php">Manage Albums</a></li>
                <li><a href="/user/products.php">Manage Products</a></li>
                <li><a href="/user/contracts.php">Manage Contracts</a></li>
                <li><a href="/user/usage.php">View Usage</a></li>
                <li><a href="/user/profile.php">Manage Profile</a></li>
                <li><a id='logout-button' href="javascript:void(0);"><em
                        class="fa fa-sign-out"></em> Logout</a></li>
            </ul></li>
                   <?php
                } else {
                    ?>
                <li class="dropdown"><a href="javascript:void(0);"
            class="dropdown-toggle" data-toggle="dropdown"><?php echo $navUser->getUsername(); ?><strong
                class="caret"></strong></a>
            <ul class="dropdown-menu">
                <li><a href="/user/index.php">View Albums</a></li>
                <li><a href="/user/profile.php">Manage Profile</a></li>
                <li><a id='logout-button' href="javascript:void(0);"><em
                        class="fa fa-sign-out"></em> Logout</a></li>
            </ul></li>
                   <?php
                }
                ?>

            </ul>
    </div>
    <!-- /.navbar-collapse -->
    </div>
    <!-- /.container -->
</nav>
<!-- Div to fix spacing -->
<div id="nav-spacer"></div>

<div id="login-modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-sm">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Login to Your Account</h4>
            </div>
            <form>
                <div class="modal-body">
                    <input class="form-control" id="login-user" type="text" name="user"
                        placeholder="Username" /> <input class="form-control"
                        id="login-pass" type="password" name="pass" placeholder="Password" />
                    <span>
                        <div class="checkbox">
                            <label id="login-remember-span" ><input id="login-remember" type="checkbox"> Remember me</label>
                        </div>
                    </span>
                    <div class="login-help">
                        <a href="/register.php">Register</a> - <a
                            id="login-forgot-password" href="javascript:void(0);">Forgot
                            Username/Password</a>
                    </div>
                </div>
            </form>
            <div class="modal-footer">
                <button id="login-submit" type="submit" class="btn btn-primary">Login</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="forgot-password-modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-sm">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Forgotten Credentials</h4>
            </div>
            <div class="modal-body">
                <input class="form-control" id="forgot-password-email" type="email"
                    name="email" placeholder="Email" />
                <div id="forgot-password-instructions">
                    A reset code will be sent to your email address. If you already
                    have one, <a id="forgot-password-prev-code"
                        href="javascript:void(0);">click here</a>
                </div>
                <input class="form-control" id="forgot-password-code" type="text"
                    name="code" placeholder="Reset Code" style="display: none;" /> <input
                    class="form-control" id="forgot-password-new-password"
                    type="password" name="pass" placeholder="Password"
                    style="display: none;" /> <input class="form-control"
                    id="forgot-password-new-password-confirm" type="password"
                    name="pass-conf" placeholder="Re-type Password"
                    style="display: none;" />
                <span>
                    <div class="checkbox">
                        <label id="forgot-password-remember-span" style="display: none;"><input id="forgot-password-remember" type="checkbox"> Remember me</label>
                    </div>
                </span>
            </div>
            <div class="modal-footer">
                <button id="forgot-password-submit" type="submit"
                    class="btn btn-primary">Send Code</button>
                <button id="forgot-password-reset-password" type="submit"
                    class="btn btn-primary" style="display: none;">Reset Password</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

</div>
</div>
</div>