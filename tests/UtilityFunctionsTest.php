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

use Aesonus\TestLib\BaseTestCase;
use function Aesonus\Paladin\Utilities\get_str_positions;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class UtilityFunctionsTest extends BaseTestCase
{
    const STR = 'str';
    const POS = 'pos';

    /**
     * @test
     * @dataProvider getStrPositionsDataProvider
     */
    public function testGet_str_positions($expected, $haystack)
    {
        $actual = get_str_positions(
            $haystack,
            '(',
            'array<',
            '[',
            'list<'
        );
        $this->assertSame($expected, $actual);
    }

    /**
     * Data Provider
     */
    public function getStrPositionsDataProvider()
    {
        return [
            [
                [
                    [
                        self::STR => '(',
                        self::POS => 0
                    ],
                    [
                        self::STR => 'array<',
                        self::POS => 1
                    ],
                    [
                        self::STR => 'list<',
                        self::POS => 7
                    ],
                    [
                        self::STR => '[',
                        self::POS => 22
                    ],
                ],
                '(array<list<int>>|int)[]'
            ],
            [
                [
                    [
                        self::STR => '(',
                        self::POS => 0
                    ],
                    [
                        self::STR => 'array<',
                        self::POS => 1
                    ],
                    [
                        self::STR => 'array<',
                        self::POS => 7
                    ],
                    [
                        self::STR => '[',
                        self::POS => 23
                    ],
                ],
                '(array<array<int>>|int)[]'
            ],
            [
                [],
                'int|string|array'
            ]
        ];
    }
}
