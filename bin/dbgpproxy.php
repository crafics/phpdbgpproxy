<?php
/*
 * This file is part of the crafics/dbgpproxy package.
 * (c) Manfred Weber <crafics@php.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Helper\HelperSet;
use Crafics\DbgpProxy\Console\Helper\DbgpProxyHelper;
use Crafics\DbgpProxy\DbgpProxy;
use Crafics\DbgpProxy\Configuration;
use Crafics\DbgpProxy\Console\ConsoleRunner;

(@include_once __DIR__ . '/../vendor/autoload.php') || @include_once __DIR__ . '/../../../autoload.php';

$directories = array(getcwd(), getcwd() . DIRECTORY_SEPARATOR . 'config');

$configFile = null;
foreach ($directories as $directory) {
    $configFile = $directory . DIRECTORY_SEPARATOR . 'dbgpproxy.config.php';
    if (file_exists($configFile)) {
        break;
    }
}
if ( ! file_exists($configFile)) {
    echo 'You are missing a "config.php" or "config/dbgpproxy.config.php" file in your project.';
    exit(1);
}
if ( ! is_readable($configFile)) {
    echo 'Configuration file [' . $configFile . '] does not have read permission.' . "\n";
    exit(1);
}

$commands = array();

$config = require $configFile;

$helperSet = new HelperSet(array(
    'proxy' => new DbgpProxyHelper(
        new DbgpProxy(new Configuration($config))
    )
));

ConsoleRunner::run($helperSet, $commands);
