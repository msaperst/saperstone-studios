<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$user = User::fromSystem();
$user->forceAdmin();

$contract = array();
$contract ['name'] = "";
$contract ['address'] = "";
$contract ['number'] = "";
$contract ['email'] = "";
$contract ['session'] = "";
$contract ['details'] = "";
$contract ['date'] = "";
$contract ['location'] = "";
$contract ['amount'] = "";
$contract ['lineItems'] = array(
    array(
        'item' => '',
        'amount' => '',
        'unit' => ''
    )
);
// get the id if set, and pull these values
if (isset ($_GET ['id'])) {
    $sql = new Sql ();
    $contract = $sql->getRow("SELECT * FROM contracts WHERE id = {$_GET['id']};");
    $contract ['lineItems'] = $sql->getRows("SELECT * FROM contract_line_items WHERE contract = {$_GET['id']};");
    $sql->disconnect();
}
?>

<div>
    <h2>Saperstone Studios LLC. Photobooth Contract</h2>
    <input id='contract-type' type='hidden' value='contractor'/> <input
            id='contract-session' type='hidden' value='contractor'/>
    <p>
        <strong>Saperstone Studios</strong> hereby hires the undersigned
        photographer (the "Contractor") to photograph material for Saperstone
        Studios and/or its associates on the following terms and conditions:
        Saperstone Studios will have full ownership of the resulting
        photographic materials including all rights to copyright in the same
        with no restrictions on its use thereof except as may be specifically
        set forth in the space provided below.
    </p>

    <p>
        <strong>Saperstone Studios</strong> grants the Contractor a license to
        include the resulting materials in his/her "portfolio" for the limited
        purpose of demonstrating his/her work to his/her prospective
        customers. Images may not be posted online/used for advertising
        purposes until 3 months after the session date.
    </p>

    <p>
        <strong>The Contractor</strong> shall produce works that contains no
        libelous or unlawful material or instructions that may cause harm or
        injury; it does not infringe upon or violate any copyright, trademark,
        trade secret or other right or the privacy of others. The Contractor
        shall hold Saperstone Studios and its associates harmless against all
        liability, including expenses and reasonable counsel fees, from any
        claim which if sustained would constitute a breach of the foregoing
        warranties.
    </p>

    <p>
        <strong>Now therefore</strong>, in consideration of the mutual
        promises contained herein and other good and valuable consideration,
        the receipt and sufficiency of which is hereby acknowledged, the
        parties agree as follows:


    <ol>
        <li><strong>Non Solicitation</strong> While Contractor is employed by
            Saperstone Studios, and 2 years thereafter, Contractor shall not:
            <ul>
                <li>encourage any consultant, independent contractor, or any other
                    person or entity to end their relationship or stop doing business
                    with Saperstone Studios, or help any person or entity do so or
                    attempt to do so;
                </li>

                <li>solicit or attempt to solicit or obtain business or trade from
                    Saperstone Studios current client in connection to this assignment
                    or help any person or entity do so or attempt to do so; or
                </li>

                <li>obtain or attempt to obtain any Confidential Information for any
                    purpose whatsoever except as required by Saperstone Studios to
                    enable Employee to perform his or her job duties.
                </li>
            </ul>
        </li>
        <li><strong>Image Delivery</strong> The Contractor shall use the provided
            equipment and memory cards.
        </li>

        <li><strong>Compensation</strong> In consideration of the Services,
            Saperstone Studios agrees to pay Contractor the following amounts as
            follows:
            <?php
            foreach ($contract ['lineItems'] as $lineItem) {
                ?>
                <span class='contract-line-item'> <input
                            class='form-control contract-item'
                            style='width: initial; display: initial;' type='text'
                            placeholder='Item' value='<?php echo $lineItem['item']; ?>'/>: $<input
                            class='form-control contract-amount'
                            style='width: initial; display: initial;' type='number' step='0.01'
                            min='0' placeholder='Amount'
                            value='<?php echo $lineItem['amount']; ?>'/> / <input
                            class='form-control contract-unit'
                            style='width: initial; display: initial;' type='text'
                            placeholder='Unit' value='<?php echo $lineItem['unit']; ?>'/>
                <button type="button"
                        class="btn btn-xs btn-danger remove-contract-line-item-btn"
                        data-toggle="tooltip" data-placement="right"
                        title="Remove Line Item">
                    <em class="fa fa-minus"></em>
                </button>
        </span>
                <?php
            }
            ?>
            <button id="add-contract-line-item-btn" type="button"
                    class="btn btn-xs btn-success" data-toggle="tooltip"
                    data-placement="right" title="Add New Line Item">
                <em class="fa fa-plus"></em>
            </button>
            <br/></li>
        <li><strong>Session Details</strong> The above session will take place
            at the below location on <input id='contract-date'
                                            class='form-control' style='width: initial; display: initial;'
                                            type='date' placeholder='Date'
                                            value='<?php echo $contract ['date']; ?>'/> <br/> <textarea
                    id='contract-location' class='form-control' type='text'
                    placeholder='Session Address'
                    value='<?php echo $contract ['location']; ?>'></textarea></li>
    </ol>
    </p>


    <h4>AGREED and ACCEPTED:</h4>
    <div class='row'>
        <div class='col-md-3'>
            <strong>Business Name:</strong>
        </div>
        <div class='col-md-9'>
            <input id='contract-name' class='form-control' type='text'
                   placeholder='Contractor Business Name'
                   value='<?php echo $contract ['name']; ?>'/>
        </div>
    </div>
    <div class='row'>
        <div class='col-md-3'>
            <strong>Contractor Name:</strong>
        </div>
        <div class='col-md-9'>
            <input id='contract-name-signature' class='form-control keep'
                   type='text' placeholder='Contractor Name' disabled/>
        </div>
    </div>
    <div class='row'>
        <div class='col-md-3'>
            <strong>Contractor Signature:</strong>
        </div>
        <div class='col-md-9'>
            <textarea id='contract-signature' class='form-control' type='text'
                      placeholder='Contractor Digital Signature' disabled></textarea>
        </div>
    </div>
    <div class='row'>
        <div class='col-md-3'>
            <strong>Contractor Address:</strong>
        </div>
        <div class='col-md-9'>
            <textarea id='contract-address' class='form-control keep' type='text'
                      placeholder='Contractor Address'
                      value='<?php echo $contract ['address']; ?>'></textarea>
        </div>
    </div>
    <div class='row'>
        <div class='col-md-3'>
            <strong>Contractor Phone Number:</strong>
        </div>
        <div class='col-md-9'>
            <input id='contract-number' class='form-control keep' type='tel'
                   placeholder='Contractor Phone Number'
                   value='<?php echo $contract ['number']; ?>'/>
        </div>
    </div>
    <div class='row'>
        <div class='col-md-3'>
            <strong>Contractor Email: </strong>
        </div>
        <div class='col-md-9'>
            <input id='contract-email' class='form-control keep' type='email'
                   placeholder='Contractor Email'
                   value='<?php echo $contract ['email']; ?>'/>
        </div>
    </div>
    <h4>Photographer:</h4>
    <div class='row'>
        <div class='col-md-3'>
            <strong>Name:</strong>
        </div>
        <div class='col-md-9'>
            Saperstone Studios<br/> Leigh Ann Saperstone
        </div>
    </div>
    <div class='row'>
        <div class='col-md-3'>
            <strong>Address: </strong>
        </div>
        <div class='col-md-9'>
            6144 S Teresa Dr<br/>Chandler, AZ 85249
        </div>
    </div>
    <div class='row'>
        <div class='col-md-3'>
            <strong>Phone Number:</strong>
        </div>
        <div class='col-md-9'>
            <a target="_blank" href="tel:5712660004">(571) 266-0004</a>
        </div>
    </div>
    <div class='row'>
        <div class='col-md-3'>
            <strong>Email:</strong>
        </div>
        <div class='col-md-9'>
            <a target='_blank' href='mailto:contracts@saperstonestudios.com'>contracts@saperstonestudios.com</a>
        </div>
    </div>
    </p>
</div>
