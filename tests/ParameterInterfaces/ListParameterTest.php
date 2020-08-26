<?php
/*
 * The MIT License
 *
 * Copyright 2020 Aesonus <corylcomposinger at gmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace Aesonus\Tests\ParameterInterfaces;

use Aesonus\Paladin\DocBlock\ListParameter;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class ListParameterTest extends ParameterInterfaceTestCase
{
    /**
     * @test
     * @dataProvider validateReturnsTrueIfTypesValidateMethodReturnsTrueDataProvider
     */
    public function validateReturnsTrueIfTypesValidateMethodReturnsTrue(...$types)
    {
        $testObj = new ListParameter($types);
        $this->assertTrue($testObj->validate(['test-value']));
    }

    /**
     * Data Provider
     */
    public function validateReturnsTrueIfTypesValidateMethodReturnsTrueDataProvider()
    {
        return [
            'single type' => [
                $this->expectMockParameterInterfaceValidateCall($this->once(), [['test-value']], true)
            ],
            'two types with first type returning true' => [
                $this->expectMockParameterInterfaceValidateCall($this->once(), [['test-value']], true),
                $this->expectMockParameterInterfaceValidateCall($this->never(), [['test-value']], false)
            ],
            'two types with last type returning true' => [
                $this->expectMockParameterInterfaceValidateCall($this->once(), [['test-value']], false),
                $this->expectMockParameterInterfaceValidateCall($this->once(), [['test-value']], true)
            ],
        ];
    }

    /**
     * @test
     */
    public function validateCallsValueValidatorOnEachArrayValue()
    {
        $givenValue = [
            'test',
            'value'
        ];
        $types = [
            $this->expectMockParameterInterfaceValidateCall($this->exactly(2), [['test'], ['value']], false),
            $this->expectMockParameterInterfaceValidateCall($this->exactly(2), [['test'], ['value']], true),
        ];
        (new ListParameter($types))->validate($givenValue);
    }

    /**
     * @test
     * @dataProvider validateReturnsFalseIfArrayValuesComparisonIsFalseDataProvider
     */
    public function validateReturnsFalseIfArrayValuesComparisonIsFalse($givenValue)
    {
        $testObj = new ListParameter;
        $this->assertFalse($testObj->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function validateReturnsFalseIfArrayValuesComparisonIsFalseDataProvider()
    {
        return [
            'string keys' => [['test' => 'test-value']],
            'non-zero keys' => [[1 => 'test-value']],
        ];
    }
}
