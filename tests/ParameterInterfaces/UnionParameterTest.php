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

use Aesonus\Paladin\DocBlock\UnionParameter;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class UnionParameterTest extends ParameterInterfaceTestCase
{
    private function createTestObj(array $types): UnionParameter
    {
        return new UnionParameter('$testParam', $types);
    }

    /**
     * @test
     * @dataProvider validateReturnsTrueIfOneCallToTypesValidateMethodsReturnTrueDataProvider
     */
    public function validateReturnsTrueIfOneCallToTypesValidateMethodsReturnTrue($types)
    {
        $testObj = $this->createTestObj($types);
        $this->assertTrue($testObj->validate('test value'));
    }

    /**
     * Data Provider
     */
    public function validateReturnsTrueIfOneCallToTypesValidateMethodsReturnTrueDataProvider()
    {
        return [
            'one type' => [
                [
                    $this->expectMockParameterInterfaceValidateCall($this->once(), [['test value']], true)
                ]
            ],
            'two types, last is true' => [
                [
                    $this->expectMockParameterInterfaceValidateCall($this->once(), [['test value']], false),
                    $this->expectMockParameterInterfaceValidateCall($this->once(), [['test value']], true),
                ]
            ],
            'two types, first is true last is skipped' => [
                [
                    $this->expectMockParameterInterfaceValidateCall($this->once(), [['test value']], true),
                    $this->expectMockParameterInterfaceValidateCall($this->never(), [['test value']], false),
                ]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider validateReturnsFalseIfAllCallsToTypesValidateMethodsReturnFalseDataProvider
     */
    public function validateReturnsFalseIfAllCallsToTypesValidateMethodsReturnFalse($types)
    {
        $testObj = $this->createTestObj($types);
        $this->assertFalse($testObj->validate('test value'));
    }

    /**
     * Data Provider
     */
    public function validateReturnsFalseIfAllCallsToTypesValidateMethodsReturnFalseDataProvider()
    {
        return [
            'one type' => [
                [
                    $this->expectMockParameterInterfaceValidateCall($this->once(), [['test value']], false)
                ]
            ],
            'two types' => [
                [
                    $this->expectMockParameterInterfaceValidateCall($this->once(), [['test value']], false),
                    $this->expectMockParameterInterfaceValidateCall($this->once(), [['test value']], false),
                ]
            ],
        ];
    }
}
