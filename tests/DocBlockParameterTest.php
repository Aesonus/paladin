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
use Aesonus\Paladin\DocBlock\BoolParameter;
use Aesonus\Paladin\DocBlock\CallableParameter;
use Aesonus\Paladin\DocBlock\CallableStringParameter;
use Aesonus\Paladin\DocBlock\ClassStringParameter;
use Aesonus\Paladin\DocBlock\FalseParameter;
use Aesonus\Paladin\DocBlock\FloatParameter;
use Aesonus\Paladin\DocBlock\IntersectionParameter;
use Aesonus\Paladin\DocBlock\IntParameter;
use Aesonus\Paladin\DocBlock\ListParameter;
use Aesonus\Paladin\DocBlock\MixedParameter;
use Aesonus\Paladin\DocBlock\NumericParameter;
use Aesonus\Paladin\DocBlock\NumericStringParameter;
use Aesonus\Paladin\DocBlock\ObjectParameter;
use Aesonus\Paladin\DocBlock\ScalarParameter;
use Aesonus\Paladin\DocBlock\StringParameter;
use Aesonus\Paladin\DocBlock\TraitStringParameter;
use Aesonus\Paladin\DocBlock\TrueParameter;
use Aesonus\Paladin\DocBlock\TypedClassStringParameter;
use Aesonus\Paladin\DocBlock\UnionParameter;
use Aesonus\TestLib\BaseTestCase;
use Aesonus\Tests\Fixtures\TestClass;
use Aesonus\Tests\Fixtures\TestIntersectionClass;
use Aesonus\Tests\Fixtures\TestTrait;
use ArrayAccess;
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
        );
        $this->assertTrue($docBlockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function validateParameterReturnsTrueIfParameterIsOfSimpleTypeDataProvider()
    {
        return [
            'mixed' => [new MixedParameter, 32],
            'int for mixed ' => [new MixedParameter, 34],
            'float for mixed' => [new MixedParameter, 34.4],
            'string for mixed' => [new MixedParameter, 'string'],
            'string' => [new StringParameter, 'test value'],
            'int' => [new IntParameter, 3],
            'true for bool' => [new BoolParameter, true],
            'false for bool' => [new BoolParameter, false],
            'true' => [new TrueParameter, true],
            'false' => [new FalseParameter, false],
            'float' => [new FloatParameter, 3.141],
            'array' => [new ArrayParameter, []],
            'stdClass for object' => [new ObjectParameter, new stdClass],
            'stdClass for object of type' => [new ObjectParameter(\stdClass::class), new stdClass],
            'int for scalar' => [new ScalarParameter, 34],
            'float for scalar' => [new ScalarParameter, 34.4],
            'string for scalar' => [new ScalarParameter, 'string'],
            'numeric-string for numeric' => [new NumericParameter, '3.32'],
            'int for numeric' => [new NumericParameter, 3],
            'float for numeric' => [new NumericParameter, 3.45],
            'callable-string for callable' => [new CallableParameter, 'array_filter'],
            'array for callable' => [new CallableParameter, [new TestClass, 'noOptionalParameters']],
            'callable-string' => [new CallableStringParameter, 'array_filter'],
            'string for array-key' => [new ArrayKeyParameter, 'key'],
            'int for array-key' => [new ArrayKeyParameter, 1],
            'class-string' => [new ClassStringParameter, stdClass::class],
            'interface for class-string' => [new ClassStringParameter, ArrayAccess::class],
            'trait-string' => [new TraitStringParameter, TestTrait::class],
            'numeric-string' => [new NumericStringParameter, '3.14159'],
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
        );
        $this->assertFalse($docBlockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function invalidParameterTypeValueDataProvider()
    {
        return [
            'int for string' => [new StringParameter, 32],
            'float for int' => [new IntParameter, 3.141],
            '2 for bool' => [new BoolParameter, 2],
            'string for bool' => [new BoolParameter, 'test'],
            '1 for true' => [new TrueParameter, 1],
            '0 for false' => [new FalseParameter, 0],
            'false for true' => [new TrueParameter, false],
            'true for false' => [new FalseParameter, true],
            'int for float' => [new FloatParameter, 3],
            'object for array' => [new ArrayParameter, new stdClass],
            'classname for object' => [new ObjectParameter, stdClass::class],
            'array for scalar' => [new ScalarParameter, []],
            'object for scalar' => [new ScalarParameter, new stdClass],
            'characters for numeric' => [new NumericParameter, 'abc'],
            'object for numeric' => [new NumericParameter, new stdClass],
            'array for numeric' => [new NumericParameter, []],
            'non-callable for callable' => [new CallableParameter, 'array_filterrsf'],
            'non-callable array callable' => [new CallableParameter, [new TestClass, 'methodNotFound']],
            'non-callable string for callable-string' => [new CallableStringParameter, 'nopenoexists'],
            'float for array-key' => [new ArrayKeyParameter, 3.141],
            'class for class-string' => [new ClassStringParameter, new stdClass],
            'trait-string for class-string' => [new ClassStringParameter, TestTrait::class],
            'interface for trait-string' => [new TraitStringParameter, ArrayAccess::class],
            'characters for numeric-string' => [new NumericStringParameter, '3.14159sd'],
            stdClass::class => [new ObjectParameter(stdClass::class), new TestIntersectionClass],
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
        );
        $this->assertTrue($docBlockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function validateParameterReturnsTrueIfParameterIsOfUnionTypeDataProvider()
    {
        return [
            'string for string|int' => [[new StringParameter, new IntParameter], 'test'],
            'int for string|int' => [[new StringParameter, new IntParameter], 23],
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
        );
        $this->assertFalse($docBlockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function invalidUnionTypeParameterValueDataProvider()
    {
        return [
            'float for string|int' => [[new StringParameter, new IntParameter], 3.14159],
            'object for string|int' => [[new StringParameter, new IntParameter], new stdClass],
        ];
    }

    /**
     * @test
     * @dataProvider validateParameterReturnsTrueIfParameterIsArrayOfTypeDataProvider
     */
    public function validateParameterReturnsTrueIfParameterIsArrayOfType($type, $givenValue)
    {
        $docBlockParameter = new UnionParameter(
            '$test',
            [
                new ArrayParameter(new ArrayKeyParameter, $type)
            ],
        );
        $this->assertTrue($docBlockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function validateParameterReturnsTrueIfParameterIsArrayOfTypeDataProvider()
    {
        return [
            'string[] or array<string>' => [[new StringParameter], ['test', 'strings']],
            'array<string|int>' => [[new StringParameter, new IntParameter], [34, 'string', 34]],
            'array<string|int|object>' => [
                [new StringParameter, new ObjectParameter, new IntParameter], [32, new stdClass, 'test']
            ]
        ];
    }

    /**
     * @test
     * @dataProvider validateParameterReturnsFalseIfParameterIsNotArrayOfTypeDataProvider
     */
    public function validateParameterReturnsFalseIfParameterIsNotArrayOfType($type, $givenValue)
    {
        $docBlockParameter = new UnionParameter(
            '$test',
            [
                new ArrayParameter(new ArrayKeyParameter, $type)
            ],
        );
        $this->assertFalse($docBlockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function validateParameterReturnsFalseIfParameterIsNotArrayOfTypeDataProvider()
    {
        return [
            'array<string> having 1 valid' => [[new StringParameter], [3.12, 23, 'string']],
            'array<string|int> having 1 valid' => [[new StringParameter, new IntParameter], [32, 34.5, [], 34.2]],
            'array<string|int> having 0 valid' => [[new StringParameter, new IntParameter], [34.5, [], 34.2]],
            'object for array<string|int>' => [[new StringParameter, new IntParameter], new stdClass],
        ];
    }

    /**
     * @test
     * @dataProvider validateParameterReturnsTrueIfParameterIsListOfTypeDataProvider
     */
    public function validateParameterReturnsTrueIfParameterIsListOfType($types, $givenValue)
    {
        $docBlockParameter = new UnionParameter(
            '$test',
            [
                new ListParameter($types)
            ]
        );
        $this->assertTrue($docBlockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function validateParameterReturnsTrueIfParameterIsListOfTypeDataProvider()
    {
        return [
            'list' => [[new MixedParameter], ['test', 23, new \stdClass()]],
            'list<int>' => [[new IntParameter], [23, 12, 55]],
            'list<class-string<stdClass>>' => [
                [new TypedClassStringParameter([\stdClass::class])],
                [\stdClass::class, TestClass::class]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider validateParameterReturnsFalseIfParameterIsNotListOfTypeDataProvider
     */
    public function validateParameterReturnsFalseIfParameterIsNotListOfType($types, $givenValue)
    {
        $docBlockParameter = new UnionParameter(
            '$test',
            [
                new ListParameter($types)
            ]
        );
        $this->assertFalse($docBlockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function validateParameterReturnsFalseIfParameterIsNotListOfTypeDataProvider()
    {
        return [
            'list with values not starting at index 0' => [
                [new MixedParameter],
                [1 => 'test', 2 => 23, 3 => new \stdClass()]
            ],
            'list<int> a value that is not int' => [[new IntParameter], [23, new \stdClass()]],
            'list<int> a value with string key' => [[new IntParameter], [23, 'test' => 32]],
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
                new ArrayParameter($keyType, $type)
            ],
        );
        $this->assertTrue($docBlockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function validateParameterReturnsTrueIfArrayKeyPairIsOfTypeDataProvider()
    {
        return [
            'array<int, string>' => [new IntParameter, [new StringParameter], ['just', 'a', 'list']],
            'array<string, string>' => [
                new StringParameter,
                [new StringParameter],
                ['a' => 'just', 'b' => 'a', 'c' => 'list']
            ],
            'array<string, string|int>' => [
                new StringParameter,
                [new StringParameter, new IntParameter],
                ['a' => 'just', 'b' => 2, 'c' => 'list']
            ],
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
                new ArrayParameter($keyType, $type)
            ],
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
                new IntParameter,
                [new StringParameter],
                ['a' => 'just', 2 => 'a', 'c' => 'list']
            ],
            'invalid element in array<int, string>' => [
                new IntParameter,
                [new StringParameter],
                ['just', 3.1441, 'list']
            ],
            'invalid key in array<string, string>' => [
                new StringParameter,
                [new StringParameter],
                ['just', 'a', 'list']
            ],
            'invalid element in array<int, string>' => [
                new IntParameter,
                [new StringParameter],
                ['just', 2, 'list']
            ],
            'invalid key in array<string, string|int>' => [
                new StringParameter,
                [new StringParameter, new IntParameter],
                [0 => 'just', 'b' => 2, 'c' => 'list']
            ],
            'invalid value for array<int, string>' => [
                new IntParameter,
                [new StringParameter],
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
                new IntersectionParameter($types)
            ],
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
            new IntParameter,
            [
                new IntersectionParameter($types)
            ],
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
                [new ObjectParameter(ArrayAccess::class), new ObjectParameter(TestIntersectionClass::class)],
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
                new IntersectionParameter($types),
            ],
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
            new ArrayKeyParameter,
            [
                new IntersectionParameter($types)
            ],
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
                [new ObjectParameter(TestIntersectionClass::class), new ObjectParameter(Iterator::class)],
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
            $classTypes,
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
            $classTypes,
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
}
