<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$user = User::fromSystem();
$user->forceAdmin();
$errors = new Errors();

$title = "";
$date = date ( "Y-m-d" );
$location = "../tmp";
if (isset ( $_GET ['p'] )) {
    try {
        $blog = Blog::withId($_GET ['p']);
        $title = $blog->getTitle();
        $date = date('Y-m-d',strtotime($blog->getDate()));
        $content = $blog->getContent();
        $location = $blog->getLocation();
    } catch (Exception $e) {
        $errors->throw404();
    }
}
$sql = new Sql ();
$categories = $sql->getRows( "SELECT * FROM `tags`;" );
$sql->disconnect();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
    <link
    href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.2/summernote.css"
    rel="stylesheet"
    integrity="sha384-lI5fxDuBpGLyfE2ACnKBNZ+0kRZ4CtzN8ROZUlI69twizLoKGeVXxhKxOehes/3t"
    crossorigin="anonymous">
<link href="<?php echo Strings::assetUrl('/css/uploadfile.css'); ?>" rel="stylesheet">
<link href="<?php echo Strings::assetUrl('/css/hover-effect.css'); ?>" rel="stylesheet">

</head>

<body>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>
    
    <!-- Post Control Bar -->
    <div data-spy="affix"
        class="blog-controls-left text-center">
        <div id='post-button-holder'>
            <br />
            <button id="add-text-button" type="button" class="btn btn-info">
                <em class="fa fa-file-text-o"></em> Add Text Area
            </button>
            <button id="add-image-button" type="button" class="btn btn-info">
                <em class="fa fa-image"></em> Add Image Area
            </button>
            <br />
            <button id="preview-post" type="button" class="btn btn-warning">
                <em class="fa fa-search"></em> Preview Post
            </button>
            <button id="edit-post" type="button" class="btn btn-warning csp-hidden">
                <em class="fa fa-pencil-square-o"></em> Edit Post
            </button>
            <?php
            if (isset ( $blog )) {
                ?>
            <button id="update-post" type="button"
                class="btn btn-warning">
                <em class="fa fa-refresh"></em> Update Post
            </button>
            <?php
            } else {
                ?>
            <button id="save-post" type="button" class="btn btn-warning">
                <em class="fa fa-save"></em> Save Post
            </button>
            <?php
            }
            ?>
            <br />
            <?php
            if (! isset ( $blog )) {
                ?>
            <button id="schedule-post" type="button"
                class="btn btn-success">
                <em class="fa fa-clock-o"></em> Schedule Post
            </button>
            <button id="publish-post" type="button" class="btn btn-success">
                <em class="fa fa-send"></em> Publish Post
            </button>
            <?php
            } elseif (! $blog->isActive()) {
                ?>
            <button id="schedule-saved-post" type="button"
                class="btn btn-success">
                <em class="fa fa-clock-o"></em> Schedule Post
            </button>
            <button id="publish-saved-post" type="button" class="btn btn-success">
                <em class="fa fa-send"></em> Publish Post
            </button>
            <?php
            }
            ?>
        </div>

        <div id='post-image-holder' class='blog-edit-area'></div>
        <!-- overflow-y:auto;  -->
    </div>

    <!-- Preview Control Bar -->
    <div data-spy="affix"
        class="blog-controls-right text-center">
        <div id='post-preview-holder' class='text-center blog-preview-holder'>
            <label class="sr-only" for="post-preview-image">Preview image</label>
            <select id='post-preview-image' class='blog-preview-select'>
                <option></option>
                <?php
                if (isset ( $blog )) {
                    foreach ( $blog->getImages() as $image ) {
                        $parts = explode(DIRECTORY_SEPARATOR, $image);
                        //TODO - figure out how to select the correct one
                        echo "<option>{$parts[sizeof($parts)-1]}</option>";
                    }
                }
                ?>
            </select>
            <?php
            if (isset ( $blog )) {
                echo "<img src='{$blog->getPreview()}' class='blog-preview-image' data-top='{$blog->getOffset()}'>";
            }
            ?>
        </div>
    </div>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <?php
                if (isset ( $blog )) {
                    ?>
                <h1 class="page-header text-center">Edit Your Blog Post</h1>
                <?php
                } else {
                    ?>
                <h1 class="page-header text-center">Write A New Blog
                    Post</h1>
                <?php
                }
                ?>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="/blog/">Blog</a></li>
                    <?php
                    if (isset ( $blog )) {
                        ?>
                    <li class="active">Edit Post</li>
                    <?php
                    } else {
                        ?>
                    <li class="active">New Post</li>
                    <?php
                    }
                    ?>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Post Section -->
        <div class="row" id="post" post-location="<?php echo $location; ?>"<?php if( isset( $blog ) ) { echo " post-id='{$blog->getId()}'"; } ?>">
            <div class="col-lg-12">
                <strong><input id='post-title-input'
                    class='form-control input-lg text-center' type='text'
                    placeholder='Blog Post Title'
                    value='<?php echo str_replace('\'', '&apos;', $title); ?>' /></strong>
            </div>
        </div>
        <div class="row">
            <div id="post-tags" class="col-md-4 text-left">
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
            <div class="col-md-4 text-center">
                <strong id="post-date"> <input id='post-date-input'
                    class='form-control input-sm inline-form-control' type='date' value='<?php echo $date; ?>' />
                </strong>
            </div>
            <div id="post-likes" class="col-md-4 text-right"></div>
        </div>
        <!-- /.row -->

        <!-- Post Content -->
        <ul id="post-content" class="ui-sortable"></ul>

    </div>
    <!-- /.row -->

        <?php
        require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php";
        ?>

    </div>
    <!-- /.container -->


    <script src="<?php echo Strings::assetUrl('/js/blog-common.js'); ?>"></script>
    <script src="<?php echo Strings::assetUrl('/js/post-admin.js'); ?>"></script>
    <script src="<?php echo Strings::assetUrl('/js/dragndrop.js'); ?>"></script>
    <script src="<?php echo Strings::assetUrl('/js/jquery.uploadfile.js'); ?>"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.2/summernote.min.js"
        integrity="sha384-1IIwthROMSfhA19NIVJd+SEXxojzNAy1tS30WL5WYNQR0vbroZa5qd8vvOBYwW7o"
        crossorigin="anonymous"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/jquery-sortable/0.9.13/jquery-sortable-min.js"
        integrity="sha384-mwD0+87SDVjJjyfTMQHNVV+IyWDM38MhzdCFZ+SRefmD75v+M5K0R3naFNLnZf1L"
        crossorigin="anonymous"></script>
    <?php
    $editorTags = array();
    $editorGroups = array();
    if (isset($blog)) {
        foreach ($blog->getTags() as $tag) {
            $editorTags[] = $tag['id'];
        }
        $groups = array();
        foreach ($content as $block) {
            $groups[$block->getGroup()][] = $block;
        }
        foreach ($groups as $group) {
            if ($group[0] instanceof BlogText) {
                $editorGroups[] = array('type' => 'text', 'text' => $group[0]->getText());
            } elseif ($group[0] instanceof BlogImage) {
                $images = array();
                foreach ($group as $image) {
                    $images[] = $image->getRaw();
                }
                $editorGroups[] = array('type' => 'images', 'images' => $images);
            }
        }
    }
    ?>
    <div id="post-editor-config" class="hidden"
         data-tags="<?php echo Strings::escapeHtmlAttribute(json_encode($editorTags)); ?>"
         data-groups="<?php echo Strings::escapeHtmlAttribute(json_encode($editorGroups)); ?>"></div>
    <script src="<?php echo Strings::assetUrl('/js/post-editor-init.js'); ?>"></script>

</body>

</html>
