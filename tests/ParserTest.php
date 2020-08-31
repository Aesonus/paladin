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

use Aesonus\Paladin\Contracts\ParameterInterface;
use Aesonus\Paladin\Contracts\DocblockParamSplitterInterface;
use Aesonus\Paladin\Contracts\TypeStringParsingInterface;
use Aesonus\Paladin\DocblockParameters\UnionParameter;
use Aesonus\Paladin\Exceptions\ParseException;
use Aesonus\Paladin\Parser;
use Aesonus\Paladin\Parsing\AtomicParser;
use Aesonus\Paladin\Parsing\TypeStringSplitter;
use Aesonus\Paladin\Parsing\PsalmArrayParser;
use Aesonus\Paladin\Parsing\PsalmClassStringParser;
use Aesonus\Paladin\Parsing\PsalmListParser;
use Aesonus\Paladin\Parsing\PsrArrayParser;
use Aesonus\Paladin\TypeLinter;
use Aesonus\Paladin\UseContext;
use Aesonus\TestLib\BaseTestCase;
use Aesonus\Tests\Fixtures\ParserContextClass;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use RuntimeException;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class ParserTest extends BaseTestCase
{
    /**
     *
     * @var Parser
     */
    public $testObj;

    public $useContext;

    /**
     *
     * @var MockObject|DocblockParamSplitterInterface
     */
    protected $mockDocParamSplitter;

    /**
     *
     * @var MockObject|TypeLinter
     */
    protected $mockTypeLinter;

    /**
     *
     * @var MockObject|TypeStringSplitter
     */
    protected $mockUnionTypeSplitter;

    /**
     *
     * @var MockObject|AtomicParser
     */
    protected $mockAtomicParser;

    /**
     *
     * @var MockObject|PsalmArrayParser
     */
    protected $mockPsalmArrayParser;

    /**
     *
     * @var MockObject|PsalmListParser
     */
    protected $mockPsalmListParser;

    /**
     *
     * @var MockObject|PsrArrayParser
     */
    protected $mockPsrArrayParser;

    /**
     *
     * @var MockObject|PsalmClassStringParser
     */
    protected $mockPsalmClassStringParser;

    /**
     *
     * @var TypeStringParsingInterface[]
     */
    protected $mockParsers;

    protected function setUp(): void
    {
        $this->useContext = new UseContext(ParserContextClass::class);
        $this->setUpMocks();
        $this->testObj = new Parser(
            $this->useContext,
            $this->mockDocParamSplitter,
            $this->mockTypeLinter,
            $this->mockUnionTypeSplitter,
            $this->mockParsers
        );
    }

    private function setUpMocks(): void
    {
        $this->mockTypeLinter = $this->getMockBuilder(TypeLinter::class)
            ->getMock();
        $this->mockUnionTypeSplitter = $this->getMockBuilder(TypeStringSplitter::class)
            ->getMock();
        $this->mockAtomicParser = $this->getMockBuilder(AtomicParser::class)
            ->getMock();
        $this->mockPsalmArrayParser = $this->getMockBuilder(PsalmArrayParser::class)
            ->getMock();
        $this->mockPsalmListParser = $this->getMockBuilder(PsalmListParser::class)
            ->getMock();
        $this->mockPsrArrayParser = $this->getMockBuilder(PsrArrayParser::class)
            ->getMock();
        $this->mockPsalmClassStringParser = $this->getMockBuilder(PsalmClassStringParser::class)
            ->getMock();
        $this->mockParsers = [
            $this->mockAtomicParser,
            $this->mockPsalmArrayParser,
            $this->mockPsalmListParser,
            $this->mockPsrArrayParser,
            $this->mockPsalmClassStringParser
        ];
        $this->mockDocParamSplitter = $this->getMockBuilder(DocblockParamSplitterInterface::class)
            ->getMockForAbstractClass();
    }

    private function expectMockParsersToThrowExceptionExcept(MockObject ...$except)
    {
        $throwsExceptions = array_filter($this->mockParsers, fn ($mock) => !in_array($mock, $except));
        foreach ($throwsExceptions as $mock) {
            $mock->expects($this->any())->method('parse')->willThrowException(new ParseException());
        }
    }

    private function expectTypeSplitterCalls(array ...$args): InvocationMocker
    {
        $consecutiveArgs = [];
        $consecutiveReturns = [];

        foreach ($args as $argSet) {
            list($expectedArg, $willReturn) = $argSet;
            $consecutiveArgs[] = [$expectedArg];
            $consecutiveReturns[] = $willReturn;
        }
        return $this->mockUnionTypeSplitter->expects($this->exactly(count($args)))->method('split')
            ->withConsecutive(...$consecutiveArgs)
            ->willReturnOnConsecutiveCalls(...$consecutiveReturns);
    }

    private function expectDocParamSplitterCall($arg, $return): InvocationMocker
    {
        return $this->mockDocParamSplitter->expects($this->once())
            ->method('getDocblockParameters')
            ->with($arg)
            ->willReturn($return);
    }

    private function expectMockParserCallFor(
        MockObject $mock,
        InvocationOrder $invocationRule,
        ...$consecutiveArgs
    ) {
        return $mock->expects($invocationRule)->method('parse')
            ->withConsecutive(...array_map(fn ($arg) => [$this->testObj, $arg], $consecutiveArgs));
    }

    private function newMockParserReturnValue(): ParameterInterface
    {
        return new UnionParameter('test', []);
    }

    /**
     * @test
     */
    public function getUseContextGetsTheUseContext()
    {
        $this->assertSame($this->useContext, $this->testObj->getUseContext());
    }

    /**
     * @test
     */
    public function getDocBlockValidatorsCallsMethodsToParseDocBlockAndReturnsValidators()
    {
        $docblock = <<<'php'
                /**
                 *
                 * @param list $testList
                 * @param class-string $testClassString
                 * @param array $testArray
                */
                php;
        $expectedParserReturn = $this->newMockParserReturnValue();
        $this->expectDocParamSplitterCall(
            $docblock,
            [
                ['name' => '$testList', 'type' => 'list'],
                ['name' => '$testClassString', 'type' => 'class-string'],
                ['name' => '$testArray', 'type' => 'array'],
            ]
        );
        $this->expectTypeSplitterCalls(
            ['list', ['list']],
            ['class-string', ['class-string']],
            ['array', ['array']]
        );
        $this->expectMockParserCallFor($this->mockAtomicParser, $this->exactly(3), 'list', 'class-string', 'array')
            ->willReturn($expectedParserReturn);

        $this->expectMockParsersToThrowExceptionExcept($this->mockAtomicParser);

        $actual = $this->testObj->getDocBlockValidators($docblock, 1);
        $expected = [
            new UnionParameter('$testList', [$expectedParserReturn]),
            new UnionParameter('$testClassString', [$expectedParserReturn]),
            new UnionParameter('$testArray', [$expectedParserReturn])
        ];

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function getDocBlockValidatorsCallsMethodsToParseDocBlockAndReturnsValidatorsUsingUnionType()
    {
        $docblock = <<<'php'
                /**
                 *
                 * @param int|string $testIntString Is a string scalar type
                */
                php;
        $expectedParserReturn = $this->newMockParserReturnValue();
        $this->expectDocParamSplitterCall(
            $docblock,
            [
                ['name' => '$testIntString', 'type' => 'int|string'],
            ]
        );
        $this->expectTypeSplitterCalls(['int|string', ['int', 'string']]);

        $this->expectMockParserCallFor($this->mockAtomicParser, $this->exactly(2), 'int', 'string')
            ->willReturn($expectedParserReturn);

        $this->expectMockParsersToThrowExceptionExcept($this->mockAtomicParser);

        $actual = $this->testObj->getDocBlockValidators($docblock, 1)[0];
        $this->assertEquals(
            new UnionParameter(
                '$testIntString',
                [$expectedParserReturn, $expectedParserReturn]
            ),
            $actual
        );
    }

    /**
     * @test
     * @dataProvider getDocBlockValidatorsCallsMethodsToParseDocBlockAndReturnsValidatorsUsingCorrectParserDataProvider
     */
    public function getDocBlockValidatorsCallsMethodsToParseDocBlockAndReturnsValidatorsUsingCorrectParser($parser)
    {
        $docblock = <<<'php'
                /**
                 *
                 * @param type-string $testParam Is a string scalar type
                */
                php;
        $expectedParserReturn = $this->newMockParserReturnValue();

        $this->expectTypeSplitterCalls(['type-string', ['type-string']]);
        $this->expectDocParamSplitterCall(
            $docblock,
            [
                ['name' => '$testParam', 'type' => 'type-string'],
            ]
        );
        $this->expectMockParserCallFor($this->$parser, $this->once(), 'type-string')
            ->willReturn($expectedParserReturn);

        $this->expectMockParsersToThrowExceptionExcept($this->$parser);

        $actual = $this->testObj->getDocBlockValidators($docblock, 1)[0];
        $this->assertEquals(new UnionParameter('$testParam', [$expectedParserReturn]), $actual);
    }

    /**
     * Data Provider
     */
    public function getDocBlockValidatorsCallsMethodsToParseDocBlockAndReturnsValidatorsUsingCorrectParserDataProvider()
    {
        return [
            'atomicParser' => ['mockAtomicParser'],
            'psrArrayParser' => ['mockPsrArrayParser'],
            'psalmArrayParser' => ['mockPsalmArrayParser'],
            'psalmListParser' => ['mockPsalmListParser'],
            'psalmClassStringParser' => ['mockPsalmClassStringParser'],
        ];
    }
    /**
     * @test
     */
    public function getDocblockCallsLintCheckOnTypeLinter()
    {
        $docblock = <<<'php'
                /**
                 *
                 * @param (int|string[] $param
                 */
                php;
        $this->expectDocParamSplitterCall(
            $docblock,
            [
                ['name' => '$param', 'type' => '(int|string[]'],
            ]
        );
        $this->mockTypeLinter->expects($this->once())->method('lintCheck')
            ->with($this->equalTo('$param'), $this->isType('string'))
            ->willThrowException(new RuntimeException);
        $this->expectException(RuntimeException::class);
        $this->testObj->getDocBlockValidators($docblock, 1);
    }

    /**
     * @test
     */
    public function getDocBlockValidatorsThrowsParseExceptionIfTypestringIsNotKnown()
    {
        $docblock = <<<'php'
                /**
                 *
                 * @param not-good $param
                 */
                php;
        $this->expectDocParamSplitterCall(
            $docblock,
            [
                ['name' => '$param', 'type' => 'not-good'],
            ]
        );
        $this->expectTypeSplitterCalls(['not-good', ['not-good']]);
        $this->expectMockParsersToThrowExceptionExcept();

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('not-good');

        $this->testObj->getDocBlockValidators($docblock);
    }
}
