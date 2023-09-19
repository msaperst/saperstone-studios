<?php
$galleryDropDown = Gallery::withTitle('B\'NaiMitzvah');
$galleryChildren = $galleryDropDown->getChildren();
?>

<!-- Collect the nav links, forms, and other content for toggling -->
<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
    <ul class="nav navbar-nav navbar-right">
        <li class="dropdown"><a href="javascript:void(0);"
                                class="dropdown-toggle" data-toggle="dropdown">Details<strong
                        class="caret"></strong></a>
            <ul class="dropdown-menu">
                <li><a href="sessions.php">Session Information</a></li>
                <li><a href="process.php">The Process</a></li>
                <li><a href="products.php">Products and Investment</a></li>
                <li><a href="experience.php">The Mitzvah Experience</a></li>
                <li><a href="photobooth.php">Photobooth</a></li>
            </ul>
        </li>
        <li class="dropdown"><a href="javascript:void(0);"
                                class="dropdown-toggle" data-toggle="dropdown">Gallery<strong
                        class="caret"></strong></a>
            <ul class="dropdown-menu">
                <?php
                for ($i = 0; $i < count($galleryChildren); $i++) {
                    $galleryChild = $galleryChildren[$i];
                    if ($galleryChild->hasChildren()) {
                        echo "<li><a href='gallery.php?w=" . $galleryChild->getId() .
                            "'>" . $galleryChild->getTitle() . "</a></li>";
                    } else {
                        echo "<li><a href='galleries.php?w=" . $galleryChild->getId() .
                            "'>" . $galleryChild->getTitle() . "</a></li>";
                    }
                }
                ?>
            </ul>
        </li>
        <li><a href="retouch.php">Retouch</a></li>
        <li><a href="reviews.php?c=4">Raves</a></li>
        <li><a href="/blog/category.php?t=4">Blog</a></li>
        <li><a href="/about.php">About</a></li>
        <li><a href="/contact.php">Contact</a></li>
