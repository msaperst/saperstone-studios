<!-- Collect the nav links, forms, and other content for toggling -->
<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
	<ul class="nav navbar-nav navbar-right">
		<li class="dropdown"><a href="javascript:void(0);"
			class="dropdown-toggle" data-toggle="dropdown">Portraits<strong
				class="caret"></strong></a>
			<ul class="dropdown-menu">
				<li><a href="/portrait/details.php">Details</a></li>
				<li><a href="/portrait/gallery.php">Gallery</a></li>
				<li><a href="#">Retouch</a></li>
			</ul></li>
		<li class="dropdown"><a href="javascript:void(0);"
			class="dropdown-toggle" data-toggle="dropdown">Weddings<strong
				class="caret"></strong></a>
			<ul class="dropdown-menu">
				<li><a href="portfolio-1-col.html">1 Column Portfolio</a></li>
				<li><a href="portfolio-2-col.html">2 Column Portfolio</a></li>
				<li><a href="portfolio-3-col.html">3 Column Portfolio</a></li>
				<li><a href="portfolio-4-col.html">4 Column Portfolio</a></li>
				<li><a href="portfolio-item.html">Single Portfolio Item</a></li>
			</ul></li>
		<li class="dropdown"><a href="javascript:void(0);"
			class="dropdown-toggle" data-toggle="dropdown">Commercial<strong
				class="caret"></strong></a>
			<ul class="dropdown-menu">
				<li><a href="portfolio-1-col.html">1 Column Portfolio</a></li>
				<li><a href="portfolio-2-col.html">2 Column Portfolio</a></li>
				<li><a href="portfolio-3-col.html">3 Column Portfolio</a></li>
				<li><a href="portfolio-4-col.html">4 Column Portfolio</a></li>
				<li><a href="portfolio-item.html">Single Portfolio Item</a></li>
			</ul></li>
		<li class="dropdown"><a href="javascript:void(0);"
			class="dropdown-toggle" data-toggle="dropdown">Blog<strong
				class="caret"></strong></a>
			<ul class="dropdown-menu">
				<li><a href="/blog/posts.php">Recent Posts</a></li>
				<li><a href="/blog/categories.php">Categories</a></li>
				<?php if ( $user->isAdmin () ) { ?>
				<li><a href="/blog/new.php">Write New Post</a></li>
				<li><a href="/blog/manage.php">Manage Posts</a></li>
				<?php } ?>
				<li><input id="nav-search-input" /> <em id="nav-search-icon"
					class="fa fa-search"></em></li>
			</ul></li>
		<li class="dropdown"><a href="javascript:void(0);"
			class="dropdown-toggle" data-toggle="dropdown">Information<strong
				class="caret"></strong></a>
			<ul class="dropdown-menu">
				<li><a href="/about.php">About</a></li>
                <?php
                if ($user->getRole () != "admins") {
                    ?>
                <li><a href="#album">Find Album</a></li>
                <?php
                }
                ?>
                <li><a href="/services.html">Services</a></li>
				<li><a href="#">Reviews</a></li>
				<li><a href="/contact.php">Contact</a></li>
			</ul></li>