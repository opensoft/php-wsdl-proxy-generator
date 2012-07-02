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

use Opensoft\WsdlProxyGenerator\Template\SoapServiceTemplate;
use Opensoft\WsdlProxyGenerator\SoapClient;

/**
 * @author Dmitry Petrov <dmitry.petrov@opensoftdev.ru>
 */
class SoapServiceGenerator extends ProxyGeneratorAbstract
{
    public function execute()
    {
        $soapClient = @new SoapClient($this->wsdlPath, array('cache_wsdl' => WSDL_CACHE_NONE));
        $operations = $soapClient->__getFunctions();
        $functions = array();
        foreach($operations as $operation) {
            $matches = array();
            $return = '';
            $call = '';
            $params = '';
            if(preg_match('/^(\w[\w\d_]*) (\w[\w\d_]*)\(([\w\$\d,_ ]*)\)$/', $operation, $matches)) {
                $return = $matches[1];
                $call = $matches[2];
                $params = $matches[3];
            } else if(preg_match('/^(list\([\w\$\d,_ ]*\)) (\w[\w\d_]*)\(([\w\$\d,_ ]*)\)$/', $operation, $matches)) {
                $return = $matches[1];
                $call = $matches[2];
                $params = $matches[3];
            }

            $tmpClass = ProxyGeneratorAbstract::getClass($return);
            if ($tmpClass != null) {
                $fullClassName = $tmpClass['namespace'] . '\\' . $tmpClass['class'];
                if (!in_array($fullClassName, $this->useBlock)) {
                    $this->useBlock[] = $fullClassName;
                }
            }

            $params = explode(', ', $params);
            $paramsArr = array();
            foreach($params as $param) {
                $tmp = explode(' ', $param);
                if (count($tmp) > 1) {
                    $tmpClass = ProxyGeneratorAbstract::getClass($tmp[0]);
                    if ($tmpClass != null) {
                        $fullClassName = $tmpClass['namespace'] . '\\' . $tmpClass['class'];
                        if (!in_array($fullClassName, $this->useBlock)) {
                            $this->useBlock[] = $fullClassName;
                        }
                    }
                }
                $paramsArr[] = $tmp;
            }
            $functions[$call] = array('name' => $call, 'return' => $return, 'params' => $paramsArr);
        }


        $soapServiceName = 'SoapService';
        $template = new SoapServiceTemplate($this->namespace, $soapServiceName, $this->useBlock,
            'Soap Service', self::$license);
        $template->setClassMaps(self::$classMaps);
        $template->setFunctions($functions);
        $this->saveCode($this->exportPath, $soapServiceName, $template->generateCode());
    }
}
