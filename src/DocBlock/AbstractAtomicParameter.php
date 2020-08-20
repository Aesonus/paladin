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
namespace Aesonus\Paladin\DocBlock;

use const Aesonus\Paladin\FUNCTION_NAMESPACE;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
abstract class AbstractAtomicParameter extends AbstractParameter
{
    public function __construct()
    {
        $matches = [];
        preg_match_all('/[A-Z][a-z]+(?=\w*Parameter)/', static::class, $matches);
        /** @var array<int, array<int, string>> $matches */
        $this->name = implode('-', array_map('strtolower', $matches[0]));
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function validate($givenValue): bool
    {
        $type = $this->getName();
        if ('mixed' === $type) {
            return true;
        }
        $builtInCallable = 'is_' . $type;
        if (function_exists($builtInCallable)) {
            /** @var callable(mixed):bool $builtInCallable */
            return $builtInCallable($givenValue);
        }

        $psalmTypeCallable = sprintf(
            '%sis_%s',
            FUNCTION_NAMESPACE,
            str_replace('-', '_', $type)
        );
        if (function_exists($psalmTypeCallable)) {
            /** @var callable(mixed):bool $psalmTypeCallable */
            return $psalmTypeCallable($givenValue);
        }
        return false;
    }
}
