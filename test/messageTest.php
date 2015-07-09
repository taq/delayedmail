<?php
/**
 * Message test
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
class MessageTest extends PHPUnit_Framework_TestCase
{
    protected static $message = null;

    /**
     * Run before each test
     *
     * @return null
     */
    public function setUp()
    {
        self::$message = new DelayedMail\Message();
    }

    /**
     * Test converting to a string
     *
     * @return null
     */
    public function testToString()
    {
        self::$message->from("Eustaquio Rangel <eustaquiorangel@gmail.com>")
            ->to("Eustaquio Rangel <taq@bluefish.com.br>")
            ->subject("DelayedMail test!")
            ->text("This is just\na test!");
        $str = self::$message;
        $exp = <<<EOT
From: Eustaquio Rangel <eustaquiorangel@gmail.com>
To: Eustaquio Rangel <taq@bluefish.com.br>
Subject: DelayedMail test!
Content-Type: text/plain

This is just
a test!
EOT;
        $this->assertEquals(trim($exp), trim($str));
    }

    /**
     * Test converting to a string with one CC
     *
     * @return null
     */
    public function testToStringWithOneCC() 
    {
        self::$message->from("Eustaquio Rangel <eustaquiorangel@gmail.com>")
            ->to("Eustaquio Rangel <taq@bluefish.com.br>")
            ->cc("Eustaquio Rangel <taq@eustaquiorangel.com>")
            ->subject("DelayedMail test!")
            ->text("This is just\na test!");
        $str = self::$message;
        $exp = <<<EOT
From: Eustaquio Rangel <eustaquiorangel@gmail.com>
To: Eustaquio Rangel <taq@bluefish.com.br>
Cc: Eustaquio Rangel <taq@eustaquiorangel.com>
Subject: DelayedMail test!
Content-Type: text/plain

This is just
a test!
EOT;
        $this->assertEquals(trim($exp), trim($str));
    }

    /**
     * Test converting to a string with multiple CCs
     *
     * @return null
     */
    public function testToStringWithMultipleCC() 
    {
        self::$message->from("Eustaquio Rangel <eustaquiorangel@gmail.com>")
            ->to("Eustaquio Rangel <taq@bluefish.com.br>")
            ->cc(array("Eustaquio Rangel <taq@eustaquiorangel.com>","Eustaquio Rangel <taq@host.com>"))
            ->subject("DelayedMail test!")
            ->text("This is just\na test!");
        $exp = <<<EOT
From: Eustaquio Rangel <eustaquiorangel@gmail.com>
To: Eustaquio Rangel <taq@bluefish.com.br>
Cc: Eustaquio Rangel <taq@eustaquiorangel.com>, Eustaquio Rangel <taq@host.com>
Subject: DelayedMail test!
Content-Type: text/plain

This is just
a test!
EOT;
        $this->assertEquals(trim($exp), trim(self::$message));
    }

    /**
     * Test converting to a string with attachments
     *
     * @return null
     */
    public function testToStringWithAttachments()
    {
        self::$message->from("Eustaquio Rangel <eustaquiorangel@gmail.com>")
            ->to("Eustaquio Rangel <taq@bluefish.com.br>")
            ->subject("DelayedMail test!")
            ->text("This is just\na test!")
            ->attach(array("./taq.jpg","./qat.jpg"))
            ->marker(1378910636);
        $this->assertEquals(trim(file_get_contents("attachment.txt")), trim(self::$message));
    }
}
?>
