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
                <li><a target="_blank"
                    href="https://www.facebook.com/SaperstoneStudios"><em
                        class="fa fa-facebook"></em></a></li>
                <li><a target="_blank" href="http://instagram.com/lasaperstone"><em
                        class="fa fa-instagram"></em></a></li>
                <li><a target="_blank" href="https://twitter.com/LaSaperstone"><em
                        class="fa fa-twitter"></em></a></li>
                <li><a target="_blank" type="application/rss+xml"
                    href="/blog.rss"><em
                        class="fa fa-rss"></em></a></li>
                <li><a target="_blank"
                    href="https://plus.google.com/+SaperstoneStudios"><em
                        class="fa fa-google-plus"></em></a></li>
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

<!-- Bootstrap Core JavaScript -->
<script
    src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"
    integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS"
    crossorigin="anonymous"></script>
<script
    src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/js/bootstrap-dialog.min.js"></script>

<!-- Custom Core JavaScript -->
<script src="/js/nav.js"></script>
<script src="/js/carousel.js"></script>


<!-- Script to Activate the Carousel -->
<script>
    $('.carousel').carousel({
        interval: 8000, //changes the display speed
        duration: 2000, //changes the slide speed
    });
</script>