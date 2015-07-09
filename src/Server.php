<?php
/**
 * Server class
 *
 * PHP version 5.3
 *
 * @category Server
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
 * @category Cleaner
 * @package  DelayedMail
 * @author   Eustáquio Rangel <eustaquiorangel@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @link     http://github.com/taq/delayedmail
 *
 */
class Server
{
    private $_host;
    private $_port;
    private $_user;
    private $_pwd;
    private $_cfg;
    private $_handle;
    private $_path;
    private $_domain;
    private $_auth;
    private $_tls;
    private $_error;

    /**
     * Constructor
     *
     * @param string $cfg config file
     */
    public function __construct($cfg = null)
    {
        $this->_port    = 25;
        $this->_path    = "/tmp/delayedmail";
        $this->_domain  = "delayedmail.com";
        $this->_auth    = false;
        $this->_tls     = false;
        $this->_cfg     = $cfg;
        $this->_error   = false;

        if ($this->_cfg) {
            $this->_readConfig();
        }
    }

    /**
     * Set the config file
     *
     * @param string $cfg config file path
     *
     * @return current object instance
     */
    public function config($cfg)
    {
        $this->_cfg = $cfg;
        $this->_readConfig();
        return $this;
    }

    /**
     * Return the mail server host
     *
     * @return string mail server
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     * Return the mail server port
     *
     * @return string mail server port
     */
    public function getPort()
    {
        return $this->_port;
    }

    /**
     * Return the mail server user
     *
     * @return string mail server user
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * Return the mail server password
     *
     * @return string mail server password
     */
    public function getPassword()
    {
        return $this->_pwd;
    }

    /**
     * Return the files path
     *
     * @return string path
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Return the delivered messages path
     *
     * @return string path
     */
    public function getDeliveryPath()
    {
        return $this->_path."/delivery";
    }

    /**
     * Return the sent messages path
     *
     * @return string path
     */
    public function getSentPath()
    {
        return $this->_path."/sent";
    }

    /**
     * Return the error messages path
     *
     * @return string path
     */
    public function getErrorPath()
    {
        return $this->_path."/error";
    }

    /**
     * Read configuration
     *
     * @return null
     */
    private function _readConfig()
    {
        if (!file_exists($this->_cfg)) {
            return false;
        }

        $contents = file_get_contents($this->_cfg);

        $tests = array(
            '/(host)(\s?=\s?)(.*)/i',
            '/(port)(\s?=\s?)(.*)/i',
            '/(user)(\s?=\s?)(.*)/i',
            '/(password)(\s?=\s?)(.*)/i',
            '/(domain)(\s?=\s?)(.*)/i',
            '/(path)(\s?=\s?)(.*)/i'
        );

        $props = array(
            &$this->_host,
            &$this->_port,
            &$this->_user,
            &$this->_pwd,
            &$this->_domain,
            &$this->_path
        );

        for ($i=0, $t = sizeof($tests); $i < $t; $i++) {
            if (!preg_match($tests[$i], $contents, $matches)) {
                continue;
            }
            $props[$i] = $matches[3];
        }
    }

    /**
     * Open server connection
     *
     * @return resource connection handle
     */
    public function open() 
    {
        $this->_handle = fsockopen($this->_host, $this->_port, $errno, $errstr, 30);
        if (!$this->_handle) {
            return false;
        }
        $this->_wait();
        $this->_ehlo();
        return $this->_handle;
    }

    /**
     * Send a server EHLO command
     *
     * @return null
     */
    private function _ehlo() 
    {
        $this->command("EHLO ".$this->_domain."\r\n");
    }

    /**
     * Send authentication
     *
     * @return null
     */
    private function _auth()
    {
        if (!is_null($this->_user) && !is_null($this->_pwd)) {
            if ($this->_tls) {
                $this->command("STARTTLS\n");
                stream_socket_enable_crypto($this->_handle, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $this->_ehlo();
            }

            $this->command("AUTH LOGIN\r\n");
            $this->command(base64_encode($this->_user)."\r\n");
            $this->command(base64_encode($this->_pwd)."\r\n");
        }
    }

    /**
     * Flush pending content
     *
     * @return null
     */
    public function flush() 
    {
        if (!$this->_handle) {
            return false;
        }
        fflush($this->_handle);
    }

    /**
     * Close connection
     *
     * @return null
     */
    public function close() 
    {
        if (!$this->_handle) {
            return false;
        }
        fputs($this->_handle, "QUIT\r\n");
        fflush($this->_handle);
        fclose($this->_handle);
    }

    /**
     * Send a mail command
     *
     * @param string  $cmd  command
     * @param boolean $wait wait flag
     *
     * @return boolean ok or not
     */
    public function command($cmd, $wait = true) 
    {
        if (!$this->_handle) {
            return false;
        }

        fputs($this->_handle, $cmd);
        fflush($this->_handle);
        return $this->_message($wait ? $this->_wait() : null, $cmd);
    }

    /**
     * Parse a message
     *
     * @param string $str message string
     * @param string $cmd command string
     *
     * @return ok or not
     */
    private function _message($str, $cmd)
    {
        if (is_null($str)) {
            return true;
        }

        if (preg_match('/^[45]/sim', $str)) {
            echo "* error: $str\n";
            echo "* command was: $cmd\n";
            $this->_error = true;
            return false;
        }

        if (preg_match('/STARTTLS/sim', $str)) {
            $this->_tls = true;
            $this->_auth();
        }

        if (preg_match('/250 AUTH/sim', $str)) {
            $this->_auth();
        }
        return true;
    }

    /**
     * Return server handle
     *
     * @return resource handle
     */
    public function getHandle()
    {
        return $this->_handle;
    }

    /**
     * Return current error
     *
     * @return string error
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * Set current error
     *
     * @param string $error error
     *
     * @return null
     */
    public function setError($error)
    {
        $this->_error = $error;
    }

    /**
     * Wait for server response
     *
     * @return string response
     */
    private function _wait()
    {
        if (!$this->_handle) {
            return false;
        }

        $rtn     = fgets($this->_handle);
        $status  = socket_get_status($this->_handle);
        $left    = intval($status["unread_bytes"]); 

        if ($left > 0) {
            $rtn .= fread($this->_handle, $left);
        }
        return $rtn;
    }

    /**
     * Create a path if not exists
     *
     * @param string $path path
     *
     * @return boolean created or not
     */
    private function _makePath($path)
    {
        if (!file_exists($path)) {
            if (!mkdir($path)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Create the base path
     *
     * @return boolean created or not
     */
    private function _makeBasePath()
    {
        return $this->_makePath($this->_path);
    }

    /**
     * Create the delivered messages path
     *
     * @return boolean created or not
     */
    private function _makeDeliveryPath()
    {
        return $this->_makePath($this->getDeliveryPath());
    }

    /**
     * Create the sent messages path
     *
     * @return boolean created or not
     */
    private function _makeSentPath()
    {
        return $this->_makePath($this->getSentPath());
    }

    /**
     * Create the error messages path
     *
     * @return boolean created or not
     */
    private function _makeErrorPath()
    {
        return $this->_makePath($this->getErrorPath());
    }

    /**
     * Push the message to the queue
     *
     * @param mixed $msg message
     *
     * @return string message content
     */
    public function push($msg)
    {
        if (   !$this->_makeBasePath()
            || !$this->_makeDeliveryPath()
            || !$this->_makeErrorPath()  
            || !$this->_makeSentPath()
        ) {
            return false;
        }

        $file = tempnam($this->getDeliveryPath(), "delayedmail");
        return file_put_contents($file, $msg);
    }
}
