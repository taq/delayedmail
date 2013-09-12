<?php
include_once "../message.php";

class MessageTest extends PHPUnit_Framework_TestCase {
   protected static $message = null;

   public static function setUpBeforeClass() {
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

   public function testToStringWithAttachments() {
      self::$message->from("Eustaquio Rangel <eustaquiorangel@gmail.com>")->
                      to("Eustaquio Rangel <taq@bluefish.com.br>")->
                      subject("DelayedMail test!")->
                      text("This is just\na test!")->
                      attach(array("./taq.jpg","./qat.jpg"))->
                      marker(1378910636);
      $str = self::$message;
      $this->assertEquals(trim(file_get_contents("attachment.txt")),trim("$str"));
   }
}
?>
