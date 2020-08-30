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

use Aesonus\Paladin\DocBlock\IntParameter;
use Aesonus\Paladin\DocBlock\ObjectLikeArrayParameter;
use Aesonus\Paladin\DocBlock\UnionParameter;
use Aesonus\Paladin\Exceptions\ParseException;
use Aesonus\Paladin\Parsing\ObjectLikeArrayParser;
use Aesonus\Paladin\Parsing\ParameterStringSplitter;
use PHPUnit\Framework\MockObject\MockObject;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class ObjectLikeArrayParserTest extends ParsingTestCase
{
    /**
     *
     * @var ObjectLikeArrayParser
     */
    public $testObj;

    /**
     *
     * @var MockObject|ParameterStringSplitter
     */
    protected $mockStringSplitter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockStringSplitter = $this->getMockBuilder(ParameterStringSplitter::class)
            ->getMock();
        $this->testObj = new ObjectLikeArrayParser($this->mockStringSplitter);
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
            'simple type' => ['array'],
            'psr array type' => ['array[]'],
            'list type' => ['list<int>'],
            'class-string type' => ['class-string<stdClass>'],
            'object like array inside list' => ['list<array{key:int}>']
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsObjectLikeArrayParameterWithRequiredKeysDataProvider
     */
    public function parseReturnsObjectLikeArrayParameterWithRequiredKeys(
        $typeString,
        $expectedSplitterArgs,
        $expectedSplitterReturns,
        $expectedParseTypeStringArgs
    ) {
        $expectedType = new ObjectLikeArrayParameter(
            [
                'string' => new UnionParameter('value', [new IntParameter]),
                0 => new UnionParameter('value', [new IntParameter]),
            ]
        );
        $this->mockStringSplitter->expects($this->exactly(count($expectedSplitterArgs)))->method('split')
            ->withConsecutive(
                ...$expectedSplitterArgs
            )
            ->willReturnOnConsecutiveCalls(...$expectedSplitterReturns);
        $this->mockParser->expects($this->exactly(2))->method('parseTypeString')
            ->withConsecutive(...$expectedParseTypeStringArgs)
            ->willReturn([new IntParameter()]);
        $actual = $this->testObj->parse($this->mockParser, $typeString);
        $this->assertEquals($expectedType, $actual);
    }

    /**
     * Data Provider
     */
    public function parseReturnsObjectLikeArrayParameterWithRequiredKeysDataProvider()
    {
        return [
            'with simple param types' => [
                'array{string:int,0:int|string}',
                [
                    ['string:int,0:int|string', ','],
                    ['string:int', ':'],
                    ['0:int|string', ':']
                ],
                [
                    ['string:int', '0:int|string'],
                    ['string', 'int'],
                    ['0', 'int|string']
                ],
                [
                    ['int'],
                    ['int|string']
                ]
            ],
            'with complex param types' => [
                'array{string:array<int>,0:array{0:string,key:int}}',
                [
                    ['string:array<int>,0:array{0:string,key:int}', ','],
                    ['string:array<int>', ':'],
                    ['0:array{0:string,key:int}', ':']
                ],
                [
                    ['string:array<int>', '0:array{0:string,key:int}'],
                    ['string', 'array<int>'],
                    ['0', 'array{0:string,key:int}']
                ],
                [
                    ['array<int>'],
                    ['array{0:string,key:int}']
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsObjectLikeArrayParameterWithOptionalKeysDataProvider
     */
    public function parseReturnsObjectLikeArrayParameterWithOptionalKeys(
        $typeString,
        $expectedSplitterArgs,
        $expectedSplitterReturns,
        $expectedParseTypeStringArgs
    ) {
        $expectedType = new ObjectLikeArrayParameter(
            [],
            [
                'string' => new UnionParameter('value', [new IntParameter]),
                0 => new UnionParameter('value', [new IntParameter]),
            ]
        );
        $this->mockStringSplitter->expects($this->exactly(count($expectedSplitterArgs)))->method('split')
            ->withConsecutive(
                ...$expectedSplitterArgs
            )
            ->willReturnOnConsecutiveCalls(...$expectedSplitterReturns);
        $this->mockParser->expects($this->exactly(2))->method('parseTypeString')
            ->withConsecutive(...$expectedParseTypeStringArgs)
            ->willReturn([new IntParameter()]);
        $actual = $this->testObj->parse($this->mockParser, $typeString);
        $this->assertEquals($expectedType, $actual);
    }

    /**
     * Data Provider
     */
    public function parseReturnsObjectLikeArrayParameterWithOptionalKeysDataProvider()
    {
        return [
            'with simple param types' => [
                'array{string?:int,0?:int|string}',
                [
                    ['string?:int,0?:int|string', ','],
                    ['string?:int', ':'],
                    ['0?:int|string', ':']
                ],
                [
                    ['string?:int', '0?:int|string'],
                    ['string?', 'int'],
                    ['0?', 'int|string']
                ],
                [
                    ['int'],
                    ['int|string']
                ]
            ],
            'with complex param types' => [
                'array{string?:array<int>,0?:array{0:string,key:int}}',
                [
                    ['string?:array<int>,0?:array{0:string,key:int}', ','],
                    ['string?:array<int>', ':'],
                    ['0?:array{0:string,key:int}', ':']
                ],
                [
                    ['string?:array<int>', '0?:array{0:string,key:int}'],
                    ['string?', 'array<int>'],
                    ['0?', 'array{0:string,key:int}']
                ],
                [
                    ['array<int>'],
                    ['array{0:string,key:int}']
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsObjectLikeArrayParameterWithRequiredAndOptionalKeysDataProvider
     */
    public function parseReturnsObjectLikeArrayParameterWithRequiredAndOptionalKeys(
        $typeString,
        $expectedSplitterArgs,
        $expectedSplitterReturns,
        $expectedParseTypeStringArgs
    ) {
        $expectedType = new ObjectLikeArrayParameter(
            ['string' => new UnionParameter('value', [new IntParameter])],
            [0 => new UnionParameter('value', [new IntParameter])]
        );
        $this->mockStringSplitter->expects($this->exactly(count($expectedSplitterArgs)))->method('split')
            ->withConsecutive(
                ...$expectedSplitterArgs
            )
            ->willReturnOnConsecutiveCalls(...$expectedSplitterReturns);
        $this->mockParser->expects($this->exactly(2))->method('parseTypeString')
            ->withConsecutive(...$expectedParseTypeStringArgs)
            ->willReturn([new IntParameter()]);
        $actual = $this->testObj->parse($this->mockParser, $typeString);
        $this->assertEquals($expectedType, $actual);
    }

    /**
     * Data Provider
     */
    public function parseReturnsObjectLikeArrayParameterWithRequiredAndOptionalKeysDataProvider()
    {
        return [
            'with simple param types' => [
                'array{string:int,0?:int|string}',
                [
                    ['string:int,0?:int|string', ','],
                    ['string:int', ':'],
                    ['0?:int|string', ':']
                ],
                [
                    ['string:int', '0?:int|string'],
                    ['string', 'int'],
                    ['0?', 'int|string']
                ],
                [
                    ['int'],
                    ['int|string']
                ]
            ],
            'with complex param types' => [
                'array{string:array<int>,0?:array{0:string,key:int}}',
                [
                    ['string:array<int>,0?:array{0:string,key:int}', ','],
                    ['string:array<int>', ':'],
                    ['0?:array{0:string,key:int}', ':']
                ],
                [
                    ['string:array<int>', '0?:array{0:string,key:int}'],
                    ['string', 'array<int>'],
                    ['0?', 'array{0:string,key:int}']
                ],
                [
                    ['array<int>'],
                    ['array{0:string,key:int}']
                ]
            ]
        ];
    }
}
