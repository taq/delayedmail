<?php
/**
 * Fire all the emails on the queue
 *
 * PHP version 5.3
 *
 * @category Main
 * @package  DelayedMail
 * @author   Eustáquio Rangel <eustaquiorangel@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @link     http://github.com/taq/delayedmail
 *
 */
if (file_exists("vendor")) {
    echo "- loading from composer\n";
    include_once "vendor/autoload.php";
} else {
    $dir = dirname(__FILE__);
    echo "- loading classes from $dir\n";
    include_once "$dir/delayedmail.php";
}

$sender = new DelayedMail\Sender(5, "delayedmail.ini");
$sender->run();
?>
