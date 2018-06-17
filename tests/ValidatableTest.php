<?php
/*
 * This software is licensed under the MIT License. Please see LICENSE for more details.
 */

namespace Aesonus\Tests;

use \Aesonus\Paladin\Traits\Validatable;

/**
 * Description of ValidatableTest
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class ValidatableTest extends \PHPUnit\Framework\TestCase
{

    protected $testObj;
    protected $testObjReflection;
    protected $mockReflectionMethod;
    protected $objName = 'TestObjia9O8W375YTDFSKGH';
    protected $methodName = 'testMethod';
    protected $methodForV;

    protected function setUp()
    {
        $this->methodForV = $this->objName . "::" . $this->methodName;

        $this->testObj = $this->getMockForTrait(Validatable::class, [], $this->objName, true, true, true, [$this->methodName, 'getReflector', 'validateCustom', 'getParamDefaults']);

        $this->mockReflectionMethod = $this->getMockBuilder(\ReflectionMethod::class)
                ->setMethods(['getDocComment', 'getParameters'])
                ->disableOriginalConstructor()->getMock();
    }

    private function getMockReflectionParameters($param_names)
    {
        foreach ($param_names as $i => $paramname) {
            $return[$i] = $this->getMockBuilder(\ReflectionParameter::class)
                ->disableOriginalConstructor()
                ->setMethods(['getName'])
                ->getMock();
            $return[$i]->expects($this->any())->method('getName')->willReturn($paramname);
        }
        return $return;
    }

    private function expectMockMethod()
    {
        $this->testObj->expects($this->any())->method('getParamDefaults')->willReturn([]);
        $this->testObj->expects($this->once())->method($this->methodName)->will($this->returnCallback(function () {
                $this->invokeMethod($this->testObj, 'v', [$this->methodForV, func_get_args()]);
            }));
    }

    private function expectMockReflectionMethods($docblock, $param_names)
    {
        $this->mockReflectionMethod->expects($this->any())->method('getDocComment')->willReturn($docblock);
        $this->mockReflectionMethod->expects($this->any())->method('getParameters')->willReturn($this->getMockReflectionParameters($param_names));
        $this->testObj->expects($this->any())->method('getReflector')->willReturn($this->mockReflectionMethod);
    }
    
    /**
     * @dataProvider validArgumentsDataProvider
     **/
    public function testValidArguments($docblock, $param_names, $given)
    {
        // We need to control the doc blocks and parameter names that are put into the system
        $this->expectMockReflectionMethods($docblock, $param_names);
        $this->expectMockMethod();
        call_user_func_array([$this->testObj, $this->methodName], $given);
        $this->assertTrue(true);
    }

    public function validArgumentsDataProvider()
    {

        $data = function ($givenNames, $givenTypes, $givenArgs) {
            if ($givenTypes === null) {
                $docs = false;
            } else {
                $docs = "\t/**\n"
                    . "\t  * \n";
                foreach ($givenTypes as $i => $type) {
                    $name = $givenNames[$i];
                    $docs .= "\t * @param  $type \$$name\n";
                }    
                $docs .="\t\t */";
            }
            return [$docs, $givenNames, $givenArgs];
        };
        return [
            'string' => $data(['param'], ["integer|string"], ["FU"]),
            'string,int' => $data(['param','intparam'], ['string','null|bool'], ["FU",TRUE]),
            'none' => $data(['param'],null,['avalue']),
            'scalar,int,array,mixed' => $data(
                ['param','nullparam','arrayparam', 'mixedparam'], 
                ['scalar','null|int', 'string|int|array', 'mixed'], 
                ["FU", null, [4, 5, 3], new \stdClass()]
            ),
            'object' => $data(['param'] , ['object'], [new \stdClass()]),
            'callable' => $data(['param'], ['string|callable'], ['count']),
            'callable2' => $data(['param'], ['string|callable'], [[$this, 'testCustomTypes']])
        ];
    }
    
    /**
     * @test
     */
    public function methodWithDefaultParametersDefinedValidateWithNoErrors()
    {
        $testClass = new ValidatableTestHelper();
        $this->assertTrue($testClass->testMethodSingleTypeParam());
        return $testClass;
    }
    
    /**
     * @test
     */
    public function methodWithDefaultParameterDefinedSetsProperValueIfParameterIsGiven()
    {
        $testClass = new ValidatableTestHelper();
        $this->assertTrue($testClass->testMethodMulitpleArgsSingleTypeParam(23));
    }
    
    /**
     * @test
     * @depends methodWithDefaultParametersDefinedValidateWithNoErrors
     */
    public function methodWithTwoParametersWithOneDefaultedValidatesOk(ValidatableTestHelper $testClass)
    {
        $this->assertTrue($testClass->testMethodMulitpleArgsSingleTypeParam(3));
    }
    
    /**
     * @test
     * @depends methodWithDefaultParametersDefinedValidateWithNoErrors
     */
    public function methodWithTwoParametersWithLastParameterSetValidates(ValidatableTestHelper $testClass)
    {
        $this->assertTrue($testClass->testMethodMulitpleArgsSingleTypeParam(null, 3));
    }
    
    /**
     * @dataProvider customTypesDataProvider
     **/
    public function testCustomTypes($docblock, $param_names, $given)
    {
        // We need to control the doc blocks and parameter names that are put into the system
        $this->expectMockReflectionMethods($docblock, $param_names);
        $this->expectMockMethod();
        $this->testObj->addCustomParameterType('Custom');
        $this->assertEquals(['Custom'], $this->getPropertyValue($this->testObj, 'customTypes'));
        $this->testObj->expects($this->once())->method('validateCustom')->willReturn(TRUE);
        call_user_func_array([$this->testObj, $this->methodName], $given);
        $this->assertTrue(true);
    }

    public function customTypesDataProvider()
    {

        $data = function ($givenNames, $givenTypes, $givenArgs) {
            if ($givenTypes === null) {
                $docs = false;
            } else {
                $docs = "\t/**\n"
                    . "\t  * \n";
                foreach ($givenTypes as $i => $type) {
                    $name = $givenNames[$i];
                    $docs .= "\t * @param  $type \$$name\n";
                }    
                $docs .="\t\t */";
            }
            return [$docs, $givenNames, $givenArgs];
        };
        return [
            'custom' => $data(['param'], ['Custom'], ["FU"]),
            'string,int|custom' => $data(['param','intcustomparam'], ['string','int|Custom'], ["FU",TRUE]),
        ];
    }
    
    /**
     * @dataProvider invalidArgumentsOnMethodsInTraitDataProvider
     */
    public function testInvalidArgumentsOnMethodsInTrait($callback, $params)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->invokeMethod($this->testObj, $callback, $params);
    }
    
    public function invalidArgumentsOnMethodsInTraitDataProvider()
    {
        return [
            ['addCustomParameterType', [3]],
            ['mapType', [54,43]],
            ['v', [34, []]],
        ];
    }

    /**
     * @dataProvider invalidArgumentsDataProvider
     */
    public function testInvalidArguments($docblock, $param_names, $given)
    {
        // We need to control the doc blocks and parameter names that are put into the system
        $this->expectMockReflectionMethods($docblock, $param_names);
        $this->expectMockMethod();

        $this->expectException(\InvalidArgumentException::class);
        $this->testObj->testMethod($given);
    }

    public function invalidArgumentsDataProvider()
    {
        $docblock = function ($type) {
            return "\t/**\n"
                . "\t * \n"
                . "\t * @param  $type \$param\n"
                . "\t */";
        };
        $param_names = ['param'];
        return [
            'string' => [$docblock('boolean'), $param_names, "FU"],
            'array' => [$docblock('integer|string'), $param_names, ['nope']],
            'float' => [$docblock('int|string|array'), $param_names, 1.2],
            'null' => [$docblock('string|array'), $param_names, NULL],
            'object' => [$docblock('array|float|null|string'), $param_names, new \stdClass()],
        ];
    }

    /**
     * 
     * @dataProvider malformedDocBlockDataProvider
     */
    public function testMalformedDocBlock($docblock, $param_names, $given)
    {
        $this->expectException(\Aesonus\Paladin\Exceptions\DocBlockSyntaxException::class);
        $this->expectExceptionMessage($docblock);
        // We need to control the doc blocks and parameter names that are put into the system
        $this->expectMockReflectionMethods($docblock, $param_names);
        $this->expectMockMethod();
        $this->testObj->testMethod($given);
    }

    public function malformedDocBlockDataProvider()
    {
        $docblock = function ($type) {
            return "\t/**\n"
                . "\t * \n"
                . "\t * @param  $type \$param\n"
                . "\t */";
        };
        $param_names = ['param'];
        return [
            'string' => [$docblock('int| string'), $param_names, "FU"],
            'array' => [$docblock('int|string| array'), $param_names, ['nope']],
            'float' => [$docblock('int| string|array'), $param_names, 1.2],
            'null' => [$docblock('string|array |null| float'), $param_names, NULL],
            'object' => [$docblock('array |float'), $param_names, new \stdClass()],
        ];
    }

    public function testGetReflector()
    {
        $mock = $this->getMockForTrait(Validatable::class);
        $reflector = $this->invokeMethod($mock, 'getReflector', [$this->objName . '::addCustomParameterType']);
        $this->assertInstanceOf(\ReflectionMethod::class, $reflector
        );
        $this->assertSame($reflector, $this->invokeMethod($mock, 'getReflector')
        );
    }
    
    /**
     * @dataProvider testMapTypeDataProvider
     **/
    public function testMapType($type, $mapTo, $expectedValidatorMappings)
    {
        $this->assertInstanceOf(get_class($this->testObj),
            $this->testObj->mapType($type, $mapTo));
        $this->assertEquals($expectedValidatorMappings,
               $this->getPropertyValue($this->testObj,
               'validatorMappings')
            );
    }

    public function testMapTypeDataProvider()
    {
        $test_data = [];
        
        $testarr = ['testMap', 'testMapTo',['testMap' => 'testMapTo',
                'integer' => 'int',
                'boolean' => 'bool'
            ]];
        $test_data[] = $testarr;
        
        return $test_data;
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
    
    /**
     * Call protected and private properties of an object
     * @param \StdClass $object
     * @param string $propertyName
     * @return mixed
     */
    public function getPropertyValue(&$object, $propertyName)
    {
        $reflection = new \ReflectionProperty(get_class($object), $propertyName);
        $reflection->setAccessible(true);
        return $reflection->getValue($object);
    }
}
