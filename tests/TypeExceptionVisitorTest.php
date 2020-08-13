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
namespace Aesonus\Tests;

use Aesonus\Paladin\DocBlock\ArrayParameter;
use Aesonus\Paladin\DocBlock\IntersectionParameter;
use Aesonus\Paladin\DocBlock\UnionParameter;
use Aesonus\Paladin\TypeExceptionVisitor;
use Aesonus\TestLib\BaseTestCase;
use Aesonus\Tests\Fixtures\TestClass;
use Aesonus\Tests\Fixtures\TestIntersectionClass;
use ArrayAccess;
use ArrayObject;
use InvalidArgumentException;
use stdClass;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class TypeExceptionVisitorTest extends BaseTestCase
{
    // 'of the type', 'int'],
    // 'of the type', 'float'],
    // 'of the type', 'int'],
    // 'true', 'of the type', 'string'],
    // 'of the type', 'int'],
    // 'of the type', 'int'],
    // 'of the type', 'false'],
    // 'of the type', 'true'],
    // 'of the type', 'int'],
    // 'an', 'object'],
    // 'an', 'string'],
    // 'of the type', 'array'],
    // 'of the type', 'object'],
    // 'of the type', 'string'],
    // 'of the type', 'object'],
    // 'of the type', 'array'],
    // 'of the type', 'string'],
    // 'of the type', 'array'],
    // 'of the type', 'string'],
    // 'of the type', 'float'],
    // 'of the type', 'object'],
    // 'a', 'string'],
    // 'a', 'string'],
    // 'a', 'string'],
    // 'an instance of', 'instance of ' . TestIntersectionClass::class]

    /**
     * @test
     * @dataProvider acceptExceptionVisitorThrowsExceptionIfValidateFailsOnUnionTypeDataProvider
     */
    public function acceptExceptionVisitorThrowsExceptionIfValidateFailsOnUnionType(
        $givenValue,
        $types,
        $exceptionPrefix,
        $exceptionPostfix
    ) {
        $parameter = new UnionParameter('$param', $types, true);
        $testObj = new TypeExceptionVisitor($givenValue);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("\$param must $exceptionPrefix; $exceptionPostfix given");
        $parameter->acceptExceptionVisitor($testObj);
    }

    /**
     * Data Provider
     */
    public function acceptExceptionVisitorThrowsExceptionIfValidateFailsOnUnionTypeDataProvider()
    {
        return [
            'int type' => [
                'given',
                ['int'],
                'be of type int',
                'string'
            ],
            'int or string type' => [
                3.141,
                ['int', 'string'],
                'be of type int, or of type string',
                'double'
            ],
            'int, string, or array type' => [
                3.141,
                ['int', 'string', 'array'],
                'be of type int, of type string, or of type array',
                'double'
            ],
            'stdClass' => [
                3.141,
                [stdClass::class],
                'be instance of stdClass',
                'double'
            ],
            'other class given for stdClass' => [
                new ArrayObject,
                [stdClass::class],
                'be instance of stdClass',
                'instance of ArrayObject'
            ],
            'stdClass or string' => [
                3.141,
                [stdClass::class, 'string'],
                'be instance of stdClass, or of type string',
                'double'
            ],
            'class-string' => [
                3.141,
                ['class-string'],
                'be of type class-string',
                'double'
            ],
            'object' => [
                3.141,
                ['object'],
                'be an object',
                'double'
            ],
        ];
    }

    /**
     * @test
     */
    public function acceptExceptionVisitorThrowsExceptionIfValidateFailsOnIntersectionType()
    {
        $parameter = new UnionParameter(
            '$param',
            [new IntersectionParameter('', [ArrayAccess::class, TestIntersectionClass::class])],
            true
        );
        $testObj = new TypeExceptionVisitor(new TestClass());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("\$param must be an intersection of ArrayAccess, and "
            . TestIntersectionClass::class . '; instance of ' . TestClass::class . ' given');
        $parameter->acceptExceptionVisitor($testObj);
    }

    /**
     * @test
     */
    public function acceptExceptionVisitorThrowsExceptionIfValidateFailsOnUnionWithIntersectionType()
    {
        $parameter = new UnionParameter(
            '$param',
            [
                new IntersectionParameter('', [ArrayAccess::class, TestIntersectionClass::class]),
                'int'
            ],
            true
        );
        $testObj = new TypeExceptionVisitor(new TestClass());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("\$param must be an intersection of ArrayAccess, and "
            . TestIntersectionClass::class . ', or of type int; instance of ' . TestClass::class . ' given');
        $parameter->acceptExceptionVisitor($testObj);
    }

    /**
     * @test
     * @dataProvider acceptExceptionVisitorThrowsExceptionIfValidateFailsOnPsrDocArrayTypeDataProvider
     */
    public function acceptExceptionVisitorThrowsExceptionIfValidateFailsOnPsrDocArrayType(
        $givenValue,
        $types,
        $exceptionPrefix,
        $exceptionPostfix
    ) {
        $parameter = new UnionParameter(
            '$param',
            [new ArrayParameter('array', 'array-key', $types)],
            true
        );
        $testObj = new TypeExceptionVisitor($givenValue);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("\$param must $exceptionPrefix; $exceptionPostfix given");
        $parameter->acceptExceptionVisitor($testObj);
    }

    /**
     * Data Provider
     */
    public function acceptExceptionVisitorThrowsExceptionIfValidateFailsOnPsrDocArrayTypeDataProvider()
    {
        return [
            'int[]' => [
                [3.141, 2],
                ['int'],
                'be an array of type <array-key, int>',
                'array of type <int, double|int>'
            ],
            'int[] mixed keys' => [
                [3.141, 'string' => 2],
                ['int'],
                'be an array of type <array-key, int>',
                'array of type <array-key, double|int>'
            ],
            'int[] string keys' => [
                ['pi' => 3.141, 'integer' => 2],
                ['int'],
                'be an array of type <array-key, int>',
                'array of type <string, double|int>'
            ],
        ];
    }
}
