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

use Aesonus\Paladin\DocBlock\ArrayParameter;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class ArrayParameterTest extends ParameterInterfaceTestCase
{
    /**
     * @test
     */
    public function validateReturnsTrueIfKeyParameterAndValueParameterValidateMethodsReturnTrue()
    {
        $givenValue = ['test-value'];
        $key = $this->expectMockParameterInterfaceValidateCall($this->once(), [[0]], true);
        $value = $this->expectMockParameterInterfaceValidateCall($this->once(), [$givenValue], true);

        $testObj = new ArrayParameter($key, [$value]);
        $this->assertTrue($testObj->validate($givenValue));
    }

    /**
     * @test
     */
    public function validateReturnsTrueIfKeyAndValueParameterValidatesForEachArrayElement()
    {
        $givenValue = [
            'test-value',
            'string-key' => 32
        ];
        $key = $this->expectMockParameterInterfaceValidateCall($this->exactly(2), [[0], ['string-key']], true);
        $value = $this->expectMockParameterInterfaceValidateCall($this->exactly(2), [['test-value'], [32]], true);

        $testObj = new ArrayParameter($key, [$value]);
        $this->assertTrue($testObj->validate($givenValue));
    }

    /**
     * @test
     */
    public function validateReturnsFalseIfGivenValueIsNotArray()
    {
        $this->assertFalse((new ArrayParameter())->validate('not an array'));
    }

    /**
     * @test
     */
    public function validateReturnsFalseIfKeyParameterValidateMethodReturnsFalseAndValueParameterReturnsTrue()
    {
        $key = $this->expectMockParameterInterfaceValidateCall($this->once(), [[0]], false);
        $value = $this->expectMockParameterInterfaceValidateCall($this->once(), [['test-value']], true);

        $testObj = new ArrayParameter($key, [$value]);
        $this->assertFalse($testObj->validate(['test-value']));
    }

    /**
     * @test
     * @dataProvider validateReturnsFalseIfKeyValidateReturnsTrueButValueParameterValidateReturnsFalseDataProvider
     */
    public function validateReturnsFalseIfKeyValidateReturnsTrueButValueParameterValidateReturnsFalse(
        $valueParameters
    ) {
        $key = $this->expectMockParameterInterfaceValidateCall($this->any(), [[0]], true);
        $testObj = new ArrayParameter($key, $valueParameters);
        $this->assertFalse($testObj->validate(['test-value']));
    }

    /**
     * Data Provider
     */
    public function validateReturnsFalseIfKeyValidateReturnsTrueButValueParameterValidateReturnsFalseDataProvider()
    {
        return [
            'one type' => [
                [
                    $this->expectMockParameterInterfaceValidateCall($this->once(), [['test-value']], false)
                ]
            ],
            'two types' => [
                [
                    $this->expectMockParameterInterfaceValidateCall($this->once(), [['test-value']], false),
                    $this->expectMockParameterInterfaceValidateCall($this->once(), [['test-value']], false),
                ]
            ],
        ];
    }

    /**
     * @test
     */
    public function toStringReturnsStringRepresentationOfArrayType()
    {
        $expected = 'array<array-key, int|float>';
        $key = $this->getMockParameterInterface();
        $key->expects($this->once())->method('__toString')
            ->willReturn('array-key');
        $value[0] = $this->getMockParameterInterface();
        $value[0]->expects($this->once())->method('__toString')
            ->willReturn('int');
        $value[1] = $this->getMockParameterInterface();
        $value[1]->expects($this->once())->method('__toString')
            ->willReturn('float');
        $testObj = new ArrayParameter($key, $value);
        $this->assertSame($expected, (string)$testObj);
    }
}
