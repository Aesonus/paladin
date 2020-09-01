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
namespace Aesonus\Paladin\ParameterValidators;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class TypedClassStringParameter extends ClassStringParameter
{
    /**
     *
     * @var array<array-key, string>
     */
    private $classTypes;

    /**
     *
     * @param string $name
     * @param array<array-key, class-string> $classTypes
     */
    public function __construct(array $classTypes)
    {
        $this->name = 'class-string';
        $this->classTypes = $classTypes;
    }

    public function validate($givenValue): bool
    {
        if (!parent::validate($givenValue)) {
            return false;
        }
        $valid = false;
        foreach ($this->classTypes as $className) {
            /**
             * @psalm-suppress MixedArgument This is suppressed because $givenValue
             * is asserted to be a string by the parent validate function
             */
            $valid = is_a($givenValue, $className, true);
            if ($valid) {
                break;
            }
        }
        return $valid;
    }

    public function __toString(): string
    {
        return "class-string<" . implode('|', $this->classTypes) . ">";
    }
}
