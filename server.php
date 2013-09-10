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
   private $domain;

   public function __construct($cfg=null) {
      $this->port    = 25;
      $this->path    = "/tmp/delayedmail";
      $this->domain  = "delayedmail.com";
      $this->cfg     = $cfg;
      if($this->cfg)
         $this->readConfig();
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
      return $this->path."/delivery";
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
                        '/(domain)(\s?=\s?)(.*)/i',
                        '/(path)(\s?=\s?)(.*)/i');

      $props    = array(&$this->host,
                        &$this->port,
                        &$this->user,
                        &$this->pwd,
                        &$this->domain,
                        &$this->path);

      for($i=0, $t=sizeof($tests); $i<$t; $i++) {
         if(!preg_match($tests[$i],$contents,$matches))
            continue;
         $props[$i] = $matches[3];
      }
   }

   public function open() {
      $this->handle = fsockopen($this->host,$this->port,$errno,$errstr,30);
      if(!$this->handle)
         return false;
      $this->wait();

      $ehlo = $this->command("EHLO ".$this->domain."\r\n");

      if(preg_match('/250 AUTH/sim',$ehlo) &&
         !is_null($this->user) &&
         !is_null($this->pwd)) {
         $this->command("AUTH LOGIN\r\n");
         $this->command(base64_encode($this->user)."\r\n");
         $this->command(base64_encode($this->pwd)."\r\n");
      }
      return $this->handle;
   }

   public function flush() {
      if(!$this->handle)
         return false;
      fflush($this->handle);
   }

   public function close() {
      if(!$this->handle)
         return false;
      fputs($this->handle,"QUIT\r\n");
      fflush($this->handle);
      fclose($this->handle);
   }

   public function command($cmd,$wait=true) {
      if(!$this->handle)
         return false;

      fputs($this->handle,$cmd);
      fflush($this->handle);
      $rtn = $wait ? $this->wait() : "";
      if(preg_match('/^[45]/sim',$rtn)) {
         echo "* error: $rtn\n";
         $this->handle = null;
         return false;
      }
      return $rtn;
   }

   public function getHandle() {
      return $this->handle;
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

   private function makeDeliveryPath() {
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
         !$this->makeDeliveryPath() ||
         !$this->makeSentPath())
         return false;

      $file = tempnam($this->getDeliveryPath(),"delayedmail");
      file_put_contents($file,$msg);
   }
}
