<?php
    $params = parse_ini_file( "env.ini" );
    $db = mysqli_connect($params['db.host'], $params['db.username'], $params['db.password'], $params['db.database']);
    if (!$db) {
        echo "Error: Unable to connect to MySQL." . PHP_EOL;
        echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
        echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
        exit;
    }
?>