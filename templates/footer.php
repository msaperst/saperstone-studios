<hr>

<!-- User Information For JS -->
<div class="hidden">
    <input id="my-user-id" value="<?php echo $user->getId(); ?>" /> <input
        id="my-user-role" value="<?php echo $user->getRole(); ?>" />
</div>

<!-- Footer -->
<footer>
    <div class="row">
        <div class="col-md-4 text-left">
            <ul class="list-inline quicklinks">
                <li><a target="_blank" href="mailto:contact@saperstonestudios.com">Contact@SaperstoneStudios.com</a>
                </li>
                <li><a target="_blank" href="tel:5712660004">571.266.0004</a></li>
            </ul>
        </div>
        <div class="col-md-4 text-center">
            <ul class="list-inline social-buttons">
                <?php $iconSize = ""; require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/social-list.php"; ?>
            </ul>
        </div>
        <div class="col-md-4 text-right">
            <ul class="list-inline quicklinks">
                <li><a href="/Privacy-Policy.php">Privacy Policy</a></li>
                <li><a href="/Terms-of-Use.php">Terms of Use</a></li>
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 text-center">
            <span class="copyright">Copyright &copy Saperstone Studios <?php echo date("Y"); ?></span>
        </div>
    </div>
</footer>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-1.12.4.min.js"
    integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
    crossorigin="anonymous"></script>
<script
    src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"
    integrity="sha256-KM512VNnjElC30ehFwehXjx1YCHPiQkOPmqnrWtpccM="
    crossorigin="anonymous"></script>

<script
    src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"
    integrity="sha256-9wRM03dUw6ABCs+AU69WbK33oktrlXamEXMvxUaF+KU="
    crossorigin="anonymous"></script>

<!-- Popper JS -->
<script
    src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
    integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
    crossorigin="anonymous"></script>

<!-- Bootstrap Core JavaScript -->
<script
    src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"
    integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS"
    crossorigin="anonymous"></script>
<script
    src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/js/bootstrap-dialog.min.js"></script>


<!-- GDPR Cookie Handling -->
<script src="/js/jquery.bs.gdpr.cookies.js"></script>

<!-- Custom Core JavaScript -->
<script src="/js/nav.js"></script>
<script src="/js/carousel.js"></script>

<!-- MD5 for login/password -->
<script
    src="https://cdnjs.cloudflare.com/ajax/libs/blueimp-md5/2.6.0/js/md5.min.js"></script>

<!-- Script to Activate the Carousel -->
<script>
    $('.carousel').carousel({
        interval: 8000, //changes the display speed
        duration: 2000, //changes the slide speed
    });
</script>

<!-- Google Analytics -->
<?php
$preferences = json_decode( $_COOKIE['CookiePreferences'] );
if (isset ( $_SERVER ['HTTPS'] ) && $_SERVER ['HTTPS'] == "on" && in_array( "analytics", $preferences ) ) {
    ?>
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-23617021-6', 'auto');
    ga('send', 'pageview');

</script>
<?php
}
?>
