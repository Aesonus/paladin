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
class StrictValidatorsTest extends \Aesonus\TestLib\BaseTestCase
{

    protected $testObj;

    protected function setUp()
    {
        $this->testObj = $this->getMockForTrait(\Aesonus\Paladin\Traits\StrictValidatable::class);
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
            [12]
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
            ['no'], [23.5], ['65.7'], [new \stdClass()], ['16']
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
            [3.141], [23.0]
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
            ['notafloat'], [null], [new \stdClass()], ['3.141']
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
            [new \stdClass(), \stdClass::class],
            [new \SplFileInfo(''), \SplFileInfo::class],
            [$this->getMockBuilder(\stdClass::class)->getMock(), \stdClass::class]
        ];
    }

    /**
     * @test
     * @dataProvider isClassReturnsFalseOnInvalidTypeDataProvider
     */
    public function isClassReturnsFalseOnInvalidType($param, $type)
    {
        $this->assertFalse($this->invokeMethod($this->testObj, 'isClassOf', [$param, $type]));
    }

    public function isClassReturnsFalseOnInvalidTypeDataProvider()
    {
        return [
            [\stdClass::class, \stdClass::class],
            ['no good', \stdClass::class],
            [23, \stdClass::class]
        ];
    }
}
