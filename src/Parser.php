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

namespace Aesonus\Paladin;

use Aesonus\Paladin\Contracts\DocblockSplitterInterface;
use Aesonus\Paladin\Contracts\ParameterValidatorInterface;
use Aesonus\Paladin\Contracts\ParserInterface;
use Aesonus\Paladin\Contracts\TypeLinterInterface;
use Aesonus\Paladin\Contracts\TypeStringParsingInterface;
use Aesonus\Paladin\Contracts\UseContextInterface;
use Aesonus\Paladin\ParameterValidators\UnionParameter;
use Aesonus\Paladin\Exceptions\ParseException;
use Aesonus\Paladin\TypeStringSplitter;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class Parser implements ParserInterface
{

    /**
     *
     * @var UseContextInterface
     */
    private $useContext;

    /**
     *
     * @var TypeLinterInterface
     */
    private $typeLinter;

    /**
     *
     * @var TypeStringSplitter
     */
    private $typeSplitter;

    /**
     *
     * @var TypeStringParsingInterface[]
     */
    private $parsers;

    /**
     *
     * @var DocblockSplitterInterface
     */
    private $docParamSplitter;

    /**
     *
     * @param UseContextInterface $useContext
     * @param DocblockSplitterInterface $docParamSplitter
     * @param TypeLinterInterface $typeLinter
     * @param TypeStringSplitter $typeSplitter
     * @param TypeStringParsingInterface[] $typeStringParsers
     */
    public function __construct(
        UseContextInterface $useContext,
        DocblockSplitterInterface $docParamSplitter,
        TypeLinterInterface $typeLinter,
        TypeStringSplitter $typeSplitter,
        array $typeStringParsers
    ) {
        $this->useContext = $useContext;
        $this->docParamSplitter = $docParamSplitter;
        $this->typeLinter = $typeLinter;
        $this->typeSplitter = $typeSplitter;
        $this->parsers = $typeStringParsers;
    }

    public function getDocBlockValidators(string $docblock): array
    {
        $params = $this->docParamSplitter->getDocblockParameters($docblock);
        return $this->constructDocBlockParameters($params);
    }

    public function parseTypeString(string $typeString): array
    {
        $unionTypes = $this->typeSplitter->split($typeString);

        return array_map(
            fn (string $typeString): ParameterValidatorInterface => $this->parseUnionTypes($typeString),
            $unionTypes
        );
    }

    public function getUseContext(): UseContextInterface
    {
        return $this->useContext;
    }

    /**
     *
     * @param array<int, array{name: string, type: string}> $paramParts
     * @return ParameterValidatorInterface[]
     */
    private function constructDocBlockParameters(array $paramParts): array
    {
        $return = [];
        foreach ($paramParts as $param) {
            $this->typeLinter->lintCheck($param['name'], $param['type']);
            $return[] = new UnionParameter(
                $param['name'],
                $this->parseTypeString($param['type'])
            );
        }
        return $return;
    }

    /**
     *
     * @param string $typeString
     * @return ParameterValidatorInterface
     * @throws ParseException
     */
    private function parseUnionTypes(string $typeString): ParameterValidatorInterface
    {
        foreach ($this->parsers as $parser) {
            try {
                return $parser->parse($this, $typeString);
            } catch (ParseException $exc) {
                continue;
            }
        }
        throw new ParseException($typeString);
    }
}
