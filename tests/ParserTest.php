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

use Aesonus\Paladin\DocBlock\ArrayKeyParameter;
use Aesonus\Paladin\DocBlock\ArrayParameter;
use Aesonus\Paladin\DocBlock\ClassStringParameter;
use Aesonus\Paladin\DocBlock\FloatParameter;
use Aesonus\Paladin\DocBlock\IntParameter;
use Aesonus\Paladin\DocBlock\ListParameter;
use Aesonus\Paladin\DocBlock\MixedParameter;
use Aesonus\Paladin\DocBlock\ObjectParameter;
use Aesonus\Paladin\DocBlock\StringParameter;
use Aesonus\Paladin\DocBlock\TypedClassStringParameter;
use Aesonus\Paladin\Parser;
use Aesonus\Paladin\TypeLinter;
use Aesonus\Paladin\UseContext;
use Aesonus\TestLib\BaseTestCase;
use Aesonus\Tests\Fixtures\ParserContextClass;
use Aesonus\Tests\Fixtures\TestClass;
use RuntimeException;
use stdClass;

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

    public $testClass;

    protected function setUp(): void
    {
        $this->testClass = new TestClass;
        $this->testObj = new Parser(new UseContext(ParserContextClass::class));
    }

    public static function isDocBlockParameter($name, $types)
    {
        return new ConstraintDocBlockParameter($name, $types);
    }

    public static function assertDocBlockParameter($actual, $name, $types): void
    {
        static::assertThat($actual, static::isDocBlockParameter($name, $types));
    }

    /**
     * @test
     */
    public function getParsedDocBlockReturnsAProperDocBlockObjectWithSimpleType()
    {
        $docblock = <<<'php'
            /**
             *
             * @param int $testString Is a string scalar type
            */
            php;
        $actual = $this->testObj->getDocBlock($docblock, 1)[0];
        $this->assertDocBlockParameter($actual, '$testString', [new IntParameter]);
    }

    /**
     * @test
     */
    public function getParsedDocBlockReturnsAProperDocBlockObjectWithSimpleUnionTypes()
    {
        $docblock = <<<'php'
            /**
            *
            * @param string|array $testUnion
            */
            php;
        $actual = $this->testObj->getDocBlock($docblock, 1)[0];
        $this->assertDocBlockParameter($actual, '$testUnion', [new StringParameter, new ArrayParameter]);
    }

    /**
     * @test
     */
    public function getParsedDocBlockReturnsAProperDocBlockObjectWithObjectOfType()
    {
        $docblock = <<<'php'
            /**
            *
            * @param stdClass $testUnion
            */
            php;
        $actual = $this->testObj->getDocBlock($docblock, 1)[0];
        $this->assertDocBlockParameter($actual, '$testUnion', [new ObjectParameter(stdClass::class)]);
    }

    /**
     * @test
     * @dataProvider getParsedDocBlockReturnsDocBlockForClassStringWithTypesDataProvider
     */
    public function getParsedDocBlockReturnsDocBlockForClassStringWithTypes($docblock, $expected)
    {
        $actual = $this->testObj->getDocBlock($docblock, 1)[0];
        $this->assertDocBlockParameter($actual, '$testClassString', $expected);
    }

    /**
     *
     * Data Provider
     */
    public function getParsedDocBlockReturnsDocBlockForClassStringWithTypesDataProvider()
    {
        return [
            'class-string<\stdClass>' => [
                <<<'php'
                /**
                *
                * @param class-string<\stdClass> $testClassString
                */
                php,
                [new TypedClassStringParameter([stdClass::class])]
            ],
            'class-string<\stdClass|Aesonus\Tests\Fixtures\TestClass>' => [
                <<<'php'
                /**
                *
                * @param class-string<\stdClass|Aesonus\Tests\Fixtures\TestClass> $testClassString
                */
                php,
                [new TypedClassStringParameter([stdClass::class, TestClass::class])]
            ],
            'class-string<stdClass|TestClass>' => [
                <<<'php'
                /**
                *
                * @param class-string<stdClass|TestClass> $testClassString
                */
                php,
                [new TypedClassStringParameter([stdClass::class, TestClass::class])]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getParsedDocBlockReturnsADocBlockObjectWithArrayTypeDataProvider
     */
    public function getParsedDocBlockReturnsADocBlockObjectWithArrayType($docblock, $expected)
    {
        $actual = $this->testObj->getDocBlock($docblock, 1)[0];
        $this->assertDocBlockParameter($actual, '$testArray', ...$expected);
    }

    /**
     * Data Provider
     */
    public function getParsedDocBlockReturnsADocBlockObjectWithArrayTypeDataProvider()
    {
        return [
            '(string|int)[]' => [
                <<<'php'
                /**
                *
                * @param (string|int)[] $testArray
                */
                php,
                [[new ArrayParameter(new ArrayKeyParameter, [new StringParameter, new IntParameter])], true]
            ],
            'string[]' => [
                <<<'php'
                /**
                *
                * @param string[] $testArray
                */
                php,
                [[new ArrayParameter(new ArrayKeyParameter, [new StringParameter])], true]
            ],
            '(string[]|int)[]' => [
                <<<'php'
                /**
                *
                * @param (string[]|int)[] $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [
                                new ArrayParameter(new ArrayKeyParameter, [new StringParameter]),
                                new IntParameter
                            ]
                        )
                    ],
                    true
                ]
            ],
            '(string[])[]' => [
                <<<'php'
                /**
                *
                * @param (string[])[] $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [
                                new ArrayParameter(new ArrayKeyParameter, [new StringParameter]),
                            ]
                        )
                    ],
                    true
                ]
            ],
            'string[][]' => [
                <<<'php'
                /**
                *
                * @param string[][] $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [
                                new ArrayParameter(new ArrayKeyParameter, [new StringParameter]),
                            ]
                        )
                    ],
                    true
                ]
            ],
            '((string[])[])[]' => [
                <<<'php'
                /**
                *
                * @param ((string[])[])[] $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [
                                new ArrayParameter(
                                    new ArrayKeyParameter,
                                    [new ArrayParameter(new ArrayKeyParameter, [new StringParameter])]
                                ),
                            ]
                        )
                    ],
                    true
                ]
            ],
            'string[][][]' => [
                <<<'php'
                /**
                *
                * @param string[][][] $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [
                                new ArrayParameter(
                                    new ArrayKeyParameter,
                                    [new ArrayParameter(new ArrayKeyParameter, [new StringParameter])]
                                ),
                            ]
                        )
                    ],
                    true
                ]
            ],
            'array<string>[]' => [
                <<<'php'
                /**
                *
                * @param array<string>[] $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [
                                new ArrayParameter(new ArrayKeyParameter, [new StringParameter]),
                            ]
                        )
                    ],
                    true
                ]
            ],
            'array<string>' => [
                <<<'php'
                /**
                *
                * @param array<string> $testArray
                */
                php,
                [[new ArrayParameter(new ArrayKeyParameter, [new StringParameter])], true]
            ],
            'array<string|float>' => [
                <<<'php'
                /**
                *
                * @param array<string|float> $testArray
                */
                php,
                [[new ArrayParameter(new ArrayKeyParameter, [new StringParameter, new FloatParameter])], true]
            ],
            'array<(string|int)[]>' => [
                <<<'php'
                /**
                *
                * @param array<(string|int)[]> $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [new ArrayParameter(new ArrayKeyParameter, [new StringParameter, new IntParameter])]
                        )
                    ],
                    true
                ]
            ],
            'array<string[]>' => [
                <<<'php'
                /**
                *
                * @param array<string[]> $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [new ArrayParameter(new ArrayKeyParameter, [new StringParameter])]
                        )
                    ],
                    true
                ]
            ],
            'array<array<string>|array<float>>' => [
                <<<'php'
                /**
                *
                * @param array<array<string>|array<float>> $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [
                                new ArrayParameter(new ArrayKeyParameter, [new StringParameter]),
                                new ArrayParameter(new ArrayKeyParameter, [new FloatParameter]),
                            ]
                        )
                    ],
                    true
                ]
            ],
            'array<string[]|float[]>' => [
                <<<'php'
                /**
                *
                * @param array<string[]|float[]> $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [
                                new ArrayParameter(new ArrayKeyParameter, [new StringParameter]),
                                new ArrayParameter(new ArrayKeyParameter, [new FloatParameter]),
                            ]
                        )
                    ],
                    true
                ]
            ],
            'array<array<string>|float[]>' => [
                <<<'php'
                /**
                *
                * @param array<array<string>|float[]> $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [
                                new ArrayParameter(new ArrayKeyParameter, [new StringParameter]),
                                new ArrayParameter(new ArrayKeyParameter, [new FloatParameter]),
                            ]
                        )
                    ],
                    true
                ]
            ],
            'array<array<string>|float|array<string, mixed>>' => [
                <<<'php'
                /**
                *
                * @param array<array<string>|float|array<string, mixed>> $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [
                                new ArrayParameter(new ArrayKeyParameter, [new StringParameter]),
                                new FloatParameter,
                                new ArrayParameter(new StringParameter, [new MixedParameter]),
                            ]
                        )
                    ],
                    true
                ]
            ],
            'array<array<string>|float[]|array<string, mixed>>' => [
                <<<'php'
                /**
                *
                * @param array<array<string>|float[]|array<string, mixed>> $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [
                                new ArrayParameter(new ArrayKeyParameter, [new StringParameter]),
                                new ArrayParameter(new ArrayKeyParameter, [new FloatParameter]),
                                new ArrayParameter(new StringParameter, [new MixedParameter]),
                            ]
                        )
                    ],
                    true
                ]
            ],
            '(array<int, string>|array<float>)[]' => [
                <<<'php'
                /**
                *
                * @param (array<int, string>|array<float>)[] $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [
                                new ArrayParameter(new IntParameter, [new StringParameter]),
                                new ArrayParameter(new ArrayKeyParameter, [new FloatParameter]),
                            ]
                        )
                    ],
                    true
                ]
            ],
            'array<array<int>|array<array<float>|string>>' => [
                <<<'php'
                /**
                *
                * @param array<array<int>|array<array<float>|string>> $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [
                                new ArrayParameter(new ArrayKeyParameter, [
                                    new IntParameter,
                                ]),
                                new ArrayParameter(new ArrayKeyParameter, [
                                    new ArrayParameter(new ArrayKeyParameter, [new FloatParameter]),
                                    new StringParameter
                                ]),
                            ]
                        )
                    ],
                    true
                ]
            ],
            'array<string[]|int[]>' => [
                <<<'php'
                /**
                *
                * @param array<string[]|int[]> $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [
                                new ArrayParameter(new ArrayKeyParameter, [new StringParameter]),
                                new ArrayParameter(new ArrayKeyParameter, [new IntParameter]),
                            ]
                        )
                    ],
                    true
                ]
            ],
            'array<int, string>' => [
                <<<'php'
                /**
                *
                * @param array<int, string> $testArray
                */
                php,
                [[new ArrayParameter(new IntParameter, [new StringParameter])], true]
            ],
            'array<int, string[]>' => [
                <<<'php'
                /**
                *
                * @param array<int, string[]> $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new IntParameter,
                            [
                                new ArrayParameter(new ArrayKeyParameter, [new StringParameter])
                            ]
                        )
                    ],
                    true
                ]
            ],
            'list' => [
                <<<'php'
                /**
                *
                * @param list $testArray
                */
                php,
                [
                    [
                        new ListParameter(
                            [
                                new MixedParameter
                            ]
                        )
                    ],
                    true
                ]
            ],
            'list<string>' => [
                <<<'php'
                /**
                *
                * @param list<string> $testArray
                */
                php,
                [
                    [
                        new ListParameter(
                            [
                                new StringParameter
                            ]
                        )
                    ],
                    true
                ]
            ],
            'list<string|int>' => [
                <<<'php'
                /**
                *
                * @param list<string|int> $testArray
                */
                php,
                [
                    [
                        new ListParameter(
                            [
                                new StringParameter,
                                new IntParameter
                            ]
                        )
                    ],
                    true
                ]
            ],
            'list<array<string>>' => [
                <<<'php'
                /**
                *
                * @param list<array<string>> $testArray
                */
                php,
                [
                    [
                        new ListParameter(
                            [
                                new ArrayParameter(new ArrayKeyParameter, [new StringParameter])
                            ]
                        )
                    ],
                    true
                ]
            ],
            'array<list<string>>' => [
                <<<'php'
                /**
                *
                * @param array<list<string>> $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [
                                new ListParameter([new StringParameter])
                            ]
                        )
                    ],
                    true
                ]
            ],
            'array<int, list<string>>' => [
                <<<'php'
                /**
                *
                * @param array<int, list<string>> $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new IntParameter,
                            [
                                new ListParameter([new StringParameter])
                            ]
                        )
                    ],
                    true
                ]
            ],
            'list[]' => [
                <<<'php'
                /**
                *
                * @param list[] $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [
                                new ListParameter([new MixedParameter])
                            ]
                        )
                    ],
                    true
                ]
            ],
            'list<int>[]' => [
                <<<'php'
                /**
                *
                * @param list<int>[] $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [
                                new ListParameter([new IntParameter])
                            ]
                        )
                    ],
                    true
                ]
            ],
            'array<string, string|float>' => [
                <<<'php'
                /**
                *
                * @param array<string, string|float> $testArray
                */
                php,
                [[new ArrayParameter(new StringParameter, [new StringParameter, new FloatParameter])], true]
            ],
            'array<class-string>' => [
                <<<'php'
                /**
                *
                * @param array<class-string> $testArray
                */
                php,
                [[new ArrayParameter(new ArrayKeyParameter, [new ClassStringParameter])], true]
            ],
            'array<class-string[]>' => [
                <<<'php'
                /**
                *
                * @param array<class-string[]> $testArray
                */
                php,
                [[new ArrayParameter(
                    new ArrayKeyParameter,
                    [new ArrayParameter(new ArrayKeyParameter, [new ClassStringParameter])]
                )], true]
            ],
            'array<class-string[]|int>' => [
                <<<'php'
                /**
                *
                * @param array<class-string[]|int> $testArray
                */
                php,
                [[new ArrayParameter(
                    new ArrayKeyParameter,
                    [new ArrayParameter(new ArrayKeyParameter, [new ClassStringParameter]), new IntParameter]
                )], true]
            ],
            'array<int, class-string>' => [
                <<<'php'
                /**
                *
                * @param array<int, class-string> $testArray
                */
                php,
                [[new ArrayParameter(new IntParameter, [new ClassStringParameter])], true]
            ],
            'array<class-string<stdClass>>' => [
                <<<'php'
                /**
                *
                * @param array<class-string<stdClass>> $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [
                                new TypedClassStringParameter(
                                    [stdClass::class]
                                )
                            ]
                        )
                    ],
                    true
                ]
            ],
            'class-string<stdClass>[]' => [
                <<<'php'
                /**
                *
                * @param class-string<stdClass>[] $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [
                                new TypedClassStringParameter(
                                    [stdClass::class]
                                )
                            ]
                        )
                    ],
                    true
                ]
            ],
            'array<class-string<stdClass|TestClass>>' => [
                <<<'php'
                /**
                *
                * @param array<class-string<stdClass|TestClass>> $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [
                                new TypedClassStringParameter(
                                    [stdClass::class, TestClass::class]
                                )
                            ]
                        )
                    ],
                    true
                ]
            ],
            'array<class-string<stdClass>|class-string<TestClass>>' => [
                <<<'php'
                /**
                *
                * @param array<class-string<stdClass>|class-string<TestClass>> $testArray
                */
                php,
                [
                    [
                        new ArrayParameter(
                            new ArrayKeyParameter,
                            [
                                new TypedClassStringParameter(
                                    [stdClass::class]
                                ),
                                new TypedClassStringParameter(
                                    [TestClass::class]
                                ),
                            ]
                        )
                    ],
                    true
                ]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getDocblockCallsLintCheckOnTypeLinterDataProvider
     */
    public function getDocblockCallsLintCheckOnTypeLinter($docblock)
    {
        $mockTypeLinter = $this->getMockBuilder(TypeLinter::class)
            ->setMethodsExcept()
            ->getMock();
        $mockTypeLinter->expects($this->atLeastOnce())->method('lintCheck')
            ->with($this->equalTo('$param'), $this->isType('string'))
            ->willThrowException(new RuntimeException);
        $this->testObj = new Parser(new UseContext(TestClass::class), $mockTypeLinter);
        try {
            $this->testObj->getDocBlock($docblock, 1);
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
