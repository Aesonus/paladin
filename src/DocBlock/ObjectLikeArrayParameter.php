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
use function Aesonus\Paladin\Utilities\implode_ext;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class ObjectLikeArrayParameter extends AbstractParameter implements ParameterInterface
{
    /**
     *
     * @var ParameterInterface[]
     */
    protected $requiredKeyPairs;

    /**
     *
     * @var ParameterInterface[]
     */
    protected $optionalKeyPairs;

    /**
     *
     * @param ParameterInterface[] $requiredKeyPairs
     * @param ParameterInterface[] $optionalKeyPairs
     */
    public function __construct(array $requiredKeyPairs, array $optionalKeyPairs = [])
    {
        $this->name = 'object-like-array';
        $this->requiredKeyPairs = $requiredKeyPairs;
        $this->optionalKeyPairs = $optionalKeyPairs;
    }

    public function validate($givenValue): bool
    {
        if (!is_array($givenValue)) {
            return false;
        }
        /** @var bool $valid */
        $valid = true;
        foreach ($this->requiredKeyPairs as $expectedKey => $valueType) {
            $valid = array_key_exists($expectedKey, $givenValue) && $valueType->validate($givenValue[$expectedKey]);
            if (!$valid) {
                return false;
            }
        }
        foreach ($this->optionalKeyPairs as $expectedKey => $valueType) {
            if (array_key_exists($expectedKey, $givenValue)) {
                $valid = $valueType->validate($givenValue[$expectedKey]);
            }
            if (!$valid) {
                return false;
            }
        }
        return true;
    }

    public function __toString(): string
    {
        $string = 'array{';
        $required = array_map(
            fn ($value, $key) => "$key: $value",
            $this->requiredKeyPairs,
            array_keys($this->requiredKeyPairs)
        );
        $optional = array_map(
            fn ($value, $key) => "$key?: $value",
            $this->optionalKeyPairs,
            array_keys($this->optionalKeyPairs)
        );
        $string .= implode(', ', $required);
        if (!empty($optional)) {
            $string .= ', ' . implode(', ', $optional);
        }
        $string .= "}";
        return $string;
    }
}
