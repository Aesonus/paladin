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

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class IntersectionParameter extends UnionParameter
{
    /**
     *
     * @param string $name
     * @param array<array-key, string> $types
     */
    public function __construct(string $name, array $types)
    {
        parent::__construct($name, $types, true);
    }

    public function validate($givenValue): bool
    {
        $valid = false;
        /** @var string $intersectionTypes */
        foreach ($this->getTypes() as $intersectionTypes) {
            /** @var bool $valid */
            $valid = call_user_func(
                $this->getValidationCallable($intersectionTypes),
                $givenValue
            );
            if (!$valid) {
                break;
            }
        }
        return $valid;
    }
}