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

use ReflectionException;
use ReflectionMethod;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class Parser
{
    /**
     *
     * @var ReflectionMethod
     */
    private $reflectionMethod;

    /**
     *
     * @param callable-string $callable
     * @throws ReflectionException
     */
    public function __construct($callable)
    {
        $this->reflectionMethod = new ReflectionMethod(...explode('::', $callable));
    }

    /**
     *
     * @return DocBlockParameter[]
     * @throws \Exception
     */
    public function getDocBlock(): array
    {
        $docblock = $this->reflectionMethod->getDocComment();
        if (!$docblock) {
            throw new \Exception;
        }
        preg_match_all('/@param.+/', $docblock, $matches);
        $params = $this->getParamsInParts($matches[0]);
        var_dump($params);
        var_dump($this->reflectionMethod->getParameters());
    }

    /**
     *
     * @param array $params
     * @return string[]
     */
    protected function getParamsInParts(array $params): array
    {
        $return = [];
        $numberOfRequired = $this->reflectionMethod->getNumberOfRequiredParameters();
        foreach ($params as $index => $param) {
            $raw = array_slice(array_filter(explode(' ', $param)), 1, 2);
            $raw[] = $numberOfRequired > $index;
            $return[] = array_combine(['type', 'name', 'required'], $raw);
        }
        return $return;
    }

}
