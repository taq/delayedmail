<?php
namespace DelayedMail;

class Cleaner {
   private $dir;
   private $time;

   public function __construct($dir,$time=null) {
      $this->dir  = $dir;
      $this->time = is_null($time) ? 1*60*24 : floatval($time);
   }

   public function __toString() {
      return "cleaning files older than {$this->time} minutes on {$this->dir}";
   }

   public function find() {
      $limit = mktime()-($this->time*60);
      $files = array_filter(glob("{$this->dir}/*"),function($file) use ($limit) {
         $handle = fopen($file,"r");
         $stat   = fstat($handle);
         fclose($handle);
         return intval($stat["mtime"])<$limit;
      });
      return $files;
   }

   public function run() {
      echo "- running cleaner on {$this->dir} ...\n";
      if(is_null($this->dir) || !file_exists($this->dir))
         return false;
      $files = $this->find();
      if(sizeof($files)<1)
         return false;
      echo "- ".$this."\n";
      foreach($files as $file) {
         echo "- removing $file\n";
         unlink($file);
      }
   }
}
?>
