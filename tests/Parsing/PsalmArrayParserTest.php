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

use Aesonus\Paladin\DocBlock\ArrayParameter;
use Aesonus\Paladin\DocBlock\IntParameter;
use Aesonus\Paladin\Parsing\PsalmArrayParser;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class PsalmArrayParserTest extends ParsingTestCase
{
    /**
     *
     * @var PsalmArrayParser
     */
    public $testObj;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testObj = new PsalmArrayParser;
    }

    /**
     * @test
     * @dataProvider parseReturnsArrayParameterWithDefaultKeyTypeDataProvider
     */
    public function parseReturnsArrayParameterWithDefaultKeyType($typeString, $expectedParseTypesArg)
    {
        $expectedArrayType = [new IntParameter()];
        $this->mockParser->expects($this->once())->method('parseTypeString')
            ->with($expectedParseTypesArg)
            ->willReturn($expectedArrayType);
        $actual = $this->testObj->parse($this->mockParser, $typeString);
        $this->assertEquals(new ArrayParameter(null, $expectedArrayType), $actual);
    }

    /**
     * Data Provider
     */
    public function parseReturnsArrayParameterWithDefaultKeyTypeDataProvider()
    {
        return [
            'array<int>' => ['array<int>', 'int'],
            'array<int[]>' => ['array<int[]>', 'int[]'],
            'array<int[]|array<string, string>>' =>
                ['array<int[]|array<string, string>>', 'int[]|array<string, string>'],
            'array<list<int>>' => ['array<list<int>>', 'list<int>'],
            'array<class-string<stdClass>>' => ['array<class-string<stdClass>>', 'class-string<stdClass>'],
            'array<array<int, string>>' => ['array<array<int, string>>', 'array<int, string>'],
            'array<int|float>' => ['array<int|float>', 'int|float'],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsArrayParameterWithKeyTypeDataProvider
     */
    public function parseReturnsArrayParameterWithKeyType($typeString, $expectedParseTypesKeyArg, $expectedParseTypesArg)
    {
        $expectedKeyType = new IntParameter();
        $expectedArrayType = [new IntParameter()];
        $this->mockParser->expects($this->exactly(2))->method('parseTypeString')
            ->withConsecutive([$expectedParseTypesKeyArg], [$expectedParseTypesArg])
            ->willReturnOnConsecutiveCalls([$expectedKeyType], $expectedArrayType);
        $actual = $this->testObj->parse($this->mockParser, $typeString);
        $this->assertEquals(new ArrayParameter($expectedKeyType, $expectedArrayType), $actual);
    }

    /**
     * Data Provider
     */
    public function parseReturnsArrayParameterWithKeyTypeDataProvider()
    {
        return [
            'array<string, int>' => ['array<string, int>', 'string' ,' int'],
            'array<int, int[]>' => ['array<int, int[]>', 'int', ' int[]'],
            'array<string, int[]|array<string, string>>' =>
                ['array<string, int[]|array<string, string>>', 'string', ' int[]|array<string, string>'],
            'array<string, list<int>>' => ['array<string, list<int>>', 'string', ' list<int>'],
            'array<string, class-string<stdClass>>' =>
                ['array<string, class-string<stdClass>>', 'string', ' class-string<stdClass>'],
            'array<int, array<int, string>>' =>
                ['array<int, array<int, string>>', 'int' ,' array<int, string>'],
            'array<int, int|float>' => ['array<int, int|float>', 'int',' int|float'],
        ];
    }
}
