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
      $this->assertEquals("smtp.gmail.com",self::$server->getHost());
      $this->assertEquals("587",self::$server->getPort());
      $this->assertEquals("taq",self::$server->getUser());
      $this->assertEquals("secret",self::$server->getPassword());
      $this->assertEquals("/tmp/delayedmailtest",self::$server->getPath());
   }

   /**
    * The following tests are used just to push messages to the server.
    * To check if they are working, you must configure a .ini file with some 
    * valid configuration and run the Runner class.
    */
   public function testPush() {
      $message = new DelayedMail\Message();
      $message->from("taq <eustaquiorangel@gmail.com>")->
                  to("Eustaquio Rangel <taq@bluefish.com.br>")->
             subject("DelayedMail test!")->
                text("This is just\na test!");
      self::$server->push($message);
   }

   public function testError() {
      $message = new DelayedMail\Message();
      $message->from("taq <eustaquiorangel@gmail.com>")->
                  to("Eustaquio Rangel")->
             subject("DelayedMail test with error!")->
                text("This is just\na test!");
      self::$server->push($message);
   }

   public function testPushWithCC() {
      $message = new DelayedMail\Message();
      $message->from("taq <eustaquiorangel@gmail.com>")->
                  to("Eustaquio Rangel <taq@bluefish.com.br>")->
                  cc("Eustaquio Rangel <taq@eustaquiorangel.com>")->
             subject("DelayedMail test with CC!")->
                text("This is just\na test!");
      self::$server->push($message);
   }

   public function testPushWithAttachment() {
      $message = new DelayedMail\Message();
      $message->from("taq <eustaquiorangel@gmail.com>")->
                  to("Eustaquio Rangel <taq@bluefish.com.br>")->
             subject("DelayedMail test!")->
                text("This is just\na test!")->
              attach(array("taq.jpg","qat.jpg"));
      self::$server->push($message);
   }
}
?>
