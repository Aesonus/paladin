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

use Aesonus\Paladin\TypeLinter;
use Aesonus\TestLib\BaseTestCase;
use RuntimeException;

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
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Declared type is not valid for @param $type $name: $info");
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
        ];
    }
}
