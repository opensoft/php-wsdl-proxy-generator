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

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use DOMNodeList;
use Opensoft\WsdlProxyGenerator\Command\Validators;

/**
 * @author Dmitry Petrov <dmitry.petrov@opensoftdev.ru>
 */
abstract class ProxyGeneratorAbstract
{
    /**
     * @var DOMDocument
     */
    protected static $xml = null;
    protected static $classMaps = array();
    protected static $license = null;
    protected $wsdlPath;
    protected $exportPath;
    protected $namespace;
    protected $useBlock;

    public function __construct($wsdlPath, $exportPath, $namespace, array $useBlock = array(), $licensePath = null)
    {
        $this->wsdlPath = $wsdlPath;
        $this->exportPath = $exportPath;
        $this->namespace = $namespace;
        if (self::$xml === null) {
            $fileContents = file_get_contents($this->wsdlPath);
            self::$xml = new DOMDocument();
            self::$xml->loadXML($fileContents);
            unset($fileContents);
        }
        if ($licensePath != null && self::$license === null) {
            self::$license = file_get_contents($licensePath);
        }
        $this->useBlock = $useBlock;
    }

    abstract public function execute();

    /**
     * @static
     * @param $key
     * @return array|null
     */
    public static function getClass($key)
    {
        if (self::hasClass($key)) {
            return self::$classMaps[$key];
        }

        return null;
    }

    /**
     * @static
     * @param $key
     * @return bool
     */
    public static function hasClass($key)
    {
        return isset(self::$classMaps[$key]);
    }

    /**
     * @param $path
     * @param $fileName
     * @param $code
     * @return int
     */
    protected function saveCode($path, $fileName, $code)
    {
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        $path = Validators::validateTargetDir($path);

        return file_put_contents($path . $fileName . '.php', $code);
    }

    /**
     * @static
     * @param string $key
     * @param array $class
     */
    protected static function addClass($key, $class)
    {
        if ($key != null && $class != null && !self::hasClass($key)) {
            self::$classMaps[$key] = $class;
        }
    }

    /**
     * Parse node element and returns documentations if exists
     *
     * @param DOMElement $element
     * @return string
     */
    protected function parseElementDocumentation(DOMElement $element)
    {
        $result = '';
        $documentations = $element->getElementsByTagName('documentation');
        if ($documentations->length) {
            foreach ($documentations as $documentation) {
                if ($element->isSameNode($documentation->parentNode->parentNode)) {
                    $result .= $this->getDOMNodeValue($documentation);
                }
            }
        }
        unset($documentations);

        return $result;
    }

    /**
     * Parse enumeration block and return values with documentations
     *
     * @param DOMElement $element
     * @return array
     */
    protected function parseEnumeration(DOMElement $element)
    {
        $result = array();
        $enumerations = $element->getElementsByTagName('enumeration');
        if ($enumerations->length) {
            foreach($enumerations as $enum) {
                $value = $enum->getAttribute('value');
                $documentation = $this->parseElementDocumentation($enum);
                $result[] = array('enumeration' => $value, 'documentation' => $documentation);
            }
            unset($value, $documentation);
        }
        unset($enumerations);

        return $result;
    }

    /**
     * @param DOMElement $element
     * @return array
     */
    protected function parseSequence(DOMElement $element)
    {
        $result = array();
        $attributes = $element->getElementsByTagName('element');
        foreach ($attributes as $attribute) {
            $property['name'] = $attribute->getAttribute('name');

            $property['type'] = $attribute->getAttribute('type');
            if(strpos($property['type'], ':')) { // keep the last part
                list($tmp, $property['type']) = explode(':', $property['type']);
            }
            //todo refactoring
            if ($property['type'] == '') {
                $property['type'] = 'string';
            }

            if ($attribute->hasAttribute('fixed')) {
                $property['fixed'] = $attribute->getAttribute('fixed');
            }

            $property['documentation'] = $this->parseElementDocumentation($attribute);

            if ($attribute->hasAttribute('maxOccurs')) {
                $maxOccurs = $attribute->getAttribute('maxOccurs');
                if ($maxOccurs != 'unbounded') {
                    $property['maxOccurs'] = (int) $maxOccurs;
                } else {
                    $property['maxOccurs'] = $maxOccurs;
                }
            } else {
                $property['maxOccurs'] = 1;
            }

            if ($attribute->hasAttribute('minOccurs')) {
                $property['minOccurs'] = (int) $attribute->getAttribute('minOccurs');
            } else {
                $property['minOccurs'] = 1;
            }

            $result[] = $property;
        }

        return $result;
    }

    /**
     * Returns text value of the node
     *
     * @param DOMNode $node
     * @return string
     */
    protected function getDOMNodeValue(DOMNode $node)
    {
        return trim($node->textContent);
    }

}
