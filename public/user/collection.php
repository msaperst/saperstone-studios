<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/src/autoloader.php';

$api = new Api();
$errors = new Errors();
try {
    $collection = Album::withId($api->retrieveGetInt('collection', 'Collection id'));
    if (!$collection->isCollection()) {
        throw new BadAlbumException('Album is not a collection');
    }
} catch (Exception $exception) {
    $errors->throw404();
}
if (!$collection->canUserAccess()) {
    $errors->throw401();
}

$user = User::fromSystem();
$children = $collection->getChildren();
$collectionName = Strings::escapeHtmlAttribute($collection->getName());
$description = Strings::escapeHtmlAttribute($collection->getDescription());
$date = $collection->getDataArray()['date'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/templates/header.php'; ?>
    <link href="<?php echo Strings::assetUrl('/css/album-collections.css'); ?>" rel="stylesheet">
    <?php if ($user->isAdmin()) { ?>
        <link href="<?php echo Strings::assetUrl('/css/album-collections-admin.css'); ?>" rel="stylesheet">
    <?php } ?>
</head>
<body>
<?php require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/templates/nav.php'; ?>

<div class="page-content container">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header text-center">
                <span id="collection-page-name"><?php echo $collectionName; ?></span>
                <small id="collection-page-description"<?php echo $description === '' ? ' class="hidden"' : ''; ?>><?php echo $description; ?></small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="/">Home</a></li>
                <?php if ($user->isLoggedIn()) { ?><li><a href="index.php">Albums</a></li><?php } ?>
                <li class="active" id="collection-breadcrumb-name"><?php echo $collectionName; ?></li>
                <?php if ($user->isAdmin()) { ?>
                    <li class="no-before pull-right">
                        <button id="edit-collection-btn" type="button"
                                class="btn btn-xs btn-warning"
                                data-collection-id="<?php echo (int)$collection->getId(); ?>"
                                title="Edit collection details" aria-label="Edit collection details">
                            <em class="fa fa-pencil-square-o" aria-hidden="true"></em>
                        </button>
                    </li>
                <?php } ?>
            </ol>
        </div>
    </div>
    <p id="collection-page-date" class="text-center text-muted<?php echo $date === null || $date === '' ? ' hidden' : ''; ?>"><?php echo $date ? Strings::escapeHtmlAttribute(substr($date, 0, 10)) : ''; ?></p>
    <?php if ($children === []) { ?>
        <div id="collection-empty-state" class="row">
            <div class="col-md-offset-2 col-md-8 text-center">
                There are no galleries in this collection yet.
            </div>
        </div>
    <?php } else { ?>
        <div id="collection-galleries" class="row">
            <?php foreach ($children as $child) {
                $childId = (int)$child->getId();
                $childName = Strings::escapeHtmlAttribute($child->getName());
                $imageCount = (int)$child->getDataArray()['images'];
                ?>
                <div class="col-md-4 col-sm-6 col-xs-12 album-collection-card"
                     data-child-album-id="<?php echo $childId; ?>">
                    <a class="thumbnail album-collection-link"
                       href="/user/album.php?album=<?php echo $childId; ?>">
                        <div class="album-collection-cover"
                             data-cover-url="/api/get-album-cover.php?album=<?php echo $childId; ?>"
                             role="img" aria-label="Cover image for <?php echo $childName; ?>"></div>
                        <div class="caption text-center">
                            <h3><?php echo $childName; ?></h3>
                            <p class="text-muted"><?php echo $imageCount; ?>
                                <?php echo $imageCount === 1 ? 'photo' : 'photos'; ?></p>
                        </div>
                    </a>
                    <div class="collection-card-actions text-center">
                        <a href="/user/album.php?album=<?php echo $childId; ?>" class="btn btn-primary btn-sm">View Gallery</a>
                        <?php if ($user->isAdmin()) { ?>
                            <button type="button" class="btn btn-warning btn-sm collection-edit-album-btn"
                                    data-album-id="<?php echo $childId; ?>"
                                    aria-label="Edit <?php echo $childName; ?> album"
                                    title="Edit album">
                                <em class="fa fa-pencil-square-o" aria-hidden="true"></em> Edit
                            </button>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
    <?php require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/templates/footer.php'; ?>
</div>
<script src="<?php echo Strings::assetUrl('/js/album-collections.js'); ?>"></script>
<?php if ($user->isAdmin()) { ?>
    <div id="collection-user-context" class="hidden" data-role="admin"
         data-id="<?php echo (int)$user->getId(); ?>"></div>
    <script src="<?php echo Strings::assetUrl('/js/jquery.uploadfile.js'); ?>"></script>
    <script src="<?php echo Strings::assetUrl('/js/albums-common.js'); ?>"></script>
    <script src="<?php echo Strings::assetUrl('/js/album-admin.js'); ?>"></script>
    <script src="<?php echo Strings::assetUrl('/js/albums-admin.js'); ?>"></script>
    <script src="<?php echo Strings::assetUrl('/js/album-cover-picker.js'); ?>"></script>
    <script src="<?php echo Strings::assetUrl('/js/album-collections-admin.js'); ?>"></script>
<?php } ?>
</body>
</html>
