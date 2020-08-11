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
namespace Aesonus\Paladin;

use RuntimeException;
use function Aesonus\Paladin\Utilities\str_count;

/**
 * Checks parameter types defined in a doc block for errors
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class TypeLinter
{
    const MISSING_OPENING_PARENTHESIS = 'Missing an opening parenthesis';
    const MISSING_CLOSING_PARENTHESIS = 'Missing a closing parenthesis';
    const MISSING_OPENING_ARROW = 'Missing an opening arrow';
    const MISSING_CLOSING_ARROW = 'Missing a closing arrow';

    /**
     *
     * @param string $paramName
     * @param string $typeString
     * @return void
     * @throws RuntimeException
     */
    public function lintCheck(string $paramName, string $typeString): void
    {
        if (str_count('(', $typeString) < str_count(')', $typeString)) {
            $this->throwExceptionWithMessage(
                $paramName,
                $typeString,
                self::MISSING_OPENING_PARENTHESIS
            );
        }
        if (str_count('(', $typeString) > str_count(')', $typeString)) {
            $this->throwExceptionWithMessage(
                $paramName,
                $typeString,
                self::MISSING_CLOSING_PARENTHESIS
            );
        }
        if (str_count('<', $typeString) < str_count('>', $typeString)) {
            $this->throwExceptionWithMessage(
                $paramName,
                $typeString,
                self::MISSING_OPENING_ARROW
            );
        }
        if (str_count('<', $typeString) > str_count('>', $typeString)) {
            $this->throwExceptionWithMessage(
                $paramName,
                $typeString,
                self::MISSING_CLOSING_ARROW
            );
        }
    }

    private function throwExceptionWithMessage(string $paramName, string $typeString, string $info): void
    {
        throw new RuntimeException("Declared type is not valid for @param $typeString $paramName: $info");
    }
}
