<?php
include_once "../server.php";
include_once "../message.php";

class ServerTest extends PHPUnit_Framework_TestCase {
   protected static $server = null;

   public static function setUpBeforeClass() {
      self::$server = new DelayedMail\Server();
   }

   public function testConfig() {
      self::$server->config("delayedmail.ini");
      $this->assertEquals("gmail.com",self::$server->getHost());
      $this->assertEquals("587",self::$server->getPort());
      $this->assertEquals("taq",self::$server->getUser());
      $this->assertEquals("secret",self::$server->getPassword());
      $this->assertEquals("/tmp/delayedmailtest",self::$server->getPath());
   }

   public function testPush() {
      $message = new DelayedMail\Message();
      $message->from("Eustaquio Rangel <eustaquiorangel@gmail.com>")->
                  to("Eustaquio Rangel <taq@bluefish.com.br>")->
             subject("DelayedMail test!")->
                text("This is just\na test!");
      self::$server->push($message);
   }
}
?>
