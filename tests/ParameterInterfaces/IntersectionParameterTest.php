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

use Aesonus\Paladin\DocBlock\IntersectionParameter;
use ArrayObject;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class IntersectionParameterTest extends ParameterInterfaceTestCase
{
    /**
     * @test
     * @dataProvider validateReturnsTrueIfAllTypesValidateMethodsReturnTrueDataProvider
     */
    public function validateReturnsTrueIfAllTypesValidateMethodsReturnTrue(...$types)
    {
        $testObj = new IntersectionParameter($types);
        $this->assertTrue($testObj->validate(new ArrayObject()));
    }

    /**
     * Data Provider
     */
    public function validateReturnsTrueIfAllTypesValidateMethodsReturnTrueDataProvider()
    {
        return [
            'intersection of two types' => [
                $this->expectMockParameterInterfaceValidateCall(
                    $this->once(),
                    [[$this->isInstanceOf(ArrayObject::class)]],
                    true
                ),
                $this->expectMockParameterInterfaceValidateCall(
                    $this->once(),
                    [[$this->isInstanceOf(ArrayObject::class)]],
                    true
                ),
            ],
            'intersection of three types' => [
                $this->expectMockParameterInterfaceValidateCall(
                    $this->once(),
                    [[$this->isInstanceOf(ArrayObject::class)]],
                    true
                ),
                $this->expectMockParameterInterfaceValidateCall(
                    $this->once(),
                    [[$this->isInstanceOf(ArrayObject::class)]],
                    true
                ),
                $this->expectMockParameterInterfaceValidateCall(
                    $this->once(),
                    [[$this->isInstanceOf(ArrayObject::class)]],
                    true
                ),
            ],
        ];
    }

    /**
     * @test
     * @dataProvider validateReturnsFalseIfAnyTypesValidateMethodReturnsFalseDataProvider
     */
    public function validateReturnsFalseIfAnyTypesValidateMethodReturnsFalse(...$types)
    {
        $testObj = new IntersectionParameter($types);
        $this->assertFalse($testObj->validate(new ArrayObject()));
    }

    /**
     * Data Provider
     */
    public function validateReturnsFalseIfAnyTypesValidateMethodReturnsFalseDataProvider()
    {
        return [
            'intersection of two types' => [
                $this->expectMockParameterInterfaceValidateCall(
                    $this->once(),
                    [[$this->isInstanceOf(ArrayObject::class)]],
                    false
                ),
                $this->expectMockParameterInterfaceValidateCall(
                    $this->never(),
                    [[$this->isInstanceOf(ArrayObject::class)]],
                    true
                ),
            ],
            'intersection of three types' => [
                $this->expectMockParameterInterfaceValidateCall(
                    $this->once(),
                    [[$this->isInstanceOf(ArrayObject::class)]],
                    true
                ),
                $this->expectMockParameterInterfaceValidateCall(
                    $this->once(),
                    [[$this->isInstanceOf(ArrayObject::class)]],
                    true
                ),
                $this->expectMockParameterInterfaceValidateCall(
                    $this->once(),
                    [[$this->isInstanceOf(ArrayObject::class)]],
                    false
                ),
            ],
        ];
    }

    /**
     * @test
     */
    public function toStringReturnsStringRepresentationOfIntersectingTypes()
    {
        $expected = 'int&float';
        $types[0] = $this->getMockParameterInterface();
        $types[0]->expects($this->once())->method('__toString')
            ->willReturn('int');
        $types[1] = $this->getMockParameterInterface();
        $types[1]->expects($this->once())->method('__toString')
            ->willReturn('float');
        $testObj = new IntersectionParameter($types);
        $this->assertSame($expected, (string)$testObj);
    }
}
