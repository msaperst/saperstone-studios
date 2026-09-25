<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$user = User::fromSystem();
$user->forceLogIn();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
    <link
    href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css"
    rel="stylesheet"
    integrity="sha384-YcTv91pbdpZ4It88TK5bVHIGTuPqoSi0CpPF9UA9eRicGHEJ3lpQZajpytN4rLkp"
    crossorigin="anonymous">
<link href="<?php echo Strings::assetUrl('/css/uploadfile.css'); ?>" rel="stylesheet">

</head>

<body>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <?php
                if ($user->isAdmin ()) {
                    ?>
                <h1 class="page-header text-center">Manage Albums</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li class="active">Administration</li>
                    <li class="active">Albums</li>
                </ol>
                <?php
                } else {
                    ?>
                <h1 class="page-header text-center">View Albums</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li class="active">Profile</li>
                    <li class="active">Albums</li>
                </ol>
                <?php
                }
                ?>
            </div>
        </div>
        <!-- /.row -->

        <!-- Services Section -->
        <div class="row">
            <div class="col-lg-12">
                <?php
                if (! $user->isAdmin ()) {
                    ?>
                    <div id="add-album-div"
                    class="form-group form-inline text-center">
                    <label for="album-code">Add Album:</label> <input type="text"
                        class="form-control" id="album-code" placeholder="Album Code" />
                    <button class="btn btn-success" id="album-code-add">
                        <em class="fa fa-plus-circle"></em>
                    </button>
                </div>
                <div id="add-album-error" class="error"></div>
                <?php
                }
                ?>
                <?php
                if ($user->isAdmin() || $user->getRole() == "uploader") {
                    ?>
                    <span id="thumbnail-status-filter-container" class="form-inline csp-hidden">
                        <label for="thumbnail-status-filter">Thumbnail Status:</label>
                        <select id="thumbnail-status-filter" class="form-control input-sm">
                            <option value="">All</option>
                            <option value="Missing">Missing</option>
                            <option value="Ready">Ready</option>
                            <option value="N/A">N/A</option>
                        </select>
                    </span>
                    <?php
                }
                ?>
                <table id="albums" class="display table-full-width">
                    <thead>
                        <tr>
                            <?php
                            if ($user->isAdmin () || $user->getRole () == "uploader") {
                                ?>
                            <th class="u-width-55">
                                <button id="add-album-btn" type="button"
                                    class="btn btn-xs btn-success" data-toggle="tooltip"
                                    data-placement="right" title="Add New Album">
                                    <em class="fa fa-plus"></em>
                                </button>
                            </th>
                            <?php
                            }
                            ?>
                            <th>Album Name</th>
                            <th>Album Description</th>
                            <th>Album Date</th>
                            <th>Images</th>
                            <?php
                            if ($user->isAdmin() || $user->getRole() == "uploader") {
                                ?>
                            <th>Thumbnails</th>
                            <?php
                            }
                            if ($user->isAdmin ()) {
                                ?>
                            <th>Last Accessed</th>
                            <th>Access Code</th>
                            <?php
                            }
                            ?>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <!-- /.row -->

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

    <script
        src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"
        integrity="sha384-89aj/hOsfOyfD0Ll+7f2dobA15hDyiNb8m1dJ+rJuqgrGR+PVqNU8pybx4pbF3Cc"
        crossorigin="anonymous"></script>
    <script src="<?php echo Strings::assetUrl('/js/jquery.uploadfile.js'); ?>"></script>
    <script src="<?php echo Strings::assetUrl('/js/albums-common.js'); ?>"></script>
    <?php
    if ($user->isAdmin ()) {
        ?>
    <script src="<?php echo Strings::assetUrl('/js/albums-admin.js'); ?>"></script>
    <script src="<?php echo Strings::assetUrl('/js/album-admin.js'); ?>"></script>
    <?php
    } elseif ($user->getRole () == "uploader") {
        ?>
    <script src="<?php echo Strings::assetUrl('/js/albums-uploader.js'); ?>"></script>
    <?php
    } else {
        ?>
    <script src="<?php echo Strings::assetUrl('/js/albums.js'); ?>"></script>
    <?php
    }
    ?>

</body>

</html>
