<?php
/**
 * The main object to send messages
 *
 * PHP version 5.3
 *
 * @category Sender
 * @package  DelayedMail
 * @author   Eustáquio Rangel <eustaquiorangel@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @link     http://github.com/taq/delayedmail
 */
namespace DelayedMail;

require_once "Message.php";
require_once "Server.php";
require_once "Cleaner.php";

/**
 * Main class
 *
 * PHP version 5.3
 *
 * @category Sender
 * @package  DelayedMail
 * @author   Eustáquio Rangel <eustaquiorangel@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @link     http://github.com/taq/delayedmail
 */
class Sender
{
    private $_cfg;
    private $_server;
    private $_interval;
    private $_cleaner;

    /**
     * Constructor
     *
     * @param int    $interval to check
     * @param string $cfg      configuration file
     */
    public function __construct($interval = 5, $cfg = null)
    {
        $this->_cfg        = $cfg;
        $this->_server     = new Server($this->_cfg);
        $this->_interval   = $interval;
        $this->_setCleaner();
    }

    /**
     * Find the cleaner config line
     *
     * @return string cleaner config line
     */
    private function _getCleanerConfig()
    {
        if (!file_exists($this->_cfg)) {
            echo "* config file ".$this->_cfg." does not exists!\n";
            exit(1);
        }
        return $this->_find('/^(cleaner\s?=\s?)(.*)/sim', file_get_contents($this->_cfg));
    }

    /**
     * Find the cleaner class
     *
     * @return string cleaner class
     */
    private function _getCleanerClass()
    {
        $str = $this->_getCleanerConfig();
        if (!$str) {
            return null;
        }
        $tokens = explode(",", $str);
        return trim($tokens[0]);
    }

    /**
     * Find the cleaner time
     *
     * @return string cleaner time
     */
    private function _getClearTime()
    {
        $str = $this->_getCleanerConfig();
        if (!$str) {
            return null;
        }
        $tokens = explode(",", $str);
        if (sizeof($tokens) > 1) {
            return floatval(trim($tokens[1]));
        }
        return null;
    }

    /**
     * Create the cleaner class
     *
     * @return mixed cleaner class instance
     */
    private function _setCleaner()
    {
        $cls  = $this->_getCleanerClass();
        $time = $this->_getClearTime();

        if (!$cls) {
            return null;
        }
        echo "- cleaner class is $cls, cleaner time is $time minutes ago\n";

        $this->_cleaner = new $cls($this->_server->getSentPath(), $time);
        return $this->_cleaner;
    }

    /**
     * Return the cleaner config line
     *
     * @return mixed cleaner class
     */
    public function getCleaner()
    {
        return $this->_cleaner;
    }

    /**
     * Run
     *
     * @param mixed $options options
     *
     * @return null
     */
    public function run($options = [])
    {
        echo "- initializing ...\n";

        $delivery_path = $this->_server->getDeliveryPath();
        $cnt           = 0;
        $max           = 0;

        if (array_key_exists("q", $options)) {
            echo "- not running on a loop.\n";
            $max = 1;
        }

        while ($max == 0 || $cnt < $max) {
            if ($this->_cleaner) {
                $this->_cleaner->run();
            }
            sleep($this->_interval);

            echo "- checking for files in {$delivery_path} ...\n";
            $files = array_filter(glob("$delivery_path/*"),
                function($file) {
                    return filesize($file)>0;
                }
            );
            $cnt ++;

            if (sizeof($files) < 1) {
                echo "- no files found.\n";
                continue;
            }

            echo "- ".sizeof($files)." file(s) found.\n";

            if (!$this->_server->open()) {
                echo "* could not open mail server.\n";
                continue;
            }

            foreach ($files as $file) {
                $this->_proc($file);
            }

            $this->_server->close();
        }
    }

    /**
     * Return the full path where the sent message file is
     *
     * @param string $file message file
     *
     * @return string directory
     */
    private function _getSentPath($file)
    {
        $sent_path = $this->_server->getSentPath();
        return "$sent_path/".basename($file);
    }

    /**
     * Return the full path where the error message file is
     *
     * @param string $file message file
     *
     * @return string directory
     */
    private function _getErrorPath($file)
    {
        $error_path = $this->_server->getErrorPath();
        return "$error_path/".basename($file);
    }

