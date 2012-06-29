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
class SimpleTypeTemplate extends TemplateAbstract
{
    protected function getClassBody()
    {
        $parentClassName = SimpleTypeAbstractTemplate::NAME;
        return <<<TEXT
class {$this->className} extends {$parentClassName}
{
{$this->generateConstantsBlock()}
}

TEXT;
    }

    protected function generateConstantsBlock()
    {
        $result = '';
        if (!empty($this->constants)) {
            foreach ($this->constants as $constant) {
                $constantName = str_replace('.', 'Point', $constant['name']);
                if ($constant['documentation'] != '') {
                    $result .= <<<TEXT
    /**
{$this->phpDocParser($constant['documentation'], '     * ')}
     */

TEXT;

                }
                $result .= '    const ' . $constantName . " = '{$constant['name']}';\n\n";
            }
        }

        return $result;
    }
}
