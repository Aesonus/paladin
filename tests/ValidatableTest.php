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
class ValidatableTest extends \PHPUnit\Framework\TestCase
{

    protected $testObj;

    protected function setUp()
    {
        $this->testObj = new ValidatableTestHelper();
    }

    /**
     * @dataProvider invalidArgumentDataProvider
     */
    public function testInvalidArgument($given)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->testObj->testMethodSingleTypeParam($given);
    }

    public function invalidArgumentDataProvider()
    {
        return [
            'string' => ["FU"],
            'array' => [['nope']],
            'float' => [1.2],
        ];
    }

    /**
     * @dataProvider validArgumentDataProvider
     */
    public function testValidArgument($given)
    {
        $this->testObj->testMethodSingleTypeParam($given);
        $this->assertTrue(true);
    }

    public function validArgumentDataProvider()
    {
        return [
            'int' => [3],
            'intstring' => ["3"],
        ];
    }

    /**
     * @dataProvider validMultiTypeArgumentDataProvider
     */
    public function testValidMultiTypeArgument($given)
    {
        $this->testObj->testMethodMultiTypeParam($given);
        $this->assertTrue(true);
    }

    public function validMultiTypeArgumentDataProvider()
    {
        return [
            'int' => [3],
            'intstring' => ["3"],
            'float' => [3.141],
            'floatstring' => ["3.141"],
        ];
    }

    /**
     * @dataProvider multiTypeMultiParamsDataProvider
     * @param integer|float $givenStringInt
     * @param null|string $givenNullArray
     */
    public function testMultiTypeMultiParams($givenStringInt, $givenNullArray)
    {
        $this->testObj->testMethodMultiTypeMultiParams($givenStringInt, $givenNullArray);
        $this->assertTrue(TRUE);
    }

    public function multiTypeMultiParamsDataProvider()
    {
        return [
            [4, ['you']],
            ["2", NULL],
            ["purple", ["3" => 5]],
            ["good", NULL]
        ];
    }

    public function testManyParamMethod()
    {
        $this->testObj->testManyParamMethod(1, "h", 3);
        $this->assertTrue(TRUE);
        $this->expectException(\InvalidArgumentException::class);
        $this->testObj->testManyParamMethod("h", 1, "person");
    }

    /**
     * @dataProvider noDocMethodDataProvider
     * @param type $given
     */
    public function testNoDocMethod($given)
    {
        $this->testObj->testNoDocMethod($given);
        $this->assertTrue(true);
    }

    public function noDocMethodDataProvider()
    {
        return [
            [3], [[3, 4]], ["idowhatiwantttt"], [4.2348], [new \stdClass()]
        ];
    }

    public function testInvalidValidator()
    {
        $this->expectException(\Aesonus\Paladin\Exceptions\ValidatorMethodNotFoundException::class);
        $this->expectExceptionMessage('validateInvalid');
        $this->testObj->addCustomParameterType('invalid');

        $this->testObj->testCustomValidatorMethod(3);
    }

    /**
     * @dataProvider noDocMethodDataProvider
     */
    public function testMixedType($given)
    {
        $this->testObj->testMethodMixedType($given);
        $this->assertTrue(TRUE);
    }
    
    /**
     * 
     * @dataProvider invalidCustomTypeDataProvider
     */
    public function testInvalidCustomType($rulename)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('string');
        $this->testObj->addCustomParameterType($rulename);
    }
    
    public function invalidCustomTypeDataProvider()
    {
        return [
            [[4]],[5423],[1.234],[null]
        ];
    }
    
    public function testCustomType()
    {
        $testvalue = 'customdsafdsfg';
        
        $mock = $this->getMockBuilder(get_class($this->testObj))->setMethods(['validateCustom'])
            ->getMock();
        $mock->expects($this->once())->method('validateCustom')->with($testvalue);
        
        $mock->addCustomParameterType('custom');
        try {
            $mock->testMethodCustomType($testvalue);
        } catch (\InvalidArgumentException $e) {
            //It's gonna throw an exception, since we are just mocking a particular function it doesn't return anything
        }
    }
}
