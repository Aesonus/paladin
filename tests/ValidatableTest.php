<?php
/*
 * This software is licensed under the MIT License. Please see LICENSE for more details.
 */

namespace Aesonus\Tests;

/**
 * Description of ValidatableTest
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class ValidatableTest extends \Aesonus\TestLib\BaseTestCase
{

    /**
     *
     * @var \PHPUnit_Framework_MockObject_MockObject 
     */
    protected $testObj;

    /**
     *
     * @var \PHPUnit_Framework_MockObject_MockBuilder 
     */
    protected $traitMockBuilder;

    protected function setUp()
    {
        $this->traitMockBuilder = $this->getMockBuilder(ValidatableTestHelper::class);
        $this->testObj = $this->traitMockBuilder->setMethods(['callValidator'])->getMock();
    }

    ////GET VALIDATOR MAPPING TESTS
    /**
     * @test
     */
    public function getValidatorMappingsReturnsDefaultOfIntegerAndBoolean()
    {
        $expected = [
            'integer' => 'int',
            'boolean' => 'bool'
        ];
        $actual = $this->testObj->getValidatorMappings();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider getValidatorMappingsReturnsPropertyIfItIsSetDataProvider
     */
    public function getValidatorMappingsReturnsPropertyIfItIsSet($expected)
    {
        $this->setPropertyValue($this->testObj, 'validatorMappings', $expected);
        $actual = $this->testObj->getValidatorMappings();
        $this->assertEquals($expected, $actual);
    }

    public function getValidatorMappingsReturnsPropertyIfItIsSetDataProvider()
    {
        return [
            [
                ['custom' => 'type']
            ],
            [
                [
                    'mulitple' => 'custom',
                    'types' => 'yay'
                ]
            ]
        ];
    }
    ////MAP TYPE TESTS

    /**
     * @test
     * @dataProvider mapTypeAppendsMappingToPropertyDefaultsDataProvider
     */
    public function mapTypeAppendsMappingToPropertyDefaults($alias, $type, $expected_custom)
    {

        $expected = array_merge(['integer' => 'int',
            'boolean' => 'bool'], $expected_custom);
        $this->testObj->mapType($alias, $type);
        $actual = $this->getPropertyValue($this->testObj, 'validatorMappings');
        $this->assertEquals($expected, $actual);
    }

    public function mapTypeAppendsMappingToPropertyDefaultsDataProvider()
    {
        return [
            ['rando', 'bool', ['rando' => 'bool']],
            ['different', 'float', ['different' => 'float']]
        ];
    }

    /**
     * @test
     * @dataProvider invalidParamsPassedToMapTypeThrowsExceptionDataProvider
     */
    public function invalidParamsPassedToMapTypeThrowsException
    ($alias, $type, $name_of_invalid_param, $datatype_of_invalid_param)
    {

        $this->expectException(\InvalidArgumentException::class);
        $messageRegex = "/$name_of_invalid_param.*string.*$datatype_of_invalid_param/";
        $this->expectExceptionMessageRegExp($messageRegex);
        $this->testObj->mapType($alias, $type);
    }

    public function invalidParamsPassedToMapTypeThrowsExceptionDataProvider()
    {
        return [
            [new \stdClass(), 'type', 'alias', 'object'],
            ['alias', 34, 'type', 'integer']
        ];
    }

    /**
     * @test
     */
    public function mapTypeThrowsExceptionIfParamTypeHasNoValidationMethod()
    {

        $bad_type = 'unknownType';
        $this->expectException(\Aesonus\Paladin\Exceptions\TypeNotDefinedException::class);
        $this->expectExceptionMessage($bad_type);
        $this->testObj->mapType('rando', $bad_type);
    }

    /**
     * @test
     */
    public function mapTypeReturnThisForFluency()
    {

        $actual = $this->testObj->mapType('test', 'int');
        $this->assertEquals($this->testObj, $actual);
    }
    ////V TESTS

    /**
     * @test
     */
    public function vCallsCallValidatorWithCorrectArguments()
    {
        $method_name = ValidatableTestHelper::class . "::testMethod";
        $args = ['stringarg', 34, true];
        $this->testObj->expects($this->exactly(3))
            ->method('callValidator')
            ->withConsecutive(
                [
                    $this->equalTo(['string']),
                    $this->equalTo('string'),
                    $this->equalTo($args[0])
                ], [
                    $this->equalTo(['int']),
                    $this->equalTo('int'),
                    $this->equalTo($args[1])
                ], [
                    $this->equalTo(['bool']),
                    $this->equalTo('bool'),
                    $this->equalTo($args[2])
                ]
        );
        $this->invokeMethod($this->testObj, 'v', [$method_name, $args]);
    }
    
    /**
     * @test
     */
    public function vThrowsExceptionOnInvalidParameter()
    {
        $this->expectException(\InvalidArgumentException::class);
        $messageRegex = "/method_name.*string.*null/i";
        $this->expectExceptionMessageRegExp($messageRegex);
        $this->invokeMethod($this->testObj, 'v', [null, []]);
    }
    
    /**
     * @test
     */
    public function vCallsCallValidatorWithCorrectDefaultArguments()
    {
        $method_name = ValidatableTestHelper::class . "::testMethodWithSomeDefaults";
        $args = ['stringarg'];
        $this->testObj->expects($this->exactly(3))
            ->method('callValidator')
            ->withConsecutive(
                [
                    $this->equalTo(['integer']),
                    $this->equalTo('no_default_int'),
                    $this->equalTo($args[0])
                ], [
                    $this->equalTo(['float']),
                    $this->equalTo('pi'),
                    $this->equalTo(3.141)
                ], [
                    $this->equalTo(['string']),
                    $this->equalTo('string'),
                    $this->equalTo('string')
                ]
        );
        $this->invokeMethod($this->testObj, 'v', [$method_name, $args]);
    }
    
    /**
     * @test
     */
    public function vCallsCallValidatorWithCorrectArgumentsWithDefaultsReplaced()
    {
        $method_name = ValidatableTestHelper::class . "::testMethodWithSomeDefaults";
        $args = ['stringarg', 34.4];
        $this->testObj->expects($this->exactly(3))
            ->method('callValidator')
            ->withConsecutive(
                [
                    $this->equalTo(['integer']),
                    $this->equalTo('no_default_int'),
                    $this->equalTo($args[0])
                ], [
                    $this->equalTo(['float']),
                    $this->equalTo('pi'),
                    $this->equalTo(34.4)
                ], [
                    $this->equalTo(['string']),
                    $this->equalTo('string'),
                    $this->equalTo('string')
                ]
        );
        $this->invokeMethod($this->testObj, 'v', [$method_name, $args]);
    }
    
    /**
     * @test
     */
    public function addCustomParameterTypeReturnsThisForFluency()
    {
        $this->assertEquals($this->testObj, $this->testObj->addCustomParameterType(''));
    }
}
