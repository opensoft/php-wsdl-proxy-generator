<?php

/*
 * This file is part of the WSDL Proxy Generator package.
 *
 * Copyright (c) 2012 Farheap Solutions (http://www.farheap.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opensoft\WsdlProxyGenerator\Command;

use Opensoft\WsdlProxyGenerator\SoapClient;
use SoapFault;
use InvalidArgumentException;
use RuntimeException;
use LogicException;

/**
 * Validator functions.
 *
 * @author Dmitry Petrov <dmitry.petrov@opensoftdev.ru>
 */
class Validators
{
    private static $reservedWords = array(
        'abstract',
        'and',
        'array',
        'as',
        'break',
        'case',
        'catch',
        'class',
        'clone',
        'const',
        'continue',
        'declare',
        'default',
        'do',
        'else',
        'elseif',
        'enddeclare',
        'endfor',
        'endforeach',
        'endif',
        'endswitch',
        'endwhile',
        'extends',
        'final',
        'for',
        'foreach',
        'function',
        'global',
        'goto',
        'if',
        'implements',
        'interface',
        'instanceof',
        'namespace',
        'new',
        'or',
        'private',
        'protected',
        'public',
        'static',
        'switch',
        'throw',
        'try',
        'use',
        'var',
        'while',
        'xor',
        '__CLASS__',
        '__DIR__',
        '__FILE__',
        '__LINE__',
        '__FUNCTION__',
        '__METHOD__',
        '__NAMESPACE__',
        'die',
        'echo',
        'empty',
        'exit',
        'eval',
        'include',
        'include_once',
        'isset',
        'list',
        'require',
        'require_once',
        'return',
        'print',
        'unset',
    );

    /**
     * Validates namespace
     *
     * @static
     * @param $namespace
     * @return string
     * @throws InvalidArgumentException
     */
    public static function validateNamespace($namespace)
    {
        $namespace = strtr($namespace, '/', '\\');
        if (!preg_match('/^(?:[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\\\?)+$/', $namespace)) {
            throw new InvalidArgumentException('The namespace contains invalid characters.');
        }

        $reservedWords = self::getReservedWords();
        foreach (explode('\\', $namespace) as $word) {
            if (in_array(strtolower($word), $reservedWords)) {
                throw new InvalidArgumentException(sprintf('The namespace cannot contain PHP reserved words ("%s").', $word));
            }
        }

        return $namespace;
    }

    /**
     * Validates wsdl file
     *
     * @static
     * @param $wsdl
     * @return string
     * @throws RuntimeException
     */
    public static function validateWsdl($wsdl)
    {
        $soapClient = @new SoapClient($wsdl, array('cache_wsdl' => WSDL_CACHE_NONE));
        unset($soapClient);

        return $wsdl;
    }

    /**
     * Validates target dirs
     *
     * @static
     * @param $dir
     * @return string
     */
    public static function validateTargetDir($dir)
    {
        if ($dir == null) {
            throw new InvalidArgumentException('The directory cannot be empty.');
        }

        if (!is_writable($dir)) {
            throw new InvalidArgumentException(sprintf('Cannot write to the directory: "%s"', $dir));
        }

        return DIRECTORY_SEPARATOR === substr($dir, -1, 1) ? $dir : $dir . DIRECTORY_SEPARATOR;
    }

    /**
     * Validates License path
     *
     * @static
     * @param string $path
     * @return bool
     */
    public static function validateLicensePath($path)
    {
        if ($path != null) {
            if (!file_exists($path)) {
                throw new InvalidArgumentException(sprintf('File does not exist: "%s"', $path));
            }
            return $path;
        }

        return null;
    }


    /**
     * Returns reserved words
     *
     * @static
     * @return array
     */
    public static function getReservedWords()
    {
        return self::$reservedWords;
    }
}
