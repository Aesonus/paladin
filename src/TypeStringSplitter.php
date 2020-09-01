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

use function Aesonus\Paladin\Utilities\array_last;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class TypeStringSplitter
{
    /**
     *
     * @param string $typeString
     * @param string $glue
     * @return array<array-key, string>
     */
    public function split(string $typeString, string $glue = '|'): array
    {
        return $this->processParamPatternMatches(explode($glue, str_replace(' ', '', $typeString)), $glue);
    }

    /**
     *
     * @param string[] $matches
     * @param string $glue
     * @return string[]
     */
    private function processParamPatternMatches(array $matches, string $glue): array
    {
        //We want to combine split parameter types back together if the are part of a
        //compound psalm array type (whew)
        /** @var bool $concat */
        $concat = false;
        $carry = [];
        foreach ($matches as $param) {
            $this->alterCarry($carry, $param, $glue, $concat);
            /** @var string $newCarry */
            $newCarry = array_last($carry);
            /** @var bool $concat */
            $concat =
                (substr_count($newCarry, '<') !== substr_count($newCarry, '>'))
                || (substr_count($newCarry, '(') !== substr_count($newCarry, ')'))
                || (substr_count($newCarry, '{') !== substr_count($newCarry, '}'));
        }
        /** @var string[] $carry */
        return $carry;
    }

    private function alterCarry(
        array &$carry,
        string $param,
        string $glue,
        bool $concat
    ): void {
        if (!$concat) {
            $carry[] = $param;
            return;
        }
        /** @psalm-suppress MixedOperand */
        $carry[array_key_last($carry) ?? 0] .= "$glue$param";
    }
}
