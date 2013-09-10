<?php
namespace DelayedMail;

class Server {
   private $host  = null;
   private $port  = null;
   private $user  = null;
   private $pwd   = null;
   private $cfg   = null;

   public function __construct() {
      $this->port = 25;
   }

   public function config($cfg) {
      $this->cfg = $cfg;
      $this->readConfig();
      return $this;
   }

   public function host($host) {
      $this->host = $host;
      return $this;
   }

   public function getHost() {
      return $this->host;
   }

   public function port($port) {
      $this->port = $port;
      return $this;
   }

   public function getPort() {
      return $this->port;
   }

   public function user($user) {
      $this->user = $user;
      return $this;
   }

   public function getUser() {
      return $this->user;
   }

   public function password($pwd) {
      $this->pwd = $pwd;
      return $this;
   }

   public function getPassword() {
      return $this->pwd;
   }

   private function readConfig() {
      if(!file_exists($this->cfg))
         return false;

      $contents = file_get_contents($this->cfg);

      $tests    = array('/(host)(\s?=\s?)(.*)/i',
                        '/(port)(\s?=\s?)(.*)/i',
                        '/(user)(\s?=\s?)(.*)/i',
                        '/(password)(\s?=\s?)(.*)/i');

      $props    = array(&$this->host,
                        &$this->port,
                        &$this->user,
                        &$this->pwd);

      for($i=0, $t=sizeof($tests); $i<$t; $i++) {
         if(!preg_match($tests[$i],$contents,$matches))
            continue;
         $props[$i] = $matches[3];
      }
   }
}
