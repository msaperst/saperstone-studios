<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();

$user = new User ();

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    include dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/errors/401.php";
    exit ();
}

$post;
$title = "";
$date = date ( "Y-m-d" );
$tags = [ ];
$content = [ ];
$images = [ ];
$preview;
$offset;
$location = "../tmp";
// if no album is set, throw a 404 error
if (isset ( $_GET ['p'] )) {
    $post = ( int ) $_GET ['p'];
    $sql = "SELECT * FROM `blog_details` WHERE id = '$post';";
    $details = $sql->getRow( $sql );
    if (! $details ['title']) {
        header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
        include dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/errors/404.php";
        $conn->disconnect ();
        exit ();
    } else {
        $title = $details ['title'];
        $date = $details ['date'];
        $preview = $details ['preview'];
        $offset = $details ['offset'];
        $location = dirname ( $preview );
        // determine our tags
        $sql = "SELECT `tags`.* FROM `tags` JOIN `blog_tags` ON tags.id = blog_tags.tag WHERE blog_tags.blog = $post;";
        $result = mysqli_query ( $conn->db, $sql );
        while ( $r = mysqli_fetch_assoc ( $result ) ) {
            $tags [] = $r ['id'];
        }
        // get our content
        $contents = array ();
        $sql = "SELECT * FROM `blog_images` WHERE blog = $post;";
        $result = mysqli_query ( $conn->db, $sql );
        while ( $s = mysqli_fetch_assoc ( $result ) ) {
            $images [] = basename ( $s ['location'] );
            $content [$s ['contentGroup']] ['type'] = 'images';
            $content [$s ['contentGroup']] [] = $s;
        }
        $sql = "SELECT * FROM `blog_texts` WHERE blog = $post;";
        $result = mysqli_query ( $conn->db, $sql );
        while ( $s = mysqli_fetch_assoc ( $result ) ) {
            $s ['type'] = 'text';
            $content [$s ['contentGroup']] = $s;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
    <link
    href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.2/summernote.css"
    rel="stylesheet">
<link href="/css/uploadfile.css" rel="stylesheet">
<link href="/css/hover-effect.css" rel="stylesheet">

</head>

<body>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>
    
    <!-- Post Control Bar -->
    <div data-spy="affix"
        style="margin-top: 35px; margin-left: 5px; max-width: 300px; z-index: 100;"
        class="text-center">
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
            <button id="edit-post" type="button" class="btn btn-warning"
                style="display: none;">
                <em class="fa fa-pencil-square-o"></em> Edit Post
            </button>
            <?php
            if (isset ( $post )) {
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
            if (! isset ( $post )) {
                ?>
            <button id="schedule-post" type="button"
                class="btn btn-success">
                <em class="fa fa-clock-o"></em> Schedule Post
            </button>
            <button id="publish-post" type="button" class="btn btn-success">
                <em class="fa fa-send"></em> Publish Post
            </button>
            <?php
            } elseif (! $details ['active']) {
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

        <div id='post-image-holder' style='z-index: 100; height: 300px;'></div>
        <!-- overflow-y:auto;  -->
    </div>

    <!-- Preview Control Bar -->
    <div data-spy="affix"
        style="right: 0px; margin-top: 35px; margin-right: 5px; max-width: 300px; z-index: 100;"
        class="text-center">
        <div id='post-preview-holder' class='text-center'
            style='width: 300px; height: 176px; background-color: red; overflow: hidden;'>
            <select id='post-preview-image'
                style='top: 50%; position: absolute; opacity: 0.65; filter: alpha(opacity = 65); z-index: 99; left: 20px;'>
                <option></option>
                <?php
                if (isset ( $post )) {
                    foreach ( $images as $image ) {
                        echo "<option>$image</option>";
                    }
                }
                ?>
            </select>
            <?php
            if (isset ( $post )) {
                echo "<img src='$preview' style='width:300px; top:${offset}px;'>";
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
                if (isset ( $post )) {
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
                    if (isset ( $post )) {
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
        <div class="row" id="post" post-location="<?php echo $location; ?>"<?php if( isset( $post ) ) { echo " post-id='$post'"; } ?>">
            <div class="col-lg-12">
                <strong><input id='post-title-input'
                    class='form-control input-lg text-center' type='text'
                    placeholder='Blog Post Title'
                    value='<?php echo str_replace('\'', '&apos;', $title); ?>' /></strong>
            </div>
        </div>
        <div class="row">
            <div id="post-tags" class="col-md-4 text-left">
                <select id='post-tags-select' class='form-control input-sm'
                    style='width: auto;'>
                    <option></option>
                    <option value='0' style='color: red;'>New Category</option>
                <?php
                $sql = new Sql ();
                $sql = "SELECT * FROM `tags`;";
                $result = mysqli_query ( $conn->db, $sql );
                while ( $row = mysqli_fetch_assoc ( $result ) ) {
                    echo "<option value='" . $row ['id'] . "'>" . $row ['tag'] . "</option>";
                }
                $conn->disconnect ();
                ?>
                </select>
            </div>
            <div class="col-md-4 text-center">
                <strong id="post-date"> <input id='post-date-input'
                    class='form-control input-sm' type='date'
                    style='width: auto; display: initial;' value='<?php echo $date; ?>' />
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


    <script src="/js/post-admin.js"></script>
    <script src="/js/dragndrop.js"></script>
    <script src="/js/jquery.uploadfile.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.2/summernote.min.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/jquery-sortable/0.9.13/jquery-sortable-min.js"></script>
    <script>
        $(document).ready(function() {
            <?php
            if (isset ( $post )) {
                foreach ( $tags as $tag ) {
                    ?>
            $('#post-tags-select').val(<?php echo $tag; ?>);
            addTag($('#post-tags-select'));
            <?php
                }
                ksort ( $content );
                foreach ( $content as $block ) {
                    if ($block ['type'] == "text") {
                        ?>
            addTextArea("<?php echo addcslashes($block['text'],'"'); ?>");
            <?php
                    } elseif ($block ['type'] == "images") {
                        unset ( $block ['type'] );
                        ?>
            addImageArea(<?php echo json_encode( $block ); ?>);
            <?php
                    }
                }
            } else {
                ?>
            addImageArea();
            <?php
            }
            ?>
            $('#post-preview-holder img').draggable({
                axis : "y",
            });
        });
    </script>

</body>

</html>