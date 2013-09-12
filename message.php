<?php
namespace DelayedMail;

class Message {
   private $from     = null;
   private $to       = null;
   private $subject  = null;
   private $text     = null;
   private $type     = null;
   private $files    = null;
   private $marker   = null;
   private $cc       = null;

   public function __construct() {
      $this->type  = "text/plain";
      $this->files = array();
   }

   public function from($from) {
      $this->from = $from;
      return $this;
   }

   public function to($to) {
      $this->to = $to;
      return $this;
   }

   public function cc($cc) {
      $this->cc = $cc;
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

   public function marker($marker) {
      $this->marker = $marker;
      return $this;
   }

   public function attach($file) {
      if(is_array($file))
         $this->files = array_merge($this->files,$file);
      else
         array_push($this->files,$file);
      return $this;
   }

   private function ccText() {
      if(is_null($this->cc))
         return "";
      $cc = "\nCc: ".(is_array($this->cc) ? join(", ",$this->cc) : $this->cc);
      return $cc;
   }

   private function header() {
      $str = <<<EOT
From: {$this->from}
To: {$this->to}{$this->ccText()}
Subject: {$this->subject}
EOT;
      return $str;
   }

   private function simpleMessageText() {
      $str = <<<EOT
{$this->header()}
Content-Type: {$this->type}

{$this->text}
EOT;
      return $str;
   }

   private function attachmentsMessageText() {
      $marker  = is_null($this->marker) ? time() : $this->marker;
      $markert = $marker+1;
      $str = <<<EOT
{$this->header()}
Content-Type: multipart/mixed; boundary={$marker}

--{$marker}
Content-Type: multipart/alternative; boundary={$markert}

--{$markert}
Content-Type: text/plain

{$this->text}

--{$markert}--
EOT;

      foreach($this->files as $file) {
         $contents = base64_encode(file_get_contents($file));
         $contents = join("\n",str_split($contents,76));
         $mime     = mime_content_type($file);
         $base     = basename($file);
         $file_str = <<<EOT
\n\n--{$marker}
Content-Type: {$mime}; name="{$base}"
Content-Disposition: attachment; filename="{$base}"
Content-Transfer-Encoding: base64
X-Attachment-Id: 1

$contents
EOT;
         $str .= $file_str;
      }
      $str .= "\n--{$marker}--";
      return trim($str);
   }

   public function __toString() {
      if(sizeof($this->files)<1)
         return $this->simpleMessageText();
      return $this->attachmentsMessageText();
   }
}
?>
