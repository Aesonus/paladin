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

namespace Aesonus\Paladin\ParameterValidators;

use Aesonus\Paladin\Contracts\ParameterValidatorInterface;
use Aesonus\Paladin\Contracts\TypeExceptionVisitorInterface;
use Aesonus\Paladin\Exceptions\TypeException;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class UnionParameter extends AbstractParameter
{

    /**
     *
     * @var ParameterValidatorInterface[]
     */
    protected array $types = [];

    /**
     *
     * @param string $name
     * @param ParameterValidatorInterface[] $types
     */
    public function __construct(string $name, array $types)
    {
        $this->name = $name;
        $this->types = $types;
    }

    /**
     *
     * @return ParameterValidatorInterface[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     *
     * @param TypeExceptionVisitorInterface $visitor
     * @return void
     * @throws TypeException
     */
    public function acceptExceptionVisitor(TypeExceptionVisitorInterface $visitor): void
    {
        $visitor->visitParameter($this);
    }



    /**
     * {@inheritdoc}
     */
    public function validate($givenValue): bool
    {
        return $this->validateUnionType($this->getTypes(), $givenValue);
    }

    /**
     *
     * @param ParameterValidatorInterface[] $types
     * @param mixed $givenValue
     * @return bool
     */
    protected function validateUnionType(array $types, $givenValue): bool
    {
        $valid = false;
        foreach ($types as $type) {
            $valid = $type->validate($givenValue);
            if ($valid) {
                break;
            }
        }
        return $valid;
    }

    public function __toString(): string
    {
        return implode('|', $this->getTypes());
    }
}
