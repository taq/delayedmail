<?php
include_once "../cleaner.php";

class CleanerTest extends PHPUnit_Framework_TestCase {
   protected static $cleaner = null;
   protected static $dir     = "/tmp/delayedmailtest/sent/";

   public function setUp() {
      self::$cleaner = new DelayedMail\Cleaner(self::$dir,5);
   }

   public function testToString() {
      $this->assertEquals("cleaning files older than 5 minutes on /tmp/delayedmailtest/sent/",self::$cleaner."");
   }
}
