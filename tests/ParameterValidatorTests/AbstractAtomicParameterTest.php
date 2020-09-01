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
namespace Aesonus\Tests\ParameterValidatorTests;

use Aesonus\Paladin\ParameterValidators\ArrayKeyParameter;
use Aesonus\Paladin\ParameterValidators\ArrayParameter;
use Aesonus\Paladin\ParameterValidators\BoolParameter;
use Aesonus\Paladin\ParameterValidators\CallableParameter;
use Aesonus\Paladin\ParameterValidators\CallableStringParameter;
use Aesonus\Paladin\ParameterValidators\ClassStringParameter;
use Aesonus\Paladin\ParameterValidators\FalseParameter;
use Aesonus\Paladin\ParameterValidators\FloatParameter;
use Aesonus\Paladin\ParameterValidators\IntParameter;
use Aesonus\Paladin\ParameterValidators\IterableParameter;
use Aesonus\Paladin\ParameterValidators\MixedParameter;
use Aesonus\Paladin\ParameterValidators\NullParameter;
use Aesonus\Paladin\ParameterValidators\NumericParameter;
use Aesonus\Paladin\ParameterValidators\NumericStringParameter;
use Aesonus\Paladin\ParameterValidators\ObjectParameter;
use Aesonus\Paladin\ParameterValidators\ResourceParameter;
use Aesonus\Paladin\ParameterValidators\ScalarParameter;
use Aesonus\Paladin\ParameterValidators\StringParameter;
use Aesonus\Paladin\ParameterValidators\TraitStringParameter;
use Aesonus\Paladin\ParameterValidators\TrueParameter;
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

    /**
     * @test
     * @dataProvider toStringReturnsStringRepresentationOfTypeDataProvider
     */
    public function toStringReturnsStringRepresentationOfType($parameterClass, $expected)
    {
        $this->assertSame($expected, (string)(new $parameterClass));
    }

    /**
     * Data Provider
     */
    public function toStringReturnsStringRepresentationOfTypeDataProvider()
    {
        return [
            MixedParameter::class => [MixedParameter::class, 'mixed'],
            StringParameter::class => [StringParameter::class, 'string'],
            IntParameter::class => [IntParameter::class, 'int'],
            BoolParameter::class => [BoolParameter::class, 'bool'],
            TrueParameter::class => [TrueParameter::class, 'true'],
            FalseParameter::class => [FalseParameter::class, 'false'],
            FloatParameter::class => [FloatParameter::class, 'float'],
            ArrayParameter::class => [ArrayParameter::class, 'array'],
            ObjectParameter::class => [ObjectParameter::class, 'object'],
            ScalarParameter::class => [ScalarParameter::class, 'scalar'],
            NumericParameter::class => [NumericParameter::class, 'numeric'],
            CallableParameter::class => [CallableParameter::class, 'callable'],
            CallableStringParameter::class => [CallableStringParameter::class, 'callable-string'],
            ArrayKeyParameter::class => [ArrayKeyParameter::class, 'array-key'],
            ClassStringParameter::class => [ClassStringParameter::class, 'class-string'],
            TraitStringParameter::class => [TraitStringParameter::class, 'trait-string'],
            NumericStringParameter::class => [NumericStringParameter::class, 'numeric-string'],
            IterableParameter::class => [IterableParameter::class, 'iterable'],
            NullParameter::class => [NullParameter::class, 'null'],
            ResourceParameter::class => [ResourceParameter::class, 'resource'],
        ];
    }
}
