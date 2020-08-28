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

use Aesonus\Paladin\DocBlock\TypedClassStringParameter;
use ArrayObject;
use SplFileObject;
use stdClass;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class TypedClassStringParameterTest extends ParameterInterfaceTestCase
{
    /**
     * @test
     */
    public function validateReturnsFalseIfGivenValueIsNotAClassString()
    {
        $this->assertFalse((new TypedClassStringParameter([stdClass::class]))->validate(23));
    }

    /**
     * @test
     * @dataProvider validateReturnsFalseIfGivenClassTypeIsNotCorrectClassTypesDataProvider
     */
    public function validateReturnsFalseIfGivenClassTypeIsNotCorrectClassTypes(...$classTypes)
    {
        $testObj = new TypedClassStringParameter($classTypes);
        $givenValue = ArrayObject::class;
        $this->assertFalse($testObj->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function validateReturnsFalseIfGivenClassTypeIsNotCorrectClassTypesDataProvider()
    {
        return [
            'one class type' => [stdClass::class],
            'two class types' => [stdClass::class, SplFileObject::class],
        ];
    }

    /**
     * @test
     */
    public function validateReturnsTrueIfGivenClassTypeIsOfCorrectClassType()
    {
        $testObj = new TypedClassStringParameter([stdClass::class, ArrayObject::class]);
        $this->assertTrue($testObj->validate(ArrayObject::class));
    }

    /**
     * @test
     */
    public function toStringReturnsStringRepresentationOfTypedClassStringParameter()
    {
        $expected = 'class-string<int|float>'; //Note this is just for testing purposes

        $classTypes = ['int', 'float'];
        $testObj = new TypedClassStringParameter($classTypes);
        $this->assertSame($expected, (string)$testObj);
    }
}
