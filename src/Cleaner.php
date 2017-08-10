<?php
/**
 * Clean all the needed files
 *
 * PHP version 5.3
 *
 * @category Cleaner
 * @package  DelayedMail
 * @author   Eustáquio Rangel <eustaquiorangel@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @link     http://github.com/taq/delayedmail
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
 */
class Cleaner
{
    private $_dir;
    private $_time;

    /**
     * Constructor
     *
     * @param string $dir  directory when the files are
     * @param int    $time time limit
     */
    public function __construct($dir, $time = null) 
    {
        $this->_dir  = $dir;
        $this->_time = is_null($time) ? 1 * 60 * 24 : floatval($time);
    }

    /**
     * Convert to a string
     *
     * @return string information
     */
    public function __toString()
    {
        return "cleaning files older than {$this->_time} minutes on {$this->_dir}";
    }

    /**
     * Find the files that needs to be cleaned
     *
     * @return mixed file array
     */
    public function find()
    {
        $limit = mktime()-($this->_time * 60);
        $files = array_filter(glob("{$this->_dir}/*"),
            function($file) use ($limit) {
                $handle = fopen($file, "r");
                $stat   = fstat($handle);
                fclose($handle);
                return intval($stat["mtime"])<$limit;
            }
        );
        return $files;
    }

    /**
     * Run 
     *
     * @return null
     */
    public function run()
    {
        echo "- running cleaner on {$this->_dir} ...\n";

        if (is_null($this->_dir) || !file_exists($this->_dir)) {
            return false;
        }

        $files = $this->find();

        if (sizeof($files) < 1) {
            return false;
        }

        echo "- ".$this."\n";

        foreach ($files as $file) {
            echo "- removing $file\n";
            unlink($file);
        }
    }
}
?>
