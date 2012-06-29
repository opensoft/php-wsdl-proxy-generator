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

use Opensoft\WsdlProxyGenerator\Template\ComplexTypeTemplate;
use DOMElement;

/**
 * @author Dmitry Petrov <dmitry.petrov@opensoftdev.ru>
 */
class ComplexTypeGenerator extends ProxyGeneratorAbstract
{
    public function execute()
    {
        $complexTypes = self::$xml->getElementsByTagName('complexType');
        /**
         * @var DOMElement $complexType
         */
        foreach ($complexTypes as $complexType) {
            $documentation = $this->parseElementDocumentation($complexType);
            $className = $complexType->getAttribute('name');
            // IMPORTANT: Need to filter out namespace on member if presented
            if(strpos($className, ':')) { // keep the last part
                list($tmp, $className) = explode(':', $className);
            }
            unset($tmp);
            $template = new ComplexTypeTemplate($this->namespace, $className, $this->useBlock, $documentation, self::$license);
            $attributes = $this->parseSequence($complexType);
            if (!empty($attributes)) {
                foreach($attributes as $attribute) {
                    $template->addAttribute($attribute);
                }
            }

            self::addClass($className, array('class' => $className, 'namespace' => $this->namespace));

            $this->saveCode($this->exportPath, $className, $template->generateCode());
        }
    }
}
