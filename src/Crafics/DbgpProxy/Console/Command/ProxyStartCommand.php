<?php
/*
 * This file is part of the crafics/dbgpproxy package.
 * (c) Manfred Weber <crafics@php.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Crafics\DbgpProxy\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ProxyCreateCommand
 * @package Crafics\DbgpProxy\Console\Command
 */
class ProxyStartCommand extends Command
{
    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('proxy:start')
            ->setDescription('Start Dbgp Proxy')
            ->addArgument('streams',InputArgument::IS_ARRAY | InputArgument::REQUIRED,'Names of streams to create')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $streams = $input->getArgument('streams');

        $success = $this
            ->getHelper('dbgpproxy')
            ->getDbgpProxy()
        ;

        // event dispatching ?!

        if($success){
            $output->writeln("<info>proxy started</info>");

            /*
             * copied form php.net
             */

            error_reporting(E_ALL);

            /* Allow the script to hang around waiting for connections. */
            set_time_limit(0);

            /* Turn on implicit output flushing so we see what we're getting
             * as it comes in. */
            ob_implicit_flush();

            $address = '127.0.0.1';
            $port = 9001;
            $output->writeln("1");
            if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {

                $output->writeln("socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n");
            }

            if (socket_bind($sock, $address, $port) === false) {
                $output->writeln("socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n");
            }

            if (socket_listen($sock, 5) === false) {
                $output->writeln( "socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n");
            }

            $output->writeln("2");
            do {
                $output->writeln("3");
                if (($msgsock = socket_accept($sock)) === false) {
                    $output->writeln("socket_accept() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n");
                    break;
                }
                $output->writeln("4");
                /* Send instructions. */
                /*$msg = "\nWelcome to the PHP Test Server. \n" .
                    "To quit, type 'quit'. To shut down the server type 'shutdown'.\n";
                socket_write($msgsock, $msg, strlen($msg));*/

                do {
                    $output->writeln("5");
                    if (false === ($buf = socket_read($msgsock, 2048))) {
                        $output->writeln( "socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n");
                        break 2;
                    }
                    $output->writeln("6");
                    if (!$buf = trim($buf)) {
                        continue;
                    }
                    $output->writeln("7");
                    if ($buf == 'quit') {
                        break;
                    }
                    $output->writeln("8");
                    if ($buf == 'shutdown') {
                        $output->writeln("okidoki2");
                        socket_close($msgsock);
                        break 2;
                    }
                    $output->writeln("9");
                    $talkback = "PHP: You said '$buf'.\n";
                    $output->writeln( $talkback );
                    socket_write($msgsock, $talkback, strlen($talkback));
                    $output->writeln( "$buf\n" );
                } while (true);
                socket_close($msgsock);
            } while (true);

            socket_close($sock);





        } else {
            $output->writeln("<error>something went wrong</error>");
        }
    }
}