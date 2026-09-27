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
    
    <main class="blog-editor-shell">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header text-center"><?php echo isset($blog) ? 'Edit Post' : 'Create Post'; ?></h1>
                    <ol class="breadcrumb blog-editor-breadcrumb">
                        <li><a href="/">Home</a></li>
                        <li><a href="/blog/">Blog</a></li>
                        <li class="active"><?php echo isset($blog) ? 'Edit Post' : 'New Post'; ?></li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="container blog-editor-container">
            <div class="blog-editor-layout">
                <section class="blog-editor-main">
                    <div class="blog-editor-card blog-editor-details">
                        <div class="blog-editor-card-heading">
                            <div>
                                <span class="blog-editor-step">Post details</span>
                                <h2>Start with the essentials</h2>
                            </div>
                            <button id="preview-post" type="button" class="btn btn-default">
                                <em class="fa fa-search"></em> Preview
                            </button>
                            <button id="edit-post" type="button" class="btn btn-default csp-hidden">
                                <em class="fa fa-pencil-square-o"></em> Back to editing
                            </button>
                        </div>
                        <div id="post" post-location="<?php echo $location; ?>"<?php if (isset($blog)) { echo " post-id='{$blog->getId()}'"; } ?>>
                            <label for="post-title-input">Title</label>
                            <input id="post-title-input" class="form-control input-lg" type="text"
                                   placeholder="Give your post a title"
                                   value="<?php echo str_replace('\'', '&apos;', $title); ?>" />
                        </div>
                        <div class="blog-editor-meta">
                            <div id="post-tags" class="blog-editor-field">
                                <label for="post-tags-select">Categories</label>
                                <select id="post-tags-select" class="form-control">
                                    <option></option>
                                    <option value="0" class="text-danger">New Category</option>
                                    <?php
                                    foreach ($categories as $category) {
                                        echo "<option value='" . $category['id'] . "'>" . $category['tag'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="blog-editor-field">
                                <label for="post-date-input">Post date</label>
                                <strong id="post-date">
                                    <input id="post-date-input" class="form-control" type="date" value="<?php echo $date; ?>" />
                                </strong>
                            </div>
                            <div id="post-likes" class="blog-editor-likes"></div>
                        </div>
                    </div>

                    <div class="blog-editor-card blog-editor-content-card">
                        <div class="blog-editor-card-heading">
                            <div>
                                <span class="blog-editor-step">Post content</span>
                                <h2>Build your story</h2>
                                <p>Drag text sections and empty image sections by their handles to reorder them. Double-click a section handle to remove it.</p>
                            </div>
                            <div class="blog-editor-add-actions">
                                <button id="add-text-button" type="button" class="btn btn-default">
                                    <em class="fa fa-file-text-o"></em> Add text
                                </button>
                                <button id="add-image-button" type="button" class="btn btn-default">
                                    <em class="fa fa-image"></em> Add image section
                                </button>
                            </div>
                        </div>
                        <ul id="post-content" class="ui-sortable"></ul>
                    </div>
                </section>

                <aside class="blog-editor-sidebar">
                    <div class="blog-editor-card blog-editor-sidebar-card">
                        <span class="blog-editor-step">Featured image</span>
                        <h2>Post preview</h2>
                        <p class="text-muted">Choose the image readers will see before opening the post. Drag the image vertically to adjust its crop.</p>
                        <div id="post-preview-holder" class="text-center blog-preview-holder">
                            <label class="sr-only" for="post-preview-image">Preview image</label>
                            <select id="post-preview-image" class="form-control blog-preview-select">
                                <option value="">Choose an image</option>
                                <?php
                                if (isset($blog)) {
                                    foreach ($blog->getImages() as $image) {
                                        $parts = explode(DIRECTORY_SEPARATOR, $image);
                                        echo "<option>{$parts[sizeof($parts)-1]}</option>";
                                    }
                                }
                                ?>
                            </select>
                            <div class="blog-preview-empty">
                                <em class="fa fa-picture-o"></em>
                                <span>Select an uploaded image</span>
                            </div>
                            <?php
                            if (isset($blog)) {
                                echo "<img src='{$blog->getPreview()}' class='blog-preview-image' data-top='{$blog->getOffset()}'>";
                            }
                            ?>
                        </div>
                    </div>

                    <div class="blog-editor-card blog-editor-sidebar-card">
                        <span class="blog-editor-step">Media library</span>
                        <h2>Images</h2>
                        <p class="text-muted">Upload images once, then drag them into image sections below. Double-click an uploaded image to remove it.</p>
                        <div id="post-button-holder" class="blog-editor-upload-actions"></div>
                        <div id="post-image-holder" class="blog-edit-area"></div>
                    </div>

                    <div class="blog-editor-card blog-editor-sidebar-card blog-editor-publish-card">
                        <span class="blog-editor-step">Finish</span>
                        <h2><?php echo isset($blog) ? 'Update your post' : 'Save or publish'; ?></h2>
                        <div class="blog-editor-save-actions">
                            <?php if (isset($blog)) { ?>
                                <button id="update-post" type="button" class="btn btn-primary btn-block">
                                    <em class="fa fa-refresh"></em> Update Post
                                </button>
                            <?php } else { ?>
                                <button id="save-post" type="button" class="btn btn-primary btn-block">
                                    <em class="fa fa-save"></em> Save Draft
                                </button>
                            <?php } ?>
                            <?php if (!isset($blog)) { ?>
                                <button id="schedule-post" type="button" class="btn btn-default btn-block">
                                    <em class="fa fa-clock-o"></em> Schedule Post
                                </button>
                                <button id="publish-post" type="button" class="btn btn-success btn-block">
                                    <em class="fa fa-send"></em> Publish Post
                                </button>
                            <?php } elseif (!$blog->isActive()) { ?>
                                <button id="schedule-saved-post" type="button" class="btn btn-default btn-block">
                                    <em class="fa fa-clock-o"></em> Schedule Post
                                </button>
                                <button id="publish-saved-post" type="button" class="btn btn-success btn-block">
                                    <em class="fa fa-send"></em> Publish Post
                                </button>
                            <?php } ?>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>

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
        ksort($groups, SORT_NUMERIC);
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
