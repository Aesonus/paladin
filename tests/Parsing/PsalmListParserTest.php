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

use Aesonus\Paladin\DocblockParameters\ListParameter;
use Aesonus\Paladin\DocblockParameters\MixedParameter;
use Aesonus\Paladin\Exceptions\ParseException;
use Aesonus\Paladin\Parsing\PsalmListParser;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class PsalmListParserTest extends ParsingTestCase
{
    /**
     *
     * @var PsalmListParser
     */
    public $testObj;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testObj = new PsalmListParser;
    }

    /**
     * @test
     * @dataProvider parseReturnsListParameterForParameterizedListDataProvider
     */
    public function parseReturnsListParameterForParameterizedList($typeString, $expectedParseTypesArg)
    {
        $expectedReturn = [new MixedParameter];
        $expected = new ListParameter($expectedReturn);
        $this->mockParser->expects($this->once())->method('parseTypeString')
            ->with($expectedParseTypesArg)
            ->willReturn($expectedReturn);
        $actual = $this->testObj->parse($this->mockParser, $typeString);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Data Provider
     */
    public function parseReturnsListParameterForParameterizedListDataProvider()
    {
        return [
            'simple type' => ['list<stdClass>', 'stdClass'],
            'union type' => ['list<stdClass|string>', 'stdClass|string'],
            'class-string type' => ['list<class-string<stdClass>>', 'class-string<stdClass>'],
            'psalm array type' => ['list<array<int, int|string[]>>', 'array<int, int|string[]>'],
        ];
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
            'invalid simple type' => ['list'],
            'psalm array type' => ['array<int>'],
            'list inside psalm array type' => ['array<list<int>>'],
            'psr array type' => ['array[]'],
            'class-string type' => ['class-string<stdClass>'],
            'object like array' => ['array{key: mixed}'],
        ];
    }
}
