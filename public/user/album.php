<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api();
$errors = new Errors();

try {
    $album = Album::withId($api->retrieveGetString('album', 'Album id'));
} catch (Exception $e) {
    $errors->throw404();
}

if (!$album->canUserAccess()) {
    $errors->throw401();
}

$session = new Session();
$session->initialize();
$user = User::fromSystem();
$sql = new Sql ();

// update our last accessed
if (!$user->isAdmin()) {
    $sql->executeStatement("UPDATE `albums` SET `lastAccessed` = now() WHERE id = '{$album->getId()}';");
}
if ($user->isLoggedIn()) {
    $sql->executeStatement("INSERT INTO `user_logs` VALUES ( {$user->getId()}, CURRENT_TIMESTAMP, 'Visited Album', NULL, {$album->getId()} );");
}

$isAlbumDownloadable = $sql->getRowCount("SELECT * FROM `download_rights` WHERE user = '0' AND album = '{$album->getId()}';");
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">
    <link href="/css/uploadfile.css" rel="stylesheet">
    <style>
        footer {
            margin-bottom: 55px;
        }
    </style>

</head>

<body>

<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/nav.php";

// get our gallery images
$images = $sql->getRows("SELECT album_images.*, albums.name, albums.description, albums.date FROM `album_images` JOIN `albums` ON album_images.album = albums.id WHERE albums.id = '{$album->getId()}' ORDER BY `sequence`;");
?>

