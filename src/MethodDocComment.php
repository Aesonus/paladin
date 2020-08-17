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

use ReflectionClass;
use ReflectionMethod;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class MethodDocComment
{
    /**
     *
     * @var ReflectionMethod
     */
    private $reflectionMethod;

    /**
     *
     * @param callable-string $method
     */
    public function __construct(string $method)
    {
        $this->reflectionMethod = new ReflectionMethod(...explode('::', $method));
    }

    public function get(): string
    {
        return $this->getParentClassMethod()->getDocComment();
    }

    protected function getParentClassMethod(): ReflectionMethod
    {
        $methodClass = $this->reflectionMethod->getDeclaringClass();
        //First look at interfaces
        $interfaces = $methodClass->getInterfaces();
        /** @var ReflectionClass $interface */
        foreach ($interfaces as $interface) {
            if (!$interface->hasMethod($this->reflectionMethod->getName())) {
                break;
            }
            return $interface->getMethod($this->reflectionMethod->getName());
        }
        //Then look at parents
        $parents = [];
        while ($methodClass = $methodClass->getParentClass()) {
            if (!$methodClass->hasMethod($this->reflectionMethod->getName())) {
                break;
            }
            $parents[] = $methodClass;
        }
        if (empty($parents)) {
            return $this->reflectionMethod;
        }
        return array_pop($parents)->getMethod($this->reflectionMethod->getName());
    }
}
