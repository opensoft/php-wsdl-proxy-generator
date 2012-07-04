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

use Opensoft\WsdlProxyGenerator\Template\SimpleTypeTemplate;
use DOMElement;

/**
 * @author Dmitry Petrov <dmitry.petrov@opensoftdev.ru>
 */
class SimpleTypeGenerator extends ProxyGeneratorAbstract
{
    public function execute()
    {
        $simpleTypes = self::$xml->getElementsByTagName('simpleType');
        /**
         * @var DOMElement $simpleType
         */
        foreach ($simpleTypes as $simpleType) {
            $documentation = $this->parseElementDocumentation($simpleType);
            if ($simpleType->hasAttribute('name')) {
                $className = $simpleType->getAttribute('name');
                // IMPORTANT: Need to filter out namespace on member if presented
                if(strpos($className, ':')) { // keep the last part
                    list($tmp, $className) = explode(':', $className);
                }
                unset($tmp);
                $template = new SimpleTypeTemplate($this->namespace, $className, $this->useBlock, $documentation, self::$license);
                $enumerations = $this->parseEnumeration($simpleType);
                if (!empty($enumerations)) {
                    foreach($enumerations as $enum) {
                        $template->addConstant(array('value' => $enum['enumeration'], 'documentation' => $enum['documentation']));
                    }
                }
                self::addClass($className, array('class' => $className, 'namespace' => $this->namespace));

                $this->saveCode($this->exportPath, $className, $template->generateCode());
            }
        }
    }
}
