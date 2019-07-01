<?php
$delta = 5 * 60;
$file = "/var/www/logs/error.log";
$updated = 0;
if (file_exists ( $file )) {
    $updated = filemtime ( $file );
} else {
    echo "Unable to locate log file: $file";
    exit ();
}
$currently = time ();

// if the modified time is less than the 5 minutes ago (plus a buffer)
if (($currently - ($delta + 60)) > $updated) {
    // then we have nothing to do
    echo "Updated " . ($currently - $updated) . " seconds ago<br/>";
    exit ();
}

echo "Last Updated: $updated";
echo "<br/>";
echo "Currently: $currently";
echo "<br/>";

$fl = fopen ( $file, "r" );
for($x_pos = 0, $ln = 0, $output = array (); fseek ( $fl, $x_pos, SEEK_END ) !== - 1; $x_pos --) {
    $char = fgetc ( $fl );
    if ($char === "\n") {
        // analyse completed line $output[$ln] if need be
        preg_match ( "/\[(.*?)\]/", $output [$ln], $matches );
        if (sizeof ( $matches ) > 0) {
            echo $output [$ln] . "<br/>";
            $time = preg_replace ( "[\.\d+]", "", substr ( $matches [0], 4, - 1 ) );
            // echo "$time<br/>";
            $seconds = strtotime ( $time );
            // echo $seconds."<br/>";
            if (($currently - ($delta + 60)) > $seconds) {
                break;
            }
        }
        $ln ++;
        continue;
    }
    $output [$ln] = $char . ((array_key_exists ( $ln, $output )) ? $output [$ln] : '');
}
fclose ( $fl );

require_once "Mail.php";
require_once "Mail/mime.php";
$to = "Webmaster <msaperst@gmail.com>";
$from = "Error <error@saperstonestudios.com>";
$subject = "Apache Errors";

$html = "<html><body>";
$html .= "This is an automatically generated message from Walter<br/>";
$html .= "Error messages were found in your apache log file<br/>";
foreach ( $output as $line ) {
    $html .= "<p>$line</p>";
}
$html .= "</body></html>";

$text = "This is an automatically generated message from Walter\n";
$text .= "Error messages were found in your apache log file\n";
foreach ( $output as $line ) {
    $text .= "$line\n";
}

$crlf = "\n";

$mime = new Mail_mime ( $crlf );

$mime->setTXTBody ( $text );
$mime->setHTMLBody ( $html );

$body = $mime->get ();
require ('email.php');

echo $error;
?>
