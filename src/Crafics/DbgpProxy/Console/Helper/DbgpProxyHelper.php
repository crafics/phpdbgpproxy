<?php
/*
 * This file is part of the crafics/dbgpproxy package.
 * (c) Manfred Weber <crafics@php.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Crafics\DbgpProxy\Console\Helper;

use Symfony\Component\Console\Helper\Helper;
use Crafics\DbgpProxy\DbgpProxy;

/**
 * Class EventStoreHelper
 *
 * @author Manfred Weber <crafics@php.net>
 * @package Malocher\EventStore\Console\Helper
 */
class DbgpProxyHelper extends Helper
{
    /**
     * @var \Crafics\DbgpProxy\DbgpProxy
     */
    protected $dbgpproxy;

    /**
     * @param DbgpProxy $dbgpproxy
     */
    public function __construct(DbgpProxy $dbgpproxy)
    {
        $this->dbgpproxy = $dbgpproxy;
    }

    /**
     * @return DbgpProxy
     */
    public function getDbgpProxy()
    {
        return $this->dbgpproxy;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dbgpproxy';
    }
}