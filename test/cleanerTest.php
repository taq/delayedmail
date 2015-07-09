<?php
include_once "../vendor/autoload.php";

class CleanerTest extends PHPUnit_Framework_TestCase {
   protected static $cleaner = null;
   protected static $dir     = "/tmp/delayedmailtest/sent/";

   public function setUp() {
      self::$cleaner = new DelayedMail\Cleaner(self::$dir,0.1);
   }

   public function testToString() {
      $this->assertEquals("cleaning files older than 0.1 minutes on /tmp/delayedmailtest/sent/",self::$cleaner."");
   }

   public function testFind() {
      self::createFiles();
      $files = self::$cleaner->find();
      $this->assertEquals(2,sizeof($files));
      self::clearFiles();
   }

   public function testRun() {
      self::createFiles();
      self::$cleaner->run();
      $this->assertEquals(1,sizeof(glob(self::$dir."/*")));
      self::clearFiles();
   }

   private function createFiles() {
      $time = mktime()-18;
      if(!file_exists(self::$dir)) 
         mkdir(self::$dir,0777,true);

      foreach(array("a","b","c") as $name) {
         $file = self::$dir."/$name";
         touch($file,$time);
         $time += 6;
      }
   }

   private function clearFiles() {
      foreach(glob(self::$dir."/*") as $file) 
         unlink($file);
   }
}
