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

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class PsalmArrayParser implements TypeStringParsingInterface
{
    /**
     *
     * @param Parser $parser
     * @param string $typeString
     * @return ParameterInterface
     */
    public function parse(Parser $parser, string $typeString): ParameterInterface
    {
        $openingArrow = (int)strpos($typeString, '<');
        $arrayTypeString = substr($typeString, $openingArrow + 1, -1);
        $types = preg_split('`,(?![\w \[\]]+>)`', $arrayTypeString);
        //echo "Split Psalm\n", var_dump($types);
        if (count($types) === 1) {
            return new ArrayParameter('array', 'array-key', $parser->parseTypes($types[0]));
        }
        $keyType = array_shift($types);
        return new ArrayParameter('array', $keyType, $parser->parseTypes($types[0]));
    }
}