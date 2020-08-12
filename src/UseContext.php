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

use Aesonus\Paladin\Contracts\UseContextInterface;
use Kdyby\ParseUseStatements\UseStatements;
use ReflectionClass;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class UseContext implements UseContextInterface
{
    /**
     *
     * @var ReflectionClass
     */
    private $reflectionClass;

    /**
     *
     * @var UseStatements
     */
    private $useStatementsParser;

    /**
     *
     * @param class-string|object $contextClass
     * @param null|UseStatements $useStatementParser [optional]
     */
    public function __construct($contextClass, ?UseStatements $useStatementsParser = null)
    {
        $this->reflectionClass = new ReflectionClass($contextClass);
        $this->useStatementsParser = $useStatementsParser ?? new UseStatements();
    }

    /**
     * {@inheritdoc}
     */
    public function getUsedClass(string $useAlias): string
    {
        if (class_exists($useAlias) || interface_exists($useAlias)) {
            return $useAlias;
        }
        /** @var class-string */
        return $this->useStatementsParser->expandClassName($useAlias, $this->reflectionClass);
    }
}