    /**
     * Process all the found files
     *
     * @param string $file file
     *
     * @return boolean ok or not
     */
    private function _proc($file)
    {
        echo "- processing $file ...\n";
        $contents = file_get_contents($file);

        $from = $this->_getFrom($contents);
        $to   = $this->_getTo($contents);
        $cc   = $this->_getCC($contents);
        $subj = $this->_getSubject($contents);
        $type = $this->_getContentType($contents);
        $text = $this->_getText($contents);

        $stripped_from = $this->_getStrippedEmail($from);
        $stripped_to   = $this->_getStrippedEmail($to);

        $this->_server->setError(false);

        $rst  = "";
        $rst .= $this->_server->command("MAIL FROM: <$stripped_from>\r\n", true);
        $rst .= $this->_server->command("RCPT TO: <$stripped_to>\r\n", true);

        if (!is_null($cc)) {
            $tokens = explode(",", $cc);
            foreach ($tokens as $cc) {
                $cc = $this->_getStrippedEmail($cc);
                $rst .= $this->_server->command("RCPT TO: <$cc>\r\n", true);
            }
        }

        $rst .= $this->_server->command("DATA\r\n", true);
        $rst .= $this->_server->command("From: $from\n", false);
        $rst .= $this->_server->command("To: $to\n", false);

        if (!is_null($cc)) {
            $rst .= $this->_server->command("Cc: $cc\n", false);
        }

        $rst .= $this->_server->command("Content-Type: $type\r\n", false);
        $rst .= $this->_server->command("Subject: $subj\n\n", false);
        $rst .= $this->_server->command("$text\r\n", false);
        $rst .= $this->_server->command("\r\n.\r\n", true);
        $this->_server->flush();

        if ($this->_server->getError()) {
            echo "* could not send email\n";
            if (!$this->_move($file, $this->_getErrorPath($file))) {
                echo "* could not move file to error dir\n";
            }
            return false;
        }

        if (!$this->_move($file, $this->_getSentPath($file))) {
            return false;
        }
        return true;
    }

    /**
     * Find an expression on a string, returning the default value if not found
     *
     * @param string $regex    regular expression
     * @param string $contents content to be searched
     * @param mixed  $default  default value to return if expression not found
     *
     * @return matches
     */
    private function _find($regex, $contents, $default = null)
    {
        $ok = preg_match($regex, $contents, $matches);
        if (!$ok) {
            return $default;
        }
        return $matches[2];
    }

    /**
     * Return the from info
     *
     * @param string $contents of the message
     *
     * @return string from
     */
    private function _getFrom($contents)
    {
        return $this->_find('/^(From:\s?)([^\n]+)/sim', $contents);
    }

    /**
     * Return the to info
     *
     * @param string $contents of the message
     *
     * @return string to
     */
    private function _getTo($contents)
    {
        return $this->_find('/^(To:\s?)([^\n]+)/sim', $contents);
    }

    /**
     * Return the CC info
     *
     * @param string $contents of the message
     *
     * @return string CC
     */
    private function _getCC($contents) 
    {
        return $this->_find('/^(Cc:\s?)([^\n]+)/sim', $contents);
    }

    /**
     * Return the subject info
     *
     * @param string $contents of the message
     *
     * @return string subject
     */
    private function _getSubject($contents)
    {
        return $this->_find('/^(Subject:\s?)([^\n]+)/sim', $contents);
    }

    /**
     * Return the content type
     *
     * @param string $contents of the message
     *
     * @return string content type
     */
    private function _getContentType($contents)
    {
        return $this->_find('/^(Content-Type:\s?)([^\n]+)/sim', $contents);
    }

    /**
     * Return the message text
     *
     * @param string $contents of the message
     *
     * @return string text
     */
    private function _getText($contents) 
    {
        $tokens = preg_split('/\n\n/sim', $contents);
        if (sizeof($tokens) < 2) {
            return null;
        }
        $text = join("\n\n", array_slice($tokens, 1));
        return $text;
    }

    /**
     * Return the stripped email address
     *
     * @param string $email address
     *
     * @return string address
     */
    private function _getStrippedEmail($email)
    {
        return $this->_find('/(<)(.*)(>)/', $email, $email);
    }

    /**
     * Move (renaming) a file from a directory to another
     *
     * @param string $from origin dir
     * @param string $to   destination dir
     *
     * @return boolean moved or not
     */
    private function _move($from, $to)
    {
        echo "- moving $from to $to\n";
        if (!rename($from, $to)) {
            echo "* error: could not move file $from to $to\n";
            return false;
        }
        return true;
    }
}
?>
