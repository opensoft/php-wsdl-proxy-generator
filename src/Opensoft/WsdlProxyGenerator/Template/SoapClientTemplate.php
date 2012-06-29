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
class SoapClientTemplate extends TemplateAbstract
{
    const NAME = 'SoapClient';

    protected function getClassBody()
    {
        return <<<TEXT
class {$this->className} extends BaseSoapClient
{
    public function __construct(\$wsdl, \$options = array('exceptions' => 1))
    {
        \$xdebugIsDisabled = false;
        try {
            if (function_exists('xdebug_is_enabled') && xdebug_is_enabled()) {
                xdebug_disable();
                \$xdebugIsDisabled = true;
            }
            if (!isset(\$options['exceptions'])) {
                \$options['exceptions'] = 1;
            }
            parent::__construct(\$wsdl, \$options);
            if (\$xdebugIsDisabled) {
                xdebug_enable();
            }
        } catch (SoapFault \$e) {
            if (\$xdebugIsDisabled) {
                xdebug_enable();
            }

            throw new RuntimeException(sprintf('Failed initialize SoapClient. Error: "%s"', \$e->getMessage()));
        }
    }
}

TEXT;
    }

}
