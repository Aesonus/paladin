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
use Aesonus\Paladin\DocBlock\TypedClassStringParameter;
use Aesonus\Paladin\DocBlock\UnionParameter;
use Aesonus\TestLib\BaseTestCase;
use Aesonus\Tests\Fixtures\TestClass;
use Aesonus\Tests\Fixtures\TestIntersectionClass;
use Aesonus\Tests\Fixtures\TestTrait;
use ArrayAccess;
use InvalidArgumentException;
use Iterator;
use RuntimeException;
use stdClass;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class DocBlockParameterTest extends BaseTestCase
{
    /**
     * @test
     * @dataProvider validateParameterReturnsTrueIfParameterIsOfSimpleTypeDataProvider
     */
    public function validateParameterReturnsTrueIfParameterIsOfSimpleType($type, $givenValue)
    {
        $docBlockParameter = new UnionParameter(
            '$test',
            [$type],
            true
        );
        $this->assertTrue($docBlockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function validateParameterReturnsTrueIfParameterIsOfSimpleTypeDataProvider()
    {
        return [
            'mixed' => ['mixed', 32],
            'string' => ['string', 'test value'],
            'int' => ['int', 3],
            'true for bool' => ['bool', true],
            'false for bool' => ['bool', false],
            'true' => ['true', true],
            'false' => ['false', false],
            'float' => ['float', 3.141],
            'array' => ['array', []],
            'stdClass for object' => ['object', new stdClass],
            'int for scalar' => ['scalar', 34],
            'float for scalar' => ['scalar', 34.4],
            'string for scalar' => ['scalar', 'string'],
            'int for mixed ' => ['mixed', 34],
            'float for mixed' => ['mixed', 34.4],
            'string for mixed' => ['mixed', 'string'],
            'numeric-string for numeric' => ['numeric', '3.32'],
            'int for numeric' => ['numeric', 3],
            'float for numeric' => ['numeric', 3.45],
            'callable-string for callable' => ['callable', 'array_filter'],
            'array for callable' => ['callable', [new TestClass, 'simpleType']],
            'callable-string' => ['callable-string', 'array_filter'],
            'string for array-key' => ['array-key', 'key'],
            'int for array-key' => ['array-key', 1],
            'class-string' => ['class-string', stdClass::class],
            'interface for class-string' => ['class-string', ArrayAccess::class],
            'trait-string' => ['trait-string', TestTrait::class],
            'numeric-string' => ['numeric-string', '3.14159'],
            stdClass::class => [stdClass::class, new stdClass]
        ];
    }

    /**
     * @test
     * @dataProvider invalidParameterTypeValueDataProvider
     */
    public function validateParameterReturnsFalseIfParameterIsNotOfSimpleType($type, $givenValue)
    {
        $docBlockParameter = new UnionParameter(
            '$test',
            [$type],
            true
        );
        $this->assertFalse($docBlockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function invalidParameterTypeValueDataProvider()
    {
        return [
            'int for string' => ['string', 32],
            'float for int' => ['int', 3.141],
            '2 for bool' => ['bool', 2],
            'string for bool' => ['bool', 'test'],
            '1 for true' => ['true', 1],
            '0 for false' => ['false', 0],
            'false for true' => ['true', false],
            'true for false' => ['false', true],
            'int for float' => ['float', 3],
            'object for array' => ['array', new stdClass],
            'classname for object' => ['object', stdClass::class],
            'array for scalar' => ['scalar', []],
            'object for scalar' => ['scalar', new stdClass],
            'characters for numeric' => ['numeric', 'abc'],
            'object for numeric' => ['numeric', new stdClass],
            'array for numeric' => ['numeric', []],
            'non-callable for callable' => ['callable', 'array_filterrsf'],
            'non-callable array callable' => ['callable', [new TestClass, 'methodNotFound']],
            'non-callable string for callable-string' => ['callable-string', 'nopenoexists'],
            'float for array-key' => ['array-key', 3.141],
            'class for class-string' => ['class-string', new stdClass],
            'trait-string for class-string' => ['class-string', TestTrait::class],
            'interface for trait-string' => ['trait-string', ArrayAccess::class],
            'characters for numeric-string' => ['numeric-string', '3.14159sd'],
            stdClass::class => [stdClass::class, new TestIntersectionClass],
        ];
    }

    /**
     * @test
     * @dataProvider validateParameterReturnsTrueIfParameterIsOfUnionTypeDataProvider
     */
    public function validateParameterReturnsTrueIfParameterIsOfUnionType($types, $givenValue)
    {
        $docBlockParameter = new UnionParameter(
            '$test',
            $types,
            true
        );
        $this->assertTrue($docBlockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function validateParameterReturnsTrueIfParameterIsOfUnionTypeDataProvider()
    {
        return [
            'string for string|int' => [['string', 'int'], 'test'],
            'int for string|int' => [['string', 'int'], 23],
        ];
    }

    /**
     * @test
     * @dataProvider invalidUnionTypeParameterValueDataProvider
     */
    public function validateParameterReturnsFalseIfParameterIsNotOfUnionType($types, $givenValue)
    {
        $docBlockParameter = new UnionParameter(
            '$test',
            $types,
            true
        );
        $this->assertFalse($docBlockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function invalidUnionTypeParameterValueDataProvider()
    {
        return [
            'float for string|int' => [['string', 'int'], 3.14159],
            'object for string|int' => [['string', 'int'], new stdClass],
        ];
    }

    /**
     * @test
     * @dataProvider validateParameterReturnsTrueIfParameterIsListOfTypeDataProvider
     */
    public function validateParameterReturnsTrueIfParameterIsListOfType($type, $givenValue)
    {
        $docBlockParameter = new UnionParameter(
            '$test',
            [
                new ArrayParameter('$test', 'array-key', $type)
            ],
            true
        );
        $this->assertTrue($docBlockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function validateParameterReturnsTrueIfParameterIsListOfTypeDataProvider()
    {
        return [
            'string[] or array<string>' => [['string'], ['test', 'strings']],
            'array<string|int>' => [['string', 'int'], [34, 'string', 34]],
            'array<string|int|object>' => [['string', 'object', 'int'], [32, new stdClass, 'test']]
        ];
    }

    /**
     * @test
     * @dataProvider validateParameterReturnsFalseIfParameterIsNotListOfTypeDataProvider
     */
    public function validateParameterReturnsFalseIfParameterIsNotListOfType($type, $givenValue)
    {
        $docBlockParameter = new UnionParameter(
            '$test',
            [
                new ArrayParameter('$test', 'array-key', $type)
            ],
            true
        );
        $this->assertFalse($docBlockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function validateParameterReturnsFalseIfParameterIsNotListOfTypeDataProvider()
    {
        return [
            'array<string> having 1 valid' => [['string'], [3.12, 23, 'string']],
            'array<string|int> having 1 valid' => [['string', 'int'], [32, 34.5, [], 34.2]],
            'array<string|int> having 0 valid' => [['string', 'int'], [34.5, [], 34.2]],
            'object for array<string|int>' => [['string', 'int'], new stdClass],
        ];
    }

    /**
     * @test
     * @dataProvider validateParameterReturnsTrueIfArrayKeyPairIsOfTypeDataProvider
     */
    public function validateParameterReturnsTrueIfArrayKeyPairIsOfType($keyType, $type, $givenValue)
    {
        $docBlockParameter = new UnionParameter(
            '$test',
            [
                new ArrayParameter('$test', $keyType, $type)
            ],
            true
        );
        $this->assertTrue($docBlockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function validateParameterReturnsTrueIfArrayKeyPairIsOfTypeDataProvider()
    {
        return [
            'array<int, string>' => ['int', ['string'], ['just', 'a', 'list']],
            'array<string, string>' => ['string', ['string'], ['a' => 'just', 'b' => 'a', 'c' => 'list']],
            'array<string, string|int>' => ['string', ['string', 'int'], ['a' => 'just', 'b' => 2, 'c' => 'list']],
        ];
    }

    /**
     * @test
     * @dataProvider validateParameterReturnsFalseIfArrayKeyPairIsNotOfTypeDataProvider
     */
    public function validateParameterReturnsFalseIfArrayKeyPairIsNotOfType($keyType, $type, $givenValue)
    {
        $docBlockParameter = new UnionParameter(
            '$test',
            [
                new ArrayParameter('$test', $keyType, $type)
            ],
            true
        );
        $this->assertFalse($docBlockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function validateParameterReturnsFalseIfArrayKeyPairIsNotOfTypeDataProvider()
    {
        return [
            'invalid key in array<int, string>' => [
                'int',
                ['string'],
                ['a' => 'just', 2 => 'a', 'c' => 'list']
            ],
            'invalid element in array<int, string>' => ['int', ['string'], ['just', 3.1441, 'list']],
            'invalid key in array<string, string>' => ['string', ['string'], ['just', 'a', 'list']],
            'invalid element in array<int, string>' => ['int', ['string'], ['just', 2, 'list']],
            'invalid key in array<string, string|int>' => [
                'string',
                ['string', 'int'],
                [0 => 'just', 'b' => 2, 'c' => 'list']
            ],
            'invalid value for array<int, string>' => [
                'int',
                ['string'],
                new stdClass()
            ],
        ];
    }

    /**
     * @test
     * @dataProvider validateParameterReturnsTrueIfParameterIsIntersectionTypeDataProvider
     */
    public function validateParameterReturnsTrueIfParameterIsIntersectionType($types, $givenValue)
    {
        $docBlockParameter = new UnionParameter(
            '$test',
            [
                new IntersectionParameter('$test', $types)
            ],
            true
        );
        $this->assertTrue($docBlockParameter->validate($givenValue));
    }

    /**
     * @test
     * @dataProvider validateParameterReturnsTrueIfParameterIsIntersectionTypeDataProvider
     */
    public function validateParameterReturnsTrueIfArrayParameterElementIsOfIntersectionType($types, $givenValue)
    {
        $docBlockParameter = new ArrayParameter(
            '$test',
            'int',
            [
                new IntersectionParameter('array', $types)
            ],
            true
        );
        $this->assertTrue($docBlockParameter->validate([$givenValue, $givenValue]));
    }

    /**
     * Data Provider
     */
    public function validateParameterReturnsTrueIfParameterIsIntersectionTypeDataProvider()
    {
        return [
            'ArrayAccess&TestIntersectionClass' => [
                [ArrayAccess::class, TestIntersectionClass::class],
                new TestIntersectionClass
            ],
        ];
    }

    /**
     * @test
     * @dataProvider validateParameterReturnsFalseIfParameterIsNotOfIntersectionTypeDataProvider
     */
    public function validateParameterReturnsFalseIfParameterIsNotOfIntersectionType($types, $givenValue)
    {
        $docBlockParameter = new UnionParameter(
            '$test',
            [
                new IntersectionParameter('$test', $types),
            ],
            true
        );
        $this->assertFalse($docBlockParameter->validate($givenValue));
    }

    /**
     * @test
     * @dataProvider validateParameterReturnsFalseIfParameterIsNotOfIntersectionTypeDataProvider
     */
    public function validateParameterReturnsFalseIfArrayParameterElementIsNotOfIntersectionType($types, $givenValue)
    {
        $docBlockParameter = new ArrayParameter(
            '$test',
            'int',
            [
                new IntersectionParameter('array', $types)
            ],
            true
        );
        //Make a valid given value to make sure this works
        $givenValidValue = new class() extends TestIntersectionClass implements Iterator {
            public function current()
            {
            }
            public function key(): \scalar
            {
            }
            public function next(): void
            {
            }
            public function rewind(): void
            {
            }
            public function valid(): bool
            {
            }
        };
        $this->assertFalse($docBlockParameter->validate([$givenValue, $givenValidValue]));
    }

    /**
     * Data Provider
     */
    public function validateParameterReturnsFalseIfParameterIsNotOfIntersectionTypeDataProvider()
    {
        return [
            'TestIntersectionClass&Iterator (Iterator not implemented)' => [
                [TestIntersectionClass::class, Iterator::class],
                new TestIntersectionClass
            ]
        ];
    }

    /**
     * @test
     * @dataProvider validateParameterReturnsTrueIfParameterIsClassStringOfTypeDataProvider
     */
    public function validateParameterReturnsTrueIfParameterIsClassStringOfType($classTypes, $givenValue)
    {
        $docblockParameter = new TypedClassStringParameter(
            '$test',
            $classTypes,
            true
        );
        $this->assertTrue($docblockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function validateParameterReturnsTrueIfParameterIsClassStringOfTypeDataProvider()
    {
        return [
            'class-string<\\stdClass>' => [[stdClass::class], stdClass::class],
            'descendant for class-string<\\stdClass>' => [[stdClass::class], TestClass::class],
        ];
    }

    /**
     * @test
     * @dataProvider validateParameterReturnsFalseIfParameterIsNotClassStringOfTypeDataProvider
     */
    public function validateParameterReturnsFalseIfParameterIsNotClassStringOfType($classTypes, $givenValue)
    {
        $docblockParameter = new TypedClassStringParameter(
            '$test',
            $classTypes,
            true
        );
        $this->assertFalse($docblockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function validateParameterReturnsFalseIfParameterIsNotClassStringOfTypeDataProvider()
    {
        return [
            'invalid class for class-string<\\stdClass>' => [[TestClass::class], stdClass::class],
            'object for class-string<\\stdClass>' => [[stdClass::class], new stdClass()],
        ];
    }

    /**
     * @test
     * @dataProvider validateParameterThrowsExceptionIfParameterCannotBeValidatedDataProvider
     */
    public function validateParameterThrowsExceptionIfParameterCannotBeValidated($types, $message)
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);
        $docblockParameter = new UnionParameter('$testParam', $types, true);
        $docblockParameter->validate(null);
    }

    /**
     * Data Provider
     */
    public function validateParameterThrowsExceptionIfParameterCannotBeValidatedDataProvider()
    {
        return [
            [
                ['not_good'],
                'Could not validate for type \'not_good\''
            ]
        ];
    }
}
