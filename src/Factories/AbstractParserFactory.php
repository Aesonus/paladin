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
namespace Aesonus\Paladin\Factories;

use Aesonus\Paladin\Contracts\ParserInterface;
use Aesonus\Paladin\Contracts\TypeStringParsingInterface;
use Aesonus\Paladin\Parsing\AtomicParser;
use Aesonus\Paladin\Parsing\ObjectLikeArrayParser;
use Aesonus\Paladin\Parsing\PsalmArrayParser;
use Aesonus\Paladin\Parsing\PsalmClassStringParser;
use Aesonus\Paladin\Parsing\PsalmListParser;
use Aesonus\Paladin\Parsing\PsrArrayParser;
use Aesonus\Paladin\Parsing\TypeStringSplitter;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
abstract class AbstractParserFactory
{
    /**
     *
     * @param class-string $useContext The class that this parser parses for
     * @return ParserInterface
     */
    abstract public static function createParser(string $useContext): ParserInterface;

    /**
     *
     * @param TypeStringSplitter $stringSplitter
     * @return TypeStringParsingInterface[]
     */
    protected static function getTypeStringParsers(TypeStringSplitter $stringSplitter): array
    {
        return [
            new PsalmArrayParser(),
            new PsalmClassStringParser(),
            new PsalmListParser(),
            new PsrArrayParser(),
            new ObjectLikeArrayParser($stringSplitter),
            new AtomicParser(),
        ];
    }
}
