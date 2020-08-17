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
namespace Aesonus\Paladin\Parsing;

use Aesonus\Paladin\Contracts\ParameterInterface;
use Aesonus\Paladin\Contracts\TypeStringParsingInterface;
use Aesonus\Paladin\DocBlock\ArrayParameter;
use Aesonus\Paladin\Parser;
use function Aesonus\Paladin\Utilities\str_contains_str;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class PsrArrayParser implements TypeStringParsingInterface
{
    public function parse(Parser $parser, string $typeString): ParameterInterface
    {
        $openingParenth = strpos($typeString, '(');
        $closingParenth = (int)strrpos($typeString, ')');
        if ($openingParenth !== false) {
            return new ArrayParameter(
                'array',
                'array-key',
                $parser->parseTypes(
                    substr(
                        $typeString,
                        $openingParenth + 1,
                        $closingParenth - $openingParenth - 1
                    )
                )
            );
        }
        $newTypeString = substr($typeString, 0, -2);
        return new ArrayParameter(
            'array',
            'array-key',
            $parser->parseTypes($newTypeString)
        );
    }
}
