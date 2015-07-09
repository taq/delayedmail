<?php
include_once "../vendor/autoload.php";

class MessageTest extends PHPUnit_Framework_TestCase {
   protected static $message = null;

   public function setUp() {
      self::$message = new DelayedMail\Message();
   }

   public function testToString() {
      self::$message->from("Eustaquio Rangel <eustaquiorangel@gmail.com>")->
                      to("Eustaquio Rangel <taq@bluefish.com.br>")->
                      subject("DelayedMail test!")->
                      text("This is just\na test!");
      $str = self::$message;
      $exp = <<<EOT
From: Eustaquio Rangel <eustaquiorangel@gmail.com>
To: Eustaquio Rangel <taq@bluefish.com.br>
Subject: DelayedMail test!
Content-Type: text/plain

This is just
a test!
EOT;
      $this->assertEquals(trim($exp),trim($str));
   }

   public function testToStringWithOneCC() {
      self::$message->from("Eustaquio Rangel <eustaquiorangel@gmail.com>")->
                      to("Eustaquio Rangel <taq@bluefish.com.br>")->
                      cc("Eustaquio Rangel <taq@eustaquiorangel.com>")->
                      subject("DelayedMail test!")->
                      text("This is just\na test!");
      $str = self::$message;
      $exp = <<<EOT
From: Eustaquio Rangel <eustaquiorangel@gmail.com>
To: Eustaquio Rangel <taq@bluefish.com.br>
Cc: Eustaquio Rangel <taq@eustaquiorangel.com>
Subject: DelayedMail test!
Content-Type: text/plain

This is just
a test!
EOT;
      $this->assertEquals(trim($exp),trim($str));
   }

   public function testToStringWithMultipleCC() {
      self::$message->from("Eustaquio Rangel <eustaquiorangel@gmail.com>")->
                      to("Eustaquio Rangel <taq@bluefish.com.br>")->
                      cc(array("Eustaquio Rangel <taq@eustaquiorangel.com>","Eustaquio Rangel <taq@host.com>"))->
                      subject("DelayedMail test!")->
                      text("This is just\na test!");
      $exp = <<<EOT
From: Eustaquio Rangel <eustaquiorangel@gmail.com>
To: Eustaquio Rangel <taq@bluefish.com.br>
Cc: Eustaquio Rangel <taq@eustaquiorangel.com>, Eustaquio Rangel <taq@host.com>
Subject: DelayedMail test!
Content-Type: text/plain

This is just
a test!
EOT;
      $this->assertEquals(trim($exp),trim(self::$message));
   }

   public function testToStringWithAttachments() {
      self::$message->from("Eustaquio Rangel <eustaquiorangel@gmail.com>")->
                      to("Eustaquio Rangel <taq@bluefish.com.br>")->
                      subject("DelayedMail test!")->
                      text("This is just\na test!")->
                      attach(array("./taq.jpg","./qat.jpg"))->
                      marker(1378910636);
      $this->assertEquals(trim(file_get_contents("attachment.txt")),trim(self::$message));
   }
}
?>
