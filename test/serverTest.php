<?php
include_once "../server.php";

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
}
?>
