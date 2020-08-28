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

use Aesonus\Paladin\DocBlock\ValueParameter;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class ValueParameterTest extends ParameterInterfaceTestCase
{
    /**
     * @test
     * @dataProvider validateReturnsTrueIfGivenValueIsExpectedValueDataProvider
     */
    public function validateReturnsTrueIfGivenValueIsExpectedValue($expectedValue)
    {
        $this->assertTrue((new ValueParameter($expectedValue))->validate($expectedValue));
    }

    /**
     * Data Provider
     */
    public function validateReturnsTrueIfGivenValueIsExpectedValueDataProvider()
    {
        return [
            'numeric' => [3],
            'string' => ['string value'],
        ];
    }

    /**
     * @test
     * @dataProvider validateReturnsFalseIfGivenValueIsNotExpectedValueDataProvider
     */
    public function validateReturnsFalseIfGivenValueIsNotExpectedValue($expectedValue, $givenValue)
    {
        $this->assertFalse((new ValueParameter($expectedValue))->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function validateReturnsFalseIfGivenValueIsNotExpectedValueDataProvider()
    {
        return [
            'numeric' => [3, '3'],
            'string' => ['string value', 3],
        ];
    }

    /**
     * @test
     * @dataProvider toStringReturnsExpectedValueAsStringDataProvider
     */
    public function toStringReturnsExpectedValueAsString($value, $expected)
    {
        $this->assertSame($expected, (string)(new ValueParameter($value)));
    }

    /**
     * Data Provider
     */
    public function toStringReturnsExpectedValueAsStringDataProvider()
    {
        return [
            'numeric' => [3, '"3"'],
            'string' => ['test', '"test"']
        ];
    }
}
