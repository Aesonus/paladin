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

use Aesonus\Paladin\DocBlockArrayParameter;
use Aesonus\Paladin\DocBlockTypedClassStringParameter;
use Aesonus\Paladin\Parser;
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

    public static function isDocBlockParameter($name, $types, $required)
    {
        return new ConstraintDocBlockParameter($name, $types, $required);
    }

    public static function assertDocBlockParameter($actual, $name, $types, $required): void
    {
        static::assertThat($actual, static::isDocBlockParameter($name, $types, $required));
    }

    /**
     * @test
     */
    public function getParsedDocBlockReturnsAProperDocBlockObjectWithSimpleType()
    {
        $docblock = <<<'php'
            /**
             *
             * @param string $testString Is a string scalar type
            */
            php;
        $actual = $this->testObj->getDocBlock($docblock, 1)[0];
        $this->assertDocBlockParameter($actual, '$testString', ['string'], true);
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
        $this->assertDocBlockParameter($actual, '$testUnion', ['string', 'array'], true);
    }

    /**
     * @test
     * @dataProvider getParsedDocBlockReturnsDocBlockForClassStringWithTypesDataProvider
     */
    public function getParsedDocBlockReturnsDocBlockForClassStringWithTypes($docblock, $expected)
    {
        $actual = $this->testObj->getDocBlock($docblock, 1)[0];
        $this->assertDocBlockParameter($actual, '$testClassString', ...$expected);
    }

    /**
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
                [[new DocBlockTypedClassStringParameter('class-string', [stdClass::class])], true]
            ],
            'class-string<\stdClass|Aesonus\Tests\Fixtures\TestClass>' => [
                <<<'php'
                /**
                *
                * @param class-string<\stdClass|Aesonus\Tests\Fixtures\TestClass> $testClassString
                */
                php,
                [[new DocBlockTypedClassStringParameter('class-string', [stdClass::class, TestClass::class])], true]
            ],
            'class-string<stdClass|TestClass>' => [
                <<<'php'
                /**
                *
                * @param class-string<stdClass|TestClass> $testClassString
                */
                php,
                [[new DocBlockTypedClassStringParameter('class-string', [stdClass::class, TestClass::class])], true]
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
                [[new DocBlockArrayParameter('array', 'int', ['string', 'int'])], true]
            ],
            'string[]' => [
                <<<'php'
                /**
                *
                * @param string[] $testArray
                */
                php,
                [[new DocBlockArrayParameter('array', 'int', ['string'])], true]
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
                        new DocBlockArrayParameter(
                            'array',
                            'int',
                            [
                                new DocBlockArrayParameter('array', 'int', ['string']),
                                'int'
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
                [[new DocBlockArrayParameter('array', 'array-key', ['string'])], true]
            ],
            'array<string|float>' => [
                <<<'php'
                /**
                *
                * @param array<string|float> $testArray
                */
                php,
                [[new DocBlockArrayParameter('array', 'array-key', ['string', 'float'])], true]
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
                        new DocBlockArrayParameter(
                            'array',
                            'array-key',
                            [new DocBlockArrayParameter('array', 'int', ['string', 'int'])]
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
                        new DocBlockArrayParameter(
                            'array',
                            'array-key',
                            [
                                new DocBlockArrayParameter('array', 'array-key', ['string']),
                                new DocBlockArrayParameter('array', 'array-key', ['float']),
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
                        new DocBlockArrayParameter(
                            'array',
                            'array-key',
                            [
                                new DocBlockArrayParameter('array', 'array-key', ['string']),
                                new DocBlockArrayParameter('array', 'int', ['float']),
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
                        new DocBlockArrayParameter(
                            'array',
                            'array-key',
                            [
                                new DocBlockArrayParameter('array', 'array-key', ['string']),
                                'float',
                                new DocBlockArrayParameter('array', 'string', ['mixed']),
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
                        new DocBlockArrayParameter(
                            'array',
                            'array-key',
                            [
                                new DocBlockArrayParameter('array', 'array-key', ['string']),
                                new DocBlockArrayParameter('array', 'int', ['float']),
                                new DocBlockArrayParameter('array', 'string', ['mixed']),
                            ]
                        )
                    ],
                    true
                ]
            ],
            '(array<string>|array<float>)[]' => [
                <<<'php'
                /**
                *
                * @param (array<string>|array<float>)[] $testArray
                */
                php,
                [
                    [
                        new DocBlockArrayParameter(
                            'array',
                            'int',
                            [
                                new DocBlockArrayParameter('array', 'array-key', ['string']),
                                new DocBlockArrayParameter('array', 'array-key', ['float']),
                            ]
                        )
                    ],
                    true
                ]
            ],
            'array<'
            . 'array<int>|array<'
            . 'array<float>|string'
            . '>'
            . '>' => [
                <<<'php'
                /**
                *
                * @param array<array<int>|array<array<float>|string>> $testArray
                */
                php,
                [
                    [
                        new DocBlockArrayParameter(
                            'array',
                            'array-key',
                            [
                                new DocBlockArrayParameter('array', 'array-key', [
                                    'int',
                                ]),
                                new DocBlockArrayParameter('array', 'array-key', [
                                    new DocBlockArrayParameter('array', 'array-key', ['float']),
                                    'string'
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
                        new DocBlockArrayParameter(
                            'array',
                            'array-key',
                            [
                                new DocBlockArrayParameter('array', 'int', ['string']),
                                new DocBlockArrayParameter('array', 'int', ['int']),
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
                [[new DocBlockArrayParameter('array', 'int', ['string'])], true]
            ],
            'array<string, string|float>' => [
                <<<'php'
                /**
                *
                * @param array<string, string|float> $testArray
                */
                php,
                [[new DocBlockArrayParameter('array', 'string', ['string', 'float'])], true]
            ],
            'array<class-string>' => [
                <<<'php'
                /**
                *
                * @param array<class-string> $testArray
                */
                php,
                [[new DocBlockArrayParameter('array', 'array-key', ['class-string'])], true]
            ],
            'array<class-string[]>' => [
                <<<'php'
                /**
                *
                * @param array<class-string[]> $testArray
                */
                php,
                [[new DocBlockArrayParameter(
                    'array',
                    'array-key',
                    [new DocBlockArrayParameter('array', 'int', ['class-string'])]
                )], true]
            ],
            'array<class-string[]|int>' => [
                <<<'php'
                /**
                *
                * @param array<class-string[]|int> $testArray
                */
                php,
                [[new DocBlockArrayParameter(
                    'array',
                    'array-key',
                    [new DocBlockArrayParameter('array', 'int', ['class-string']), 'int']
                )], true]
            ],
            'array<int, class-string>' => [
                <<<'php'
                /**
                *
                * @param array<int, class-string> $testArray
                */
                php,
                [[new DocBlockArrayParameter('array', 'int', ['class-string'])], true]
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
                        new DocBlockArrayParameter(
                            'array',
                            'array-key',
                            [
                                new DocBlockTypedClassStringParameter(
                                    'class-string',
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
                        new DocBlockArrayParameter(
                            'array',
                            'array-key',
                            [
                                new DocBlockTypedClassStringParameter(
                                    'class-string',
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
                        new DocBlockArrayParameter(
                            'array',
                            'array-key',
                            [
                                new DocBlockTypedClassStringParameter(
                                    'class-string',
                                    [stdClass::class]
                                ),
                                new DocBlockTypedClassStringParameter(
                                    'class-string',
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
     * @dataProvider malformedDocblockThrowsExceptionDataProvider
     */
    public function malformedDocblockThrowsException($docblock)
    {
        $this->expectException(RuntimeException::class);
        $actual = $this->testObj->getDocBlock($docblock, 1);
        var_dump($actual[0]);
    }

    /**
     * Data Provider
     */
    public function malformedDocblockThrowsExceptionDataProvider()
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
