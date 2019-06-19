<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$user = new User ();
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
                <h1 class="page-header text-center">Contact</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li class="active">Information</li>
                    <li class="active">Contact</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Content Row -->
        <div class="row">
            <!-- Map Column -->
            <div class="col-md-8">
                <!-- Embedded Google Map -->
                <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d795385.7040435313!2d-77.45577126502923!3d38.84398800429194!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x4c75c5462bac5863!2sSaperstone+Studios!5e0!3m2!1sen!2suk!4v1560954738867!5m2!1sen!2suk" width="100%" height="450px" frameborder="0" style="border:0" allowfullscreen></iframe>
            </div>
            <!-- Contact Details Column -->
            <div class="col-md-4">
                <h3>Contact Details</h3>
                <p>
                    5012 Whisper Willow Dr<br>Fairfax, VA 22030<br>
                </p>
                <p>
                    <em class="fa fa-phone"></em> <abbr title="Phone">P</abbr>: <a
                        href="tel:571.266.0004">571.266.0004</a>
                </p>
                <p>
                    <em class="fa fa-envelope-o"></em> <abbr title="Email">E</abbr>: <a
                        target="_blank" href="mailto:contact@saperstonestudios.com">Contact@SaperstoneStudios.com</a>
                </p>
                <p>
                    <em class="fa fa-clock-o"></em> <abbr title="Hours">H</abbr>:
                    Studio and session time by appointment only<br /> <em
                        style='padding-left: 35px;'>Please no walk ins.</em>
                </p>
                <ul class="list-unstyled list-inline list-social-icons">
                     <?php $iconSize = "fa-2x"; require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/social-list.php"; ?>
                </ul>
            </div>
        </div>
        <!-- /.row -->

        <!-- Contact Form -->
        <!-- In order to set the email address and subject line for the contact form go to the bin/contact_me.php file. -->
        <div class="row">
            <div class="col-md-8">
                <h3>Send us a Message</h3>
                <form name="sentMessage" id="contactForm" novalidate>
                    <div class="control-group form-group">
                        <div class="controls">
                            <label>Full Name:</label> <input type="text" class="form-control"
                                id="name" required value="<?php echo $user->getName(); ?>"
                                data-validation-required-message="Please enter your name.">
                            <p class="help-block"></p>
                        </div>
                    </div>
                    <div class="control-group form-group">
                        <div class="controls">
                            <label>Phone Number:</label> <input type="tel"
                                class="form-control" id="phone" required
                                data-validation-required-message="Please enter your phone number.">
                        </div>
                    </div>
                    <div class="control-group form-group">
                        <div class="controls">
                            <label>Email Address:</label> <input type="email"
                                class="form-control" id="email" required
                                value="<?php echo $user->getEmail(); ?>"
                                data-validation-required-message="Please enter your email address.">
                        </div>
                    </div>
                    <div class="control-group form-group">
                        <div class="controls">
                            <label>Message:</label>
                            <textarea rows="10" cols="100" class="form-control" id="message"
                                required
                                data-validation-required-message="Please enter your message"
                                maxlength="999" style="resize: none"></textarea>
                        </div>
                    </div>
                    <div id="success"></div>
                    <!-- For success/fail messages -->
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>

        </div>
        <!-- /.row -->

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

    <!-- Contact Form JavaScript -->
    <script src="js/jqBootstrapValidation.js"></script>
    <script src="js/contact_me.js"></script>

</body>

</html>