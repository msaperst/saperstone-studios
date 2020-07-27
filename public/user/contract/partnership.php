<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();
$user = new User ($sql);
$user->forceAdmin();

$contract = array ();
$contract ['name'] = "";
$contract ['address'] = "";
$contract ['number'] = "";
$contract ['email'] = "";

// get the id if set, and pull these values
if (isset ( $_GET ['id'] )) {
    $contract = $sql->getRow( "SELECT * FROM contracts WHERE id = {$_GET['id']};" );
    $contract ['lineItems'] = $sql->getRows( "SELECT * FROM contract_line_items WHERE contract = {$_GET['id']};" );
}
$sql->disconnect ();
?>

<div>
    <h2>Saperstone Studios LLC. Partnership Contract</h2>
    <input id='contract-type' type='hidden' value='partnership' />
    <p>
        <strong>This Contract</strong> is made by and between <u>&nbsp;Saperstone
            Studios&nbsp;</u> (the "Photographer") and <u>&nbsp;<input
            id='contract-name' class='form-control'
            style='width: initial; display: initial;' type='text'
            placeholder='Client Name' value='<?php echo $contract ['name']; ?>' />&nbsp;
        </u>(the "Client").<br /> <strong>Whereas</strong>, Client wishes to partner with
        Photographer to mutually benefit each other's businesses in which Photographer
        provides photography services in exchange for marketing opportunities through
        Client's business. Both parties agree to engage in this relationship for 1 year
        after contract has been signed with option to continue relationship. Photographer
        is willing to accept such engagement, all on the terms and conditions set forth
        herein. Now therefore, in consideration of the mutual promises contained herein
        and other good and valuable consideration, the receipt and sufficiency of which
        is hereby acknowledged, the parties agree as follows:
    </p>
    <ol>
        <li><strong>Services.</strong> Photographer hereby agrees to provide the photography
            services set forth on the attached Statement of Services (the "Services") to
            the best of her abilities. Client hereby agrees to provide marketing and accurate
            representation of Photographer services in exchange for provided Services.</li>
        <li><strong>Compensation.</strong> Photographer agrees to provide all services
            as listed below.</li>
        <li><strong>Session Details.</strong> Client may terminate this Contract at any
            time upon 30 days advance written notice. If Client chooses to terminate
            contract early without due cause, Client shall be liable for all expenses
            incurred and equivalent value of services provided by Photographer. Photographer
            shall have the right to terminate this Agreement upon 30 days advance written
            notice.</li>
        <li><strong>Standard Terms.</strong> Attached hereto is a statement of Standard
            Terms and Conditions which will apply to the relationship between Photographer
            and Client; such terms and conditions are incorporated herein by this reference.
            In witness whereof, the undersigned have caused this Contract to be executed as
            of the date first above written.</li>
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
                placeholder='Client Business Address'
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
    <input id='contract-session' class='form-control' type='hidden'
                    placeholder='Session' value='partnership-session' />
    <p>
        <strong>Services.</strong> Photographer hereby agrees to provide the
        photography services for Client as outlined below.
    </p>
    <p>
        <strong>Business Headshots.</strong> Provided no more than 3 times annually
        upon mutually agreed upon dates and locations of current employees. Sessions
        will be a total of 3-5 minutes per person and include a web gallery for making
        selects. One file of Clients choice will be retouched and color corrected per
        person. Additional files can be purchased for $80/file.
    </p>
    <p>
        <strong>Commercial Photography.</strong> Defined as photography of Client's
        office space, Client engaging with patients and Client's product photography
        needs. Provided no more than 3 times annually upon mutually agreed upon dates
        and locations. Sessions will be up to 1 hour to cover a shot list provided by
        Client. All images will be delivered for download via web gallery.
    </p>
    <h3>Standard Terms and Conditions</h3>
    <p>
        <strong>Copyright.</strong> The photographs produced by Photographer shall be
        the intellectual property and copyrighted works of Photographer, and shall not
        be deemed to be works made for hire. Photographer agrees to give a limited usage
        license to Client for reproduction and display of selected photographs, including
        private or internal use by Client or its employees and reasonable use in business
        communications by Client (such as website, newsletter, marketing materials or
        annual report). Additionally, the files delivered may only be reproduced for
        Client’s use and cannot under any circumstances be sold, licensed, or used by a
        third-party without written authorization from Photographer. Photographer retains
        creative control over the images produced and all decisions made by Photographer
        with regards to edits are deemed final.
    </p>
    <p>
        <strong>Models.</strong> All models needed for above services provided by Photographer
        will be provided by Client. Photographer is not held liable for any issues with models,
        including attendance. Client is responsible for obtaining release of models likeliness
        for all print and web published needs for both Client and Photographer.
    </p>
    <p>
        <strong>Artwork on Display.</strong> Client agrees to hang Photographer’s artwork on
        their office space walls for purposes of marketing/advertising Photographer's business.
        Images hung will be agreed upon by both parties. Installations will also have
        Photographer’s marketing materials and/or business cards hung next to them for easy
        accessibility. Artwork is property of Photographer and may not be changed, manipulated or
        taken down without prior conversation/consent from Photographer. In the event of the
        Client/Photographer relationship ending, all artwork will be returned to photographer in
        the state it was delivered in.
    </p>
    <p>
        <strong>Installation.</strong> Artwork will be hung by Photographer at an agreed upon
        date and time by both parties. Photographer will not be held responsible for any damages
        to Client's property.
    </p>
    <p>
        <strong>Exclusivity.</strong> Photographer shall be the sole photographer allowed to market
        within all of Client's locations. No other photographer shall have permission to distribute
        marketing materials and/or display artwork in conjunction with Clients business.
    </p>
    <p>
        <strong>Credit.</strong> In any publication, display, exhibit or other permitted use of
        images, Client agrees to include photo credit to Photographer and link to Photographer’s
        website or social media outlets as applicable. In addition, in any publication in which
        artists are recognized, Client shall include a brief biography of Photographer approved by
        Photographer of comparable position and prominence to other artists whose work is included.
    </p>
    <p>
        <strong>Retouch.</strong> Retouching is included but at the Photographer’s discretion.
        Any additional retouch requests are handled on a case-by-case basis and a separate fee
        may be negotiated.
    </p>
    <p>
        <strong>Scheduling.</strong> Client will be responsible for ensuring access and authority
        to enable Photographer to shoot on the dates agreed upon. If Photographer is unable to
        shoot on the dates scheduled for reasons beyond Photographer’s or Client’s reasonable
        control, the parties will endeavor to agree on mutually acceptable alternative dates,
        subject to Photographer availability.
    </p>
    <p>
        <strong>Color Changes.</strong> Client recognizes that color dyes in photography may
        fade or discolor over time due to the inherent qualities of dyes; Photographer shall
        have no liability for any claims based upon fading or discoloration due to such inherent
        qualities.
    </p>
    <p>
        <strong>Advertising.</strong> Client gives Photographer permission to
        use photographs for advertising (example: website).
    </p>
    <p>
        <strong>Payment & Charges.</strong> If any payment due to Photographer is not made
        within 30 days of its due date, Photographer shall have the option to assess a service
        charge equal 1.5% of the outstanding amount each month (or fraction thereof) thereafter
        that payment is late. If Photographer institutes any proceeding to obtain payment from
        Client, Client will pay any costs and expenses (including reasonable attorney's fees)
        incurred by Photographer in connection therewith. If check payment does not process,
        client is liable for all processing, late and overdraft charges.
    </p>
    <p>
        <strong>Limit on Liability.</strong> Absent willful misconduct or infringement by
        Photographer of intellectual property rights of any third party, the liability of
        Photographer to Client arising out of the Services shall not exceed the equivalent value
        of services rendered by Photographer hereunder. In no event shall Photographer have any
        liability for special, incidental, indirect, consequential or punitive damages hereunder.
    </p>
    <p>
        <strong>Permissions.</strong> Client shall be solely responsible for obtaining all
        permissions and consent of persons whose photographs are taken and of owners of property
        and indemnify Photographer from any claims, costs and expenses arising from any
        failure to do so.
    </p>
    <p>
        <strong>Relationship.</strong> The relationship between Photographer and Client is one
        of independent contractor. Photographer shall be responsible for payment of all federal,
        state and local income, payroll and withholding taxes and assessments arising from or
        attributable to receipt of payments hereunder.
    </p>
    <p>
        <strong>Miscellaneous.</strong> <em>(a)</em> The provisions hereof shall survive termination of
        this Agreement; <em>(b)</em> this Agreement will be governed by the laws of the State of
        Virginia <em>(c)</em> this Agreement contains the entire agreement between the parties with
        respect to the subject matter hereof and supersedes all prior understandings among the
        parties with respect thereto; this Agreement may be changed only in writing signed by
        the party against whom enforcement is sought; <em>(d)</em> this Agreement may be executed in
        counterpart originals, each of which shall constitute an original and all of which together
        shall constitute a single document and shall be effective upon execution by both parties;
        and <em>(e)</em> this Agreement shall be binding upon the parties hereto and their respective
        successors and assigns; no party shall assign its duties or obligations hereunder without
        the prior written consent of the other party hereto.
    </p>
</div>