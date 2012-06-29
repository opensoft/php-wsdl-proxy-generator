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

use Opensoft\WsdlProxyGenerator\Template\ComplexTypeAbstractTemplate;

/**
 * @author Dmitry Petrov <dmitry.petrov@opensoftdev.ru>
 */
class ComplexTypeAbstractGenerator extends ProxyGeneratorAbstract
{
    public function execute()
    {
        $template = new ComplexTypeAbstractTemplate($this->namespace, ComplexTypeAbstractTemplate::NAME, $this->useBlock,'Abstract class for all simple data types', self::$license);
        $this->saveCode($this->exportPath, ComplexTypeAbstractTemplate::NAME, $template->generateCode());
    }
}
