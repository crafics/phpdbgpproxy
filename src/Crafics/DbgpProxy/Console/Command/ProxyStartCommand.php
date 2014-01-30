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

    private function send( $socket, $msg )
    {
        $msg = utf8_encode($msg."\0");
        $length = strlen($msg);
        while (true) {
            echo $msg;
            $sent = socket_write($socket, $msg, $length);
            if ($sent === false) {
                return;
            }
            if ($sent < $length) {
                $msg = substr($msg, $sent);
                $length -= $sent;
            } else {
                return;
            }
        }
    }

    private function proxy( $input, $output )
    {
        error_reporting(E_ALL);
        set_time_limit(0);
        ob_implicit_flush();

        $clients = array();

        // debugger
        /*if (($sock_debugger = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
            $output->writeln("socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n");
        }
        if (socket_bind($sock_debugger, '127.0.0.1', 9000) === false) {
            $output->writeln("socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock_debugger)) . "\n");
        }
        if (socket_listen($sock_debugger, 5) === false) {
            $output->writeln( "socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock_debugger)) . "\n");
        }
        array_push($clients,$sock_debugger);*/

        // ide
        if (($sock_ide = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
            $output->writeln("socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n");
        }
        if (socket_bind($sock_ide, '127.0.0.1', 9001) === false) {
            $output->writeln("socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock_ide)) . "\n");
        }
        if (socket_listen($sock_ide, 5) === false) {
            $output->writeln( "socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock_ide)) . "\n");
        }
        array_push($clients,$sock_ide);

        while (true) {
            // create a copy, so $clients doesn't get modified by socket_select()
            $read = $clients;
            $write = null;
            $except = null;

            // get a list of all the clients that have data to be read from
            // if there are no clients with data, go to next iteration
            if (socket_select($read, $write, $except, 0) < 1)
                continue;

            if (in_array($sock_ide, $read)) {
                $clients[] = $newsock = socket_accept($sock_ide);

                // remove the listening socket from the clients-with-data array
                $key = array_search($sock_ide, $read);
                unset($read[$key]);
            }

            // loop through all the clients that have data to read from
            foreach ($read as $read_sock) {
                // read until newline or 1024 bytes
                // socket_read while show errors when the client is disconnected, so silence the error messages
                //$data = @socket_read($read_sock, 1024, PHP_NORMAL_READ);
                $data = socket_read($read_sock, 1024);

                // check if the client is disconnected
                if ($data === false) {
                    // remove client for $clients array
                    $key = array_search($read_sock, $clients);
                    unset($clients[$key]);
                    //echo "client disconnected.\n";
                    // continue to the next client to read from, if any
                    continue;
                }

                // trim off the trailing/beginning white spaces
                $data = trim($data);

                // check if there is any data after trimming off the spaces
                if (!empty($data)) {
                    $this->send( $newsock, '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<proxyinit success="1" idekey="crafics" address="127.0.0.1" port="9001"/>');

                    // send this to all the clients in the $clients array (except the first one, which is a listening socket)
                    foreach ($clients as $send_sock) {

                        // if its the listening sock or the client that we got the message from, go to the next one in the list
                        if ($send_sock == $sock_ide || $send_sock == $read_sock)
                            continue;

                        // write the message to the client -- add a newline character to the end of the message
                        //socket_write($send_sock, '...'."\n");

                    } // end of broadcast foreach

                }

            } // end of reading foreach
        }

        // close the listening sockets
        socket_close($sock_ide);
        socket_close($sock_debugger);
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

            $this->proxy( $input, $output );
            //$this->ide( $input, $output );

        } else {
            $output->writeln("<error>something went wrong</error>");
        }
    }
}