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
namespace Aesonus\Paladin\Contracts;

use Aesonus\Paladin\Contracts\ParameterValidatorInterface;
use Aesonus\Paladin\Exceptions\ParseException;

/**
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
interface ParserInterface
{
    /**
     *
     * @param string $docblock
     * @return ParameterValidatorInterface[]
     */
    public function getDocBlockValidators(string $docblock): array;

    /**
     * Gets the current use context for class parsing
     * @return UseContextInterface
     */
    public function getUseContext(): UseContextInterface;

    /**
     * Parses a type string and returns all of its parts
     * @param string $typeString
     * @return ParameterValidatorInterface[]
     * @throws ParseException
     */
    public function parseTypeString(string $typeString): array;
}
