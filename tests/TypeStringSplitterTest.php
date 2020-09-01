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

use Aesonus\Paladin\TypeStringSplitter;
use Aesonus\TestLib\BaseTestCase;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class TypeStringSplitterTest extends BaseTestCase
{
    public $testObj;

    protected function setUp(): void
    {
        $this->testObj = new TypeStringSplitter;
    }
    /**
     * @test
     * @dataProvider splitSplitsUpBaseLevelUnionsDataProvider
     */
    public function splitSplitsUpBaseLevelUnions($typeString, $expected)
    {
        $this->assertSame($expected, $this->testObj->split($typeString, '|'));
    }

    /**
     * Data Provider
     */
    public function splitSplitsUpBaseLevelUnionsDataProvider()
    {
        return [
            [
                'string|array', ['string', 'array']
            ],
            [
                'class-string<\stdClass>', ['class-string<\stdClass>']
            ],
            [
                'class-string<\stdClass|Aesonus\Tests\Fixtures\TestClass>',
                ['class-string<\stdClass|Aesonus\Tests\Fixtures\TestClass>']
            ],
            [
                'class-string<stdClass|TestClass>', ['class-string<stdClass|TestClass>']
            ],
            [
                '(string|int)[]', ['(string|int)[]']
            ],
            [
                'string[]', ['string[]']
            ],
            [
                '(string[]|int)[]', ['(string[]|int)[]']
            ],
            [
                '(string[])[]', ['(string[])[]']
            ],
            [
                'string[][]', ['string[][]']
            ],
            [
                '((string[])[])[]', ['((string[])[])[]']
            ],
            [
                'array<string>', ['array<string>']
            ],
            [
                'array<string|float>', ['array<string|float>']
            ],
            [
                'array<(string|int)[]>', ['array<(string|int)[]>']
            ],
            [
                'array<array<string>|array<float>>', ['array<array<string>|array<float>>']
            ],
            [
                'array<array<string>|float[]>', ['array<array<string>|float[]>']
            ],
            [
                'array<array<string>|float|array<string, mixed>>',
                ['array<array<string>|float|array<string,mixed>>']
            ],
            [
                'array<array<string>|float[]|array<string, mixed>>',
                ['array<array<string>|float[]|array<string,mixed>>']
            ],
            [
                'array<string>|float[]|array<string, mixed>',
                ['array<string>', 'float[]', 'array<string,mixed>']
            ],
            [
                '(array<string>|array<float>)[]',
                ['(array<string>|array<float>)[]']
            ],
            [
                'array<string[]|int[]>',
                ['array<string[]|int[]>']
            ],
            [
                'array<int, string>', ['array<int,string>']
            ],
            [
                'array<int, string[]>', ['array<int,string[]>']
            ],
            [
                'array<string, string|float>', ['array<string,string|float>']
            ],
            [
                'array<class-string>', ['array<class-string>']
            ],
            [
                'array<class-string[]>', ['array<class-string[]>']
            ],
            [
                'array<class-string[]|int>', ['array<class-string[]|int>']
            ],
            [
                'array<int, class-string>', ['array<int,class-string>']
            ],
            [
                'array<class-string<stdClass>>', ['array<class-string<stdClass>>']
            ],
            [
                'array<class-string<stdClass|TestClass>>', ['array<class-string<stdClass|TestClass>>']
            ],
            [
                'array<class-string<stdClass>|class-string<TestClass>>',
                ['array<class-string<stdClass>|class-string<TestClass>>']
            ],
            [
                'array<class-string<stdClass>|class-string<TestClass>>|float',
                ['array<class-string<stdClass>|class-string<TestClass>>', 'float']
            ],
            [
                'array<class-string<stdClass>|class-string<TestClass>>|float|class-string<stdClass>',
                ['array<class-string<stdClass>|class-string<TestClass>>', 'float', 'class-string<stdClass>']
            ],
            [
                'array{0: string, key: array{0: string|int}}',
                ['array{0:string,key:array{0:string|int}}']
            ],
            [
                'array{0: string, key: array{0: string|int}}|array{0: float|string}',
                ['array{0:string,key:array{0:string|int}}', 'array{0:float|string}']
            ],
        ];
    }

    /**
     * @test
     * @dataProvider splitSplitsUpObjectLikeArrayIntoKeyPairsDataProvider
     */
    public function splitSplitsUpObjectLikeArrayIntoKeyPairs($typeString, $expected)
    {
        $this->assertSame($expected, $this->testObj->split($typeString, ','));
    }

    /**
     * Data Provider
     */
    public function splitSplitsUpObjectLikeArrayIntoKeyPairsDataProvider()
    {
        return [
            [
                '0: string, key: array{0: string|int, key: int}',
                ['0:string', 'key:array{0:string|int,key:int}']
            ],
            [
                'string:array<int>,0:array{0:string,array{key:float},key:int}',
                ['string:array<int>', '0:array{0:string,array{key:float},key:int}']
            ]
        ];
    }

    /**
     * @test
     * @dataProvider splitSplitsUpObjectLikeArrayKeysAndValuesDataProvider
     */
    public function splitSplitsUpObjectLikeArrayKeysAndValues($typeString, $expected)
    {
        $this->assertSame($expected, $this->testObj->split($typeString, ':'));
    }

    /**
     * Data Provider
     */
    public function splitSplitsUpObjectLikeArrayKeysAndValuesDataProvider()
    {
        return [
            [
                '0:array{0:string,array{key:float},key:int}',
                ['0', 'array{0:string,array{key:float},key:int}']
            ],
            [
                'key:array{other:string}',
                ['key', 'array{other:string}']
            ]
        ];
    }
}
