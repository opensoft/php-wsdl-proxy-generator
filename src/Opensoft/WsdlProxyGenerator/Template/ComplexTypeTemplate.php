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

use Opensoft\WsdlProxyGenerator\Generator\ProxyGeneratorAbstract;

/**
 * @author Dmitry Petrov <dmitry.petrov@opensoftdev.ru>
 */
class ComplexTypeTemplate extends TemplateAbstract
{
    protected function getClassBody()
    {
        $parentClassName = ComplexTypeAbstractTemplate::NAME;
        return <<<TEXT
class {$this->className} extends {$parentClassName}
{
    const CLASS_NAME = '{$this->className}';

{$this->generateAttributesBlock()}
{$this->generateGettersAndSettersBlock()}
}

TEXT;
    }

    protected function generateAttributesBlock()
    {
        $result = '';
        if (!empty($this->attributes)) {
            foreach ($this->attributes as $attribute) {
                $result .= <<<TEXT
    /**

TEXT;

                $result .= <<<TEXT
{$this->phpDocParser($attribute['documentation'], '     * ')}
     * minOccurs = {$attribute['minOccurs']}
     * maxOccurs = {$attribute['maxOccurs']}
     *

TEXT;
                $isArray = false;
                if ($attribute['maxOccurs'] > 1 || $attribute['maxOccurs'] == 'unbounded') {
                    $isArray = true;
                }
                if (in_array($attribute['type'], self::getDataTypes())) {
                    $type = self::convertDataType($attribute['type']);
                } else {
                    $tmpClass = ProxyGeneratorAbstract::getClass($attribute['type']);
                    if ($tmpClass != null && $this->namespace != $tmpClass['namespace']) {
                        $type = 'SimpleType\\' . $tmpClass['class'];
                    } else {
                        $type = $attribute['type'];
                    }
                }
                if ($isArray) {
                    $type .= '[]';
                }

                $defaultValue = '';
                if (isset($attribute['fixed'])) {
                    if ($attribute['type'] == 'string') {
                        $defaultValue = " = '" . $attribute['fixed'] . "'";
                    } else {
                        $defaultValue = ' = ' . $attribute['fixed'];
                    }
                }
                if ($attribute['minOccurs'] === 0) {
                    if (!$isArray) {
                        $type .= '|null';
                    } else {
                        $defaultValue = ' = array()';
                    }
                }
                $result .= <<<TEXT
     * @var {$type} \${$attribute['name']}
     */
    protected \${$attribute['name']}{$defaultValue};


TEXT;
            }
        }

        return $result;
    }

    protected function generateGettersAndSettersBlock()
    {
        $result = '';
        if (!empty($this->attributes)) {
            foreach ($this->attributes as $attribute) {
                $result .= <<<TEXT
    /**

TEXT;
                $isArray = false;
                $typeHint = null;
                if ($attribute['maxOccurs'] > 1 || $attribute['maxOccurs'] == 'unbounded') {
                    $isArray = true;
                }
                if (in_array($attribute['type'], self::getDataTypes())) {
                    $type = self::convertDataType($attribute['type']);
                } else {
                    $tmpClass = ProxyGeneratorAbstract::getClass($attribute['type']);
                    if ($tmpClass != null && $this->namespace != $tmpClass['namespace']) {
                        $type = 'SimpleType\\' . $tmpClass['class'];
                    } else {
                        $type = $attribute['type'];
                    }
                    $typeHint = $type . ' ';
                }
                if ($isArray) {
                    $type .= '[]';
                    $typeHint = 'array ';
                }

                $nullable = '';
                if (isset($attribute['minOccurs'])) {
                    if ($attribute['minOccurs'] === 0) {
                        if (!$isArray) {
                            $type .= '|null';
                            if ($typeHint !== null) {
                                $nullable = ' = null';
                            }
                        } else {
                            if ($typeHint !== null) {
                                $nullable = ' = array()';
                            }
                        }
                    }
                }

                $functionName = ucfirst($attribute['name']);
                $parameterName = lcfirst($attribute['name']);
                $result .= <<<TEXT
     * @param {$type} \${$parameterName}
     * @return {$this->className}
     */
    public function set{$functionName}({$typeHint}\${$parameterName}{$nullable})
    {
        \$this->{$attribute['name']} = \${$parameterName};

        return \$this;
    }

    /**
     * @return {$type}
     */
    public function get{$functionName}()
    {
        return \$this->{$attribute['name']};
    }


TEXT;
            }
        }

        return $result;
    }
}
