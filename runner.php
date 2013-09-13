<?php
$dir = dirname(__FILE__);
echo "- loading classes from $dir\n";
include_once "$dir/delayedmail.php";

$sender = new DelayedMail\Sender(5,"delayedmail.ini");
$sender->run();
?>
