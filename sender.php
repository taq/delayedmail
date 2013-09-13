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

   private function getErrorPath($file) {
      $error_path = $this->server->getErrorPath();
      return "$error_path/".basename($file);
   }

   private function proc($file) {
      echo "- processing $file ...\n";
      $contents = file_get_contents($file);

      $from = $this->getFrom($contents);
      $to   = $this->getTo($contents);
      $cc   = $this->getCC($contents);
      $subj = $this->getSubject($contents);
      $subj = $this->getSubject($contents);
      $type = $this->getContentType($contents);
      $text = $this->getText($contents);

      $stripped_from = $this->getStrippedEmail($from);
      $stripped_to   = $this->getStrippedEmail($to);

      $this->server->setError(false);

      $rst  = "";
      $rst .= $this->server->command("MAIL FROM: <$stripped_from>\r\n",true);
      $rst .= $this->server->command("RCPT TO: <$stripped_to>\r\n",true);

      if(!is_null($cc)) {
         $tokens = explode(",",$cc);
         foreach($tokens as $cc) {
            $cc = $this->getStrippedEmail($cc);
            $rst .= $this->server->command("RCPT TO: <$cc>\r\n",true);
         }
      }

      $rst .= $this->server->command("DATA\r\n",true);
      $rst .= $this->server->command("From: $from\n",false);
      $rst .= $this->server->command("To: $to\n",false);

      if(!is_null($cc))
         $rst .= $this->server->command("Cc: $cc\n",false);
      
      $rst .= $this->server->command("Content-Type: $type\r\n",false);
      $rst .= $this->server->command("Subject: $subj\n\n",false);
      $rst .= $this->server->command("$text\r\n",false);
      $rst .= $this->server->command("\r\n.\r\n",true);
      $this->server->flush();

      if($this->server->getError()) {
         echo "* could not send email\n";
         if(!$this->move($file,$this->getErrorPath($file)))
            echo "* could not move file to error dir\n";
         return false;
      }

      if(!$this->move($file,$this->getSentPath($file)))
         return false;
      return true;
   }

   private function find($regex,$contents,$default=null) {
      $ok = preg_match($regex,$contents,$matches);
      if(!$ok)
         return $default;
      return $matches[2];
   }

   private function getFrom($contents) {
      return $this->find('/^(From:\s?)([^\n]+)/sim',$contents);
   }

   private function getTo($contents) {
      return $this->find('/^(To:\s?)([^\n]+)/sim',$contents);
   }

   private function getCC($contents) {
      return $this->find('/^(Cc:\s?)([^\n]+)/sim',$contents);
   }

   private function getSubject($contents) {
      return $this->find('/^(Subject:\s?)([^\n]+)/sim',$contents);
   }

   private function getContentType($contents) {
      return $this->find('/^(Content-Type:\s?)([^\n]+)/sim',$contents);
   }

   private function getText($contents) {
      $tokens = preg_split('/\n\n/sim',$contents);
      if(sizeof($tokens)<2)
         return null;
      $text = join("\n\n",array_slice($tokens,1));
      return $text;
   }

   private function getStrippedEmail($email) {
      return $this->find('/(<)(.*)(>)/',$email,$email);
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
