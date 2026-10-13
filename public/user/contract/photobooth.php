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
// Photo Booth event metadata is stored in the existing contract details field.
$photoBoothDetails = [];
// get the id if set, and pull these values
if (isset ($_GET ['id'])) {
    $contract = Contract::withId($_GET['id'])->getDataArray();
}
$decodedDetails = json_decode((string) ($contract['details'] ?? ''), true);
if (is_array($decodedDetails) && isset($decodedDetails['photo_booth']) && is_array($decodedDetails['photo_booth'])) {
    $photoBoothDetails = $decodedDetails['photo_booth'];
}

?>

<div>
    <h2>Saperstone Studios LLC. Photo Booth Attendant Contract</h2>
    <input id='contract-type' type='hidden' value='photobooth'/> <input
            id='contract-session' type='hidden' value='contractor'/>
    <p>This Photo Booth Attendant Agreement ("Agreement") is entered into between <strong>Saperstone Studios
            LLC</strong> ("Company") and the undersigned Photo Booth Attendant ("Contractor").</p>
    <ol class="photo-booth-terms">
        <li><strong>Scope of Services.</strong>
            Contractor agrees to provide photo booth attendant services for the event specified below.
            Responsibilities include:
            <ul>
                <li>Arriving at the designated time for setup, training, and equipment testing.</li>
                <li>Operating and monitoring the photo booth throughout the contracted event.</li>
                <li>Greeting guests, explaining how to use the booth, and assisting with photo sessions.</li>
                <li>Maintaining an organized, clean, and inviting photo booth area.</li>
                <li>Monitoring photo printing, replenishing supplies, and addressing basic technical issues.</li>
                <li>Notifying Saperstone Studios promptly of equipment malfunctions, damage, or other significant
                    concerns.
                </li>
                <li>Remaining at the assigned photo booth station throughout the contracted hours, except for approved
                    breaks or necessary event-related duties.
                </li>
            </ul>
            Contractor is not responsible for professional event photography, photo editing, or gallery delivery
            unless separately agreed upon in writing.
        </li>
        <li><strong>Professional Conduct.</strong>
            Contractor agrees to:
            <ul>
                <li>Dress professionally in accordance with Saperstone Studios' event attire requirements, typically all
                    black unless otherwise instructed.
                </li>
                <li>Be courteous, friendly, and professional toward guests, clients, and venue staff.</li>
                <li>Refrain from smoking, vaping, consuming alcohol, or using recreational drugs while working.</li>
                <li>Limit personal phone use to emergencies or work-related communication.</li>
                <li>Avoid discussing compensation, internal business matters, or client agreements with guests or
                    vendors.
                </li>
                <li>Represent Saperstone Studios professionally throughout the event.</li>
            </ul>
        </li>
        <li><strong>Equipment and Supplies.</strong>
            All photo booth equipment, cameras, printers, tablets, lighting, props, cables, memory cards, and other
            supplies provided by Saperstone Studios remain the property of Saperstone Studios.
            <br/>
            Contractor agrees to:
            <ul>
                <li>Use equipment only for its intended purpose.</li>
                <li>Exercise reasonable care when handling, transporting, setting up, and operating equipment.</li>
                <li>Report equipment damage, loss, or malfunction promptly.</li>
                <li>Return all equipment, supplies, and memory cards as instructed at the conclusion of the event.</li>
                <li>Not copy, distribute, delete, or use event images for personal purposes without written
                    authorization.
                </li>
            </ul>
            Contractor may be held responsible for damage or loss resulting from intentional misconduct, gross
            negligence, or unauthorized use, to the extent permitted by applicable law. Contractor is not responsible
            for ordinary wear and tear or equipment failure occurring despite reasonable care.
        </li>
        <li><strong>Scheduling and Attendance.</strong>
            <p>Contractor agrees to arrive at the designated reporting time and remain until assigned duties are
                completed.</p>
            <p>Contractor must notify Saperstone Studios as soon as possible if an emergency, illness, or other
                circumstance may prevent timely attendance.</p>
            <p>Contractor may not send a substitute or leave the event early without prior authorization from Saperstone
                Studios.</p>
            <p>Additional hours must be approved by Saperstone Studios.</p>
        </li>
        <li><strong>Compensation.</strong>
            <p>Saperstone Studios agrees to compensate Contractor according to the event details below.</p>
            <p>Payment will be made the same day after completion of services, unless otherwise agreed in writing.</p>
            <p>Contractor is responsible for applicable taxes if properly classified as an independent contractor.</p>
        </li>
        <li><strong>Cancellation and Unavailability.</strong>
            <p>If Contractor is unable to fulfill the assignment, Contractor agrees to notify Saperstone Studios
                immediately.</p>
            <p>If Saperstone Studios cancels the event assignment, Contractor will be notified as soon as reasonably
                possible.</p>
            <p> Any cancellation compensation will be determined by the written terms agreed upon for the specific
                event. Contractor will be compensated for services already performed as required by applicable law.</p>
        </li>
        <li><strong>Confidentiality and Client Privacy.</strong>
            <p>Contractor agrees to maintain the confidentiality of client information, event details, business
                operations, pricing, and other nonpublic information obtained while working for Saperstone Studios.</p>
            <p>Contractor may not use client contact information for personal solicitation or share event images or
                information without authorization. </p>
        </li>
        <li><strong>Photography and Social Media.</strong>
            <p>All images, digital files, and printed photographs generated through the photo booth equipment are
                controlled by Saperstone Studios, subject to applicable client agreements and law.</p>
            <p>Contractor may not post event photographs, behind-the-scenes images, or client information on personal
                social media accounts without prior approval.</p>
        </li>
    </ol>

    <h4>AGREEMENT AND EVENT DETAILS:</h4>
    <div class="row form-group">
        <div class="col-md-3"><label for="contract-date">Event Date:</label></div>
        <div class="col-md-9"><input id="contract-date" class="form-control" type="date" value="<?php echo htmlspecialchars((string) ($contract['date'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"/></div>
    </div>
    <div class="row form-group">
        <div class="col-md-3"><label for="contract-location">Event Location:</label></div>
        <div class="col-md-9"><textarea id="contract-location" class="form-control" placeholder="Event Address"><?php echo htmlspecialchars((string) ($contract['location'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea></div>
    </div>
    <div class="row form-group">
        <div class="col-md-3"><label for="contract-arrival-time">Contractor Arrival Time:</label></div>
        <div class="col-md-9"><input id="contract-arrival-time" class="form-control keep" type="text" value="<?php echo htmlspecialchars((string) ($photoBoothDetails['arrival_time'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"/></div>
    </div>
    <div class="row form-group">
        <div class="col-md-3"><label for="contract-operating-hours">Photo Booth Operating Hours:</label></div>
        <div class="col-md-9"><input id="contract-operating-hours" class="form-control keep" type="text" value="<?php echo htmlspecialchars((string) ($photoBoothDetails['operating_hours'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"/></div>
    </div>
    <div class="row form-group">
        <div class="col-md-3"><label for="contract-amount">Compensation:</label></div>
        <div class="col-md-9"><div class="row"><div class="col-sm-6"><div class="input-group"><span class="input-group-addon">$</span><input id="contract-amount" class="form-control" type="number" step="0.01" min="0" value="<?php echo htmlspecialchars((string) ($contract['amount'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"/></div></div><div class="col-sm-6"><input id="contract-compensation-unit" class="form-control" type="text" aria-label="Compensation Unit" placeholder="per hour, event, etc." value="<?php echo htmlspecialchars((string) ($photoBoothDetails['compensation_unit'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"/></div></div></div>
    </div>

    <h4>AGREED and ACCEPTED:</h4>
    <div class='row'>
        <div class='col-md-3'>
            <label for='contract-name'>Business Name:</label>
        </div>
        <div class='col-md-9'>
            <input id='contract-name' class='form-control' type='text' placeholder='Contractor Business Name'
                   value='<?php echo $contract ['name']; ?>'/>
        </div>
    </div>
    <div class='row'>
        <div class='col-md-3'>
            <label for='contract-name-signature'>Contractor Name:</label>
        </div>
        <div class='col-md-9'>
            <input id='contract-name-signature' class='form-control keep' type='text' placeholder='Contractor Name'
                   disabled/>
        </div>
    </div>
    <div class='row'>
        <div class='col-md-3'>
            <label for='contract-signature'>Contractor Signature:</label>
        </div>
        <div class='col-md-9'>
            <textarea id='contract-signature' class='form-control'
                      placeholder='Contractor Digital Signature' disabled></textarea>
        </div>
    </div>
    <div class='row'>
        <div class='col-md-3'>
            <label for='contract-address'>Contractor Address:</label>
        </div>
        <div class='col-md-9'>
            <textarea id='contract-address' class='form-control keep'
                      placeholder='Contractor Address'><?php echo $contract ['address']; ?></textarea>
        </div>
    </div>
    <div class='row'>
        <div class='col-md-3'>
            <label for='contract-number'>Contractor Phone Number:</label>
        </div>
        <div class='col-md-9'>
            <input id='contract-number' class='form-control keep' type='tel' placeholder='Contractor Phone Number'
                   value='<?php echo $contract ['number']; ?>'/>
        </div>
    </div>
    <div class='row'>
        <div class='col-md-3'>
            <label for='contract-email'>Contractor Email: </label>
        </div>
        <div class='col-md-9'>
            <input id='contract-email' class='form-control keep' type='email' placeholder='Contractor Email'
                   value='<?php echo $contract ['email']; ?>'/>
        </div>
    </div>
    <h4>Company:</h4>
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
            5701 S Quartz St<br/>Gilbert, AZ 85298
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
</div>
