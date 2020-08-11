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

declare (strict_types=1);

namespace Aesonus\Paladin;

use Aesonus\Paladin\Contracts\ParameterInterface;
use const Aesonus\Paladin\FUNCTION_NAMESPACE;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class DocBlockParameter implements ParameterInterface
{
    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var bool
     */
    private $required;

    /**
     *
     * @var (string|DocBlockParameter)array
     */
    private $types;

    /**
     *
     * @param string $name
     * @param array $types
     * @param bool $required
     */
    public function __construct(string $name, array $types, bool $required)
    {
        $this->name = $name;
        $this->types = $types;
        $this->required = $required;
    }

    public function validate($givenValue): bool
    {
        return $this->validateUnionType($this->getTypes(), $givenValue);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getTypes(): array
    {
        return $this->types;
    }

    protected function validateUnionType(array $types, $givenValue): bool
    {
        $valid = false;
        foreach ($types as $unionTypes) {
            $valid = call_user_func(
                $this->getValidationCallable($unionTypes),
                $givenValue
            );
            if ($valid) {
                break;
            }
        }
        return $valid;
    }

    protected function getValidationCallable($type): callable
    {
        if ($type instanceof DocBlockParameter) {
            return [$type, 'validate'];
        }
        if ('mixed' === $type) {
            return fn() => true;
        }
        $builtInCallable = 'is_' . $type;
        if (function_exists($builtInCallable)) {
            return $builtInCallable;
        }
        $simplePsalmTypeCallable = sprintf(
            '%sis_%s',
            FUNCTION_NAMESPACE,
            str_replace('-', '_', $type)
        );
        if (function_exists($simplePsalmTypeCallable)) {
            return $simplePsalmTypeCallable;
        }
        if (is_class_string($type)) {
            return fn($value) => is_a($value, $type);
        }
    }
}
