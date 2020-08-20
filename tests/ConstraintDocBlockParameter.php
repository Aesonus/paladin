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

namespace Aesonus\Tests;

use Aesonus\Paladin\Contracts\ParameterInterface;
use Aesonus\Paladin\DocBlock\UnionParameter;
use PHPUnit\Framework\Constraint\Constraint;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class ConstraintDocBlockParameter extends Constraint
{
    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var array
     */
    private $type;

    /**
     *
     * @var bool
     */
    private $required;

    /**
     *
     * @param string $name
     * @param array $type
     */
    public function __construct($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     *
     * @param ParameterInterface $other
     * @return bool
     */
    protected function matches($other): bool
    {
        if (!$other instanceof ParameterInterface) {
            return false;
        }
        $valid = true;
        foreach ($this->type as $index => $expectedType) {
            $otherType = $other->getTypes()[$index] ?? null;

            if ($expectedType instanceof ParameterInterface) {
                $constrait = new self($expectedType->getName(), $expectedType->getTypes());
                $valid = $constrait->matches($otherType);
            }
            if (is_string($expectedType) && $otherType !== $expectedType) {
                $valid = false;
                break;
            }
            if (!$valid) {
                break;
            }
        }
        return $other->getName() === $this->name
            && $valid;
    }

    public function toString(): string
    {
        return 'is ' . UnionParameter::class . ' with properties ' . $this->exporter()->export(
            [
                'name' => $this->name,
                'types' => $this->type,
            ]
        );
    }
}
