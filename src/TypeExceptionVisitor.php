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
namespace Aesonus\Paladin;

use Aesonus\Paladin\Contracts\ParameterInterface;
use Aesonus\Paladin\Contracts\TypeExceptionVisitorInterface;
use Aesonus\Paladin\DocBlock\ArrayParameter;
use Aesonus\Paladin\DocBlock\IntersectionParameter;
use Aesonus\Paladin\DocBlock\UnionParameter;
use InvalidArgumentException;
use function Aesonus\Paladin\Utilities\array_last;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class TypeExceptionVisitor implements TypeExceptionVisitorInterface
{
    /**
     *
     * @var mixed
     */
    private $givenValue;

    /**
     *
     * @param mixed $givenValue
     */
    public function __construct($givenValue)
    {
        $this->givenValue = $givenValue;
    }

    /**
     * {@inheritdoc}
     */
    public function visitUnion(UnionParameter $docblock): void
    {
        if (!$docblock->validate($this->givenValue)) {
            throw new InvalidArgumentException($this->getExceptionMessage($docblock));
        }
    }

    protected function getExceptionMessage(ParameterInterface $docblock): string
    {
        return $docblock->getName()
            . ' must be '
            . $this->getExceptedTypeMessage($docblock)
            . '; '
            . $this->getGivenTypeMessage($docblock)
            .' given';
    }

    protected function getTypeClause($type): string
    {
        if ($type instanceof ArrayParameter) {
            return $this->getArrayType($type);
        } elseif ($type instanceof IntersectionParameter) {
            return $this->getIntersectionType($type);
        } elseif (is_class_string($type)) {
            return 'instance of ' . $type;
        } elseif (is_string($type) && $type === 'object') {
            return 'an object';
        } elseif (is_string($type)) {
            return 'of type ' . $type;
        }
    }

    protected function getArrayType(ArrayParameter $docblock): string
    {
        $keyType = $docblock->getKeyType();
        $typeString = implode('|', $docblock->getTypes());
        return sprintf('an array of type <%s, %s>', $keyType, $typeString);
    }

    protected function getIntersectionType(IntersectionParameter $docblock): string
    {
        $message = 'an intersection of ';
        $message .= $this->getTypeListString($docblock->getTypes(), ', ', ', and ');
        return $message;
    }

    protected function getExceptedTypeMessage(ParameterInterface $docblock): string
    {
        $types = array_map([$this, 'getTypeClause'], $docblock->getTypes());
        return $this->getTypeListString($types, ', ', ', or ');
    }

    private function getTypeListString(array $types, string $glue, string $glueLast): string
    {
        if (count($types) < 3) {
            return implode($glueLast, $types);
        }
        $splitTypes = array_slice($types, 0, -1);
        return implode($glue, $splitTypes) . $glueLast . array_last($types);
    }

    protected function getGivenTypeMessage(ParameterInterface $docblock): string
    {
        if (is_object($this->givenValue)) {
            return 'instance of ' . get_class($this->givenValue);
        } elseif (is_array($this->givenValue)) {
            return $this->getGivenArrayType($this->givenValue);
        }
        return $this->getType($this->givenValue);
    }

    protected function getGivenArrayType(array $value): string
    {
        $foundKeyType = '';
        $foundTypes = [];
        foreach ($value as $key => $arrayValue) {
            if (is_object($arrayValue)) {
                $foundTypes[] = get_class($arrayValue);
            } else {
                $foundTypes[] = $this->getType($arrayValue);
            }
            if ($foundKeyType === 'array-key') {
                break;
            } elseif ($foundKeyType === 'int'
                && is_string($key)
                || $foundKeyType === 'string' && is_int($key)) {
                $foundKeyType = 'array-key';
                break;
            }
            $foundKeyType = $this->getType($key);
        }
        return sprintf('array of type <%s, %s>', $foundKeyType, implode('|', $foundTypes));
    }

    private function getType($value): string
    {
        return is_int($value) ? 'int' : gettype($value);
    }
}
