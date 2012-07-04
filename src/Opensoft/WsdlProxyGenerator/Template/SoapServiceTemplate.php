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
class SoapServiceTemplate extends TemplateAbstract
{
    /**
     * @var array
     */
    private $classMaps = array();

    /**
     * @var array
     */
    private $functions = array();

    /**
     * @param array $classMaps
     * @return SoapServiceTemplate
     */
    public function setClassMaps(array $classMaps)
    {
        $this->classMaps = $classMaps;

        return $this;
    }

    /**
     * @return array
     */
    public function getClassMaps()
    {
        return $this->classMaps;
    }

    /**
     * @param array $functions
     * @return SoapServiceTemplate
     */
    public function setFunctions(array $functions)
    {
        $this->functions = $functions;

        return $this;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return $this->functions;
    }

    protected function getClassBody()
    {
        return <<<TEXT
class {$this->className}
{
    /**
     * @var BaseSoapClient
     */
    protected \$soapClient;

{$this->renderClassMaps()}

    public function __construct(\$soapClientClassName, \$wsdl, array \$options = array())
    {
        if (!empty(self::\$classMaps)) {
            foreach(self::\$classMaps as \$key => \$value) {
                if(!isset(\$options['classmap'][\$key])) {
                    \$options['classmap'][\$key] = \$value;
                }
            }
        }
        \$this->soapClient = new \$soapClientClassName(\$wsdl, \$options);
        if (!\$this->soapClient instanceof BaseSoapClient) {
            throw new InvalidArgumentException(sprintf('%s class must implement SoapClient', \$soapClientClassName));
        }
    }

    /**
     * Returns the SoapClient instance
     *
     * @return BaseSoapClient
     */
    public function getSoapClient()
    {
        return \$this->soapClient;
    }

{$this->renderFunctions()}
}

TEXT;
    }

    /**
     * Returns formatted string
     *
     * @return string
     */
    private function renderClassMaps()
    {
        $result = <<<TEXT
    /**
     * @var array
     */

TEXT;
        if (empty($this->classMaps)) {
            $result .= <<<TEXT
    private static \$classMaps = array();


TEXT;

        } else {
            $result .= <<<TEXT
    private static \$classMaps = array(

TEXT;
            foreach ($this->classMaps as $map) {
                $result .= <<<TEXT
        '{$map['class']}' => '{$map['namespace']}\\{$map['class']}',

TEXT;
            }
            $result .= <<<TEXT
    );

TEXT;
        }

        return $result;
    }

    private function renderFunctions()
    {
        $result = '';
        foreach ($this->functions as $function) {
            $result .= <<<TEXT
    /**

TEXT;
            $signature = array();
            $signatureWithoutType = array();
            if(count($function['params']) > 0) {
                foreach($function['params'] as $param) {
                    $type = '';
                    $parameterName = $param[0];
                    if (count($param) > 1) {
                        $parameterName = $param[1];
                        if (in_array($param[0], self::getDataTypes())) {
                            $type = self::convertDataType($param[0]);
                        } else {
                            $type = $param[0];
                        }
                    }
                    $result .= '     * @param ' . $type . ' ' . $parameterName . "\n";
                    $signature[] = $type . ' ' . $parameterName;
                    $signatureWithoutType[] = $parameterName;
                }
            }
            if (in_array($function['return'], self::getDataTypes())) {
                $function['return'] = self::convertDataType($function['return']);
            }
            $parameters = implode(', ', $signature);
            $soapParameters = '';
            if (count($signatureWithoutType) > 1) {
                $soapParameters = 'array(' . implode(', ', $signatureWithoutType) . ')';
            } elseif (count($signatureWithoutType) == 1) {
                if (!in_array($signatureWithoutType[0], self::getDataTypes())) {
                    $soapParameters = $signatureWithoutType[0] . '->toArray()';
                }
            }
            $result .= <<<TEXT
     * @return {$function['return']}
     */
    public function {$function['name']}({$parameters})
    {
        return \$this->getSoapClient()->{$function['name']}({$soapParameters});
    }


TEXT;

        }

        return $result;
    }

}
