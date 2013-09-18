<?php
namespace DelayedMail;

class Cleaner {
   private $dir;
   private $time;

   public function __construct($dir,$time=null) {
      $this->dir  = $dir;
      $this->time = is_null($time) ? 1*60*24 : intval($time);
   }

   public function __toString() {
      return "cleaning files older than {$this->time} minutes on {$this->dir}";
   }

   private function find() {
   }

   public function run() {
      if(is_null($this->dir) || !file_exists($this->dir))
         return false;
   }
}
?>
