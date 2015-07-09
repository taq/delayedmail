<?php
/**
 * Build a message
 *
 * PHP version 5.3
 *
 * @category Message
 * @package  DelayedMail
 * @author   Eustáquio Rangel <eustaquiorangel@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @link     http://github.com/taq/delayedmail
 *
 */
namespace DelayedMail;

/**
 * Main class
 *
 * PHP version 5.3
 *
 * @category Message
 * @package  DelayedMail
 * @author   Eustáquio Rangel <eustaquiorangel@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @link     http://github.com/taq/delayedmail
 *
 */
class Message
{
    private $_from     = null;
    private $_to       = null;
    private $_subject  = null;
    private $_text     = null;
    private $_type     = null;
    private $_files    = null;
    private $_marker   = null;
    private $_cc       = null;

    /**
     * Constructor
     */
    public function __construct() 
    {
        $this->_type  = "text/plain";
        $this->_files = array();
    }

    /**
     * Who this message comes from
     *
     * @param string $from email address
     *
     * @return mixed the current object instance
     */
    public function from($from)
    {
        $this->_from = $from;
        return $this;
    }

    /**
     * Who this message goes to
     *
     * @param string $to email address
     *
     * @return mixed the current object instance
     */
    public function to($to)
    {
        $this->_to = $to;
        return $this;
    }

    /**
     * Who needs a carbon copy of this message
     *
     * @param string $cc email address
     *
     * @return mixed the current object instance
     */
    public function cc($cc) 
    {
        $this->_cc = $cc;
        return $this;
    }

    /**
     * What is this message subject
     *
     * @param string $subject message subject
     *
     * @return mixed the current object instance
     */
    public function subject($subject)
    {
        $this->_subject = $subject;
        return $this;
    }

    /**
     * What is this message text
     *
     * @param string $text message text
     *
     * @return mixed the current object instance
     */
    public function text($text)
    {
        $this->_text = $text;
        return $this;
    }

    /**
     * Message marker, used to split the attachments
     *
     * @param int $marker number
     *
     * @return mixed the current object instance
     */
    public function marker($marker)
    {
        $this->_marker = $marker;
        return $this;
    }

    /**
     * Attachments
     *
     * Can be a string or an array
     *
     * @param mixed $file file
     *
     * @return mixed the current object instance
     */
    public function attach($file)
    {
        if (is_array($file)) {
            $this->_files = array_merge($this->_files, $file);
        } else {
            array_push($this->_files, $file);
        }
        return $this;
    }

    /**
     * Carbon copy text
     *
     * @return string with all the needed emails
     */
    private function _ccText()
    {
        if (is_null($this->_cc)) {
            return "";
        }
        $cc = "\nCc: ".(is_array($this->_cc) ? join(", ", $this->_cc) : $this->_cc);
        return $cc;
    }

    /**
     * Message header
     *
     * @return string with the header
     */
    private function _header()
    {
        $str = <<<EOT
From: {$this->_from}
To: {$this->_to}{$this->_ccText()}
Subject: {$this->_subject}
EOT;
        return $str;
    }

    /**
     * Return the simplified message text
     *
     * @return string text
     */
    private function _simpleMessageText()
    {
        $str = <<<EOT
{$this->_header()}
Content-Type: {$this->_type}

{$this->_text}
EOT;
        return $str;
    }

    /**
     * Returns the attachment text
     *
     * @return string text
     */
    private function _attachmentsMessageText()
    {
        $marker  = is_null($this->_marker) ? time() : $this->_marker;
        $markert = $marker + 1;

        $str = <<<EOT
{$this->_header()}
Content-Type: multipart/mixed; boundary={$marker}

--{$marker}
Content-Type: multipart/alternative; boundary={$markert}

--{$markert}
Content-Type: text/plain

{$this->_text}

--{$markert}--
EOT;

        foreach ($this->_files as $file) {
            $contents = base64_encode(file_get_contents($file));
            $contents = join("\n", str_split($contents, 76));
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

    /**
     * Convert the object instance to a string
     *
     * @return string 
     */
    public function __toString()
    {
        // if there is no attachments, return the simplified text version
        if (sizeof($this->_files) < 1) {
            return $this->_simpleMessageText();
        }
        return $this->_attachmentsMessageText();
    }
}
?>
