<?php
require_once "../../php/sql.php";
$conn = new Sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../../php/user.php";
$user = new User ();

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    $conn->disconnect ();
    exit ();
}

$contract = array ();
$contract ['name'] = "";
$contract ['address'] = "";
$contract ['number'] = "";
$contract ['email'] = "";
$contract ['session'] = "";
$contract ['details'] = "";
$contract ['date'] = "";
$contract ['location'] = "";
$contract ['amount'] = "";
$contract ['lineItems'] = array (
        array (
                'item' => '',
                'amount' => '',
                'unit' => '' 
        ) 
);
// get the id if set, and pull these values
if (isset ( $_GET ['id'] )) {
    $sql = "SELECT * FROM contracts WHERE id = {$_GET['id']};";
    $contract = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
    $contract ['lineItems'] = array ();
    
    $sql = "SELECT * FROM contract_line_items WHERE contract = {$_GET['id']};";
    $result = mysqli_query ( $conn->db, $sql );
    while ( $r = mysqli_fetch_assoc ( $result ) ) {
        $contract ['lineItems'] [] = $r;
    }
}
$conn->disconnect ();
?>

<div>
	<h2>Saperstone Studios LLC. Contractor Contract</h2>
	<input id='contract-type' type='hidden' value='contractor' />
	<input id='contract-session' type='hidden' value='contractor' />
	<p><strong>Saperstone Studios</strong> hereby hires the undersigned photographer (the
		"Contractor") to photograph material for Saperstone Studios and/or its
		associates on the following terms and conditions: Saperstone Studios
		will have full ownership of the resulting photographic materials
		including all rights to copyright in the same with no restrictions on
		its use thereof except as may be specifically set forth in the space
		provided below.</p>

	<p><strong>Saperstone Studios</strong> grants the Contractor a license to
		include the resulting materials in his/her "portfolio" for the limited
		purpose of demonstrating his/her work to his/her prospective
		customers. Images may not be posted online/used for advertising
		purposes until 3 months after the session date.</p>

	<p><strong>The Contractor</strong> shall produce works that contains no libelous or
		unlawful material or instructions that may cause harm or injury; it
		does not infringe upon or violate any copyright, trademark, trade
		secret or other right or the privacy of others. The Contractor shall
		hold Saperstone Studios and its associates harmless against all
		liability, including expenses and reasonable counsel fees, from any
		claim which if sustained would constitute a breach of the foregoing
		warranties.</p>
		
	<p><strong>Now therefore</strong>, in consideration of the mutual promises contained
		herein and other good and valuable consideration, the receipt and
		sufficiency of which is hereby acknowledged, the parties agree as
		follows:
	<ol>
	<li>
		<strong>Non Solicitation</strong> While Contractor is employed by
		Saperstone Studios, and 2 years thereafter, Contractor shall not:
	<ul>
		<li>encourage any consultant, independent contractor, or any other
			person or entity to end their relationship or stop doing business
			with Saperstone Studios, or help any person or entity do so or
			attempt to do so;</li>

		<li>solicit or attempt to solicit or obtain business or trade from
			Saperstone Studios current client in connection to this assignment or
			help any person or entity do so or attempt to do so; or</li>

		<li>obtain or attempt to obtain any Confidential Information for any
			purpose whatsoever except as required by Saperstone Studios to enable
			Employee to perform his or her job duties.</li>
	</ul></li>
	<li>		<strong>Image Delivery</strong> The Contractor shall use their own
		camera equipment and memory cards. RAW files will be uploaded to a web
		gallery link provided by Saperstone Studios within 1 week of event
		date.
	</li>

	<li>
		<strong>Compensation</strong> In consideration of the Services,
		Saperstone Studios agrees to pay Contractor the following amounts as
		follows:
		<?php
            foreach ( $contract ['lineItems'] as $lineItem ) {
                ?>
                <span class='contract-line-item'> <input
					class='form-control contract-item'
					style='width: initial; display: initial;' type='text'
					placeholder='Item' value='<?php echo $lineItem['item']; ?>' />: $<input
					class='form-control contract-amount'
					style='width: initial; display: initial;' type='number' step='0.01'
					min='0' placeholder='Amount'
					value='<?php echo $lineItem['amount']; ?>' /> / <input
					class='form-control contract-unit'
					style='width: initial; display: initial;' type='text'
					placeholder='Unit' value='<?php echo $lineItem['unit']; ?>' />
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
				<br />
	</li>
	<li>
		<strong>Session Details</strong> The above session will take place at
		the below location on <input id='contract-date' class='form-control'
			style='width: initial; display: initial;' type='date'
			placeholder='Date' value='<?php echo $contract ['date']; ?>' /> <br />
		<textarea id='contract-location' class='form-control' type='text'
			placeholder='Session Address'
			value='<?php echo $contract ['location']; ?>'></textarea>
	</li></ol></p>


	<h4>AGREED and ACCEPTED:</h4>
	<div class='row'>
		<div class='col-md-3'>
			<strong>Business Name:</strong>
		</div>
		<div class='col-md-9'>
			<input id='contract-name' class='form-control' type='text'
				placeholder='Contractor Business Name' value='<?php echo $contract ['name']; ?>' />
		</div>
	</div>
	<div class='row'>
		<div class='col-md-3'>
			<strong>Contractor Name:</strong>
		</div>
		<div class='col-md-9'>
			<input id='contract-name-signature' class='form-control keep'
				type='text' placeholder='Contractor Name' disabled />
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
				value='<?php echo $contract ['number']; ?>' />
		</div>
	</div>
	<div class='row'>
		<div class='col-md-3'>
			<strong>Contractor Email: </strong>
		</div>
		<div class='col-md-9'>
			<input id='contract-email' class='form-control keep' type='email'
				placeholder='Contractor Email'
				value='<?php echo $contract ['email']; ?>' />
		</div>
	</div>
	<h4>Photographer:</h4>
	<div class='row'>
		<div class='col-md-3'>
			<strong>Name:</strong>
		</div>
		<div class='col-md-9'>
			Saperstone Studios<br /> Leigh Ann Saperstone
		</div>
	</div>
	<div class='row'>
		<div class='col-md-3'>
			<strong>Address: </strong>
		</div>
		<div class='col-md-9'>
			5012 Whisper Willow Dr.<br />Fairfax, VA 22030
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