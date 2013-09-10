<?php
namespace DelayedMail;
include_once "message.php";
include_once "server.php";

class Sender {
   private $cfg;
   private $server;
   private $interval;

   public function __construct($interval=5,$cfg=null) {
      $this->cfg        = $cfg;
      $this->server     = new Server($this->cfg);
      $this->interval   = $interval;
   }

   public function run() {
      echo "- initializing ...\n";
      $delivery_path = $this->server->getDeliveryPath();
      $sent_path     = $this->server->getSentPath();

      while(true) {
         sleep($this->interval);

         echo "- checking for files in {$delivery_path} ...\n";
         $files = array_filter(glob("$delivery_path/*"),function($file) {
            return filesize($file)>0;
         });

         if(sizeof($files)<1) {
            echo "- no files found.\n";
            continue;
         }
         echo "- ".sizeof($files)." files found.\n";

         foreach($files as $file) {
            echo "- processing $file ...\n";
            $dest = "$sent_path/".basename($file);
            echo "- moving to $dest\n";
            if(!rename($file,$dest)) {
               echo "* error: could not move file $file to $dest\n";
            }
         }
      }
   }
}
?>
