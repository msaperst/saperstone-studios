<?php
session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
$user = new User ();

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    include "../errors/401.php";
    exit ();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once "../header.php"; ?>
    <link
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/css/bootstrap-dialog.min.css"
    rel="stylesheet">

</head>

<body>

    <?php require_once "../nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Manage Products</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li class="active">Administration</li>
                    <li class="active">Manage Products</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <?php
        require_once "../php/sql.php";
        $conn = new Sql ();
        $conn->connect ();
        $sql = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'product_types' AND COLUMN_NAME = 'category';";
        $row = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
        $categories = explode ( ",", str_replace ( "'", "", substr ( $row ['COLUMN_TYPE'], 5, (strlen ( $row ['COLUMN_TYPE'] ) - 6) ) ) );
        
        foreach ( $categories as $category ) {
            ?>
        <div class="row">
            <div class="col-lg-12">
                <h2 class="text-center"><?php echo ucwords($category); ?> <button
                        type="button" class="btn btn-xs btn-success add-product-button">
                        <em class="fa fa-plus"></em>
                    </button>
                </h2>
                <?php
            $sql = "SELECT `id`,`name` FROM `product_types` WHERE `category` = '$category';";
            $result = mysqli_query ( $conn->db, $sql );
            while ( $r = mysqli_fetch_assoc ( $result ) ) {
                ?>
                <div class="col-md-4 col-sm-6 bootstrap-dialog"
                    product-type='<?php echo $r['id']; ?>'>
                    <button type="button"
                        class="btn btn-xs btn-warning edit-product-button">
                        <em class="fa fa-pencil-square-o"></em>
                    </button>
                    <button type="button"
                        class="btn btn-xs btn-danger delete-product-button">
                        <em class="fa fa-trash-o"></em>
                    </button>
                    <button type="button"
                        class="btn btn-xs btn-success save-product-button hidden">
                        <em class="fa fa-save"></em>
                    </button>
                    <button type="button"
                        class="btn btn-xs btn-warning cancel-product-button hidden">
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
                $sql = "SELECT * FROM `products` WHERE `product_type` = '" . $r ['id'] . "';";
                $sesult = mysqli_query ( $conn->db, $sql );
                while ( $s = mysqli_fetch_assoc ( $sesult ) ) {
                    ?>
                            <tr product-id='<?php echo $s['id']; ?>'>
                                <td>
                                    <button type="button"
                                        class="btn btn-xs btn-warning edit-size-button">
                                        <em class="fa fa-pencil-square-o"></em>
                                    </button>
                                    <button type="button"
                                        class="btn btn-xs btn-danger delete-size-button">
                                        <em class="fa fa-trash-o"></em>
                                    </button>
                                    <button type="button"
                                        class="btn btn-xs btn-success save-size-button hidden">
                                        <em class="fa fa-save"></em>
                                    </button>
                                    <button type="button"
                                        class="btn btn-xs btn-warning cancel-size-button hidden">
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
                                        class="btn btn-xs btn-success add-size-button">
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
                $sql = "SELECT * FROM `product_options` WHERE `product_type` = '" . $r ['id'] . "';";
                $sesult = mysqli_query ( $conn->db, $sql );
                while ( $s = mysqli_fetch_assoc ( $sesult ) ) {
                    echo "<span class='selected-album'>" . $s ['opt'] . "</span>";
                }
                ?>
                    </div>
                    <div>
                        <button type="button"
                            class="btn btn-xs btn-success add-option-button">
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
        require_once "../footer.php";
        $conn->disconnect ();
        ?>

    </div>
    <!-- /.container -->

    <script src="/js/products-admin.js"></script>

</body>

</html>