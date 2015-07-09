<?php
/**
 * Sender test
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
class SenderTest extends PHPUnit_Framework_TestCase
{
    protected static $sender = null;

    /**
     * Run before each test
     *
     * @return null
     */
    public function setUp()
    {
        self::$sender = new DelayedMail\Sender(5, "delayedmail.ini");
    }

    /**
     * Test cleaner
     *
     * @return null
     */
    public function testCleaner()
    {
        $this->assertNotNull(self::$sender->getCleaner());
        $this->assertEquals("cleaning files older than 1 minutes on /tmp/delayedmailtest/sent", self::$sender->getCleaner(). "");
    }
}
