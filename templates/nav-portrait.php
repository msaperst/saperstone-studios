<?php
$sql = new Sql();
$galleries = $sql->getRows("SELECT * FROM galleries WHERE parent = (SELECT id FROM galleries WHERE title = 'Portrait') AND title != 'Product';");
$sql->disconnect();
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
                <li><a href="what-to-wear.php">What to Wear</a></li>
                <li><a href="studio.php">Home Studio</a></li>
                <li><a href="faq.php">FAQs</a></li>
            </ul>
        </li>
        <li class="dropdown"><a href="javascript:void(0);"
                                class="dropdown-toggle" data-toggle="dropdown">Gallery<strong
                        class="caret"></strong></a>
            <ul class="dropdown-menu">
                <?php
                for ($i = 0; $i < count($galleries); $i++) {
                    $id = $galleries[$i]['id'];
                    $title = $galleries[$i]['title'];
                    $sql = new Sql ();
                    $subGalleries = $sql->getRows("SELECT * FROM `galleries` WHERE parent = '" . $id . "';");
                    $sql->disconnect();
                    if (sizeof($subGalleries) == 0) {
                        echo "<li><a href='galleries.php?w=" . $id . "'>" . $title . "</a></li>";
                    } else {
                        echo "<li><a href='gallery.php?w=" . $id . "'>" . $title . "</a></li>";
                    }
                }
                ?>
            </ul>
        </li>
        <li class="dropdown"><a href="javascript:void(0);"
                                class="dropdown-toggle" data-toggle="dropdown">Retouch<strong
                        class="caret"></strong></a>
            <ul class="dropdown-menu">
                <li><a href="portrait-retouch.php">Portrait Retouch</a></li>
                <li><a href="restoration.php">Restoration</a></li>
                <li><a href="manipulation.php">Other Edits</a></li>
            </ul>
        </li>
        <li><a href="reviews.php?c=1">Raves</a></li>
        <li><a href="/blog/category.php?t=20">Blog</a></li>
        <li><a href="/about.php">About</a></li>
        <li><a href="/contact.php">Contact</a></li>
