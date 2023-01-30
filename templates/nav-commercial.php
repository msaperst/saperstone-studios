<?php
$sql = new Sql();
$galleries = $sql->getRows("SELECT * FROM galleries WHERE parent = (SELECT id FROM galleries WHERE title = 'Commercial') AND title != 'Product';");
$sql->disconnect();
?>

<!-- Collect the nav links, forms, and other content for toggling -->
<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
    <ul class="nav navbar-nav navbar-right">
        <li class="dropdown"><a href="javascript:void(0);"
                                class="dropdown-toggle" data-toggle="dropdown">Details<strong
                        class="caret"></strong></a>
            <ul class="dropdown-menu">
                <li><a href="services.php">Services</a></li>
                <li><a href="background.php">Background Options</a></li>
                <li><a href="expect.php">What to Expect</a></li>
                <li><a href="pricing.php">Pricing</a></li>
                <li><a href="faq.php">FAQ</a></li>
            </ul>
        </li>
        <li class="dropdown"><a href="javascript:void(0);"
                                class="dropdown-toggle" data-toggle="dropdown">Gallery<strong
                        class="caret"></strong></a>
            <ul class="dropdown-menu">
                <?php
                for ($i = 0; $i < count($galleries); $i++) {
                    $gallery = $galleries [$i];
                    echo "<li><a href='gallery.php?w=" . $gallery['id'] . "'>" . $gallery['title'] . "</a></li>";
                }
                ?>
            </ul>
        </li>
        <li><a href="retouch.php">Retouch</a></li>
        <li><a href="reviews.php?c=3">Raves</a></li>
        <li><a href="/blog/category.php?t=75">Blog</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="/contact.php">Contact</a></li>
