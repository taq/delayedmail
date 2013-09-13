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
   private $auth;
   private $tls;
   private $error;

   public function __construct($cfg=null) {
      $this->port    = 25;
      $this->path    = "/tmp/delayedmail";
      $this->domain  = "delayedmail.com";
      $this->auth    = false;
      $this->tls     = false;
      $this->cfg     = $cfg;
      $this->error   = false;
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

   public function getErrorPath() {
      return $this->path."/error";
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
      $this->ehlo();
      return $this->handle;
   }

   private function ehlo() {
      $this->command("EHLO ".$this->domain."\r\n");
   }

   private function auth() {
      if(!is_null($this->user) &&
         !is_null($this->pwd)) {

         if($this->tls) {
            $this->command("STARTTLS\n");
            stream_socket_enable_crypto($this->handle,true,STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->ehlo();
         }

         $this->command("AUTH LOGIN\r\n");
         $this->command(base64_encode($this->user)."\r\n");
         $this->command(base64_encode($this->pwd)."\r\n");
      }
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
      return $this->message($wait ? $this->wait() : null,$cmd);
   }

   private function message($str,$cmd) {
      if(is_null($str))
         return true;

      if(preg_match('/^[45]/sim',$str)) {
         echo "* error: $str\n";
         echo "* command was: $cmd\n";
         $this->error = true;
         return false;
      }

      if(preg_match('/STARTTLS/sim',$str)) {
         $this->tls = true;
         $this->auth();
      }

      if(preg_match('/250 AUTH/sim',$str))
         $this->auth();
      return true;
   }

   public function getHandle() {
      return $this->handle;
   }

   public function getError() {
      return $this->error;
   }

   public function setError($error) {
      $this->error = $error;
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

   private function makePath($path) {
      if(!file_exists($path)) {
         if(!mkdir($path))
            return false;
      }
      return true;
   }

   private function makeBasePath() {
      return $this->makePath($this->path);
   }

   private function makeDeliveryPath() {
      return $this->makePath($this->getDeliveryPath());
   }

   private function makeSentPath() {
      return $this->makePath($this->getSentPath());
   }

   private function makeErrorPath() {
      return $this->makePath($this->getErrorPath());
   }

   public function push($msg) {
      if(!$this->makeBasePath()     ||
         !$this->makeDeliveryPath() ||
         !$this->makeErrorPath()    ||
         !$this->makeSentPath())
         return false;

      $file = tempnam($this->getDeliveryPath(),"delayedmail");
      return file_put_contents($file,$msg);
   }
}
