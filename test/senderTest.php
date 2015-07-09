<?php
include_once "../vendor/autoload.php";

class SenderTest extends PHPUnit_Framework_TestCase {
   protected static $sender = null;

   public function setUp() {
      self::$sender = new DelayedMail\Sender(5,"delayedmail.ini");
   }

   public function testCleaner() {
      $this->assertNotNull(self::$sender->getCleaner());
      $this->assertEquals("cleaning files older than 1 minutes on /tmp/delayedmailtest/sent",self::$sender->getCleaner()."");
   }
}
