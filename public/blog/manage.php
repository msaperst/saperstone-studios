<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = User::fromSystem();
$user->forceAdmin();
$categories = $sql->getRows( "SELECT * FROM `tags`;" );
$sql->disconnect();
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

</head>

<body>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>
    
    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Manage Blog Posts</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="/blog/">Blog</a></li>
                    <li class="active">Manage</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Post Section -->
        <div class="row">
            <div class="col-lg-12">
                <table id="posts" class="display table-full-width">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Active</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <!-- /.row -->

        <?php
        require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php";
        ?>

    </div>
    <!-- /.container -->

    <!-- Slideshow Modal -->
    <div id="post" class="modal fade modal-carousel" role="dialog">
        <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-7">
                            <input id='post-title-input' class='form-control' type='text'
                                placeholder='Blog Post Title' /> <input id='post-date-input'
                                class='form-control' type='date' /> <input
                                id='post-active-input' type='checkbox' /> Active <br /> <br />
                            <div id="post-tags">
                                <label class="sr-only" for="post-tags-select">Blog post category</label>
                                <select id='post-tags-select' class='form-control input-sm u-width-auto'>
                                    <option></option>
                                    <option value='0' class='text-danger'>New Category</option>
                                    <?php
                                    foreach ( $categories as $category ) {
                                        echo "<option value='" . $category ['id'] . "'>" . $category ['tag'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div id='post-preview-holder' class='text-center blog-preview-holder'>
                                <label class="sr-only" for="post-preview-image">Preview image</label>
                                <select id='post-preview-image' class='blog-preview-select'><option></option></select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="post-update-button"
                        class="btn btn-default btn-success">
                        <em class="fa fa-refresh"></em> Update
                    </button>
                    <button type="button" id="post-delete-button"
                        class="btn btn-default btn-danger">
                        <em class="fa fa-trash"></em> Delete
                    </button>
                    <button type="button" id="post-update-close-button"
                        class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>



        <script src="<?php echo Strings::assetUrl('/js/blog-common.js'); ?>"></script>
        <script src="<?php echo Strings::assetUrl('/js/post-admin.js'); ?>"></script>
        <script src="<?php echo Strings::assetUrl('/js/posts-manage.js'); ?>"></script>
        <script
            src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"
            integrity="sha384-89aj/hOsfOyfD0Ll+7f2dobA15hDyiNb8m1dJ+rJuqgrGR+PVqNU8pybx4pbF3Cc"
            crossorigin="anonymous"></script>

</body>

</html>
