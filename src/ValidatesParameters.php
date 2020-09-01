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

use Aesonus\Paladin\Contracts\TypeExceptionVisitorInterface;
use Aesonus\Paladin\Exceptions\TypeException;
use Aesonus\Paladin\Factories\MethodParserFactory;
use InvalidArgumentException;

/**
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
trait ValidatesParameters
{
    /**
     *
     * @var null|Parser
     */
    private $parser = null;

    /**
     *
     * @param callable-string $method
     * @param mixed[] $args
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validate(callable $method, array $args): void
    {
        $docblock = (new MethodDocblockSource($method))->get();
        $validators = $this->getParser()->getDocBlockValidators($docblock);
        /** @var mixed $argValue */
        foreach ($args as $i => $argValue) {
            if (!array_key_exists($i, $validators)) {
                break;
            }
            try {
                $exceptionVisitor = $this->getTypeExceptionVisitor($argValue);
                $validators[$i]->acceptExceptionVisitor($exceptionVisitor);
            } catch (TypeException $exc) {
                throw new InvalidArgumentException(
                    $method . ': Parameter ' . $i . ' '
                        . $exc->getMessage()
                );
            }
        }
    }

    private function getParser(): Parser
    {
        if (!isset($this->parser)) {
            $this->parser = MethodParserFactory::createParser(get_class($this));
        }
        return $this->parser;
    }

    /**
     *
     * @param mixed $givenValue
     * @return TypeExceptionVisitorInterface
     */
    private function getTypeExceptionVisitor($givenValue): TypeExceptionVisitorInterface
    {
        return new TypeExceptionVisitor($givenValue);
    }
}
