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
				class="nav-logo" id="nav-logo" src="img/2014websitelogo250px.png"></a>
			<a id="nav-logo-link-2" class="nav-logo-link navbar-brand" href="/"><img
				class="nav-logo" id="nav-logo-2" src="img/2014websitelogo250px.png"></a>
		</div>
		<!-- Collect the nav links, forms, and other content for toggling -->
		
		<?php
		    switch( $nav ) {
		        case "portrait":
		            require "nav-portrait.php";
		            break;
		        default:
		            require "nav-main.php";
		    }
		    ?>
		    
				<?php
                    if (getRole () == "admin") {
                ?>
				<li class="dropdown"><a href="javascript:void(0);"
					class="dropdown-toggle" data-toggle="dropdown">Admin<b
						class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a href="#">Manage Users</a></li>
						<li><a href="#">Manage Galleries</a></li>
						<li><a href="#">Manage Posts</a></li>
						<li><a href="#">Write New Post</a></li>
					</ul></li>
				<?php
                    }
                ?>
				<li>
                  	<?php
                        if (!isLoggedIn ()) {
                    ?>
                    <a href="javascript:void(0);" data-toggle="modal" data-target="#login-modal"><i class="fa fa-sign-in"></i> Login</a>
                    <?php
                        } else {
                    ?>
                    <a id='logout-button' href="javascript:void(0);"><i class="fa fa-sign-out"></i> Logout (<?php echo getUser (); ?>)</a>               	        
               	    <?php
                        }
                    ?>
                    </li>

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
    		<div id="login-error"></div>
    		<div id="login-message"></div>
    		<button id="login-submit" type="submit" class="btn btn-primary">Login</button>
    		<span>Remember Me <input id="login-remember" type="checkbox" name="remember" /></span>
    	    <div class="login-help">
    		    <a href="#">Register</a> - <a href="#">Forgot Password</a>
    	    </div>
    	</div>
    </div>
</div>