<?php

/*
 * This file is part of the WSDL Proxy Generator package.
 *
 * Copyright (c) 2012 Farheap Solutions (http://www.farheap.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opensoft\WsdlProxyGenerator\Generator;

use Opensoft\WsdlProxyGenerator\Template\SoapClientTemplate;

/**
 * @author Dmitry Petrov <dmitry.petrov@opensoftdev.ru>
 */
class SoapClientGenerator extends ProxyGeneratorAbstract
{
    public function execute()
    {
        $template = new SoapClientTemplate($this->namespace, SoapClientTemplate::NAME, $this->useBlock,
            'Wrapper for \SoapClient', self::$license);
        $this->saveCode($this->exportPath, SoapClientTemplate::NAME, $template->generateCode());
    }
}
