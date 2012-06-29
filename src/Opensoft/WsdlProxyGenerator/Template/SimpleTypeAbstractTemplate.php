<?php

/*
 * This file is part of the WSDL Proxy Generator package.
 *
 * Copyright (c) 2012 Farheap Solutions (http://www.farheap.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opensoft\WsdlProxyGenerator\Template;

/**
 * @author Dmitry Petrov <dmitry.petrov@opensoftdev.ru>
 */
class SimpleTypeAbstractTemplate extends TemplateAbstract
{
    const NAME = 'SimpleTypeAbstract';

    protected function getClassBody()
    {
        return <<<TEXT
abstract class {$this->className}
{
    /**
     * @var string
     */
    protected \$value;

    /**
     * @param string \$value
     */
    public function __construct(\$value)
    {
        \$this->value = \$value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return \$this->value;
    }
}

TEXT;
    }

}
