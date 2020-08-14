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

use function Aesonus\Paladin\Utilities\array_last;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class UnionTypeSplitter
{
    public function split(string $typeString): array
    {
        return $this->processParamPatternMatches(explode('|', str_replace(' ', '', $typeString)));
    }

    /**
     *
     * @param array<array-key, mixed> $matches
     * @return array<array-key, string>
     */
    private function processParamPatternMatches(array $matches): array
    {
        //echo "Raw:\n", var_dump($matches[0]);
        //We want to combine split parameter types back together if the are part of a
        //compound psalm array type (whew)
        $concat = false;
        /**  @var array<array-key, string> $return */
        $return = array_reduce($matches, function (array $carry, string $param) use (&$concat): array {
            if (!$concat) {
                $carry[] = $param;
            } else {
                /** @psalm-suppress MixedOperand */
                $carry[array_key_last($carry) ?? 0] .= "|$param";
            }
            /** @var string $newCarry */
            $newCarry = array_last($carry);
            $concat =
                substr_count($newCarry, '<') !== substr_count($newCarry, '>')
                || substr_count($newCarry, '(') !== substr_count($newCarry, ')');
            return $carry;
        }, []);
        return $return;
        //echo "Reduced:\n", var_dump($return);
    }
}
