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

use Aesonus\Paladin\DocBlock\ArrayKeyParameter;
use Aesonus\Paladin\DocBlock\ArrayParameter;
use Aesonus\Paladin\DocBlock\FloatParameter;
use Aesonus\Paladin\DocBlock\IntParameter;
use Aesonus\Paladin\Parsing\PsrArrayParser;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class PsrArrayParserTest extends ParsingTestCase
{
    /**
     *
     * @var PsrArrayParser
     */
    public $testObj;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testObj = new PsrArrayParser();
    }

    /**
     * @test
     */
    public function parseReturnsArrayParameterForNonUnionArrayType()
    {
        $expectedArrayTypes = [new IntParameter()];
        $expected = new ArrayParameter(new ArrayKeyParameter(), $expectedArrayTypes);
        $this->mockParser->expects($this->once())->method('parseTypeString')->with('int')
            ->willReturn($expectedArrayTypes);
        $actual = $this->testObj->parse($this->mockParser, 'int[]');
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function parseReturnsArrayParameterForUnionArrayType()
    {
        $expectedArrayTypes = [new IntParameter(), new FloatParameter()];
        $expected = new ArrayParameter(new ArrayKeyParameter(), $expectedArrayTypes);
        $this->mockParser->expects($this->once())->method('parseTypeString')->with('int|float')
            ->willReturn($expectedArrayTypes);
        $actual = $this->testObj->parse($this->mockParser, '(int|float)[]');
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider parseReturnsArrayParameterForComplexUnionArrayTypeDataProvider
     */
    public function parseReturnsArrayParameterForComplexUnionArrayType($typeString, $subTypeString)
    {
        $expectedArrayTypes = [new ArrayParameter()];
        $expected = new ArrayParameter(new ArrayKeyParameter(), $expectedArrayTypes);

        $this->mockParser->expects($this->once())->method('parseTypeString')->with($subTypeString)
            ->willReturn($expectedArrayTypes);

        $actual = $this->testObj->parse($this->mockParser, $typeString);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Data Provider
     */
    public function parseReturnsArrayParameterForComplexUnionArrayTypeDataProvider()
    {
        return [
            '(int|float)[][]' => ['(int|float)[][]', '(int|float)[]'],
            '((int|float)[])[]' => ['((int|float)[])[]', '(int|float)[]'],
            'array<int, string>[]' => ['array<int, string>[]', 'array<int, string>'],
            'array<int, string[]>[]' => ['array<int, string[]>[]', 'array<int, string[]>'],
            'array<int, (string|int)[]>[]' => ['array<int, (string|int)[]>[]', 'array<int, (string|int)[]>'],
        ];
    }
}
