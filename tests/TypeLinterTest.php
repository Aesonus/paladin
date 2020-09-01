<?php

/*
 * This file is part of the aesonus/paladin project.
 *
 * (c) Cory Laughlin <corylcomposinger@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aesonus\Tests;

use Aesonus\Paladin\Exceptions\TypeLintError;
use Aesonus\Paladin\TypeLinter;
use Aesonus\TestLib\BaseTestCase;

class TypeLinterTest extends BaseTestCase
{
    public $testObj;

    protected function setUp() : void
    {
        $this->testObj = new TypeLinter();
    }

    /**
     * @test
     * @dataProvider malformedDocblockThrowsExceptionDataProvider
     */
    public function malformedDocblockThrowsException($name, $type, $info)
    {
        $this->expectError();
        $this->expectErrorMessage("Declared type is not valid for @param $type $name: $info");
        $this->testObj->lintCheck($name, $type);
    }

    /**
     * Data Provider
     */
    public function malformedDocblockThrowsExceptionDataProvider()
    {
        return [
            'missing closing parenthesis' => [
                '$param', '(int|string[]', 'Missing a closing parenthesis'
            ],
            'missing opening parenthesis' => [
                '$param', 'int|string)[]', 'Missing an opening parenthesis'
            ],
            'missing closing arrow' => [
                '$param', 'class-string<nope', 'Missing a closing arrow'
            ],
            'missing opening arrow' => [
                '$param', 'class-stringnerp>', 'Missing an opening arrow'
            ],
            'missing closing bracket' => [
                '$param', 'class-string[', 'Missing a closing bracket'
            ],
            'missing opening bracket' => [
                '$param', 'class-string]', 'Missing an opening bracket'
            ],
            'trailing bar plain union' => [
                '$param', 'class-string|', 'A trailing bar was found'
            ],
            'trailing bar psr array type union' => [
                '$param', '(class-string|)[]', 'A trailing bar was found'
            ],
            'trailing bar psalm array type union' => [
                '$param', 'array<int|>', 'A trailing bar was found'
            ],
            'trailing bar object like array type union' => [
                '$param', 'array{string: int|}', 'A trailing bar was found'
            ],
            'leading bar plain union' => [
                '$param', '|class-string', 'A leading bar was found'
            ],
            'leading bar psr array type union' => [
                '$param', '(|class-string)[]', 'A leading bar was found'
            ],
            'leading bar psalm array type union' => [
                '$param', 'array< |int>', 'A leading bar was found'
            ],
            'leading bar object like array type union' => [
                '$param', 'array{string: |int}', 'A leading bar was found'
            ],
            'missing type after comma in psalm array' => [
                '$param', 'array<int, >', 'Missing type after comma in array type'
            ],
            'missing type before comma in psalm array' => [
                '$param', 'array< , int>', 'Missing type before comma in array type'
            ],
            'unexpected type before comma in psalm array' => [
                '$param', 'array<object, array<int>>', 'Unexpected array type \'object\'; '
                . 'expects array-key, int, or string'
            ],
            'unexpected type[] before comma in psalm array' => [
                '$param', 'array<string[], array<int>>', 'Unexpected array type \'string[]\'; '
                . 'expects array-key, int, or string'
            ],
            'missing opening curly brace' => [
                '$param', 'array0: int}', 'Missing an opening curly brace'
            ],
            'missing closing curly brace' => [
                '$param', 'array{0: int', 'Missing a closing curly brace'
            ],
            'no keys in object like array' => [
                '$param', 'array{no-keys}', 'Object like array must have keys and values'
            ],
            'no keys before : in object like array' => [
                '$param', 'array{:string}', 'Object like array must have keys and values'
            ],
            'no values after : in object like array' => [
                '$param', 'array{string: }', 'Object like array must have keys and values'
            ],
            'no keys before ?: in object like array' => [
                '$param', 'array{?:string}', 'Object like array must have keys and values'
            ],
            'no values after ?: in object like array' => [
                '$param', 'array{string?: }', 'Object like array must have keys and values'
            ],
            'no keys in deep object like array' => [
                '$param', 'array{key: array{no-keys}}', 'Object like array must have keys and values'
            ],
            'no keys before : in deep object like array' => [
                '$param', 'array{key: array{:string}}', 'Object like array must have keys and values'
            ],
            'no keys after : in deep object like array' => [
                '$param', 'array{key: array{string:}}', 'Object like array must have keys and values'
            ],
            'no keys before ?: in deep object like array' => [
                '$param', 'array{key: array{?:string}}', 'Object like array must have keys and values'
            ],
            'no keys after ?: in deep object like array' => [
                '$param', 'array{key: array{string?:}}', 'Object like array must have keys and values'
            ],
            'no keys before : outside deep object like array' => [
                '$param', 'array{:array{key: string}}', 'Object like array must have keys and values'
            ],
            'no keys before ?: outside deep object like array' => [
                '$param', 'array{?: array{key?:string}}', 'Object like array must have keys and values'
            ],
        ];
    }

    /**
     * @test
     * @dataProvider properFormDocblockDoesNothingDataProvider
     */
    public function properFormDocblockDoesNothing($typeString)
    {
        $actual = $this->testObj->lintCheck('$param', $typeString);
        $this->assertNull($actual);
    }

    /**
     * Data Provider
     */
    public function properFormDocblockDoesNothingDataProvider()
    {
        return [
            'psalm-array' => [
                'array<string, int>'
            ],
            'deep psalm-array' => [
                'array<array<int,array<string,float>>>'
            ],
            'deep psalm-array with list type' => [
                'array<array<string>|float[]|array<string,mixed>>'
            ],
            'simple type' => ['int'],
            'simple union type' => ['int|string'],
            'object like array type' => ['array{0: string|int, optional?: false}'],
            'deep object like array type' => ['array{0: string|class-string<stdClass>, optional?: false}'],
            'object like array type in object like array' => ['array{0: array{string: value}, optional?: false}']
        ];
    }
}