<!-- Page Content -->
<div class="page-content container">

    <!-- Page Heading/Breadcrumbs -->
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header text-center">
                <span id='album-title'><?php echo $album->getName(); ?></span>
                <small><?php echo $album->getDescription(); ?></small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="/">Home</a></li>
                <?php
                if ($user->isLoggedIn()) {
                    echo "<li><a href=\"index.php\">Albums</a></li>";
                }
                ?>
                <li class="active"><?php echo $album->getName(); ?></li>

                <span id="actions">
                    <?php
                    $result = $sql->getRow("SELECT COUNT(*) AS total FROM `favorites` WHERE `user` = '" . $user->getId() . "' AND `album` = '{$album->getId()}';");
                    $sql->disconnect();
                    ?>
                    <li class="no-before pull-right">
                        <button
                                id="favorite-btn"
                                type="button"
                                class="btn btn-xs btn-success"
                                data-toggle="tooltip"
                                data-placement="bottom"
                                title="View favorites from this album"
                        >
                            <em class="fa fa-heart error">
                                <strong
                                        id="favorite-count"
                                        class="error"
                                        <?php
                                        if ($result ['total'] > 0) {
                                        ?>
                                        style="padding-left: 10px;"
                                >
                                    <?php echo $result['total'];
                                    } else {
                                        ?>
                                        >
                                        <?php
                                    }
                                    ?>
                                </strong>
                            </em>
                        </button>
                    </li>
                    <li class="no-before pull-right">
                        <button
                                id="submit-favorites-btn"
                                type="button"
                                class="btn btn-xs btn-success"
                                data-toggle="tooltip"
                                data-placement="bottom"
                                title="Submit album favorites as selection"
                                style="display: none"
                        >
                            <em class="fa fa-paper-plane"></em>
                        </button>
                    </li>
                    <li class="no-before pull-right">
                        <button
                                type="button"
                                data-toggle="tooltip"
                                data-placement="bottom"
                                style="display: none"
                                <?php
                                if (!$user->isLoggedIn() && !$isAlbumDownloadable) {
                                    ?>
                                    id="disabled-downloadable-favorites-btn"
                                    class="btn btn-xs"
                                    title="Login or create an account to download images"
                                    disabled
                                    <?php
                                } else {
                                    ?>
                                    id="downloadable-favorites-btn"
                                    class="btn btn-xs btn-action btn-success"
                                    title="Download all favorite images in this album"
                                    <?php
                                }
                                ?>
                        >
                            <em class="fa fa-download"></em>
                        </button>
                    </li>
                    <li class="no-before pull-right">
                        <button
                                type="button"
                                data-toggle="tooltip"
                                data-placement="bottom"
                                <?php
                                if (!$user->isLoggedIn() && !$isAlbumDownloadable) {
                                    ?>
                                    id="disabled-downloadable-all-btn"
                                    class="btn btn-xs"
                                    title="Login or create an account to download images"
                                    disabled
                                    <?php
                                } else {
                                    ?>
                                    id="downloadable-all-btn"
                                    class="btn btn-xs btn-action btn-success"
                                    title="Download all images in this album"
                                    <?php
                                }
                                ?>
                        >
                            <em class="fa fa-download"></em>
                        </button>
                    </li>
                    <?php
                    if ($user->isAdmin()) {
                        ?>
                        <li class="no-before pull-right">
                            <button
                                    id="access-btn"
                                    type="button"
                                    class="btn btn-xs btn-info"
                                    data-toggle="tooltip"
                                    data-placement="bottom"
                                    title="Set access for this album"
                            >
                                <em class="fa fa-picture-o"></em>
                            </button>
                        </li>
                        <?php
                    }
                    if ($album->canUserGetData()) {
                        ?>
                        <li class="no-before pull-right">
                            <button
                                    id="edit-album-btn"
                                    type="button"
                                    class="btn btn-xs btn-warning"
                                    data-toggle="tooltip"
                                    data-placement="bottom"
                                    title="Edit album details">
                                <em class="fa fa-pencil-square-o"></em>
                            </button>
                        </li>
                        <?php
                    }
                    ?>
                </span>
            </ol>
        </div>
    </div>
    <!-- /.row -->

    <!-- Services Section -->
    <div id="album-thumbs" class="album-page">
        <?php
        $notification_emails = $sql->getRows("SELECT * FROM notification_emails WHERE album = {$album->getId()} AND contacted = FALSE;");
        if ($user->isAdmin() && sizeof($notification_emails) > 0) {
            ?>
            <div class="row">
                <div class="col-md-offset-2 col-md-8 text-center">Several people have requested updates once images are
                    added to this album.
                    Please be sure to email them once images have added, or updates have been made.
                </div>
            </div>
            <div id="email-list" class="row">
                <?php
                foreach ($notification_emails as $notification_email) {
                    echo "<div class='col-md-2 text-truncate'><a href='mailto:{$notification_email['email']}'>{$notification_email['email']}</a></div>";
                }
                ?>
            </div>
            <div class="row">
                <div class="col-md-4 col-md-offset-4 text-center">
                    <button id="email-users" type="submit" class="btn btn-primary">
                        <em class="fa fa-paper-plane-o" aria-hidden="true"></em> Email All Users
                    </button>
                </div>
            </div>
            <div class="row page-header"></div>
            <?php
        }
        if (count($images) > 0) {
            ?>
            <div id="album-viewer-overlay" class="album-viewer-overlay hidden" album-id="<?php echo $album->getId(); ?>"
                 role="dialog" aria-modal="true" aria-hidden="true">
                <button id="album-viewer-close" type="button" class="album-viewer-close" aria-label="Close viewer">
                    <em class="fa fa-times"></em>
                </button>
                <div class="album-viewer-shell">
                    <button id="album-prev-btn" type="button" class="album-nav album-nav-prev"
                            aria-label="Previous image">
                        <em class="fa fa-chevron-left"></em>
                    </button>

                    <div class="album-viewer-stage">
                        <div class="album-viewer-frame">
                            <img id="album-viewer-image" class="album-viewer-image" src="" alt="">

                            <div class="album-viewer-copy">
                                <h2 id="album-viewer-title"></h2>
                                <p id="album-viewer-caption"></p>
                            </div>

                            <div class="album-viewer-actions">
                                <?php if ($user->isLoggedIn() || $isAlbumDownloadable) { ?>
                                    <button id="downloadable-image-btn"
                                            type="button" class="btn btn-default album-icon-btn btn-success"
                                            aria-label="Download image">
                                        <em class="fa fa-download"></em>
                                    </button>
                                <?php } ?>
                                <button id="submit-image-btn" type="button"
                                        class="btn btn-default album-icon-btn btn-success"
                                        aria-label="Submit image">
                                    <em class="fa fa-paper-plane"></em>
                                </button>
                                <button id="set-favorite-image-btn" type="button"
                                        class="btn btn-default album-icon-btn"
                                        aria-label="Favorite image">
                                    <em class="fa fa-heart"></em>
                                </button>
                                <button id="unset-favorite-image-btn" type="button"
                                        class="btn btn-default btn-success album-icon-btn hidden"
                                        aria-label="Remove favorite">
                                    <em class="fa fa-heart error"></em>
                                </button>
                                <?php
                                if ($user->isAdmin()) {
                                    ?>
                                    <button id="access-image-btn" type="button"
                                            class="btn btn-default btn-info album-icon-btn"
                                            aria-label="Access image">
                                        <em class="fa fa-picture-o"></em>
                                    </button>
                                    <button id="delete-image-btn" type="button"
                                            class="btn btn-default btn-danger album-icon-btn"
                                            aria-label="Delete image">
                                        <em class="fa fa-trash"></em>
                                    </button>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <button id="album-next-btn" type="button" class="album-nav album-nav-next" aria-label="Next image">
                        <em class="fa fa-chevron-right"></em>
                    </button>
                </div>
            </div>
            <div id="album-grid" class="album-grid"></div>
            <?php
        } else {
            ?>
            <div class="row">
                <div class="col-md-offset-2 col-md-8 text-center">Sorry, no images have
                    been uploaded to your gallery yet. You can submit your email to be
                    notified when images are added if you would like. Your email address
                    will not be used for any other purposes.
                </div>
                <div class="col-md-4 col-md-offset-4 text-center">
                    <form>
                        <label class="sr-only" for="cart-email">Email</label> <input
                                id="notify-email" type="email" placeholder="Email"
                                class="form-control" value="<?php echo $user->getEmail(); ?>"
                                required/>
                    </form>
                    <button id="notify-submit" type="submit" class="btn btn-primary">
                        <em class="fa fa-paper-plane-o" aria-hidden="true"></em> Submit
                    </button>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <!-- /.row -->

    <?php require_once dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

</div>
<!-- /.container -->

<!-- Submit Selections Modal -->
<div id="submit" album-id="<?php echo $album->getId(); ?>" what=""
     class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Submit My Selection</h4>
            </div>
            <div class="modal-body">
                <p>
                    <em class="fa fa-exclamation-triangle"></em> Have you finished
                    making your selections?
                </p>
                <p>Submit your selections to us, along with any comments you may
                    have. We will receive your request and start processing your order
                    as soon as possible.</p>
                <div class="row">
                    <div class="col-md-6">
                        <label class="sr-only" for="submit-name">Name</label> <input
                                id="submit-name" type="text" placeholder="Name"
                                class="form-control" value="<?php echo $user->getName(); ?>"
                                required/>
                    </div>
                    <div class="col-md-6">
                        <label class="sr-only" for="submit-email">Email</label> <input
                                id="submit-email" type="email" placeholder="Email"
                                class="form-control" value="<?php echo $user->getEmail(); ?>"
                                required/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label class="sr-only" for="submit-comment">Comment</label>
                        <textarea id="submit-comment" type="text" placeholder="Comment"
                                  class="form-control"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bootstrap-dialog">
                <button id="submit-send" type="button"
                        class="btn btn-default btn-success">
                    <em class="fa fa-paper-plane"></em> Submit Selection
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- End of Modal -->

<?php
if ($user->isAdmin() && sizeof($notification_emails) > 0) {
    ?>
    <!-- Submit Selections Modal -->
    <div id="notifications" album-id="<?php echo $album->getId(); ?>"
         class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Send Album Update Information</h4>
                </div>
                <div class="modal-body">
                    <p>
                        <em class="fa fa-exclamation-triangle"></em> What message do you want to send to the users?
                    </p>
                    <textarea id="notifications-message" class="form-control" maxlength="999"
                              style="resize: none; height:100px">Images have been posted to album <?php echo $album->getName(); ?>. You can access your images by<?php
                        if ($album->hasCode()) {
                            echo " navigating to https://saperstonestudios.com/#album={$album->getCode()}";
                        } else {
                            echo " logging in at https://saperstonestudios.com/ and then navigating to https://saperstonestudios.com/user/album.php?album={$album->getId()}";
                        }
                        ?>.</textarea>
                </div>
                <div class="modal-footer bootstrap-dialog">
                    <button id="notifications-send-btn" type="button"
                            class="btn btn-default btn-success">
                        <em class="fa fa-paper-plane"></em> Submit Selection
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Modal -->
    <?php
}
?>

<!-- Gallery JavaScript -->
<script>
    window.albumCanDownload = <?php echo ($user->isLoggedIn() || $isAlbumDownloadable) ? 'true' : 'false'; ?>;
    window.showImageTitle = <?php echo $user->isAdmin() ? 'true' : 'false'; ?>;
</script>
<script src="/js/album.js?v=<?php echo @filemtime($_SERVER['DOCUMENT_ROOT'] . '/js/album.js'); ?>"></script>
<?php
if ($user->isAdmin()) {
    ?>
    <script src="/js/album-admin.js?v=<?php echo @filemtime($_SERVER['DOCUMENT_ROOT'] . '/js/album-admin.js'); ?>"></script>
    <script src="/js/albums-admin.js?v=<?php echo @filemtime($_SERVER['DOCUMENT_ROOT'] . '/js/albums-admin.js'); ?>"></script>
    <script src="/js/jquery.uploadfile.js?v=<?php echo @filemtime($_SERVER['DOCUMENT_ROOT'] . '/js/jquery.uploadfile.js'); ?>"></script>
    <?php
}
if ($user->getRole() == "uploader" && $user->getId() == $album->getOwner()) {
    ?>
    <script src="/js/albums-uploader.js?v=<?php echo @filemtime($_SERVER['DOCUMENT_ROOT'] . '/js/albums-uploader.js'); ?>"></script>
    <script src="/js/jquery.uploadfile.js?v=<?php echo @filemtime($_SERVER['DOCUMENT_ROOT'] . '/js/jquery.uploadfile.js'); ?>"></script>
    <?php
}
?>

<!-- Script to Activate the Gallery -->
<script>
    $('[data-toggle="tooltip"]').tooltip();
    var album = new Album("<?php echo $album->getId(); ?>", 4, <?php echo count($images); ?> );
    $(window, document).on("scroll resize", function () {
        album.loadImages();
    });
</script>

</body>

</html>
