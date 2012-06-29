<?php

/*
 * This file is part of the WSDL Proxy Generator package.
 *
 * Copyright (c) 2012 Farheap Solutions (http://www.farheap.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opensoft\WsdlProxyGenerator\Tests;

use Opensoft\WsdlProxyGenerator\SoapClient;

/**
 * @author Dmitry Petrov <dmitry.petrov@opensoftdev.ru>
 */
class SoapClientTest extends \PHPUnit_Framework_TestCase
{
    public function testAssertInstance()
    {
        $soapClient = new SoapClient(null, array('uri' => 'someUri', 'location' => 'someWsdlPath'));
        $this->assertInstanceOf('\SoapClient', $soapClient);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testFailedInstance()
    {
        $soapClient = @new SoapClient('nonExistsWsdl.wsdl');
    }
}
