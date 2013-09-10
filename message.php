<?php
namespace DelayedMail;

class Message {
   private $from     = null;
   private $to       = null;
   private $subject  = null;
   private $text     = null;
   private $type     = null;

   public function __construct() {
      $this->type = "text/plain";
   }

   public function from($from) {
      $this->from = $from;
      return $this;
   }

   public function to($to) {
      $this->to = $to;
      return $this;
   }

   public function subject($subject) {
      $this->subject = $subject;
      return $this;
   }

   public function text($text) {
      $this->text = $text;
      return $this;
   }

   public function __toString() {
      $str = <<<EOT
From: {$this->from}
To: {$this->to}
Content-Type: {$this->type}
Subject: {$this->subject}

{$this->text}
EOT;
      return $str;
   }
}

?>
