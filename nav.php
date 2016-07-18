<!-- Navigation -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
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
                class="nav-logo" id="nav-logo" src="/img/2014websitelogo250px.png"></a>
            <a id="nav-logo-link-2" class="nav-logo-link navbar-brand" href="/"><img
                class="nav-logo" id="nav-logo-2" src="/img/2014websitelogo250px.png"></a>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        
        <?php
            switch( $nav ) {
                case "portrait":
                    require_once "nav-portrait.php";
                    break;
                default:
                    require_once "nav-main.php";
            }
            ?>
            
                  <?php
                    if (!$user->isLoggedIn ()) {
                ?>
                <li><a href="javascript:void(0);" data-toggle="modal" data-target="#login-modal"><em class="fa fa-sign-in"></em> Login</a></li>
                <?php
                    } elseif ($user->getRole () == "admin") {
                ?>
                <li class="dropdown"><a href="javascript:void(0);"
                    class="dropdown-toggle" data-toggle="dropdown"><?php echo $user->getUser (); ?><strong
                        class="caret"></strong></a>
                    <ul class="dropdown-menu">
                        <li><a href="/albums/users.php">Manage Users</a></li>
                        <li><a href="/albums/index.php">Manage Albums</a></li>
                        <li><a href="/albums/products.php">Manage Products</a></li>
                        <li><a href="#">Manage Posts</a></li>
                        <li><a href="#">Write New Post</a></li>
                        <li><a href="#">Manage Profile</a></li>
                        <li><a id='logout-button' href="javascript:void(0);"><em class="fa fa-sign-out"></em> Logout</a></li>
                    </ul></li>
                   <?php
                    } else {
                ?>
                <li class="dropdown"><a href="javascript:void(0);"
                    class="dropdown-toggle" data-toggle="dropdown"><?php echo $user->getUser (); ?><strong
                        class="caret"></strong></a>
                    <ul class="dropdown-menu">
                        <li><a href="/albums/index.php">View Albums</a></li>
                        <li><a href="#">Manage Profile</a></li>
                        <li><a id='logout-button' href="javascript:void(0);"><em class="fa fa-sign-out"></em> Logout</a></li>
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

<div class="modal fade" id="login-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="loginmodal-container">
            <h2>Login to Your Account</h2><br>
            <input id="login-user" type="text" name="user" placeholder="Username" />
            <input id="login-pass" type="password" name="pass" placeholder="Password" />
            <div id="login-error" class="error"></div>
            <div id="login-message" class="success"></div>
            <span>
                <div class="checkbox">
                    <label><input type="checkbox"> Remember me</label>
                  </div>
            </span>
            <button id="login-submit" type="submit" class="btn btn-primary">Login</button>
<!--             <span>Remember Me <input id="login-remember" type="checkbox" name="remember" /></span> -->
            <div class="login-help">
                <a href="#">Register</a> - <a id="login-forgot-password" href="javascript:void(0);">Forgot Username/Password</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="forgot-password-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="loginmodal-container">
            <h2>Forgotten Credentials</h2><br>
            <input id="forgot-password-email" type="email" name="email" placeholder="Email" />
            <div id="forgot-password-instructions">A reset code will be sent to your email address. If you already have one, <a id="forgot-password-prev-code" href="javascript:void(0);">click here</a></div>
            <div id="forgot-password-error" class="error"></div>
            <div id="forgot-password-message" class="success"></div>
            <input id="forgot-password-code" type="text" name="code" placeholder="Reset Code" style="display:none;" />
            <input id="forgot-password-new-password" type="password" name="pass" placeholder="Password" style="display:none;" />
            <input id="forgot-password-new-password-confirm" type="password" name="pass-conf" placeholder="Re-type Password" style="display:none;" />
            <button id="forgot-password-submit" type="submit" class="btn btn-primary">Send Code</button>
            <button id="forgot-password-reset-password" type="submit" class="btn btn-primary" style="display:none;">Reset Password</button>
        </div>
    </div>
</div>