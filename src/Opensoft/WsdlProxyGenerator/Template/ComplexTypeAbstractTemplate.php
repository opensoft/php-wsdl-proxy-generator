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
     * @param array \$options Data as key => value array
     */
    public function __construct(array \$options = null)
    {
        if (is_array(\$options)) {
            foreach (\$options as \$name => \$value) {
                \$functionName = 'set' . ucfirst(\$name);
                if (method_exists(\$this, \$functionName)) {
                    \$this->\$functionName(\$value);
                }
            }
        }
    }

    /**
     * Returns the complex type as an array
     *
     * @param boolean \$renderTopKey
     * @return array
     */
    public function toArray(\$renderTopKey = false)
    {
        \$returnArray = \$this->convertToArray(\$this->getListOfProperties());

        if (\$renderTopKey) {
            return array(static::CLASS_NAME => \$returnArray);
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
        if (!empty(\$arrayValues)) {
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

    /**
     * Returns array which contains all properties of the class
     *
     * @return array
     */
    private function getListOfProperties()
    {
        \$nameOfProperties = array();
        \$reflector = new \ReflectionClass(get_class(\$this));
        \$properties = \$reflector->getProperties();
        foreach(\$properties as \$reflectionProperty) {
            \$reflectionProperty->setAccessible(true);
            \$nameOfProperties[\$reflectionProperty->getName()] = \$reflectionProperty->getValue(\$this);
        }

        return \$nameOfProperties;
    }
}

TEXT;
    }

}
