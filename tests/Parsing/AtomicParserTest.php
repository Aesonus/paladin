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
namespace Aesonus\Tests\Parsing;

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
use Aesonus\Paladin\ParameterValidators\ListParameter;
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
use Aesonus\Paladin\Exceptions\ParseException;
use Aesonus\Paladin\Parsing\AtomicParser;
use stdClass;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class AtomicParserTest extends ParsingTestCase
{
    /**
     *
     * @var AtomicParser
     */
    public $testObj;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testObj = new AtomicParser;
    }

    /**
     * @test
     * @dataProvider parseReturnsAtomicTypeForTypestringDataProvider
     */
    public function parseReturnsAtomicTypeForTypestring($typeString, $expectedClass)
    {
        $actual = $this->testObj->parse($this->mockParser, $typeString);
        $this->assertInstanceOf($expectedClass, $actual);
    }

    /**
     * Data Provider
     */
    public function parseReturnsAtomicTypeForTypestringDataProvider()
    {
        return [
            'int' => ['int', IntParameter::class],
            'integer' => ['integer', IntParameter::class],
            'float' => ['float', FloatParameter::class],
            'double' => ['double', FloatParameter::class],
            'bool' => ['bool', BoolParameter::class],
            'boolean' => ['boolean', BoolParameter::class],
            'string' => ['string', StringParameter::class],
            'class-string' => ['class-string', ClassStringParameter::class],
            'trait-string' => ['trait-string', TraitStringParameter::class],
            'callable-string' => ['callable-string', CallableStringParameter::class],
            'numeric-string' => ['numeric-string', NumericStringParameter::class],
            'array-key' => ['array-key', ArrayKeyParameter::class],
            'numeric' => ['numeric', NumericParameter::class],
            'scalar' => ['scalar', ScalarParameter::class],
            'resource' => ['resource', ResourceParameter::class],
            'callable' => ['callable', CallableParameter::class],
            'null' => ['null', NullParameter::class],
            'true' => ['true', TrueParameter::class],
            'false' => ['false', FalseParameter::class],
            'mixed' => ['mixed', MixedParameter::class],
            'iterable' => ['iterable', IterableParameter::class],
        ];
    }

    /**
     * @test
     */
    public function parseReturnsGenericArrayParameterForArrayTypestring()
    {
        $actual = $this->testObj->parse($this->mockParser, 'array');
        $this->assertEquals(new ArrayParameter(), $actual);
    }

    /**
     * @test
     */
    public function parseReturnsGenericListParameterForArrayTypestring()
    {
        $actual = $this->testObj->parse($this->mockParser, 'list');
        $this->assertEquals(new ListParameter(), $actual);
    }

    /**
     * @test
     */
    public function parseReturnsGenericObjectParameterForObjectTypestring()
    {
        $this->expectGetUseContextMethod($this->never());

        $actual = $this->testObj->parse($this->mockParser, 'object');
        $this->assertEquals(new ObjectParameter(), $actual);
    }

    /**
     * @test
     */
    public function parseReturnsObjectParameterOfClassStringForTypestringThatIsAClass()
    {
        $this->expectGetUseContextMethod($this->once());
        $this->mockUseContext->expects($this->once())->method('getUsedClass')
            ->with(stdClass::class)
            ->willReturnArgument(0);
        $actual = $this->testObj->parse($this->mockParser, stdClass::class);
        $this->assertEquals(new ObjectParameter(stdClass::class), $actual);
    }

    /**
     * @test
     * @dataProvider parseThrowsParseExceptionIfTypestringIsNotKnownDataProvider
     */
    public function parseThrowsParseExceptionIfTypestringIsNotKnown($typeString)
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage("Unknown type: '$typeString'");
        $this->testObj->parse($this->mockParser, $typeString);
    }

    /**
     * Data Provider
     */
    public function parseThrowsParseExceptionIfTypestringIsNotKnownDataProvider()
    {
        return [
            'invalid simple type' => ['no-good'],
            'psalm array type' => ['array<int>'],
            'psr array type' => ['array[]'],
            'list type' => ['list<int>'],
            'class-string type' => ['class-string<stdClass>'],
            'object like array' => ['array{key: mixed}'],
        ];
    }
}
