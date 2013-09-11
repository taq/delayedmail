<?php
namespace DelayedMail;
include_once "delayedmail.php";

$sender = new Sender(5,"delayedmail.ini");
$sender->run();
?>
