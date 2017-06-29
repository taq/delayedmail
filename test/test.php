<?php
   include_once "../delayedmail.php";

   $server = new DelayedMail\Server("delayedmail.ini");
   $msg    = new DelayedMail\Message();
   $msg->setType("text/html");
   $msg->from("taq <eustaquiorangel@gmail.com>")->
           to("Eustaquio Rangel <taq@bluefish.com.br>")->
           cc("Eustaquio Rangel <taq@eustaquiorangel.com>")->
      subject("DelayedMail test!")->
         text("<h1>Hi!</h1><p>This is just\na test!</p>")->
       attach("taq.jpg");
   $server->push($msg);

   $sender = new DelayedMail\Sender(5,"delayedmail.ini");
   $sender->run();
?>
