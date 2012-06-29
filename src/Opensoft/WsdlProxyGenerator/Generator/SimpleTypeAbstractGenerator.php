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

use Opensoft\WsdlProxyGenerator\Template\SimpleTypeAbstractTemplate;

/**
 * @author Dmitry Petrov <dmitry.petrov@opensoftdev.ru>
 */
class SimpleTypeAbstractGenerator extends ProxyGeneratorAbstract
{
    public function execute()
    {
        $template = new SimpleTypeAbstractTemplate($this->namespace, SimpleTypeAbstractTemplate::NAME, $this->useBlock, 'Abstract class for all simple data types', self::$license);
        $this->saveCode($this->exportPath, SimpleTypeAbstractTemplate::NAME, $template->generateCode());
    }
}
