<?php

/*
 * This file is part of the WSDL Proxy Generator package.
 *
 * Copyright (c) 2012 Farheap Solutions (http://www.farheap.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opensoft\WsdlProxyGenerator\Tests\Command;

use Opensoft\WsdlProxyGenerator\Command\Validators;

/**
 * @author Dmitry Petrov <dmitry.petrov@opensoftdev.ru>
 */
class ValidatorsTest extends \PHPUnit_Framework_TestCase
{
   public function testValidateNamespace()
   {
       $namespace = 'Test\\Name';
       $this->assertEquals($namespace, Validators::validateNamespace($namespace));
   }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testValidateNamespaceInvalidCharacters()
    {
        $namespace = 'Test\\Na#me';
        Validators::validateNamespace($namespace);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testValidateNamespaceKeywords()
    {
        $namespace = 'Test\\Echo';
        Validators::validateNamespace($namespace);
    }

    public function testValidateWsdl()
    {
        $wsdlPath = __DIR__ . '/../../../../data/test.wsdl';
        $this->assertEquals($wsdlPath, Validators::validateWsdl($wsdlPath));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testValidateWsdlNotValidPath()
    {
        $wsdlPath = __DIR__ . '/../../../../data/notExist.wsdl';
        Validators::validateWsdl($wsdlPath);
    }

    public function testValidateLicensePath()
    {
        $licensePath = __DIR__ . '/../../../../data/LICENSE';
        $this->assertEquals($licensePath, Validators::validateLicensePath($licensePath));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testValidateLicensePathFileNotExists()
    {
        $licensePath = __DIR__ . '/../../../../data/LICENSE_NOT_EXISTS';
        Validators::validateLicensePath($licensePath);
    }

    public function testValidateTargetDir()
    {
        $targetDir = __DIR__ . '/../../../../data/';
        $this->assertEquals($targetDir, Validators::validateTargetDir($targetDir));
    }

    public function testValidateTargetDirAddedSlash()
    {
        $targetDir = __DIR__ . '/../../../../data';
        $this->assertEquals($targetDir . '/', Validators::validateTargetDir($targetDir));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testValidateTargetDirEmptyDirArgument()
    {
        $targetDir = null;
        Validators::validateTargetDir($targetDir);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testValidateTargetDirNotWriteable()
    {
        $targetDir = '/';
        Validators::validateTargetDir($targetDir);
    }
}
