<?php
/**
 * Server test
 *
 * PHP version 5.3
 *
 * @category Tests
 * @package  DelayedMail
 * @author   Eustáquio Rangel <eustaquiorangel@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @link     http://github.com/taq/delayedmail
 *
 */
require_once "../vendor/autoload.php";

/**
 * Main class
 *
 * PHP version 5.3
 *
 * @category Tests
 * @package  DelayedMail
 * @author   Eustáquio Rangel <eustaquiorangel@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @link     http://github.com/taq/delayedmail
 *
 */
class ServerTest extends PHPUnit_Framework_TestCase
{
    protected static $server = null;

    /**
     * Run before each test
     *
     * @return null
     */
    public static function setUpBeforeClass() 
    {
        self::$server = new DelayedMail\Server();
    }

    /**
     * Config test
     *
     * @return null
     */
    public function testConfig()
    {
        self::$server->config("delayedmail.ini");
        $this->assertEquals("smtp.gmail.com", self::$server->getHost());
        $this->assertEquals("587", self::$server->getPort());
        $this->assertEquals("taq", self::$server->getUser());
        $this->assertEquals("secret", self::$server->getPassword());
        $this->assertEquals("/tmp/delayedmailtest", self::$server->getPath());
    }

    /**
     * The following tests are used just to push messages to the server.
     * To check if they are working, you must configure a .ini file with some 
     * valid configuration and run the Runner class.
     *
     * @return null
     */
    public function testPush()
    {
        $message = new DelayedMail\Message();
        $message->from("taq <eustaquiorangel@gmail.com>")
            ->to("Eustaquio Rangel <taq@bluefish.com.br>")
            ->subject("DelayedMail test!")
            ->text("This is just\na test!");
        self::$server->push($message);
    }

    /**
     * Test error sending email
     *
     * @return null
     */
    public function testError()
    {
        $message = new DelayedMail\Message();
        $message->from("taq <eustaquiorangel@gmail.com>")
            ->to("Eustaquio Rangel")
            ->subject("DelayedMail test with error!")
            ->text("This is just\na test!");
        self::$server->push($message);
    }

    /**
     * Test pushing message with CC
     *
     * @return null
     */
    public function testPushWithCC() 
    {
        $message = new DelayedMail\Message();
        $message->from("taq <eustaquiorangel@gmail.com>")
            ->to("Eustaquio Rangel <taq@bluefish.com.br>")
            ->cc("Eustaquio Rangel <taq@eustaquiorangel.com>")
            ->subject("DelayedMail test with CC!")
            ->text("This is just\na test!");
        self::$server->push($message);
    }

    /**
     * Test pushing message with attachment
     *
     * @return null
     */
    public function testPushWithAttachment()
    {
        $message = new DelayedMail\Message();
        $message->from("taq <eustaquiorangel@gmail.com>")
            ->to("Eustaquio Rangel <taq@bluefish.com.br>")
            ->subject("DelayedMail test!")
            ->text("This is just\na test!")
            ->attach(array("taq.jpg","qat.jpg"));
        self::$server->push($message);
    }
}
?>
