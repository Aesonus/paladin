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

use Aesonus\Paladin\DocBlock\ArrayKeyParameter;
use Aesonus\Paladin\DocBlock\ArrayParameter;
use Aesonus\Paladin\DocBlock\ClassStringParameter;
use Aesonus\Paladin\DocBlock\FloatParameter;
use Aesonus\Paladin\DocBlock\IntersectionParameter;
use Aesonus\Paladin\DocBlock\IntParameter;
use Aesonus\Paladin\DocBlock\MixedParameter;
use Aesonus\Paladin\DocBlock\ObjectParameter;
use Aesonus\Paladin\DocBlock\ResourceParameter;
use Aesonus\Paladin\DocBlock\StringParameter;
use Aesonus\Paladin\DocBlock\TypedClassStringParameter;
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
    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function acceptExceptionVisitorDoesNothingIfValidateSucceeds()
    {
        $parameter = new UnionParameter('$param', [new MixedParameter], true);
        $testObj = new TypeExceptionVisitor(23);
        $parameter->acceptExceptionVisitor($testObj);
    }

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
                [new IntParameter],
                'be of type int',
                'string'
            ],
            'int or string type' => [
                3.141,
                [new IntParameter, new StringParameter],
                'be of type int, or of type string',
                'double'
            ],
            'int, string, or array type' => [
                3.141,
                [new IntParameter, new StringParameter, new ArrayParameter],
                'be of type int, of type string, or of type array<array-key, mixed>',
                'double'
            ],
            'stdClass' => [
                3.141,
                [new ObjectParameter(stdClass::class)],
                'be instance of stdClass',
                'double'
            ],
            'other class given for stdClass' => [
                new ArrayObject,
                [new ObjectParameter(stdClass::class)],
                'be instance of stdClass',
                'instance of ArrayObject'
            ],
            'stdClass or string' => [
                3.141,
                [new ObjectParameter(stdClass::class), new StringParameter],
                'be instance of stdClass, or of type string',
                'double'
            ],
            'class-string' => [
                3.141,
                [new ClassStringParameter],
                'be of type class-string',
                'double'
            ],
            'object' => [
                3.141,
                [new ObjectParameter],
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
            [new IntersectionParameter([
                new ObjectParameter(ArrayAccess::class),
                new ObjectParameter(TestIntersectionClass::class)
                ])],
            true
        );
        $testObj = new TypeExceptionVisitor(new TestClass());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("\$param must be an intersection of ArrayAccess and "
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
                new IntersectionParameter([new ObjectParameter(ArrayAccess::class),
                new ObjectParameter(TestIntersectionClass::class)]),
                new IntParameter
            ],
            true
        );
        $testObj = new TypeExceptionVisitor(new TestClass());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("\$param must be an intersection of ArrayAccess and "
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
            [new ArrayParameter(new ArrayKeyParameter, $types)],
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
                [new IntParameter],
                'be of type array<array-key, int>',
                'array<int, double|int>'
            ],
            'int[] mixed keys' => [
                [3.141, 'string' => 2, 23, 'also' => 'string'],
                [new IntParameter],
                'be of type array<array-key, int>',
                'array<array-key, double|int>'
            ],
            'int[] string keys' => [
                ['pi' => 3.141, 'integer' => 2],
                [new IntParameter],
                'be of type array<array-key, int>',
                'array<string, double|int>'
            ],
            '(int|resource)[]' => [
                [3.141, 2],
                [new IntParameter, new ResourceParameter],
                'be of type array<array-key, int|resource>',
                'array<int, double|int>'
            ],
            '(int|resource)[] mixed keys' => [
                [3.141, 'string' => 2, 23, 'also' => 'string'],
                [new IntParameter, new ResourceParameter],
                'be of type array<array-key, int|resource>',
                'array<array-key, double|int>'
            ],
            '(int|resource)[] string keys' => [
                ['pi' => 3.141, 'integer' => 2],
                [new IntParameter, new ResourceParameter],
                'be of type array<array-key, int|resource>',
                'array<string, double|int>'
            ],
            'array<array-key, ArrayAccess&' . TestIntersectionClass::class . '>' => [
                ['pi' => 3.141, ['nested' => 23]],
                [new IntersectionParameter([new ObjectParameter(ArrayAccess::class),
                new ObjectParameter(TestIntersectionClass::class)])],
                'be of type array<array-key, ArrayAccess&' . TestIntersectionClass::class . '>',
                'array<array-key, double|array<string, int>>'
            ]
        ];
    }

    /**
     * @test
     * @dataProvider acceptExceptionVisitorThrowsExceptionIfValidateFailsOnTypedClassStringTypeDataProvider
     */
    public function acceptExceptionVisitorThrowsExceptionIfValidateFailsOnTypedClassStringType(
        $givenValue,
        $types,
        $exceptionPostfix
    ) {
        $parameter = new UnionParameter(
            '$param',
            [new TypedClassStringParameter($types)],
            true
        );
        $testObj = new TypeExceptionVisitor($givenValue);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("\$param must be of type class-string<$exceptionPostfix>");
        $parameter->acceptExceptionVisitor($testObj);
    }

    /**
     * Data Provider
     */
    public function acceptExceptionVisitorThrowsExceptionIfValidateFailsOnTypedClassStringTypeDataProvider()
    {
        return [
            [\stdClass::class, [\ArrayObject::class], \ArrayObject::class]
        ];
    }

    /**
     * @test
     */
    public function acceptExceptionVisitorThrowsExceptionIfValidateFailsOnNestedArrayType()
    {
        $parameter = new UnionParameter(
            '$param',
            [
                new ArrayParameter(
                    new ArrayKeyParameter,
                    [
                        new ArrayParameter(new ArrayKeyParameter, [new StringParameter]),
                        new FloatParameter
                    ]
                ),

            ],
            true
        );
        $testObj = new TypeExceptionVisitor('bad');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$param must be of type array<array-key, array<array-key, string>|float>; ');
        $parameter->acceptExceptionVisitor($testObj);
    }
}
