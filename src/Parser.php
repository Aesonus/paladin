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
use Aesonus\Paladin\Contracts\TypeStringParsingInterface;
use Aesonus\Paladin\Contracts\UseContextInterface;
use Aesonus\Paladin\DocBlock\UnionParameter;
use Aesonus\Paladin\Exceptions\ParseException;
use Aesonus\Paladin\Parsing\AtomicParser;
use Aesonus\Paladin\Parsing\PsalmArrayParser;
use Aesonus\Paladin\Parsing\PsalmClassStringParser;
use Aesonus\Paladin\Parsing\PsalmListParser;
use Aesonus\Paladin\Parsing\PsrArrayParser;
use Aesonus\Paladin\Parsing\UnionTypeSplitter;
use function Aesonus\Paladin\Utilities\get_str_positions;

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
     * @var TypeStringParsingInterface[]
     */
    private $parsers;

    /**
     *
     * @param UseContextInterface $useContext
     * @param null|TypeLinter $typeLinter
     * @param null|UnionTypeSplitter $typeSplitter
     * @param null|TypeStringParsingInterface[] $typeStringParsers
     */
    public function __construct(
        UseContextInterface $useContext,
        ?TypeLinter $typeLinter = null,
        ?UnionTypeSplitter $typeSplitter = null,
        ?array $typeStringParsers = null
    ) {
        $this->useContext = $useContext;
        $this->typeLinter = $typeLinter ?? new TypeLinter;
        $this->typeSplitter = $typeSplitter ?? new UnionTypeSplitter;
        $this->parsers = $typeStringParsers ?? [
            new PsalmArrayParser,
            new PsalmListParser,
            new PsrArrayParser,
            new PsalmClassStringParser,
            new AtomicParser,
        ];
    }

    /**
     *
     * @param string $docblock
     * @return ParameterInterface[]
     */
    public function getDocBlockValidators(string $docblock): array
    {
        $matches = $this->getParamMatches($docblock);
        $params = $this->getParamsInParts($matches);
        return $this->constructDocBlockParameters($params);
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
                $this->parseTypeString($param['type'])
            );
        }
        return $return;
    }

    /**
     * Parses a type string and returns all of its parts
     * @param string $typeString
     * @return ParameterInterface[]
     * @throws ParseException
     */
    public function parseTypeString(string $typeString): array
    {
        $unionTypes = $this->typeSplitter->split($typeString);

        return array_map(
            fn (string $typeString): ParameterInterface => $this->parseUnionTypes($typeString),
            $unionTypes
        );
    }

    /**
     *
     * @param string $typeString
     * @return ParameterInterface
     * @throws ParseException
     */
    protected function parseUnionTypes(string $typeString): ParameterInterface
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
