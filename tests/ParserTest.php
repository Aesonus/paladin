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
use Aesonus\Paladin\Contracts\TypeStringParsingInterface;
use Aesonus\Paladin\DocBlock\UnionParameter;
use Aesonus\Paladin\Parser;
use Aesonus\Paladin\Parsing\AtomicParser;
use Aesonus\Paladin\Parsing\PsalmArrayParser;
use Aesonus\Paladin\Parsing\PsalmClassStringParser;
use Aesonus\Paladin\Parsing\PsalmListParser;
use Aesonus\Paladin\Parsing\PsrArrayParser;
use Aesonus\Paladin\Parsing\UnionTypeSplitter;
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
     * @var MockObject|TypeLinter
     */
    protected $mockTypeLinter;

    /**
     *
     * @var MockObject|UnionTypeSplitter
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
            $this->mockTypeLinter,
            $this->mockUnionTypeSplitter,
            $this->mockAtomicParser,
            $this->mockPsalmArrayParser,
            $this->mockPsalmListParser,
            $this->mockPsrArrayParser,
            $this->mockPsalmClassStringParser,
        );
    }

    private function setUpMocks(): void
    {
        $this->mockTypeLinter = $this->getMockBuilder(TypeLinter::class)
            ->getMock();
        $this->mockUnionTypeSplitter = $this->getMockBuilder(UnionTypeSplitter::class)
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
    }

    public static function isDocBlockParameter($name, $types)
    {
        return new ConstraintDocBlockParameter($name, $types);
    }

    public static function assertDocBlockParameter($actual, $name, $types): void
    {
        static::assertThat($actual, static::isDocBlockParameter($name, $types));
    }

    private function expectNoMockParserCallsExcept(MockObject ...$except)
    {
        $neverExpected = array_filter($this->mockParsers, fn ($mock) => !in_array($mock, $except));
        foreach ($neverExpected as $mock) {
            $mock->expects($this->never())->method('parse');
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
    public function getDocBlockValidatorsCallsMethodsToParseSimpleTypeDocBlockAndReturnsValidators()
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
        $this->expectTypeSplitterCalls(
            ['list', ['list']],
            ['class-string', ['class-string']],
            ['array', ['array']]
        );
        $this->expectMockParserCallFor($this->mockAtomicParser, $this->exactly(3), 'list', 'class-string', 'array')
            ->willReturn($expectedParserReturn);

        $this->expectNoMockParserCallsExcept($this->mockAtomicParser);

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
        $this->expectTypeSplitterCalls(['int|string', ['int', 'string']]);

        $this->expectMockParserCallFor($this->mockAtomicParser, $this->exactly(2), 'int', 'string')
            ->willReturn($expectedParserReturn);

        $this->expectNoMockParserCallsExcept($this->mockAtomicParser);

        $actual = $this->testObj->getDocBlockValidators($docblock, 1)[0];
        $this->assertEquals(new UnionParameter('$testIntString', [$expectedParserReturn, $expectedParserReturn]), $actual);
    }

    /**
     * @test
     * @dataProvider getDocBlockValidatorsCallsMethodsToParseDocBlockAndReturnsValidatorsUsingPsrArrayParserDataProvider
     */
    public function getDocBlockValidatorsCallsMethodsToParseDocBlockAndReturnsValidatorsUsingPsrArrayParser($docblock, $splitterCallArg)
    {
        $expectedParserReturn = $this->newMockParserReturnValue();
        $this->expectTypeSplitterCalls([$splitterCallArg, [$splitterCallArg]]);

        $this->expectMockParserCallFor($this->mockPsrArrayParser, $this->once(), $splitterCallArg)
            ->willReturn($expectedParserReturn);

        $this->expectNoMockParserCallsExcept($this->mockPsrArrayParser);

        $actual = $this->testObj->getDocBlockValidators($docblock, 1)[0];
        $this->assertEquals(new UnionParameter('$testParam', [$expectedParserReturn]), $actual);
    }

    /**
     * Data Provider
     */
    public function getDocBlockValidatorsCallsMethodsToParseDocBlockAndReturnsValidatorsUsingPsrArrayParserDataProvider()
    {
        return [
            'array with union types' => [
                <<<'php'
                /**
                 *
                 * @param (int|string)[] $testParam Is a string scalar type
                */
                php,
                '(int|string)[]'
            ],
            'array with psr array type' => [
                <<<'php'
                /**
                 *
                 * @param int[][] $testParam Is a string scalar type
                */
                php,
                'int[][]'
            ],
            'array with psalm array type' => [
                <<<'php'
                /**
                 *
                 * @param array<int>[] $testParam Is a string scalar type
                */
                php,
                'array<int>[]'
            ],
            'array with psalm list type' => [
                <<<'php'
                /**
                 *
                 * @param list<string>[] $testParam Is a string scalar type
                */
                php,
                'list<string>[]'
            ],
            'array with psalm class-string type' => [
                <<<'php'
                /**
                 *
                 * @param class-string<stdClass>[] $testParam Is a string scalar type
                */
                php,
                'class-string<stdClass>[]'
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getDocBlockValidatorsCallsMethodsToParseDocBlockAndReturnsValidatorsUsingPsalmArrayParserDataProvider
     */
    public function getDocBlockValidatorsCallsMethodsToParseDocBlockAndReturnsValidatorsUsingPsalmArrayParser($docblock, $splitterCallArg)
    {
        $expectedParserReturn = $this->newMockParserReturnValue();
        $this->expectTypeSplitterCalls([$splitterCallArg, [$splitterCallArg]]);
        $this->expectMockParserCallFor($this->mockPsalmArrayParser, $this->once(), $splitterCallArg)
            ->willReturn($expectedParserReturn);
        $this->expectNoMockParserCallsExcept($this->mockPsalmArrayParser);

        $actual = $this->testObj->getDocBlockValidators($docblock, 1)[0];
        $this->assertEquals(new UnionParameter('$testParam', [$expectedParserReturn]), $actual);
    }

    /**
     * Data Provider
     */
    public function getDocBlockValidatorsCallsMethodsToParseDocBlockAndReturnsValidatorsUsingPsalmArrayParserDataProvider()
    {
        return [
            'array with union types' => [
                <<<'php'
                /**
                 *
                 * @param array<int|string> $testParam Is a string scalar type
                */
                php,
                'array<int|string>'
            ],
            'array with psr array type' => [
                <<<'php'
                /**
                 *
                 * @param array<int[]> $testParam Is a string scalar type
                */
                php,
                'array<int[]>'
            ],
            'array with psr union typed array type' => [
                <<<'php'
                /**
                 *
                 * @param array<(class-string|string)[]> $testParam Is a string scalar type
                */
                php,
                'array<(class-string|string)[]>'
            ],
            'array with psalm array type' => [
                <<<'php'
                /**
                 *
                 * @param array<int, array<object>> $testParam Is a string scalar type
                */
                php,
                'array<int, array<object>>'
            ],
            'array with psalm list type' => [
                <<<'php'
                /**
                 *
                 * @param array<string, list<object>> $testParam Is a string scalar type
                */
                php,
                'array<string, list<object>>'
            ],
            'array with psalm class-string type' => [
                <<<'php'
                /**
                 *
                 * @param array<int, class-string<stdClass>> $testParam Is a string scalar type
                */
                php,
                'array<int, class-string<stdClass>>'
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getDocBlockValidatorsCallsMethodsToParseDocBlockAndReturnsValidatorsUsingPsalmListParserDataProvider
     */
    public function getDocBlockValidatorsCallsMethodsToParseDocBlockAndReturnsValidatorsUsingPsalmListParser($docblock, $splitterCallArg)
    {
        $expectedParserReturn = $this->newMockParserReturnValue();
        $this->expectTypeSplitterCalls([$splitterCallArg, [$splitterCallArg]]);

        $this->expectMockParserCallFor($this->mockPsalmListParser, $this->once(), $splitterCallArg)
            ->willReturn($expectedParserReturn);

        $this->expectNoMockParserCallsExcept($this->mockPsalmListParser);

        $actual = $this->testObj->getDocBlockValidators($docblock, 1)[0];
        $this->assertEquals(new UnionParameter('$testParam', [$expectedParserReturn]), $actual);
    }

    /**
     * Data Provider
     */
    public function getDocBlockValidatorsCallsMethodsToParseDocBlockAndReturnsValidatorsUsingPsalmListParserDataProvider()
    {
        return [
            'list with union types' => [
                <<<'php'
                /**
                 *
                 * @param list<int|string> $testParam Is a string scalar type
                */
                php,
                'list<int|string>'
            ],
            'list with psr array type' => [
                <<<'php'
                /**
                 *
                 * @param list<int[]> $testParam Is a string scalar type
                */
                php,
                'list<int[]>'
            ],
            'list with psr union typed array type' => [
                <<<'php'
                /**
                 *
                 * @param list<(class-string|string)[]> $testParam Is a string scalar type
                */
                php,
                'list<(class-string|string)[]>'
            ],
            'list with psalm array type' => [
                <<<'php'
                /**
                 *
                 * @param list<array<object>> $testParam Is a string scalar type
                */
                php,
                'list<array<object>>'
            ],
            'list with psalm list type' => [
                <<<'php'
                /**
                 *
                 * @param list<list<object>> $testParam Is a string scalar type
                */
                php,
                'list<list<object>>'
            ],
            'list with psalm class-string type' => [
                <<<'php'
                /**
                 *
                 * @param list<class-string<stdClass>> $testParam Is a string scalar type
                */
                php,
                'list<class-string<stdClass>>'
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getDocBlockValidatorsCallsMethodsToParseDocBlockAndReturnsValidatorsUsingClassStringParserDataProvider
     */
    public function getDocBlockValidatorsCallsMethodsToParseDocBlockAndReturnsValidatorsUsingClassStringParser($docblock, $splitterCallArg)
    {
        $expectedParserReturn = $this->newMockParserReturnValue();
        $this->expectTypeSplitterCalls([$splitterCallArg, [$splitterCallArg]]);

        $this->expectMockParserCallFor($this->mockPsalmClassStringParser, $this->once(), $splitterCallArg)
            ->willReturn($expectedParserReturn);

        $this->expectNoMockParserCallsExcept($this->mockPsalmClassStringParser);

        $actual = $this->testObj->getDocBlockValidators($docblock, 1)[0];
        $this->assertEquals(new UnionParameter('$testParam', [$expectedParserReturn]), $actual);
    }

    /**
     * Data Provider
     */
    public function getDocBlockValidatorsCallsMethodsToParseDocBlockAndReturnsValidatorsUsingClassStringParserDataProvider()
    {
        return [
            'class-string with simple type' => [
                <<<'php'
                /**
                 *
                 * @param class-string<stdClass> $testParam Is a string scalar type
                */
                php,
                'class-string<stdClass>'
            ],
            'class-string with union types' => [
                <<<'php'
                /**
                 *
                 * @param class-string<stdClass|ArrayObject> $testParam Is a string scalar type
                */
                php,
                'class-string<stdClass|ArrayObject>'
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getDocblockCallsLintCheckOnTypeLinterDataProvider
     */
    public function getDocblockCallsLintCheckOnTypeLinter($docblock)
    {
        $this->mockTypeLinter->expects($this->once())->method('lintCheck')
            ->with($this->equalTo('$param'), $this->isType('string'))
            ->willThrowException(new RuntimeException);
        try {
            $this->testObj->getDocBlockValidators($docblock, 1);
        } catch (RuntimeException $ex) {
        }
    }

    /**
     * Data Provider
     */
    public function getDocblockCallsLintCheckOnTypeLinterDataProvider()
    {
        return [
            'missing closing parenthesis' => [
                <<<'php'
                /**
                 *
                 * @param (int|string[] $param
                 */
                php
            ],
        ];
    }
}
