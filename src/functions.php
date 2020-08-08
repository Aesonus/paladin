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

declare (strict_types=1);

if (!function_exists('is_array_key')) {
    function is_array_key($value): bool
    {
        return is_int($value) || is_string($value);
    }
}

if (!function_exists('is_class_string')) {
    function is_class_string($value, ?string $ofType = null): bool
    {
        return is_string($value) && class_exists($value);
    }
}

if (!function_exists('is_trait_string')) {
    function is_trait_string($value): bool
    {
        return is_string($value) && trait_exists($value);
    }
}

if (!function_exists('is_callable_string')) {
    function is_callable_string($value): bool
    {
        return is_string($value) && is_callable($value);
    }
}

if (!function_exists('is_numeric_string')) {
    function is_numeric_string($value): bool
    {
        return is_string($value) && is_numeric($value);
    }
}

if (!function_exists('is_true')) {
    function is_true($value): bool {
        return true === $value;
    }
}

if (!function_exists('is_false')) {
    function is_false($value): bool {
        return false === $value;
    }
}