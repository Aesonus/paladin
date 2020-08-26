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

use Aesonus\Paladin\DocBlock\ArrayKeyParameter;
use Aesonus\Paladin\DocBlock\ArrayParameter;
use Aesonus\Paladin\DocBlock\BoolParameter;
use Aesonus\Paladin\DocBlock\CallableParameter;
use Aesonus\Paladin\DocBlock\CallableStringParameter;
use Aesonus\Paladin\DocBlock\ClassStringParameter;
use Aesonus\Paladin\DocBlock\FalseParameter;
use Aesonus\Paladin\DocBlock\FloatParameter;
use Aesonus\Paladin\DocBlock\IntParameter;
use Aesonus\Paladin\DocBlock\IterableParameter;
use Aesonus\Paladin\DocBlock\MixedParameter;
use Aesonus\Paladin\DocBlock\NullParameter;
use Aesonus\Paladin\DocBlock\NumericParameter;
use Aesonus\Paladin\DocBlock\NumericStringParameter;
use Aesonus\Paladin\DocBlock\ObjectParameter;
use Aesonus\Paladin\DocBlock\ResourceParameter;
use Aesonus\Paladin\DocBlock\ScalarParameter;
use Aesonus\Paladin\DocBlock\StringParameter;
use Aesonus\Paladin\DocBlock\TraitStringParameter;
use Aesonus\Paladin\DocBlock\TrueParameter;
use Aesonus\TestLib\BaseTestCase;
use Aesonus\Tests\Fixtures\TestClass;
use Aesonus\Tests\Fixtures\TestTrait;
use ArrayAccess;
use ArrayObject;
use stdClass;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class AbstractAtomicParameterTest extends BaseTestCase
{
    /**
     * @test
     * @dataProvider validateReturnsTrueIfParameterIsOfSimpleTypeDataProvider
     */
    public function validateReturnsTrueIfParameterIsOfSimpleType($parameterClass, $givenValue)
    {
        $docBlockParameter = new $parameterClass;
        $this->assertTrue($docBlockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function validateReturnsTrueIfParameterIsOfSimpleTypeDataProvider()
    {
        return [
            'mixed' => [MixedParameter::class, 32],
            'int for mixed ' => [MixedParameter::class, 34],
            'float for mixed' => [MixedParameter::class, 34.4],
            'string for mixed' => [MixedParameter::class, 'string'],
            'string' => [StringParameter::class, 'test value'],
            'int' => [IntParameter::class, 3],
            'true for bool' => [BoolParameter::class, true],
            'false for bool' => [BoolParameter::class, false],
            'true' => [TrueParameter::class, true],
            'false' => [FalseParameter::class, false],
            'float' => [FloatParameter::class, 3.141],
            'array' => [ArrayParameter::class, []],
            'stdClass for object' => [ObjectParameter::class, new stdClass],
            'int for scalar' => [ScalarParameter::class, 34],
            'float for scalar' => [ScalarParameter::class, 34.4],
            'string for scalar' => [ScalarParameter::class, 'string'],
            'numeric-string for numeric' => [NumericParameter::class, '3.32'],
            'int for numeric' => [NumericParameter::class, 3],
            'float for numeric' => [NumericParameter::class, 3.45],
            'callable-string for callable' => [CallableParameter::class, 'array_filter'],
            'array for callable' => [CallableParameter::class, [new TestClass, 'noOptionalParameters']],
            'callable-string' => [CallableStringParameter::class, 'array_filter'],
            'string for array-key' => [ArrayKeyParameter::class, 'key'],
            'int for array-key' => [ArrayKeyParameter::class, 1],
            'class-string' => [ClassStringParameter::class, stdClass::class],
            'interface for class-string' => [ClassStringParameter::class, ArrayAccess::class],
            'trait-string' => [TraitStringParameter::class, TestTrait::class],
            'numeric-string' => [NumericStringParameter::class, '3.14159'],
            'array for iterable' => [IterableParameter::class, []],
            'ArrayObject for iterable' => [IterableParameter::class, new ArrayObject],
            'null' => [NullParameter::class, null],
            'resource' => [ResourceParameter::class, fopen('php://output', 'w')],
        ];
    }

    /**
     * @test
     * @dataProvider invalidParameterTypeValueDataProvider
     */
    public function validateReturnsFalseIfParameterIsNotOfSimpleType($parameterClass, $givenValue)
    {
        $docBlockParameter = new $parameterClass;
        $this->assertFalse($docBlockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function invalidParameterTypeValueDataProvider()
    {
        return [
            'int for string' => [StringParameter::class, 32],
            'float for int' => [IntParameter::class, 3.141],
            '2 for bool' => [BoolParameter::class, 2],
            'string for bool' => [BoolParameter::class, 'test'],
            '1 for true' => [TrueParameter::class, 1],
            '0 for false' => [FalseParameter::class, 0],
            'false for true' => [TrueParameter::class, false],
            'true for false' => [FalseParameter::class, true],
            'int for float' => [FloatParameter::class, 3],
            'object for array' => [ArrayParameter::class, new stdClass],
            'classname for object' => [ObjectParameter::class, stdClass::class],
            'array for scalar' => [ScalarParameter::class, []],
            'object for scalar' => [ScalarParameter::class, new stdClass],
            'characters for numeric' => [NumericParameter::class, 'abc'],
            'object for numeric' => [NumericParameter::class, new stdClass],
            'array for numeric' => [NumericParameter::class, []],
            'non-callable for callable' => [CallableParameter::class, 'array_filterrsf'],
            'non-callable array callable' => [CallableParameter::class, [new TestClass, 'methodNotFound']],
            'non-callable string for callable-string' => [CallableStringParameter::class, 'nopenoexists'],
            'float for array-key' => [ArrayKeyParameter::class, 3.141],
            'class for class-string' => [ClassStringParameter::class, new stdClass],
            'trait-string for class-string' => [ClassStringParameter::class, TestTrait::class],
            'interface for trait-string' => [TraitStringParameter::class, ArrayAccess::class],
            'characters for numeric-string' => [NumericStringParameter::class, '3.14159sd'],
            'int for iterable' => [IterableParameter::class, 1],
            'not null' => [NullParameter::class, 0],
            'false for resource' => [ResourceParameter::class, false],
        ];
    }
}