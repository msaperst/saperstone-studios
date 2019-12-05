<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
$conn = new Sql ();
$conn->connect ();

$contract_link;
// if no contract is set, throw a 404 error
if (! isset ( $_GET ['c'] ) || $_GET ['c'] == "") {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/errors/404.php";
    exit ();
} else {
    $contract_link = mysqli_real_escape_string ( $conn->db, $_GET ['c'] );
}

$sql = "SELECT * FROM `contracts` WHERE link = '" . $contract_link . "';";
$contract_info = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
// if the contract doesn't exist, throw a 404 error
if (! $contract_info ['link']) {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/errors/404.php";
    $conn->disconnect ();
    exit ();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>

</head>

<body>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

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

        <input type='hidden' id='contract-id'
            value='<?php echo $contract_info['id']; ?>' />

        <?php
            // if the contract is already signed
            if ($contract_info ['file']) {
        ?>
        <embed src='<?php echo substr( $contract_info['file'], 2 ); ?>' type="application/pdf" width="100%" height="600px" />
        <?php
            } else {
        ?>

        <div id='contract'>
        <?php echo $contract_info['content']; ?>
        </div>

        <hr />

        <div class="row">
            <div class="col-md-2 text-left">
                <div id='contract-initial-holder' class='signature-holder'>
                    Initial inside the dotted area
                    <div id='contract-initial' class='signature'></div>
                </div>
            </div>
            <div id='contract-messages' class="col-md-8 text-center">
                <?php
                if ($contract_info ['invoice'] != null && $contract_info ['invoice'] != "") {
                    ?>
                    <a target='_blank'
                    href='
                    <?php
                    echo $contract_info ['invoice'];
                    ?>
                    '>Paypal Invoice Link</a>
                    <?php
                }
                ?>
            </div>
            <div class="col-md-2 text-right">
                <button id='contract-submit' class='btn btn-success disabled'
                    disabled>
                    <em class='fa fa-paper-plane'></em> Submit Contract
                </button>
            </div>
        </div>
        <?php
            }
        ?>
    </div>

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

    <!--[if lt IE 9]>
    <script type="text/javascript" src="/js/jSignature/flashcanvas.js"></script>
    <![endif]-->
    <script src="/js/jSignature/jSignature.min.js"></script>
    <script src="/js/contract.js"></script>

</body>

</html>
