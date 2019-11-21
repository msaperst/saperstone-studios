<!-- Collect the nav links, forms, and other content for toggling -->
<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
    <ul class="nav navbar-nav navbar-right">
        <li class="dropdown"><a href="javascript:void(0);"
            class="dropdown-toggle" data-toggle="dropdown">Portraits<strong
                class="caret"></strong></a>
            <ul class="dropdown-menu">
                <li><a href="/portrait/details.php">Details</a></li>
                <li><a href="/portrait/gallery.php?w=1">Gallery</a></li>
                <li><a href="/portrait/retouch.php">Retouch</a></li>
                <li><a href="/portrait/reviews.php?c=1">Raves</a></li>
            </ul></li>
        <li class="dropdown"><a href="javascript:void(0);"
            class="dropdown-toggle" data-toggle="dropdown">Weddings<strong
                class="caret"></strong></a>
            <ul class="dropdown-menu">
                <li><a href="/wedding/details.php">Details</a></li>
                <li><a href="/wedding/gallery.php?w=8">Gallery</a></li>
                <li><a href="/wedding/retouch.php">Retouch</a></li>
                <li><a href="/wedding/reviews.php?c=2">Raves</a></li>
            </ul></li>
        <li class="dropdown"><a href="javascript:void(0);"
            class="dropdown-toggle" data-toggle="dropdown">Commercial<strong
                class="caret"></strong></a>
            <ul class="dropdown-menu">
                <li><a href="/commercial/services.php">Services</a></li>
                <li><a href="/commercial/gallery.php?w=52">Gallery</a></li>
                <li><a href="/commercial/retouch.php">Retouch</a></li>
                <li><a href="/commercial/reviews.php?c=3">Raves</a></li>
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
                <li style="padding-left: 15px;"><input id="nav-search-input"/>
                    <em id="nav-search-icon" class="fa fa-search"></em></li>
            </ul></li>
        <li class="dropdown"><a href="javascript:void(0);"
            class="dropdown-toggle" data-toggle="dropdown">Information<strong
                class="caret"></strong></a>
            <ul class="dropdown-menu">
                <?php
                if (! $user->isAdmin ()) {
                    ?>
                <li><a href="#album">Find Album</a></li>
                <?php
                }
                ?>
                <li><a href="/about.php">About</a></li>
                <li><a href="/leighAnn.php">Meet Leigh Ann</a></li>
                <li><a href="/reviews.php">Raves</a></li>
                <li><a href="/contact.php">Contact</a></li>
            </ul></li>