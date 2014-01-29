<?php
/*
 * This file is part of the crafics/dbgpproxy package.
 * (c) Manfred Weber <crafics@php.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Crafics\DbgpProxy\Console;

use Crafics\DbgpProxy\Console\Command\ProxyStartCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * Class ConsoleRunner
 *
 * @author Manfred Weber <crafics@php.net>
 * @package Crafics\DbgpProxy\Console
 */
class ConsoleRunner
{
    /**
     * Version of CLI
     */
    const VERSION = 0.1;

    /**
     * @param HelperSet $helperSet
     * @param array $commands
     */
    static public function run(HelperSet $helperSet, $commands = array())
    {
        $cli = new \Symfony\Component\Console\Application('Crafics DbgpProxy CLI', self::VERSION);

        $cli->setHelperSet($helperSet);
        $cli->setCatchExceptions(true);
        self::addCommands($cli);
        $cli->run();
    }

    /**
     * @param Application $cli
     */
    static public function addCommands(Application $cli)
    {
        $cli->addCommands(array(
            new ProxyStartCommand()
        ));
    }

}