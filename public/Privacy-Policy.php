<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
</head>
<body>
<?php require_once dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

<div class="page-content container">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header text-center">Privacy Policy</h1>
            <ol class="breadcrumb">
                <li><a href="/">Home</a></li>
                <li class="active">Information</li>
                <li class="active">Privacy</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <p><strong>Effective September 23, 2026</strong></p>
            <p>Saperstone Studios LLC ("Saperstone Studios," "we," "us," or "our") operates
                saperstonestudios.com. This policy explains what information the website collects,
                why we use it, when it may be shared, and the choices available to you.</p>

            <h3>Information we collect</h3>
            <p>Depending on how you use the site, we may collect:</p>
            <ul>
                <li><strong>Contact and account information,</strong> such as your name, email address,
                    telephone number, mailing address, username, and password hash.</li>
                <li><strong>Photography-service information,</strong> including contracts, signatures,
                    initials, session details, invoices or payment links, album access, image selections,
                    favorites, comments, and photographs associated with your services.</li>
                <li><strong>Communications,</strong> including information you submit through contact,
                    registration, password-reset, and other forms.</li>
                <li><strong>Gallery notification information,</strong> such as an email address you provide
                    to be notified when images become available in an album. We use the address only to send
                    the requested notification and delete it after the notification is sent.</li>
                <li><strong>Technical information,</strong> such as server logs, IP address, browser and
                    device information, requested pages, and timestamps used to operate, secure, and
                    troubleshoot the site.</li>
                <li><strong>Optional analytics and social information</strong> collected by the providers
                    described below, but only after you enable the applicable cookie category.</li>
            </ul>
            <p>We do not collect full payment-card numbers through this website. If you pay through a
                third-party payment service, that provider processes the payment under its own terms and
                privacy policy.</p>

            <h3>How we use information</h3>
            <ul>
                <li>Provide photography services, accounts, contracts, galleries, downloads, and support.</li>
                <li>Respond to inquiries and send service-related communications.</li>
                <li>Operate, maintain, secure, diagnose, and improve the website.</li>
                <li>Measure site traffic and marketing performance when Analytics cookies are accepted.</li>
                <li>Provide blog sharing and social features when Social Media cookies are accepted.</li>
                <li>Meet legal, accounting, contractual, and fraud-prevention obligations.</li>
            </ul>

            <h3>Cookies and similar technologies</h3>
            <p>The site uses necessary cookies for core functions and offers optional cookie categories.
                Optional Analytics and Social Media integrations do not load until you select those
                categories. You can reopen the preference center and change your choices
                <a id="edit-cookies" href="#cookie-settings">here</a>. Withdrawing consent prevents those
                integrations from loading on subsequent page views and removes known first-party cookies
                where practical.</p>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Cookie or service</th>
                        <th>Purpose</th>
                        <th>Category</th>
                        <th>Typical duration</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><code>session</code></td>
                        <td>Authentication, CSRF protection, and temporary album access</td>
                        <td>Necessary</td>
                        <td>Browser session; one hour for administrator impersonation</td>
                    </tr>
                    <tr>
                        <td><code>CookiePreferences</code></td>
                        <td>Stores your cookie-category choices</td>
                        <td>Necessary</td>
                        <td>One year</td>
                    </tr>
                    <tr>
                        <td><code>announcement-{id}</code></td>
                        <td>Remembers that you dismissed a site announcement</td>
                        <td>Necessary user-interface state</td>
                        <td>Browser session</td>
                    </tr>
                    <tr>
                        <td><code>remember_me</code></td>
                        <td>Restores a login when you explicitly choose "Remember me"</td>
                        <td>Preferences</td>
                        <td>30 days</td>
                    </tr>
                    <tr>
                        <td><code>searched</code></td>
                        <td>Remembers albums unlocked with a valid album code</td>
                        <td>Preferences</td>
                        <td>30 days</td>
                    </tr>
                    <tr>
                        <td>Google Analytics and Meta Pixel</td>
                        <td>Traffic measurement and marketing-performance analytics</td>
                        <td>Analytics</td>
                        <td>Controlled by Google and Meta and by our provider settings</td>
                    </tr>
                    <tr>
                        <td>Facebook SDK and AddToAny</td>
                        <td>Blog likes, sharing controls, and links to social services</td>
                        <td>Social Media</td>
                        <td>Controlled by the applicable provider</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <p>If you block all cookies in your browser, login, contracts, saved album access, and other
                site features may not work correctly. Core public content should remain available.</p>

            <h3>Third-party services</h3>
            <p>We do not sell personal information. We disclose information only as needed to provide the
                site and our services, comply with law, protect rights and security, or complete a business
                transaction. Providers may process technical information under their own privacy policies:</p>
            <ul>
                <li><strong>Google Analytics</strong> receives site-interaction and device information when
                    Analytics is enabled. See <a target="_blank" rel="noopener noreferrer"
                    href="https://policies.google.com/privacy">Google's Privacy Policy</a>.</li>
                <li><strong>Meta Pixel</strong> receives page-view and device information when Analytics is
                    enabled. The Facebook SDK loads only when Social Media is enabled. See
                    <a target="_blank" rel="noopener noreferrer"
                    href="https://www.facebook.com/privacy/policy/">Meta's Privacy Policy</a>.</li>
                <li><strong>AddToAny</strong> provides sharing controls when Social Media is enabled. See
                    <a target="_blank" rel="noopener noreferrer"
                    href="https://www.addtoany.com/privacy">AddToAny's Privacy Policy</a>.</li>
                <li><strong>Google Maps</strong> is embedded on the contact page. Loading that page may send
                    Google your IP address, browser information, and interaction with the map.</li>
                <li>Hosting, email, payment, and other operational providers process information only as
                    needed to supply their services to us.</li>
            </ul>
            <p>Links to third-party websites take you away from our site. Their privacy practices are not
                controlled by Saperstone Studios.</p>

            <h3>Retention</h3>
            <p>We retain account, contract, transaction, communication, and photography-service records for
                as long as reasonably necessary to provide services, maintain business and legal records,
                resolve disputes, and enforce agreements. Security and server logs are retained according to
                operational needs. Optional-provider data is retained according to our settings and the
                provider's policy. In September 2026, we stopped our legacy first-party visitor-statistics
                collection and deleted its historical table because it was no longer needed.</p>

            <h3>Your choices</h3>
            <ul>
                <li>Use the cookie preference center linked above to grant or withdraw optional Analytics,
                    Social Media, and Preferences choices.</li>
                <li>Use your browser controls to delete or block cookies.</li>
                <li>Log in to update available account information, or contact us to request access,
                    correction, or deletion where applicable. We may retain information when required for
                    legal, security, contractual, or recordkeeping reasons.</li>
                <li>Opt out of nonessential email by following the instructions in the message or contacting us.</li>
            </ul>
            <p>Browsers do not implement a single, consistently interpreted Do Not Track standard. The site
                therefore uses the cookie preference center rather than treating a browser DNT signal as a
                cookie choice.</p>

            <h3>Security and children</h3>
            <p>We use reasonable administrative and technical safeguards appropriate to the information we
                maintain, including HTTPS and access controls. No internet transmission or storage system can
                be guaranteed completely secure.</p>
            <p>The website is not directed to children under 13. Some gallery features allow a visitor
                to provide an email address to request a one-time notification when images become available.
                We use that address only for the requested notification and delete it after the message is sent.
                We do not knowingly collect other personal information directly from children under 13 through
                account registration or marketing. Our photography services may include photographs of children
                supplied or authorized by a parent, guardian, school, organization, or client.</p>

            <h3>Policy changes</h3>
            <p>We may update this policy as our practices or legal obligations change. The effective date at
                the top of this page identifies the current version.</p>

            <h3>Contact us</h3>
            <p>Questions or privacy requests may be sent to:</p>
            <p>Saperstone Studios LLC<br>
                5701 S Quartz St<br>
                Gilbert, AZ 85298<br>
                United States<br>
                <a href="mailto:contact@saperstonestudios.com">contact@saperstonestudios.com</a><br>
                <a href="tel:5712660004">(571) 266-0004</a></p>
        </div>
    </div>

    <?php require_once dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>
</div>
</body>
</html>
