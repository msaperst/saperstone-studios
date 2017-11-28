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

$contract;
$contract ['name'] = "";
$contract ['date'] = "";
$contract ['address'] = "";
$contract ['number'] = "";
$contract ['email'] = "";
$contract ['session'] = "";
$contract ['details'] = "";
$contract ['amount'] = "";
$contract ['deposit'] = "";
$contract ['invoice'] = "";
$contract ['ceremony-name'] = "";
$contract ['ceremony-address'] = "";
$contract ['ceremony-number'] = "";
$contract ['ceremony-email'] = "";
$contract ['reception-name'] = "";
$contract ['reception-address'] = "";
$contract ['reception-number'] = "";
$contract ['reception-email'] = "";
// get the id if set, and pull these values
if (isset ( $_GET ['id'] )) {
    $sql = "SELECT * FROM contracts WHERE id = {$_GET['id']};";
    $contract = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
}
$conn->disconnect ();
?>

<div>
	<h2>Saperstone Studios LLC. Wedding Contract</h2>
	<input id='contract-type' type='hidden' value='wedding' />
	<p>
		<strong>This Contract</strong> is made by and between <u>&nbsp;Saperstone
			Studios&nbsp;</u> (the "Photographer") and <u>&nbsp;<input
			id='contract-name' class='form-control'
			style='width: initial; display: initial;' type='text'
			placeholder='Client Name' value='<?php echo $contract ['name']; ?>' />&nbsp;
		</u> (the "Client").<br /> <strong>Whereas</strong>, Client wishes to
		engage Photographer to provide certain photography services and
		Photographer is willing to accept such engagement, all on the terms
		and conditions set forth herein.<br /> <strong>Now therefore</strong>,
		in consideration of the mutual promises contained herein and other
		good and valuable consideration, the receipt and sufficiency of which
		is hereby acknowledged, the parties agree as follows:
	</p>
	<ol>
		<li><strong>Services.</strong> Photographer hereby agrees to provide
			the photography services set forth on the attached Statement of
			Services (the "Services") to the best of her abilities.</li>
		<li><strong>Compensation.</strong> In consideration of the Services,
			Client agrees to pay Photographer the following amounts as follows:
			<p>
				<select id='contract-session' class='form-control'
					style='width: initial; display: initial;' type='text'
					placeholder='Wedding Package'
					value='<?php echo $contract ['session']; ?>'>
					<option cost=''></option>
					<option cost='4300.00'
						details='800+ color managed and retouched images with print release
9 hours of wedding day coverage
2nd photographer
Engagement session
Engagement sign in book
Photobooth
Web gallery for sharing
USB of final images
$200 print credit'
						<?php if ( $contract ['session'] == "Platinum Package" ) { echo " selected"; } ?>>Platinum
						Package</option>
					<option cost='3600.00'
						details='700 color managed and retouched images with print release
8 hours of wedding day coverage
2nd Photographer
Engagement session
Web gallery for sharing
USB of final images
$150 print credit'
						<?php if ( $contract ['session'] == "Gold Package" ) { echo " selected"; } ?>>Gold
						Package</option>
					<option cost='2900.00'
						details='500 color managed and retouched images with print release
