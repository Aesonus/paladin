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

use Aesonus\Paladin\Contracts\ParameterInterface;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class ArrayParameter extends UnionParameter
{
    /**
     *
     * @var string
     */
    private $keyType;

    /**
     *
     * @param string $name
     * @param string $keyType
     * @param array<array-key, ParameterInterface|string> $valueType
     */
    public function __construct(string $name, string $keyType, array $valueType)
    {
        parent::__construct($name, $valueType, true);
        $this->keyType = $keyType;
    }

    /**
     *
     * @param mixed $givenValue
     * @return bool
     */
    public function validate($givenValue): bool
    {
        if (!is_array($givenValue)) {
            return false;
        }
        $valid = false;
        /** @var mixed $value */
        foreach ($givenValue as $key => $value) {
            $valid = parent::validate($value) && $this->validateUnionType([$this->keyType], $key);
            if (!$valid) {
                break;
            }
        }
        return $valid;
    }

    public function getKeyType(): string
    {
        return $this->keyType;
    }
}
