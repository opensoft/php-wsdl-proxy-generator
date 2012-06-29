<?php

/*
 * This file is part of the WSDL Proxy Generator package.
 *
 * Copyright (c) 2012 Farheap Solutions (http://www.farheap.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author Dmitry Petrov <dmitry.petrov@opensoftdev.ru>
 */
spl_autoload_register(function($class)
{
   if (0 === strpos($class, 'Opensoft\\WsdlProxyGenerator\\Tests')) {
       $path = __DIR__.'/../tests/'.strtr($class, '\\', '/').'.php';
       if (file_exists($path) && is_readable($path)) {
           require_once $path;

           return true;
       }
   } else if (0 === strpos($class, 'Opensoft\\WsdlProxyGenerator\\')) {
       $path = __DIR__.'/../src/'.($class = strtr($class, '\\', '/')).'.php';
       if (file_exists($path) && is_readable($path)) {
           require_once $path;

           return true;
       }
   }
});

if (file_exists($loader = __DIR__.'/../vendor/autoload.php')) {
    require_once $loader;
}

