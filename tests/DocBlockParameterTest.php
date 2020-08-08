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

use Aesonus\Paladin\DocBlockParameter;
use Aesonus\TestLib\BaseTestCase;
use Aesonus\Tests\Fixtures\TestClass;
use Aesonus\Tests\Fixtures\TestTrait;
use stdClass;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class DocBlockParameterTest extends BaseTestCase
{
    /**
     * @test
     * @dataProvider validateParameterReturnsTrueIfParameterPassesSimpleTypeDataProvider
     */
    public function validateParameterReturnsTrueIfParameterPassesSimpleType($type, $givenValue)
    {
        $docBlockParameter = new DocBlockParameter(
            '$test',
            [
                [$type]
            ],
            true
        );
        $this->assertTrue($docBlockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function validateParameterReturnsTrueIfParameterPassesSimpleTypeDataProvider()
    {
        return [
            'string' => ['string', 'test value'],
            'int' => ['int', 3],
            'bool true' => ['bool', true],
            'bool false' => ['bool', false],
            'true' => ['true', true],
            'false' => ['false', false],
            'float' => ['float', 3.141],
            'array' => ['array', []],
            'object' => ['object', new stdClass],
            'scalar int' => ['scalar', 34],
            'scalar float' => ['scalar', 34.4],
            'scalar string' => ['scalar', 'string'],
            'mixed int' => ['mixed', 34],
            'mixed float' => ['mixed', 34.4],
            'mixed string' => ['mixed', 'string'],
            'numeric as string' => ['numeric', '3.32'],
            'numeric as int' => ['numeric', 3],
            'numeric as float' => ['numeric', 3.45],
            'callable as string' => ['callable', 'array_filter'],
            'callable as array' => ['callable', [new TestClass, 'simpleType']],
            'callable-string' => ['callable-string', 'array_filter'],
            'array-key as string' => ['array-key', 'key'],
            'array-key as int' => ['array-key', 1],
            'class-string' => ['class-string', stdClass::class],
            'trait-string' => ['trait-string', TestTrait::class],
            'numeric-string' => ['numeric-string', '3.14159'],
            stdClass::class => [stdClass::class, new stdClass]
        ];
    }

    /**
     * @test
     * @dataProvider validateParameterReturnsTrueIfParameterIsArrayOfTypeDataProvider
     */
    public function validateParameterReturnsTrueIfParameterIsArrayOfType($type, $givenValue)
    {
        $docBlockParameter = new DocBlockParameter(
            '$test',
            [
                [['array' => $type]]
            ],
            true
        );
        $this->assertTrue($docBlockParameter->validate($givenValue));
    }

    /**
     * Data Provider
     */
    public function validateParameterReturnsTrueIfParameterIsArrayOfTypeDataProvider()
    {
        return [
            [['string'], ['test', 'strings']],
            [['string', 'int'], [34, 'string']]
        ];
    }
}
