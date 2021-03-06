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
declare(strict_types=1);

namespace Aesonus\Paladin\Utilities;

/**
 * Returns all positions of $needle in $haystack. Returns empty array if none are found
 * @param string $haystack
 * @param string $needle
 * @return int[]
 */
function strpos_all(string $haystack, string $needle): array
{
    $return = [];
    $position = strpos($haystack, $needle);
    while ($position !== false) {
        $return[] = $position;
        $position = strpos($haystack, $needle, $position + strlen($needle));
    }
    return $return;
}

/**
 * Returns if $needle occurs in $haystack
 * @param string $haystack
 * @param string $needle
 * @return bool
 */
function str_contains_str(string $haystack, string $needle): bool
{
    return strpos($haystack, $needle) !== false;
}

/**
 * Returns the sign of given number, -1, 0, or 1
 * @param numeric $number
 * @return int
 */
function sign($number): int
{
    if ((float) $number > 0) {
        return 1;
    } elseif ((float) $number < 0) {
        return -1;
    }
    return 0;
}

/**
 * Returns the last element in the array
 * @param array $array
 * @return mixed
 */
function array_last(array $array)
{
    $key = array_key_last($array);
    if ($key !== null) {
        return $array[$key];
    }
    return null;
}

/**
 *
 * @param string $glue
 * @param string $glueLast
 * @param string[] $pieces
 * @return string
 */
function implode_ext(string $glue, string $glueLast, array $pieces): string
{
    if (count($pieces) < 3) {
        return implode($glueLast, $pieces);
    }
    $splitTypes = array_slice($pieces, 0, -1);
    /**
     * The array_last function will return a string since the pieces should be strings
     * @psalm-suppress MixedOperand
     */
    return implode($glue, $splitTypes) . $glueLast . array_last($pieces);
}
