<?php
namespace DelayedMail;

class Server {
   private $host;
   private $port;
   private $user;
   private $pwd;
   private $cfg;
   private $handle;
   private $path;

   public function __construct() {
      $this->port = 25;
      $this->path = "/tmp/delayedmail";
   }

   public function config($cfg) {
      $this->cfg = $cfg;
      $this->readConfig();
      return $this;
   }

   public function getHost() {
      return $this->host;
   }

   public function getPort() {
      return $this->port;
   }

   public function getUser() {
      return $this->user;
   }

   public function getPassword() {
      return $this->pwd;
   }

   public function getPath() {
      return $this->path;
   }

   public function getDeliveryPath() {
      return $this->path."/deliver";
   }

   public function getSentPath() {
      return $this->path."/sent";
   }

   private function readConfig() {
      if(!file_exists($this->cfg))
         return false;

      $contents = file_get_contents($this->cfg);

      $tests    = array('/(host)(\s?=\s?)(.*)/i',
                        '/(port)(\s?=\s?)(.*)/i',
                        '/(user)(\s?=\s?)(.*)/i',
                        '/(password)(\s?=\s?)(.*)/i',
                        '/(path)(\s?=\s?)(.*)/i');

      $props    = array(&$this->host,
                        &$this->port,
                        &$this->user,
                        &$this->pwd,
                        &$this->path);

      for($i=0, $t=sizeof($tests); $i<$t; $i++) {
         if(!preg_match($tests[$i],$contents,$matches))
            continue;
         $props[$i] = $matches[3];
      }
   }

   public function open($domain=null) {
      $this->handle = fsockopen($this->host,$this->port,$errno,$errstr,30);

      if($domain)
         $this->command("EHLO $domain\r\n");

      if(!is_null($this->user) &&
         !is_null($this->pwd)) {
         $this->command("AUTH LOGIN\r\n");
         $this->command(base64_encode($this->user)."\r\n");
         $this->command(base64_encode($this->pwd)."\r\n");
      }
      return $this->handle;
   }

   public function close() {
      if(!$this->handle)
         return false;
      fputs("QUIT\r\n");
      fflush($this->handle);
      fclose($this->handle);
   }

   public function command($cmd,$wait=true) {
      if(!$this->handle)
         return false;

      fputs($this->handle,$cmd);
      return $wait ? $this->wait() : "";
   }

   private function wait() {
      if(!$this->handle)
         return false;

      $rtn     = fgets($this->handle);
      $status  = socket_get_status($this->handle);
      $left    = intval($status["unread_bytes"]); 
      if($left>0)
         $rtn .= fread($this->handle,$left);
      return $rtn;
   }

   private function makeBasePath() {
      if(!file_exists($this->path)) {
         if(!mkdir($this->path))
            return false;
      }
      return true;
   }

   private function makeDeliverPath() {
      if(!file_exists($this->getDeliveryPath())) {
         if(!mkdir($this->getDeliveryPath()))
            return false;
      }
      return true;
   }

   private function makeSentPath() {
      if(!file_exists($this->getSentPath())) {
         if(!mkdir($this->getSentPath()))
            return false;
      }
      return true;
   }

   public function push($msg) {
      if(!$this->makeBasePath()     ||
         !$this->makeDeliverPath()  ||
         !$this->makeSentPath())
         return false;

      $file = tempnam($this->getDeliveryPath(),"delayedmail");
      file_put_contents($file,$msg);
   }
}
