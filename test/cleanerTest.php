<?php
/**
 * Cleaner test
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
class CleanerTest extends PHPUnit_Framework_TestCase
{
    protected static $cleaner = null;
    protected static $dir     = "/tmp/delayedmailtest/sent/";

    /**
     * Run before every test
     *
     * @return null
     */
    public function setUp() 
    {
        self::$cleaner = new DelayedMail\Cleaner(self::$dir, 0.1);
    }

    /**
     * Test converting to a string
     *
     * @return null
     */
    public function testToString()
    {
        $this->assertEquals("cleaning files older than 0.1 minutes on /tmp/delayedmailtest/sent/", self::$cleaner. "");
    }

    /**
     * Test finding files
     *
     * @return null
     */
    public function testFind()
    {
        self::_createFiles();
        $files = self::$cleaner->find();
        $this->assertEquals(2, sizeof($files));
        self::_clearFiles();
    }

    /**
     * Test running the cleaner
     *
     * @return null
     */
    public function testRun()
    {
        self::_createFiles();
        self::$cleaner->run();
        $this->assertEquals(1, sizeof(glob(self::$dir."/*")));
        self::_clearFiles();
    }

    /**
     * Create test files
     *
     * @return null
     */
    private function _createFiles()
    {
        $time = mktime()-18;

        if (!file_exists(self::$dir)) {
            mkdir(self::$dir, 0777, true);
        }

        foreach (array("a","b","c") as $name) {
            $file = self::$dir."/$name";
            touch($file, $time);
            $time += 6;
        }
    }

    /**
     * Clear test files
     *
     * @return null
     */
    private function _clearFiles()
    {
        foreach (glob(self::$dir."/*") as $file) {
            unlink($file);
        }
    }
}