5 hours of wedding day coverage
2nd Photographer
Engagement session
Web gallery for sharing
USB of final images
$100 print credit'
						<?php if ( $contract ['session'] == "Silver Package" ) { echo " selected"; } ?>>Silver
						Package</option>
					<option cost=''>Custom</option>
				</select>: $<input id='contract-amount' class='form-control'
					style='width: initial; display: initial;' type='number' step='0.01'
					min='0' placeholder='Amount'
					value='<?php echo $contract ['amount']; ?>' /><br /> <input
					id='contract-invoice' class='form-control' type='text'
					placeholder='Invoice Link'
					value='<?php echo $contract ['invoice']; ?>' />
			</p>
			<p>
				Non refundable deposit of $<input id='contract-deposit'
					class='form-control' style='width: initial; display: initial;'
					type='number' step='0.01' min='0' placeholder='Deposit'
					value='<?php echo $contract ['deposit']; ?>' /> and contract to
				reserve the date <input id='contract-date' class='form-control'
					style='width: initial; display: initial;' type='date'
					placeholder='Date' value='<?php echo $contract ['date']; ?>' /> <br />
				Checks should be made payable to <em>Saperstone Studios</em> and
				mailed to <em>5012 Whisper Willow Dr, Fairfax VA 22030</em> <br />Remaining
				balance due 3 weeks prior to wedding date.
			</p></li>
		<li><strong>Term.</strong> The initial term of this Contract shall
			commence on the date hereof and terminate upon completion of the
			services.</li>
		<li><strong>Standard Terms.</strong> Attached hereto is a statement of
			Standard Terms and Conditions which will apply to the relationship
			between Photographer and Client; such terms and conditions are
			incorporated herein by this reference.</li>
	</ol>
	<p>
		<strong>In witness whereof</strong>, the undersigned have caused this
		Contract to be executed as of the date first above written.
	
	
	<h4>Client:</h4>
	<div class='row'>
		<div class='col-md-3'>
			<strong>Name:</strong>
		</div>
		<div class='col-md-9'>
			<input id='contract-name-signature' class='form-control keep'
				type='text' placeholder='Client Name' disabled />
		</div>
	</div>
	<div class='row'>
		<div class='col-md-3'>
			<strong>Signature:</strong>
		</div>
		<div class='col-md-9'>
			<textarea id='contract-signature' class='form-control' type='text'
				placeholder='Client Digital Signature' disabled></textarea>
		</div>
	</div>
	<div class='row'>
		<div class='col-md-3'>
			<strong>Address:</strong>
		</div>
		<div class='col-md-9'>
			<textarea id='contract-address' class='form-control keep' type='text'
				placeholder='Client Address'
				value='<?php echo $contract ['address']; ?>'></textarea>
		</div>
	</div>
	<div class='row'>
		<div class='col-md-3'>
			<strong>Phone Number:</strong>
		</div>
		<div class='col-md-9'>
			<input id='contract-number' class='form-control keep' type='tel'
				placeholder='Client Phone Number'
				value='<?php echo $contract ['number']; ?>' />
		</div>
	</div>
	<div class='row'>
		<div class='col-md-3'>
			<strong>Email: </strong>
		</div>
		<div class='col-md-9'>
			<input id='contract-email' class='form-control keep' type='email'
				placeholder='Client Email'
				value='<?php echo $contract ['email']; ?>' />
		</div>
	</div>
	<h4>Ceremony Venue:</h4>
	<div class='row'>
		<div class='col-md-3'>
			<strong>Name: </strong>
		</div>
		<div class='col-md-9'>
			<input id='contract-ceremony-venue-name' class='form-control keep'
				type='text' placeholder='Ceremony Venue Name'
				value='<?php echo $contract ['ceremony-name']; ?>' />
		</div>
	</div>
	<div class='row'>
		<div class='col-md-3'>
			<strong>Address: </strong>
		</div>
		<div class='col-md-9'>
			<textarea id='contract-ceremony-venue-address'
				class='form-control keep' type='text'
				placeholder='Ceremony Venue Address'
				value='<?php echo $contract ['ceremony-address']; ?>'></textarea>
		</div>
	</div>
	<div class='row'>
		<div class='col-md-3'>
			<strong>Phone Number: </strong>
		</div>
		<div class='col-md-9'>
			<input id='contract-ceremony-venue-number' class='form-control keep'
				type='text' placeholder='Ceremony Venue Phone Number'
				value='<?php echo $contract ['ceremony-number']; ?>' />
		</div>
	</div>
	<div class='row'>
		<div class='col-md-3'>
			<strong>Email: </strong>
		</div>
		<div class='col-md-9'>
			<input id='contract-ceremony-venue-email' class='form-control keep'
				type='text' placeholder='Ceremony Venue Email'
				value='<?php echo $contract ['ceremony-email']; ?>' />
		</div>
	</div>
	<h4>Reception Venue:</h4>
	<div class='row'>
		<div class='col-md-3'>
			<strong>Name: </strong>
		</div>
		<div class='col-md-9'>
			<input id='contract-reception-venue-name' class='form-control keep'
				type='text' placeholder='Reception Venue Name'
				value='<?php echo $contract ['ceremony-name']; ?>' />
		</div>
	</div>
	<div class='row'>
		<div class='col-md-3'>
			<strong>Address: </strong>
		</div>
		<div class='col-md-9'>
			<textarea id='contract-reception-venue-address'
				class='form-control keep' type='text'
				placeholder='Reception Venue Address'
				value='<?php echo $contract ['ceremony-address']; ?>'></textarea>
		</div>
	</div>
	<div class='row'>
		<div class='col-md-3'>
			<strong>Phone Number: </strong>
		</div>
		<div class='col-md-9'>
			<input id='contract-reception-venue-number' class='form-control keep'
				type='text' placeholder='Reception Venue Phone Number'
				value='<?php echo $contract ['ceremony-number']; ?>' />
		</div>
	</div>
	<div class='row'>
		<div class='col-md-3'>
			<strong>Email: </strong>
		</div>
		<div class='col-md-9'>
			<input id='contract-reception-venue-email' class='form-control keep'
				type='text' placeholder='Reception Venue Email'
				value='<?php echo $contract ['ceremony-email']; ?>' />
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
	<h3>Exhibit A: Statement Of Services</h3>
	<div class='row'>
		<div class='col-md-3'>
			<strong>Project/Assignment:</strong>
		</div>
		<div class='col-md-9'>
			Wedding Photography | <input id='contract-session-dup'
				class='form-control' style='width: initial; display: initial;'
				type='text' placeholder='Wedding Package'
				value='<?php echo $contract ['session']; ?>' disabled /> |
			photojournalistic style documentation
		</div>
	</div>
	<textarea id='contract-details' class='form-control' type='text'
		placeholder='Wedding Package Details'
		value='<?php echo $contract ['details']; ?>'></textarea>
	<h3>Standard Terms and Conditions</h3>
	<p>
		<strong>Copyright.</strong> The photographs produced by Photographer
		shall be the intellectual property and copyrighted works of
		Photographer, and shall not be deemed to be works made for hire.
		Photographer agrees to give, upon payment in full, a limited usage
		license to Client for personal, non-commercial reproduction and
		display of selected photographs. Additionally, the files delivered may
		only be reproduced for personal use and cannot under any circumstance
		be sold or used for business, advertising, or trade purposes without
		written authorization from Photographer. Photographer retains creative
		control over the images produced and all decisions made by
		Photographer with regards to edits are deemed final.
	</p>
	<p>
		<strong>Credit.</strong> In any publication, display, exhibit or other
		permitted use of the photographs of Photographer, Client agrees to
		include photo credit to Photographer and include a copyright notice
		evidence of Photographer’s ownership thereof. In addition, in any
		publication in which the artists are recognized, Client shall include
		a brief biography of Photographer approved by Photographer of
		comparable position and prominence to other artists whose work is
		included.
	</p>
	<p>
		<strong>Retouch.</strong> Retouching is included but at the
		Photographers discretion. Any additional retouch requests are handled
		on a case by case basis and a separate fee may be negotiated.
	</p>
	<p>
		<strong>Scheduling.</strong> Client will be responsible for insuring
		access and authority to enable Photographer to shoot on the dates set
		forth. If Photographer is unable to shoot on the dates scheduled for
		reasons beyond Photographer’s or Client’s reasonable control, the
		parties will endeavor to agree on mutually acceptable alternative
		dates, subject to Photographer availability.
	</p>
	<p>
		<strong>Color Changes.</strong> Client recognizes that color dyes in
		photography may fade or discolor over time due to the inherent
		qualities of dyes; Photographer shall have no liability for any claims
		based upon fading or discoloration due to such inherent qualities.
	</p>
	<p>
		<strong>Advertising.</strong> Client gives Photographer permission to
		use photographs for advertising (example: website).
	</p>
	<p>
		<strong>Payment & Charges.</strong> If any payment due Photographer is
		not be made within 30 days of its due date, Photographer shall have
		the options to assess a service charge equal 1.5% of the outstanding
		amount each month (or fraction thereof) thereafter that payment is
		late. If Photographer institutes any proceeding to obtain payment from
		Client, Client will pay any costs and expenses (including reasonable
		attorney's fees) incurred by Photographer in connection therewith. If
		check payment does not process, client is liable for all processing,
		late and overdraft charges.
	</p>
	<p>
		<strong>Limit on Liability.</strong> Absent willful misconduct or
		infringement by Photographer of intellectual property rights of any
		third party, the liability of Photographer to Client arising out of
		the Services shall not exceed the fees paid to Photographer hereunder.
		In no event shall Photographer have any liability for special,
		incidental, indirect, consequential or punitive damages hereunder.
	</p>
	<p>
		<strong>Permissions.</strong> Client shall be solely responsible for
		obtaining all permissions and consent of persons whose photographs are
		taken and of owners of property and indemnify Photographer from any
		claims, costs and expenses arising from any failure to do so.
	</p>
	<p>
		<strong>Relationship.</strong> The relationship between Photographer
		and Client is one of independent contractor. Photographer shall be
		responsible for payment of all federal, state and local income,
		payroll and withholding taxes and assessments arising from or
		attributable to receipt of payments hereunder.
	</p>
	<p>
		<strong>Miscellaneous.</strong> (a) The provisions hereof shall
		survive termination of this Agreement; (b) this Agreement will be
		governed by the laws of the State of Virginia (c) this Agreement
		contains the entire agreement between the parties with respect to the
		subject matter hereof and supersedes all prior understandings among
		the parties with respect thereto; this Agreement may be changed only
		in writing signed by the party against whom enforcement is sought; (d)
		this Agreement may be executed in counterpart originals, each of which
		shall constitute an original and all of which together shall
		constitute a single document and shall be effective upon execution by
		both parties; and (e) this Agreement shall be binding upon the parties
		hereto and their respective successors and assigns; no party shall
		assign its duties or obligations hereunder without the prior written
		consent of the other party hereto.
	</p>
</div>