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
class ComplexTypeAbstractTemplate extends TemplateAbstract
{
    const NAME = 'ComplexTypeAbstract';

    protected function getClassBody()
    {
        return <<<TEXT
abstract class {$this->className}
{
    /**
     * Holds the data as a key => value array
     *
     * @var array
     */
    protected \$values;

    /**
     * The name of the extended class/data type
     *
     * @var string
     */
    protected \$name;

    /**
     * @param array \$options Data as key => value array
     */
    public function __construct(array \$options = null)
    {
        if (is_array(\$options)) {
            foreach (\$options as \$name => \$value) {
                \$this->\$name = \$value;
            }
        }
    }

    /**
     * @param string \$name
     * @param string \$value
     */
    public function __set(\$name, \$value)
    {
        \$this->values[\$name] = \$value;
    }

    /**
     * Returns the complex type as an array
     *
     * @param boolean \$renderTopKey
     * @return array
     */
    public function toArray(\$renderTopKey = false)
    {
        \$returnArray = \$this->convertToArray(\$this->values);

        if (\$renderTopKey) {
            return array(\$this->name => \$returnArray);
        } else {
            return \$returnArray;
        }
    }

    /**
     * Recursive algorithm to convert complex types to and array
     *
     * @param array \$arrayValues
     * @return array
     */
    protected function convertToArray(\$arrayValues)
    {
        \$returnArray = array();
        if (!empty(\$arrayValues) {
            foreach (\$arrayValues as \$key => \$value) {
                if (\$value instanceof self) {
                    \$returnArray[\$key] = \$value->toArray();
                } elseif (is_array(\$value)) {
                    \$returnArray[\$key] = \$this->convertToArray(\$value);
                } else {
                    if (\$value instanceof SimpleType\SimpleTypeAbstract) {
                        \$returnArray[\$key] = (string) \$value;
                    } else {
                        \$returnArray[\$key] = \$value;
                    }
                }
            }
        }

        return \$returnArray;
    }
}

TEXT;
    }

}
