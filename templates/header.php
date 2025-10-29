<?php
// Define our default menu
$nav = "main";
$page_title = "Saperstone Studios | Chandler AZ Photography and Retouch";
if (strpos($_SERVER['REQUEST_URI'], 'blog/post.php') !== false) {
    try {
        $blog = Blog::withId($_GET ['p']);
        $page_title = $blog->getTitle() . " | " . $page_title;
    } catch (Exception $e) {
        // do nothing, this will throw a 404 error
    }
}
?>

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="<?php echo $page_title; ?>">
<meta name="author" content="Saperstone Studios">
<meta name="keywords"
      content="Arizona mitzvah photographer, bar mitzvah photography Chandler, bat mitzvah photographer Scottsdale, East Valley bar mitzvah photos, Jewish event photography Arizona, Saperstone Studios, mitzvah photography Tempe, Temple Emanuel photographer, Scottsdale synagogue photographer, Phoenix wedding photographer, Chandler wedding photography, Scottsdale wedding photographer, Arizona destination weddings, East Valley engagement photos, Gilbert family photographer, Chandler family portraits, Scottsdale family photography, Tempe lifestyle family photos, Arizona portrait sessions, Phoenix family photographer, Saperstone Studios weddings, Saperstone Studios family sessions">

<link rel="apple-touch-icon" sizes="57x57"
      href="/img/favicon/apple-icon-57x57.png?v=2">
<link rel="apple-touch-icon" sizes="60x60"
      href="/img/favicon/apple-icon-60x60.png?v=2">
<link rel="apple-touch-icon" sizes="72x72"
      href="/img/favicon/apple-icon-72x72.png?v=2">
<link rel="apple-touch-icon" sizes="76x76"
      href="/img/favicon/apple-icon-76x76.png?v=2">
<link rel="apple-touch-icon" sizes="114x114"
      href="/img/favicon/apple-icon-114x114.png?v=2">
<link rel="apple-touch-icon" sizes="120x120"
      href="/img/favicon/apple-icon-120x120.png?v=2">
<link rel="apple-touch-icon" sizes="144x144"
      href="/img/favicon/apple-icon-144x144.png?v=2">
<link rel="apple-touch-icon" sizes="152x152"
      href="/img/favicon/apple-icon-152x152.png?v=2">
<link rel="apple-touch-icon" sizes="180x180"
      href="/img/favicon/apple-icon-180x180.png?v=2">
<link rel="icon" type="image/png" sizes="192x192"
      href="/img/favicon/android-icon-192x192.png?v=2">
<link rel="icon" type="image/png" sizes="32x32"
      href="/img/favicon/favicon-32x32.png?v=2">
<link rel="icon" type="image/png" sizes="96x96"
      href="/img/favicon/favicon-96x96.png?v=2">
<link rel="icon" type="image/png" sizes="16x16"
      href="/img/favicon/favicon-16x16.png?v=2">
<link rel="manifest" href="/img/favicon/manifest.json">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage"
      content="/img/favicon/ms-icon-144x144.png?v=2">
<meta name="theme-color" content="#ffffff">

<title><?php echo $page_title; ?></title>
<link rel="alternate" type="application/rss+xml" href="/blog.rss"
      title="RSS feed for Saperstone Studios Blogs">

<!-- JQuery UI CSS -->
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css"
      integrity="sha256-rByPlHULObEjJ6XQxW/flG2r+22R5dKiAoef+aXWfik="
      crossorigin="anonymous"/>

<!-- Bootstrap Core CSS -->
<link
        href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7"
        crossorigin="anonymous">
<link
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/css/bootstrap-dialog.min.css"
        rel="stylesheet">

<!-- Custom CSS -->
<link href="/css/modern-business.css" rel="stylesheet">
<link href="/css/saperstone-studios.css" rel="stylesheet">

<!-- Custom Fonts -->
<script src="https://use.fontawesome.com/5b39eac726.js"></script>

<!-- Ties to Google Business Profile -->
<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "LocalBusiness",
        "name": "Saperstone Studios",
        "image": "https://saperstonestudios.com/img/2014websitelogo250px.png",
        "@id": "https://saperstonestudios.com",
        "url": "https://saperstonestudios.com",
        "telephone": "+1-571-266-0004",
        "priceRange": "$$",
        "description": "Saperstone Studios provides professional mitzvah, wedding, portrait, and corporate photography services across the Phoenix metro area.",
        "areaServed": [
            {
                "@type": "City",
                "name": "Chandler"
            },
            {
                "@type": "City",
                "name": "Gilbert"
            },
            {
                "@type": "City",
                "name": "Mesa"
            },
            {
                "@type": "City",
                "name": "Tempe"
            },
            {
                "@type": "City",
                "name": "Phoenix"
            }
        ],
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Chandler",
            "addressRegion": "AZ",
            "addressCountry": "US"
        },
        "openingHoursSpecification": [
            {
                "@type": "OpeningHoursSpecification",
                "dayOfWeek": [
                    "Monday",
                    "Tuesday",
                    "Wednesday",
                    "Thursday",
                    "Friday",
                    "Saturday",
                    "Sunday"
                ],
                "opens": "09:00",
                "closes": "18:00"
            }
        ],
        "sameAs": [
            "https://www.facebook.com/SaperstoneStudios",
            "https://instagram.com/saperstonestudios",
            "https://twitter.com/LaSaperstone"
        ]
    }
</script>

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->

<!-- Facebook Pixel Code -->
<?php
if (Session::useAnalytics()) {
    ?>
    <script>
        !function (f, b, e, v, n, t, s) {
            if (f.fbq) return;
            n = f.fbq = function () {
                n.callMethod ?
                    n.callMethod.apply(n, arguments) : n.queue.push(arguments)
            };
            if (!f._fbq) f._fbq = n;
            n.push = n;
            n.loaded = !0;
            n.version = '2.0';
            n.queue = [];
            t = b.createElement(e);
            t.async = !0;
            t.src = v;
            s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s)
        }(window, document, 'script',
            'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '269624791467010');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
                   src="https://www.facebook.com/tr?id=269624791467010&ev=PageView&noscript=1"
        /></noscript>
    <?php
}
?>
<!-- End Facebook Pixel Code -->
