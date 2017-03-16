<?php
session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "php/sql.php";
$conn = new Sql ();
$conn->connect ();

$contract_link;
// if no album is set, throw a 404 error
if (! isset ( $_GET ['c'] )) {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include "errors/404.php";
    exit ();
} else {
    $contract_link = mysqli_real_escape_string ( $conn->db, $_GET ['c'] );
}

$sql = "SELECT * FROM `contracts` WHERE link = '" . $contract_link . "';";
$contract_info = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
// if the album doesn't exist, throw a 404 error
if (! $contract_info ['name']) {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include "errors/404.php";
    $conn->disconnect ();
    exit ();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once "header.php"; ?>

</head>

<body>

    <?php require_once "nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Saperstone Studios Contracts</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li class="active">Information</li>
                    <li class="active">Contract</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

		<?php echo $contract_info['content']; ?>

        <?php require_once "footer.php"; ?>

    </div>
    <!-- /.container -->

</body>

</html>
