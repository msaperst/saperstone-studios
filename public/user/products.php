<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$user = User::fromSystem();
$user->forceAdmin();
$sql = new Sql ();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
    <link
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/css/bootstrap-dialog.min.css"
    rel="stylesheet">

</head>

<body>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Manage Products</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li class="active">Administration</li>
                    <li class="active">Products</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <?php
        $row = $sql->getRow( "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'product_types' AND COLUMN_NAME = 'category';" );
        $categories = explode ( ",", str_replace ( "'", "", substr ( $row ['COLUMN_TYPE'], 5, (strlen ( $row ['COLUMN_TYPE'] ) - 6) ) ) );
        
        foreach ( $categories as $category ) {
            ?>
        <div class="row">
            <div class="col-lg-12">
                <h2 class="text-center"><?php echo ucwords($category); ?> <button
                        type="button" class="btn btn-xs btn-success add-product-button"
                        data-toggle="tooltip" data-placement="right"
                        title="Add New Product to <?php echo ucwords($category); ?>">
                        <em class="fa fa-plus"></em>
                    </button>
                </h2>
                <?php
            foreach ( $sql->getRows( "SELECT `id`,`name` FROM `product_types` WHERE `category` = '$category';" ) as $r ) {
                ?>
                <div class="col-md-4 col-sm-6 bootstrap-dialog"
                    product-type='<?php echo $r['id']; ?>'>
                    <button type="button" data-toggle="tooltip" data-placement="right"
                        title="Edit <?php echo $r['name']; ?> Name"
                        class="btn btn-xs btn-warning edit-product-button">
                        <em class="fa fa-pencil-square-o"></em>
                    </button>
                    <button type="button"
                        class="btn btn-xs btn-danger delete-product-button"
                        data-toggle="tooltip" data-placement="right"
                        title="Delete <?php echo $r['name']; ?>">
                        <em class="fa fa-trash-o"></em>
                    </button>
                    <button type="button"
                        class="btn btn-xs btn-success save-product-button hidden"
                        data-toggle="tooltip" data-placement="right" title="Save New Name">
                        <em class="fa fa-save"></em>
                    </button>
                    <button type="button"
                        class="btn btn-xs btn-warning cancel-product-button hidden"
                        data-toggle="tooltip" data-placement="right"
                        title="Cancel Changes">
                        <em class="fa fa-ban"></em>
                    </button>
                    <h3 product-type='<?php echo $r['id']; ?>'
                        class="text-center editable-header"><?php echo ucwords($r['name']); ?></h3>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style='width: 67px;'></th>
                                <th>Size</th>
                                <th>Cost</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                foreach ( $sql->getRows( "SELECT * FROM `products` WHERE `product_type` = '" . $r ['id'] . "';" ) as $s ) {
                    ?>
                            <tr product-id='<?php echo $s['id']; ?>'>
                                <td>
                                    <button type="button"
                                        class="btn btn-xs btn-warning edit-size-button"
                                        data-toggle="tooltip" data-placement="right"
                                        title="Edit <?php echo $s['size']; ?> Details">
                                        <em class="fa fa-pencil-square-o"></em>
                                    </button>
                                    <button type="button"
                                        class="btn btn-xs btn-danger delete-size-button"
                                        data-toggle="tooltip" data-placement="right"
                                        title="Delete <?php echo $s['size']; ?> Size">
                                        <em class="fa fa-trash-o"></em>
                                    </button>
                                    <button type="button"
                                        class="btn btn-xs btn-success save-size-button hidden"
                                        data-toggle="tooltip" data-placement="right"
                                        title="Save Details">
                                        <em class="fa fa-save"></em>
                                    </button>
                                    <button type="button"
                                        class="btn btn-xs btn-warning cancel-size-button hidden"
                                        data-toggle="tooltip" data-placement="right"
                                        title="Cancel Changes">
                                        <em class="fa fa-ban"></em>
                                    </button>
                                </td>
                                <td class='product-size'><?php echo $s['size']; ?></td>
                                <td class='product-cost'>$<?php echo $s['cost']; ?></td>
                                <td class='product-price'>$<?php echo $s['price']; ?></td>
                            </tr>
                            <?php
                }
                ?>
                            <tr>
                                <td>
                                    <button type="button"
                                        class="btn btn-xs btn-success add-size-button"
                                        data-toggle="tooltip" data-placement="right"
                                        title="Add Size to <?php echo $r['name']; ?>">
                                        <em class="fa fa-save"></em>
                                    </button>
                                </td>
                                <td class='product-size'><input class='form-control input-sm' /></td>
                                <td class='product-cost'><input class='form-control input-sm'
                                    type='number' step='0.01' min='0' /></td>
                                <td class='product-price'><input class='form-control input-sm'
                                    type='number' step='0.01' min='0' /></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class='product-size-error error'></div>
                    <hr />
                    <div class='product-options'>
                    <?php
                foreach ( $sql->getRows( "SELECT * FROM `product_options` WHERE `product_type` = '" . $r ['id'] . "';" ) as $s ) {
                    echo "<span class='selected-album'>" . $s ['opt'] . "</span>";
                }
                ?>
                    </div>
                    <div>
                        <button type="button"
                            class="btn btn-xs btn-success add-option-button"
                            data-toggle="tooltip" data-placement="right"
                            title="Add Options for <?php echo $r['name']; ?>">
                            <em class="fa fa-save"></em>
                        </button>
                        <input class='form-control input-sm'
                            style='width: 80%; float: right' />
                    </div>
                </div>
                <?php
            }
            ?>
            </div>
        </div>
        <?php
        }
        ?>

        <?php
        $sql->disconnect();
        require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php";
        ?>

    </div>
    <!-- /.container -->

    <script src="/js/products-admin.js"></script>

</body>

</html>
