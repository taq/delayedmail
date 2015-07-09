<?php
if (file_exists("vendor")) {
    echo "- loading from composer\n";
    include_once "vendor/autoload.php";
} else {
    $dir = dirname(__FILE__);
    echo "- loading classes from $dir\n";
    include_once "$dir/delayedmail.php";
}

$sender = new DelayedMail\Sender(5, "delayedmail.ini");
$sender->run();
?>
