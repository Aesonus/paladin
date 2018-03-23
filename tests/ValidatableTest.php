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

        $this->testObj = $this->getMockForTrait(Validatable::class, [], $this->objName, true, true, true, [$this->methodName, 'getReflector']);

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
            'string' => $data(['param'], ['int|string'], ["FU"]),
            'string,int' => $data(['param','intparam'], ['string','null|int'], ["FU",4]),
            'none' => $data(['param'],null,['avalue']),
            'string,int,array,mixed' => $data(
                ['param','nullparam','arrayparam', 'mixedparam'], 
                ['string','null|int', 'string|int|array', 'mixed'], 
                ["FU", null, [4, 5, 3], new \stdClass()]
            ),
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
            'string' => [$docblock('int'), $param_names, "FU"],
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
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}