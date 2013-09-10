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
      $str = self::$message."";
      $exp = <<<EOT
From: Eustaquio Rangel <eustaquiorangel@gmail.com>
To: Eustaquio Rangel <taq@bluefish.com.br>
Content-Type: text/plain
Subject: DelayedMail test!

This is just
a test!
EOT;
      $this->assertEquals($exp,$str);
   }
}
?>
