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

use Aesonus\Paladin\Contracts\ParameterValidatorInterface;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class ArrayParameter extends UnionParameter
{
    /**
     *
     * @var ParameterValidatorInterface
     */
    private $keyType;

    /**
     *
     * @param ParameterValidatorInterface|null $keyType
     * @param ParameterValidatorInterface[]|null $valueType
     */
    public function __construct(?ParameterValidatorInterface $keyType = null, ?array $valueType = null)
    {
        parent::__construct('array', $valueType ?? [new MixedParameter]);
        $this->keyType = $keyType ?? new ArrayKeyParameter;
    }

    /**
     *
     * @psalm-assert-if-true array $givenValue
     * @param mixed $givenValue
     * @return bool
     */
    public function validate($givenValue): bool
    {
        if (!is_array($givenValue)) {
            return false;
        }
        $valid = true;
        /** @var mixed $value */
        foreach ($givenValue as $key => $value) {
            $valid = parent::validate($value) && $this->keyType->validate($key);
            if (!$valid) {
                break;
            }
        }
        return $valid;
    }

    public function getKeyType(): ParameterValidatorInterface
    {
        return $this->keyType;
    }

    public function __toString(): string
    {
        if ($this->getKeyType() instanceof ArrayKeyParameter
            && count($this->getTypes()) === 1
            && $this->getTypes()[0] instanceof MixedParameter) {
            return 'array';
        }
        return sprintf('array<%s, %s>', (string)$this->getKeyType(), parent::__toString());
    }
}
