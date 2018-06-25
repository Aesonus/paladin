<?php
/*
 * This software is licensed under the MIT License. Please see LICENSE for more details.
 */

namespace Aesonus\Tests;

/**
 * Description of CoreValidatorsTest
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class ValidatorsTest extends \Aesonus\TestLib\BaseTestCase
{

    protected $testObj;

    protected function setUp()
    {
        $this->testObj = $this->getMockForTrait(\Aesonus\Paladin\Traits\DefaultValidators::class);
        parent::setUp();
    }
    ////VALIDATE INT

    /**
     * @test
     * @dataProvider validateIntReturnsTrueOnValidTypeDataProvider
     */
    public function validateIntReturnsTrueOnValidType($param)
    {
        $actual = $this->invokeMethod($this->testObj, 'validateInt', [$param]);
        $this->assertTrue($actual);
    }

    public function validateIntReturnsTrueOnValidTypeDataProvider()
    {
        return [
            [12], ['16']
        ];
    }

    /**
     * @test
     * @dataProvider validateIntReturnsFalseOnInvalidTypeDataProvider
     */
    public function validateIntReturnsFalseOnInvalidType($param)
    {
        $actual = $this->invokeMethod($this->testObj, 'validateInt', [$param]);
        $this->assertFalse($actual);
    }

    public function validateIntReturnsFalseOnInvalidTypeDataProvider()
    {
        return [
            ['no'], [23.5], ['65.7'], [new \stdClass()]
        ];
    }
    ////VALIDATE STRING

    /**
     * @test
     * @dataProvider validateStringReturnsTrueOnValidTypeDataProvider
     */
    public function validateStringReturnsTrueOnValidType($param)
    {
        $actual = $this->invokeMethod($this->testObj, 'validateString', [$param]);
        $this->assertTrue($actual);
    }

    public function validateStringReturnsTrueOnValidTypeDataProvider()
    {
        return [
            ['good'], ['great']
        ];
    }

    /**
     * @test
     * @dataProvider validateStringReturnsFalseOnInvalidTypeDataProvider
     */
    public function validateStringReturnsFalseOnInvalidType($param)
    {
        $actual = $this->invokeMethod($this->testObj, 'validateString', [$param]);
        $this->assertFalse($actual);
    }

    public function validateStringReturnsFalseOnInvalidTypeDataProvider()
    {
        return [
            [3.141], [4], [new \stdClass()]
        ];
    }

    ////VALIDATE FLOAT
    /**
     * @test
     * @dataProvider validateFloatReturnsTrueOnValidTypeDataProvider
     */
    public function validateFloatReturnsTrueOnValidType($param)
    {
        $actual = $this->invokeMethod($this->testObj, 'validateFloat', [$param]);
        $this->assertTrue($actual);
    }

    public function validateFloatReturnsTrueOnValidTypeDataProvider()
    {
        return [
            [3.141], ['3.141'], [23]
        ];
    }

    /**
     * @test
     * @dataProvider validateFloatReturnsFalseOnInvalidTypeDataProvider
     */
    public function validateFloatReturnsFalseOnInvalidType($param)
    {
        $actual = $this->invokeMethod($this->testObj, 'validateFloat', [$param]);
        $this->assertFalse($actual);
    }

    public function validateFloatReturnsFalseOnInvalidTypeDataProvider()
    {
        return [
            ['notafloat'], [null], [new \stdClass()]
        ];
    }
    
    ////VALIDATE NULL
    /**
     * @test
     * @dataProvider validateNullReturnsTrueOnValidTypeDataProvider
     */
    public function validateNullReturnsTrueOnValidType($param)
    {
        $actual = $this->invokeMethod($this->testObj, 'validateNull', [$param]);
        $this->assertTrue($actual);
    }

    public function validateNullReturnsTrueOnValidTypeDataProvider()
    {
        return [
            [null]
        ];
    }

    /**
     * @test
     * @dataProvider validateNullReturnsFalseOnInvalidTypeDataProvider
     */
    public function validateNullReturnsFalseOnInvalidType($param)
    {
        $actual = $this->invokeMethod($this->testObj, 'validateNull', [$param]);
        $this->assertFalse($actual);
    }

    public function validateNullReturnsFalseOnInvalidTypeDataProvider()
    {
        return [
            ['notnull'], [32], [new \stdClass()]
        ];
    }
    
    ////VALIDATE ARRAY
    /**
     * @test
     * @dataProvider validateArrayReturnsTrueOnValidTypeDataProvider
     */
    public function validateArrayReturnsTrueOnValidType($param)
    {
        $actual = $this->invokeMethod($this->testObj, 'validateArray', [$param]);
        $this->assertTrue($actual);
    }

    public function validateArrayReturnsTrueOnValidTypeDataProvider()
    {
        return [
            [[null]], [['good' => 'array']]
        ];
    }

    /**
     * @test
     * @dataProvider validateArrayReturnsFalseOnInvalidTypeDataProvider
     */
    public function validateArrayReturnsFalseOnInvalidType($param)
    {
        $actual = $this->invokeMethod($this->testObj, 'validateArray', [$param]);
        $this->assertFalse($actual);
    }

    public function validateArrayReturnsFalseOnInvalidTypeDataProvider()
    {
        return [
            ['notarray'], [32], [new \stdClass()]
        ];
    }
    
    ////VALIDATE SCALAR
    /**
     * @test
     * @dataProvider validateScalarReturnsTrueOnValidTypeDataProvider
     */
    public function validateScalarReturnsTrueOnValidType($param)
    {
        $actual = $this->invokeMethod($this->testObj, 'validateScalar', [$param]);
        $this->assertTrue($actual);
    }

    public function validateScalarReturnsTrueOnValidTypeDataProvider()
    {
        return [
            [23], ['good'], [43.5], [false]
        ];
    }

    /**
     * @test
     * @dataProvider validateScalarReturnsFalseOnInvalidTypeDataProvider
     */
    public function validateScalarReturnsFalseOnInvalidType($param)
    {
        $actual = $this->invokeMethod($this->testObj, 'validateScalar', [$param]);
        $this->assertFalse($actual);
    }

    public function validateScalarReturnsFalseOnInvalidTypeDataProvider()
    {
        return [
            [[]], [null], [new \stdClass()]
        ];
    }
    
    ////VALIDATE BOOL
    /**
     * @test
     * @dataProvider validateBoolReturnsTrueOnValidTypeDataProvider
     */
    public function validateBoolReturnsTrueOnValidType($param)
    {
        $actual = $this->invokeMethod($this->testObj, 'validateBool', [$param]);
        $this->assertTrue($actual);
    }

    public function validateBoolReturnsTrueOnValidTypeDataProvider()
    {
        return [
            [true], [false]
        ];
    }

    /**
     * @test
     * @dataProvider validateBoolReturnsFalseOnInvalidTypeDataProvider
     */
    public function validateBoolReturnsFalseOnInvalidType($param)
    {
        $actual = $this->invokeMethod($this->testObj, 'validateBool', [$param]);
        $this->assertFalse($actual);
    }

    public function validateBoolReturnsFalseOnInvalidTypeDataProvider()
    {
        return [
            [[]], [null], [new \stdClass()]
        ];
    }
    
    ////VALIDATE OBJECT
    /**
     * @test
     * @dataProvider validateObjectReturnsTrueOnValidTypeDataProvider
     */
    public function validateObjectReturnsTrueOnValidType($param)
    {
        $actual = $this->invokeMethod($this->testObj, 'validateObject', [$param]);
        $this->assertTrue($actual);
    }

    public function validateObjectReturnsTrueOnValidTypeDataProvider()
    {
        return [
            [new \stdClass()]
        ];
    }

    /**
     * @test
     * @dataProvider validateObjectReturnsFalseOnInvalidTypeDataProvider
     */
    public function validateObjectReturnsFalseOnInvalidType($param)
    {
        $actual = $this->invokeMethod($this->testObj, 'validateObject', [$param]);
        $this->assertFalse($actual);
    }

    public function validateObjectReturnsFalseOnInvalidTypeDataProvider()
    {
        return [
            [[]], [null], ['new \stdClass()'], [false]
        ];
    }
    
    ////VALIDATE CALLABLE
    /**
     * @test
     * @dataProvider validateCallableReturnsTrueOnValidTypeDataProvider
     */
    public function validateCallableReturnsTrueOnValidType($param)
    {
        $actual = $this->invokeMethod($this->testObj, 'validateCallable', [$param]);
        $this->assertTrue($actual);
    }

    public function validateCallableReturnsTrueOnValidTypeDataProvider()
    {
        return [
            ['sort'], [[$this, 'validateCallableReturnsTrueOnValidType']]
        ];
    }

    /**
     * @test
     * @dataProvider validateCallableReturnsFalseOnInvalidTypeDataProvider
     */
    public function validateCallableReturnsFalseOnInvalidType($param)
    {
        $actual = $this->invokeMethod($this->testObj, 'validateCallable', [$param]);
        $this->assertFalse($actual);
    }

    public function validateCallableReturnsFalseOnInvalidTypeDataProvider()
    {
        return [
            [[]], [null], ['new \stdClass()'], [false]
        ];
    }
    
    ////VALIDATE MIXED
    
    /**
     * @test
     * @dataProvider validateMixedReturnsTrueAlwaysDataProvider
     */
    public function validateMixedReturnsTrueAlways($param)
    {
        $actual = $this->invokeMethod($this->testObj, 'validateMixed', [$param]);
        $this->assertTrue($actual);
    }

    public function validateMixedReturnsTrueAlwaysDataProvider()
    {
        return [
            [[]], [null], ['new \stdClass()'], [false]
        ];
    }
    
    ////IS CLASS OF TESTS

    /**
     * @test
     * @dataProvider isClassOfReturnsTrueOnValidTypeDataProvider
     */
    public function isClassOfReturnsTrueOnValidType($param, $type)
    {
        $actual = $this->invokeMethod($this->testObj, 'isClassOf', [$param, $type]);
        $this->assertTrue($actual);
    }

    public function isClassOfReturnsTrueOnValidTypeDataProvider()
    {
        return [
            [\stdClass::class, \stdClass::class],
            [new \stdClass(), \stdClass::class],
            [new \SplFileInfo(''), \SplFileInfo::class],
            [get_class($this->getMockBuilder(\stdClass::class)->getMock()), \stdClass::class]
        ];
    }

    /**
     * @test
     * @dataProvider isClassReturnsFalseOnInvalidTypeDataProvider
     */
    public function isClassReturnsFalseOnInvalidType($param, $type)
    {
        $this->invokeMethod($this->testObj, 'isClassOf', [$param, $type]);
    }

    public function isClassReturnsFalseOnInvalidTypeDataProvider()
    {
        return [
            
            ['no good', \stdClass::class],
            [23, \stdClass::class]
        ];
    }
}
