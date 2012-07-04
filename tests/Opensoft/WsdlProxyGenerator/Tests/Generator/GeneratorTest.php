<?php

/*
 * This file is part of the WSDL Proxy Generator package.
 *
 * Copyright (c) 2012 Farheap Solutions (http://www.farheap.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opensoft\WsdlProxyGenerator\Tests\Generator;

use Opensoft\WsdlProxyGenerator\Generator\ComplexTypeAbstractGenerator;
use Opensoft\WsdlProxyGenerator\Generator\ComplexTypeGenerator;
use Opensoft\WsdlProxyGenerator\Generator\SimpleTypeAbstractGenerator;
use Opensoft\WsdlProxyGenerator\Generator\SimpleTypeGenerator;
use Opensoft\WsdlProxyGenerator\Generator\SoapClientGenerator;
use Opensoft\WsdlProxyGenerator\Generator\SoapServiceGenerator;

/**
 * @author Dmitry Petrov <dmitry.petrov@opensoftdev.ru>
 */
class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    protected static $wsdlPath;
    protected static $exportPath;
    protected static $namespace;
    protected static $licensePath;

    /**
     * @static
     * {@inheritDoc}
     */
    public static function setUpBeforeClass()
    {
        self::$wsdlPath = __DIR__ . '/../../../../data/test.wsdl';
        self::$exportPath = __DIR__ . '/../../../../data/Opensoft/WsdlProxyGenerator/Generated';
        self::$namespace = 'Opensoft\\WsdlProxyGenerator\\Generated';
        self::$licensePath = __DIR__ . '/../../../../data/LICENSE';
    }

    public static function tearDownAfterClass()
    {
        system('rm -rf ' . self::$exportPath);
    }

    public function testSoapClientGenerator()
    {
        $generator = new SoapClientGenerator(
            self::$wsdlPath, self::$exportPath, self::$namespace,
            array('RuntimeException', 'SoapFault', 'SoapClient as BaseSoapClient'), self::$licensePath
        );
        $generator->execute();

        $reflection = new \ReflectionClass(self::$namespace . '\\SoapClient');
        $this->assertTrue($reflection->isInstantiable());
        $this->assertEquals('SoapClient', $reflection->getParentClass()->getName());
    }

    public function testSimpleTypeAbstractGenerator()
    {
        $generator = new SimpleTypeAbstractGenerator(
            self::$wsdlPath, self::$exportPath . '/SimpleType', self::$namespace . '\\SimpleType',
            array(), self::$licensePath
        );
        $generator->execute();

        $reflection = new \ReflectionClass(self::$namespace . '\\SimpleType\\SimpleTypeAbstract');
        $this->assertTrue($reflection->isAbstract());
        $this->assertPublicMethod($reflection, '__toString');
    }

    /**
     * @depends testSimpleTypeAbstractGenerator
     */
    public function testSimpleTypeGenerator()
    {
        $generator = new SimpleTypeGenerator(
            self::$wsdlPath, self::$exportPath . '/SimpleType', self::$namespace . '\\SimpleType',
            array(), self::$licensePath
        );
        $generator->execute();

        $reflection = new \ReflectionClass(self::$namespace . '\\SimpleType\\TestChangeType');
        $this->assertClass($reflection, self::$namespace . '\\SimpleType\\SimpleTypeAbstract');

        $constants = array(
            'APARTMENT_NUMBER_NOT_FOUND' => 'APARTMENT_NUMBER_NOT_FOUND',
            'APARTMENT_NUMBER_REQUIRED' => 'APARTMENT_NUMBER_REQUIRED',
            'NORMALIZED' => 'NORMALIZED'
        );
        $this->assertConstants($reflection, $constants);

        $reflection = new \ReflectionClass(self::$namespace . '\\SimpleType\\TestStatusType');
        $this->assertClass($reflection, self::$namespace . '\\SimpleType\\SimpleTypeAbstract');

        $constants = array(
            'UNDETERMINED' => 'UNDETERMINED',
            'BUSINESS' => 'BUSINESS',
            'RESIDENTIAL' => 'RESIDENTIAL',
            'INSUFFICIENT_DATA' => 'INSUFFICIENT_data',
            'UNAVAILABLE' => 'UNAVAILABLE'
        );
        $this->assertConstants($reflection, $constants);

    }

    /**
     * @depends testSimpleTypeAbstractGenerator
     */
    public function testComplexTypeAbstractGenerator()
    {
        $generator = new ComplexTypeAbstractGenerator(
            self::$wsdlPath, self::$exportPath . '/ComplexType', self::$namespace . '\\ComplexType',
            array(self::$namespace . '\\SimpleType'), self::$licensePath
        );
        $generator->execute();

        $reflection = new \ReflectionClass(self::$namespace . '\\ComplexType\\ComplexTypeAbstract');
        $this->assertTrue($reflection->isAbstract());
        $this->assertPublicMethod($reflection, 'toArray');
        $this->assertProtectedMethod($reflection, 'convertToArray');
        $this->assertPrivateMethod($reflection, 'getListOfProperties');
    }

    /**
     * @depends testComplexTypeAbstractGenerator
     */
    public function testComplexTypeGenerator()
    {
        $generator = new ComplexTypeGenerator(self::$wsdlPath, self::$exportPath . '/ComplexType',
            self::$namespace . '\\ComplexType', array(self::$namespace . '\\SimpleType'), self::$licensePath);
        $generator->execute();

        $this->exampleDetailTest();
        $this->arrayExampleTest();
        $this->detailTest();
        $this->replyTest();
        $this->requestTest();
    }

    /**
     * @depends testComplexTypeGenerator
     * @depends testSimpleTypeGenerator
     * @depends testSoapClientGenerator
     */
    public function testSoapServiceGenerator()
    {
        $generator = new SoapServiceGenerator(self::$wsdlPath, self::$exportPath, self::$namespace,
            array(
                'RuntimeException', 'SoapFault',
                'SoapClient as BaseSoapClient',
                'InvalidArgumentException'
            ), self::$licensePath);
        $generator->execute();
        $reflection = new \ReflectionClass(self::$namespace . '\\SoapService');
        $this->assertClass($reflection);
        $this->assertPublicMethod($reflection, 'Test');
        $this->assertPublicMethod($reflection, 'getSoapClient');
        $soapService = $reflection->newInstanceArgs(array(self::$namespace . '\\SoapClient', self::$wsdlPath));
        $this->assertInstanceOf(self::$namespace . '\\SoapService', $soapService);
    }

    protected function assertPublicMethod(\ReflectionClass $reflection, $name)
    {
        $this->assertTrue($reflection->hasMethod($name));
        $method = $reflection->getMethod($name);
        $this->assertTrue($method->isPublic());
    }

    protected function assertProtectedMethod(\ReflectionClass $reflection, $name)
    {
        $this->assertTrue($reflection->hasMethod($name));
        $method = $reflection->getMethod($name);
        $this->assertTrue($method->isProtected());
    }

    protected function assertPrivateMethod(\ReflectionClass $reflection, $name)
    {
        $this->assertTrue($reflection->hasMethod($name));
        $method = $reflection->getMethod($name);
        $this->assertTrue($method->isPrivate());
    }

    protected function assertConstants(\ReflectionClass $reflection, array $constants)
    {
        foreach ($constants as $key => $value) {
            $this->assertTrue($reflection->hasConstant($key));
            $this->assertEquals($value, $reflection->getConstant($key));
        }
        $reflectionConstants = $reflection->getConstants();
        $this->assertCount(count($constants), $reflectionConstants);
    }

    protected function assertClass(\ReflectionClass $reflection, $parentClassName = null)
    {
        $this->assertTrue($reflection->isInstantiable());
        if ($parentClassName != null) {
            $this->assertEquals($parentClassName, $reflection->getParentClass()->getName());
        }
    }

    /**
     * Tests ComplexType\\ExampleDetail class
     */
    private function exampleDetailTest()
    {
        $reflection = new \ReflectionClass(self::$namespace . '\\ComplexType\\ExampleDetail');
        $this->assertClass($reflection, self::$namespace . '\\ComplexType\\ComplexTypeAbstract');
        $reflection->hasConstant('CLASS_NAME');
        $reflectionTestStatusType = new \ReflectionClass(self::$namespace . '\\SimpleType\\TestStatusType');
        $testStatusType1 = $reflectionTestStatusType->newInstanceArgs(array('UNDETERMINED'));
        $testStatusType2 = $reflectionTestStatusType->newInstanceArgs(array('BUSINESS'));
        $exampleDetail = $reflection->newInstance();
        $exampleDetail->setName('Name')->setValue('Value')->setChanges(array($testStatusType1, $testStatusType2));
        $result = array('Name' => 'Name', 'Value' => 'Value', 'Changes' => array('UNDETERMINED', 'BUSINESS'));
        $this->assertEquals($result, $exampleDetail->toArray());
        $this->assertEquals(array('ExampleDetail' => $result), $exampleDetail->toArray(true));
        $exampleDetailFromArray = $reflection->newInstanceArgs(array(array('Name' => 'Name', 'Value' => 'Value', 'Changes' => array($testStatusType1, $testStatusType2))));
        $this->assertEquals($result, $exampleDetailFromArray->toArray());
        $this->assertEquals(array('ExampleDetail' => $result), $exampleDetailFromArray->toArray(true));
    }

    /**
     * Tests ComplexType\\TestArrayExample class
     */
    private function arrayExampleTest()
    {
        $reflection = new \ReflectionClass(self::$namespace . '\\ComplexType\\TestArrayExample');
        $this->assertClass($reflection, self::$namespace . '\\ComplexType\\ComplexTypeAbstract');
        $reflection->hasConstant('CLASS_NAME');
        $testArrayExample = $reflection->newInstance();
        $testArrayExample->setTitle('TitleTest');
        $result = array('Title' => 'TitleTest');
        $this->assertEquals($result, $testArrayExample->toArray());
        $this->assertEquals(array('TestArrayExample' => $result), $testArrayExample->toArray(true));
        $testArrayExampleFromArray = $reflection->newInstanceArgs(array($result));
        $this->assertEquals($result, $testArrayExampleFromArray->toArray());
        $this->assertEquals(array('TestArrayExample' => $result), $testArrayExampleFromArray->toArray(true));
    }

    /**
     * Tests ComplexType\\TestDetail class
     */
    private function detailTest()
    {
        $reflection = new \ReflectionClass(self::$namespace . '\\ComplexType\\TestDetail');
        $this->assertClass($reflection, self::$namespace . '\\ComplexType\\ComplexTypeAbstract');
        $reflection->hasConstant('CLASS_NAME');
        $testDetail = $reflection->newInstance();
        $testDetail->setMaximumNumberOfMatches(23)->setConvertToUpperCase(true);
        $result = array('MaximumNumberOfMatches' => 23, 'ConvertToUpperCase' => true);
        $this->assertEquals($result, $testDetail->toArray());
        $this->assertEquals(array('TestDetail' => $result), $testDetail->toArray(true));
        $testDetailFromArray = $reflection->newInstanceArgs(array($result));
        $this->assertEquals($result, $testDetailFromArray->toArray());
        $this->assertEquals(array('TestDetail' => $result), $testDetailFromArray->toArray(true));
    }

    /**
     * Tests ComplexType\\TestReply class
     */
    private function replyTest()
    {
        $reflection = new \ReflectionClass(self::$namespace . '\\ComplexType\\TestReply');
        $this->assertClass($reflection, self::$namespace . '\\ComplexType\\ComplexTypeAbstract');
        $reflection->hasConstant('CLASS_NAME');
        $testReply = $reflection->newInstance();

        $reflectionTestArrayExample = new \ReflectionClass(self::$namespace . '\\ComplexType\\TestArrayExample');
        $testArrayExample1 = $reflectionTestArrayExample->newInstanceArgs(array(array('Title' => 'Title1')));
        $testArrayExample2 = $reflectionTestArrayExample->newInstanceArgs(array(array('Title' => 'Title2')));

        $reflectionTestDetail = new \ReflectionClass(self::$namespace . '\\ComplexType\\TestDetail');
        $testDetail = $reflectionTestDetail->newInstanceArgs(array(array('MaximumNumberOfMatches' => 23, 'ConvertToUpperCase' => true)));

        $dateTime = new \DateTime('now');

        $testReply->setNotifications(array($testArrayExample1, $testArrayExample2))->setTestDetail($testDetail);
        $testReply->setReplyTimestamp($dateTime->format(\DateTime::W3C));
        $result = array(
            'Notifications' => array(array('Title' => 'Title1'), array('Title' => 'Title2')),
            'TestDetail' => array('MaximumNumberOfMatches' => 23, 'ConvertToUpperCase' => true),
            'ReplyTimestamp' => $dateTime->format(\DateTime::W3C)
        );
        $this->assertEquals($result, $testReply->toArray());
        $this->assertEquals(array('TestReply' => $result), $testReply->toArray(true));

        $instanceArray = array(
            'Notifications' => array($testArrayExample1, $testArrayExample2),
            'TestDetail' => $testDetail,
            'ReplyTimestamp' => $dateTime->format(\DateTime::W3C)
        );

        $testReplyFromArray = $reflection->newInstanceArgs(array($instanceArray));
        $this->assertEquals($result, $testReplyFromArray->toArray());
        $this->assertEquals(array('TestReply' => $result), $testReplyFromArray->toArray(true));
    }

    /**
     * Tests ComplexType\\TestRequest class
     */
    private function requestTest()
    {
        $reflection = new \ReflectionClass(self::$namespace . '\\ComplexType\\TestRequest');
        $this->assertClass($reflection, self::$namespace . '\\ComplexType\\ComplexTypeAbstract');
        $reflection->hasConstant('CLASS_NAME');
        $testRequest = $reflection->newInstance();

        $reflectionTestArrayExample = new \ReflectionClass(self::$namespace . '\\ComplexType\\TestArrayExample');
        $testArrayExample1 = $reflectionTestArrayExample->newInstanceArgs(array(array('Title' => 'Title1')));
        $testArrayExample2 = $reflectionTestArrayExample->newInstanceArgs(array(array('Title' => 'Title2')));

        $dateTime = new \DateTime('now');

        $reflectionTestDetail = new \ReflectionClass(self::$namespace . '\\ComplexType\\TestDetail');
        $testDetail = $reflectionTestDetail->newInstanceArgs(array(array('MaximumNumberOfMatches' => 23, 'ConvertToUpperCase' => true)));

        $reflectionTestStatusType = new \ReflectionClass(self::$namespace . '\\SimpleType\\TestStatusType');
        $testStatusType1 = $reflectionTestStatusType->newInstanceArgs(array('UNDETERMINED'));
        $testStatusType2 = $reflectionTestStatusType->newInstanceArgs(array('BUSINESS'));

        $reflectionExampleDetail = new \ReflectionClass(self::$namespace . '\\ComplexType\\ExampleDetail');
        $exampleDetail = $reflectionExampleDetail->newInstanceArgs(array(array('Name' => 'Name', 'Value' => 'Value', 'Changes' => array($testStatusType1, $testStatusType2))));

        $result = array(
            'TestDetail' => array('MaximumNumberOfMatches' => 23, 'ConvertToUpperCase' => true),
            'ExampleDetail' => array('Name' => 'Name', 'Value' => 'Value', 'Changes' => array('UNDETERMINED', 'BUSINESS')),
            'RequestTimestamp' => $dateTime->format(\DateTime::W3C),
            'TestString' => 'TestString',
            'TestArray' => array(array('Title' => 'Title1'), array('Title' => 'Title2'))
        );
        $testRequest->setTestDetail($testDetail)->setExampleDetail($exampleDetail)
            ->setRequestTimestamp($dateTime->format(\DateTime::W3C))
            ->setTestString('TestString')
            ->setTestArray(array($testArrayExample1, $testArrayExample2));

        $this->assertEquals($result, $testRequest->toArray());

        $instanceArray = array(
            'TestDetail' => $testDetail,
            'ExampleDetail' => $exampleDetail,
            'RequestTimestamp' => $dateTime->format(\DateTime::W3C),
            'TestString' => 'TestString',
            'TestArray' => array($testArrayExample1, $testArrayExample2)
        );

        $testRequestFromArray = $reflection->newInstanceArgs(array($instanceArray));
        $this->assertEquals($result, $testRequestFromArray->toArray());
        $this->assertEquals(array('TestRequest' => $result), $testRequestFromArray->toArray(true));
    }



}
