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
         echo "- ".sizeof($files)." file(s) found.\n";

         if(!$this->server->open()) {
            echo "* could not open mail server.\n";
            continue;
         }

         foreach($files as $file) 
            $this->proc($file);

         $this->server->close();
      }
   }

   private function getSentPath($file) {
      $sent_path = $this->server->getSentPath();
      return "$sent_path/".basename($file);
   }

   private function proc($file) {
      echo "- processing $file ...\n";
      $contents = file_get_contents($file);

      $from = $this->getFrom($contents);
      $to   = $this->getTo($contents);
      $subj = $this->getSubject($contents);
      $subj = $this->getSubject($contents);
      $type = $this->getContentType($contents);
      $text = $this->getText($contents);

      $stripped_from = $this->getStrippedEmail($from);
      $stripped_to   = $this->getStrippedEmail($to);

      $rst = "";

      $rst .= $this->server->command("MAIL FROM: <$stripped_from>\r\n",true);
      $rst .= $this->server->command("RCPT TO: <$stripped_to>\r\n",true);
      $rst .= $this->server->command("DATA\r\n",true);
      $rst .= $this->server->command("From: $from\n",false);
      $rst .= $this->server->command("To: $to\n",false);
      $rst .= $this->server->command("Content-Type: $type\r\n",false);
      $rst .= $this->server->command("Subject: $subj\n\n",false);
      $rst .= $this->server->command("$text\r\n",false);
      $rst .= $this->server->command("\r\n.\r\n",true);
      $this->server->flush();

      if(!$this->server->getHandle()) {
         echo "* could not send email\n";
         return false;
      }

      if(!$this->move($file,$this->getSentPath($file)))
         return false;
      return true;
   }

   private function getFrom($contents) {
      $ok = preg_match('/^(From:\s?)([^\n]+)/sim',$contents,$matches);
      if(!$ok)
         return null;
      return $matches[2];
   }

   private function getTo($contents) {
      $ok = preg_match('/^(To:\s?)([^\n]+)/sim',$contents,$matches);
      if(!$ok)
         return null;
      return $matches[2];
   }

   private function getSubject($contents) {
      $ok = preg_match('/^(Subject:\s?)([^\n]+)/sim',$contents,$matches);
      if(!$ok)
         return null;
      return $matches[2];
   }

   private function getContentType($contents) {
      $ok = preg_match('/^(Content-Type:\s?)([^\n]+)/sim',$contents,$matches);
      if(!$ok)
         return null;
      return $matches[2];
   }

   private function getText($contents) {
      $tokens = preg_split('/\n\n/sim',$contents);
      if(sizeof($tokens)<2)
         return null;
      $text = join("\n\n",array_slice($tokens,1));
      return $text;
   }

   private function getStrippedEmail($email) {
      $ok = preg_match('/(<)(.*)(>)/',$email,$matches);
      if(!$ok)
         return $email;
      return $matches[2];
   }

   private function send($file) {
   }

   private function move($from,$to) {
      echo "- moving $from to $to\n";
      if(!rename($from,$to)) {
         echo "* error: could not move file $from to $to\n";
         return false;
      }
      return true;
   }
}
?>
