<?php
namespace DelayedMail;
include_once "message.php";
include_once "server.php";
include_once "sender.php";

$sender = new Sender(5,"delayedmail.ini");
$sender->run();
?>
