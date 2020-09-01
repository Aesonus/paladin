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
class ObjectParameter extends AbstractParameter
{
    /**
     *
     * @var null|string
     */
    private $ofClass = null;

    public function __construct(?string $ofClass = null)
    {
        $this->ofClass = $ofClass;
        $this->name = 'object';
    }

    public function validate($givenValue): bool
    {
        return is_object($givenValue)
            && (isset($this->ofClass) ? is_a($givenValue, $this->ofClass): true);
    }

    public function __toString(): string
    {
        return isset($this->ofClass) ? $this->ofClass: parent::__toString();
    }
}
