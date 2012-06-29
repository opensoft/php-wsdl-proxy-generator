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
abstract class TemplateAbstract
{
    protected $className;
    protected $classDocumentation;
    protected $namespace;
    protected $constants;
    protected $attributes;
    protected $license;
    protected $useBlock;

    public function generateCode()
    {
        return <<<TEXT
<?php
{$this->getLicense()}
{$this->getNamespace()}
{$this->getUseBlock()}
{$this->getClassDocumentation()}
{$this->getClassBody()}

TEXT;
    }

    public function __construct($namespace, $className = null, array $useBlock = array(), $classDocumentation = null, $license = null)
    {
        $this->namespace = $namespace;
        if ($className != null) {
            $this->className = $className;
        }
        if ($classDocumentation != null) {
            $this->classDocumentation = $classDocumentation;
        }
        if ($license != null) {
            $this->license = $license;
        }
        $this->useBlock = $useBlock;
    }

    public function __toString()
    {
        return $this->generateCode();
    }

    /**
     * @static
     * @return array
     */
    public static function getDataTypes()
    {
        return array(
            'duration', 'dateTime', 'time', 'date', 'gYearMonth', 'gYear', 'gMonthDay', 'gDay', 'gMonth', 'string',
            'boolean', 'base64Binary', 'hexBinary', 'float', 'decimal', 'double', 'anyURI', 'qName', 'NOTATION',
            'normalizedString', 'token', 'language', 'Name', 'NMTOKEN', 'NCName', 'NMTOKENS', 'ID', 'IDREF', 'ENTITY',
            'IDREFS', 'ENTITIES', 'integer', 'nonPositiveInteger', 'long', 'nonNegativeInteger', 'negativeInteger',
            'int', 'unsignedLong', 'positiveInteger', 'short', 'unsignedInt', 'byte', 'unsignedShort', 'unsignedByte');
    }

    public static function convertDataType($type)
    {
        switch ($type) {
            case 'boolean':
                return 'boolean';
            break;
            case 'float':
            case 'decimal':
            case 'double':
                return 'float';
            break;
            case 'int':
            case 'unsignedLong':
            case 'positiveInteger':
            case 'short':
            case 'unsignedInt':
            case 'byte':
            case 'unsignedShort':
            case 'unsignedByte':
            case 'negativeInteger':
            case 'nonNegativeInteger':
            case 'long':
            case 'nonPositiveInteger':
            case 'integer':
                return 'integer';
            break;
            default:
                return 'string';
            break;
        }
    }

    public function addConstant($constant)
    {
        $this->constants[] = $constant;
    }

    public function addAttribute($attribute)
    {
        $this->attributes[] = $attribute;
    }

    /**
     * @return string
     */
    protected function getClassDocumentation()
    {
        $classDocumentation = $this->phpDocParser($this->classDocumentation, ' * ');
        if ($classDocumentation != '') {
            $classDocumentation = "\n" . $classDocumentation;
        }
        return <<<TEXT
/**{$classDocumentation}
 *
 * @link https://github.com/opensoft/php-wsdl-proxy-generator WSDL parser and Proxy class generator on PHP
 * @author WSDL parser and Proxy class generator on PHP
 */
TEXT;
    }

    protected function phpDocParser($doc, $prefix = '     * ') {
        $code = '';
        if ($doc != null) {
            $words = preg_split('/ /', $doc);
            $line = $prefix;
            foreach ($words as $word) {
                $line .= $word . ' ';
                if (strlen($line) > 90) {
                    $code .= $line . "\n";
                    $line = $prefix;
                }
            }
            $code .= $line;
        }

        return $code;
    }

    protected function getLicense()
    {
        if ($this->license !== null) {
            return <<<TEXT
/**
{$this->phpDocParser($this->license, ' * ')}
 */

TEXT;
        }
    }

    protected function getNamespace()
    {
        return 'namespace ' . $this->namespace . ';';
    }

    protected function getUseBlock()
    {
        $result = '';
        if (!empty($this->useBlock)) {
            $result .= "\n";
            foreach ($this->useBlock as $block) {
                $result .= 'use ' . $block . ";\n";
            }
        }

        return $result;
    }

    protected function getClassBody()
    {
        return <<<TEXT
abstract class Test
{

}
TEXT;
    }
}
