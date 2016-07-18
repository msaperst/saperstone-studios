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
                <h1 class="page-header text-center">Contact
                </h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a>
                    </li>
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
                <iframe width="100%" height="400px" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d198865.28560409523!2d-77.53850004379905!3d38.84917415728946!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89b637e9f071197b%3A0x4c75c5462bac5863!2sSaperstone+Studios!5e0!3m2!1sen!2sus!4v1463780133083"></iframe>
            </div>
            <!-- Contact Details Column -->
            <div class="col-md-4">
                <h3>Contact Details</h3>
                <p>
                    5012 Whisper Willow Dr<br>Fairfax, VA 22030<br>
                </p>
                <p><i class="fa fa-phone"></i> 
                    <abbr title="Phone">P</abbr>: <a href="tel:571.266.0004">571.266.0004</a></p>
                <p><i class="fa fa-envelope-o"></i> 
                    <abbr title="Email">E</abbr>: <a href="mailto:contact@saperstonestudios.com">Contact@SaperstoneStudios.com</a>
                </p>
                <p><i class="fa fa-clock-o"></i> 
                    <abbr title="Hours">H</abbr>: Studio and session time by appointment only<br/>
                    <em style='padding-left:35px;'>Please no walk ins.</em></p>
                <ul class="list-unstyled list-inline list-social-icons">
                    <li>
                        <a target="_blank" href="https://www.facebook.com/SaperstoneStudios"><i class="fa fa-facebook-square fa-2x"></i></a>
                    </li>
                    <li>
                        <a target="_blank" href="http://instagram.com/lasaperstone"><i class="fa fa-instagram fa-2x"></i></a>
                    </li>
                    <li>
                        <a target="_blank" href="https://twitter.com/LaSaperstone"><i class="fa fa-twitter-square fa-2x"></i></a>
                    </li>
                    <li>
                        <a target="_blank" href="https://saperstonestudios.com/Blog/blogFeed.xml"><i class="fa fa-rss-square fa-2x"></i></a>
                    </li>
                    <li>
                        <a target="_blank" href="https://plus.google.com/+SaperstoneStudios"><i class="fa fa-google-plus-square fa-2x"></i></a>
                    </li>
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
                            <label>Full Name:</label>
                            <input type="text" class="form-control" id="name" required data-validation-required-message="Please enter your name.">
                            <p class="help-block"></p>
                        </div>
                    </div>
                    <div class="control-group form-group">
                        <div class="controls">
                            <label>Phone Number:</label>
                            <input type="tel" class="form-control" id="phone" required data-validation-required-message="Please enter your phone number.">
                        </div>
                    </div>
                    <div class="control-group form-group">
                        <div class="controls">
                            <label>Email Address:</label>
                            <input type="email" class="form-control" id="email" required data-validation-required-message="Please enter your email address.">
                        </div>
                    </div>
                    <div class="control-group form-group">
                        <div class="controls">
                            <label>Message:</label>
                            <textarea rows="10" cols="100" class="form-control" id="message" required data-validation-required-message="Please enter your message" maxlength="999" style="resize:none"></textarea>
                        </div>
                    </div>
                    <div id="success"></div>
                    <!-- For success/fail messages -->
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>

        </div>
        <!-- /.row -->

        <?php require_once "footer.php"; ?>

    </div>
    <!-- /.container -->

    <!-- Contact Form JavaScript -->
    <script src="js/jqBootstrapValidation.js"></script>
    <script src="js/contact_me.js"></script>

</body>

</html>