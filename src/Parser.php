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

use Aesonus\Paladin\Contracts\ParameterInterface;
use Aesonus\Paladin\Contracts\UseContextInterface;
use Aesonus\Paladin\DocBlock\UnionParameter;
use Aesonus\Paladin\Exceptions\TypeLintException;
use Aesonus\Paladin\Parsing\PsalmArrayParser;
use Aesonus\Paladin\Parsing\PsalmClassStringParser;
use Aesonus\Paladin\Parsing\PsalmListParser;
use Aesonus\Paladin\Parsing\PsrArrayParser;
use Aesonus\Paladin\Parsing\UnionTypeSplitter;
use function Aesonus\Paladin\Utilities\get_str_positions;
use function Aesonus\Paladin\Utilities\str_contains_str;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class Parser
{
    const MAX_LENGTH = 99999999999999999999999999999999999999999999;

    /**
     *
     * @var UseContextInterface
     */
    private $useContext;

    /**
     *
     * @var TypeLinter
     */
    private $typeLinter;

    /**
     *
     * @var UnionTypeSplitter
     */
    private $typeSplitter;

    /**
     *
     * @var PsalmArrayParser
     */
    private $psalmArrayParser;

    /**
     *
     * @var PsalmListParser
     */
    private $psalmListParser;

    /**
     *
     * @var PsalmClassStringParser
     */
    private $classStringParser;

    /**
     *
     * @var PsrArrayParser
     */
    private $psrArrayParser;

    public function __construct(
        UseContextInterface $useContext,
        ?UnionTypeSplitter $typeSplitter = null,
        ?PsalmArrayParser $psalmArrayParser = null,
        ?PsalmListParser $psalmListParser = null,
        ?PsrArrayParser $psrArrayParser = null,
        ?PsalmClassStringParser $classStringParser = null,
        ?TypeLinter $typeLinter = null
    ) {
        $this->useContext = $useContext;
        $this->typeLinter = $typeLinter ?? new TypeLinter;
        $this->typeSplitter = $typeSplitter ?? new UnionTypeSplitter;
        $this->psalmArrayParser = $psalmArrayParser ?? new PsalmArrayParser;
        $this->psalmListParser = $psalmListParser ?? new PsalmListParser;
        $this->psrArrayParser = $psrArrayParser ?? new PsrArrayParser;
        $this->classStringParser = $classStringParser ?? new PsalmClassStringParser;
    }

    /**
     *
     * @param string $docblock
     * @return ParameterInterface[]
     * @throws TypeLintException
     */
    public function getDocBlock(string $docblock): array
    {
        $matches = $this->getParamMatches($docblock);
        $params = $this->getParamsInParts($matches);
        try {
            return $this->constructDocBlockParameters($params);
        } catch (TypeLintException $exc) {
            throw $exc;
        }
    }

    /**
     *
     * @param string $docblock
     * @return list<string>
     */
    private function getParamMatches(string $docblock): array
    {
        $matches = [];
        preg_match_all('/@param.+/', $docblock, $matches);
        /** @var list<list<string>> $matches */
        return $matches[0];
    }

    public function getUseContext(): UseContextInterface
    {
        return $this->useContext;
    }

    /**
     *
     * @param array<int, array{name: string, type: string}> $paramParts
     * @return ParameterInterface[]
     */
    protected function constructDocBlockParameters(array $paramParts): array
    {
        $return = [];
        foreach ($paramParts as $param) {
            $this->typeLinter->lintCheck($param['name'], $param['type']);
            $return[] = new UnionParameter(
                $param['name'],
                $this->parseTypes($param['type'])
            );
        }
        return $return;
    }

    /**
     * Parses a type string and returns all of its parts
     * @param string $typeString
     * @return array<array-key, ParameterInterface|string>
     */
    public function parseTypes(string $typeString): array
    {
        $unionTypes = $this->typeSplitter->split($typeString);

        return array_map([$this, 'parseParameterizedTypes'], $unionTypes);
    }

    /**
     *
     * @param string $typeString
     * @return ParameterInterface|string
     */
    protected function parseParameterizedTypes(string $typeString)
    {
        $parseables = get_str_positions($typeString, '(', 'array<', 'list<', 'class-string<');
        $isPsrArray = (int)strrpos($typeString, '[]') + 2 === strlen($typeString);
        foreach ($parseables as $parseableType) {
            switch ($parseableType['str']) {
                case '(':
                    return $this->psrArrayParser->parse($this, $typeString);
                case 'array<':
                    return $isPsrArray ? $this->psrArrayParser->parse($this, $typeString)
                        : $this->psalmArrayParser->parse($this, $typeString);
                case 'list<':
                    return $isPsrArray ? $this->psrArrayParser->parse($this, $typeString)
                        : $this->psalmListParser->parse($this, $typeString);
                case 'class-string<':
                    return $isPsrArray ? $this->psrArrayParser->parse($this, $typeString)
                        : $this->classStringParser->parse($this, $typeString);
            }
        }
        if ($isPsrArray) {
            return $this->psrArrayParser->parse($this, $typeString);
        }
        if (str_contains_str($typeString, 'list')) {
            return $this->psalmListParser->parse($this, $typeString);
        }
        return $typeString;
    }

    /**
     *
     * @param array<int, string> $params
     * @return array<int, array{name: string, type: string}>
     */
    protected function getParamsInParts(array $params): array
    {
        $return = [];
        foreach ($params as $param) {
            $raw = array_slice(array_filter(preg_split('/(?<!,) /', $param)), 1, 2);
            /** @var array{name: string, type: string, required: bool} */
            $return[] = array_combine(['type', 'name'], $raw);
        }
        return $return;
    }
}
