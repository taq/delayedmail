<?php
include_once "../sender.php";
include_once "../cleaner.php";

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
