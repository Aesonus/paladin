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

use Aesonus\Paladin\DocblockParameters\TypedClassStringParameter;
use Aesonus\Paladin\Exceptions\ParseException;
use Aesonus\Paladin\Parsing\PsalmClassStringParser;
use ArrayObject;
use stdClass;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class PsalmClassStringParserTest extends ParsingTestCase
{
    /**
     *
     * @var PsalmClassStringParser
     */
    public $testObj;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testObj = new PsalmClassStringParser;
    }

    /**
     * @test
     */
    public function parseReturnsTypedClassStringParameterForSimpleTypedClassString()
    {
        $this->expectGetUseContextMethod($this->once());
        $this->mockUseContext->expects($this->once())->method('getUsedClass')
            ->with(stdClass::class)
            ->willReturnArgument(0);
        $expected = new TypedClassStringParameter([stdClass::class]);
        $actual = $this->testObj->parse($this->mockParser, 'class-string<stdClass>');
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function parseReturnsTypedClassStringParameterForUnionTypedClassString()
    {
        $this->expectGetUseContextMethod($this->exactly(2));
        $this->mockUseContext->expects($this->exactly(2))->method('getUsedClass')
            ->withConsecutive([stdClass::class], [ArrayObject::class])
            ->willReturnOnConsecutiveCalls(stdClass::class, ArrayObject::class);
        $expected = new TypedClassStringParameter([stdClass::class, ArrayObject::class]);
        $actual = $this->testObj->parse($this->mockParser, 'class-string<stdClass|ArrayObject>');
        $this->assertEquals($expected, $actual);
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
            'invalid simple type' => ['class-string'],
            'psalm array type' => ['array<int>'],
            'psr array type' => ['array[]'],
            'list type' => ['list<int>'],
            'class-string inside list' => ['list<class-string<stdClass>>'],
            'object like array' => ['array{key: mixed}'],
        ];
    }
}
