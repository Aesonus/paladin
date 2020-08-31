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
use Aesonus\Paladin\DocblockParameters\ObjectLikeArrayParameter;
use Aesonus\Paladin\DocblockParameters\UnionParameter;
use Aesonus\Paladin\Exceptions\ParseException;
use Aesonus\Paladin\Parser;
use function Aesonus\Paladin\Utilities\str_contains_str;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class ObjectLikeArrayParser implements TypeStringParsingInterface
{
    /**
     *
     * @var TypeStringSplitter
     */
    private $stringSplitter;

    public function __construct(TypeStringSplitter $stringSplitter)
    {
        $this->stringSplitter = $stringSplitter;
    }

    public function assertThatStringCanBeParsed(string $typeString): void
    {
        if (strpos($typeString, 'array{') !== 0) {
            throw new ParseException($typeString);
        }
    }

    public function parse(Parser $parser, string $typeString): ParameterInterface
    {
        $this->assertThatStringCanBeParsed($typeString);
        $openingBrace = (int) strpos($typeString, '{');
        $segments = $this->stringSplitter->split(substr($typeString, $openingBrace + 1, -1), ',');

        $requiredParameters = [];
        $optionalParameters = [];

        foreach ($segments as $segment) {
            list($key, $valueTypeString) = $this->stringSplitter->split($segment, ':');
            $valueTypes = new UnionParameter('value', $parser->parseTypeString($valueTypeString));
            if (strpos($key, '?') !== false) {
                $optionalParameters[substr($key, 0, -1)] = $valueTypes;
                continue;
            }
            $requiredParameters[$key] = $valueTypes;
        }

        return new ObjectLikeArrayParameter($requiredParameters, $optionalParameters);
    }
}
