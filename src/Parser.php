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

use Aesonus\Paladin\Contracts\UseContextInterface;
use Aesonus\Paladin\DocBlock\ArrayParameter;
use Aesonus\Paladin\DocBlock\UnionParameter;
use Aesonus\Paladin\DocBlock\TypedClassStringParameter;
use Aesonus\Paladin\Exceptions\TypeLintException;
use Aesonus\Paladin\Contracts\ParameterInterface;
use function Aesonus\Paladin\Utilities\str_contains_chars;
use function Aesonus\Paladin\Utilities\str_contains_str;
use function Aesonus\Paladin\Utilities\strpos_default;

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

    public function __construct(UseContextInterface $useContext, ?TypeLinter $typeLinter = null)
    {
        $this->useContext = $useContext;
        $this->typeLinter = $typeLinter ?? new TypeLinter;
    }

    /**
     *
     * @param string $docblock
     * @param int $requiredParamCount
     * @return array<array-key, ParameterInterface>
     * @throws TypeLintException
     */
    public function getDocBlock(string $docblock, int $requiredParamCount): array
    {
        $matches = [];
        preg_match_all('/@param.+/', $docblock, $matches);
        /**
         * @psalm-suppress MixedArgument
         */
        $params = $this->getParamsInParts($matches[0], $requiredParamCount);
        try {
            return $this->constructDocBlockParameters($params);
        } catch (TypeLintException $exc) {
            throw $exc;
        }
    }

    /**
     *
     * @param array<int, array{name: string, type: string, required: bool}> $paramParts
     * @return array<array-key, ParameterInterface>
     */
    protected function constructDocBlockParameters(array $paramParts): array
    {
        $return = [];
        foreach ($paramParts as $param) {
            $this->typeLinter->lintCheck($param['name'], $param['type']);
            $return[] = new UnionParameter(
                $param['name'],
                $this->parseTypes($param['type']),
                $param['required']
            );
        }
        return $return;
    }

    /**
     *
     * @param string $typeString
     * @return array<array-key, ParameterInterface|string>
     */
    protected function parseTypes(string $typeString): array
    {
        $unionTypes = $this->parseUnionTypes($typeString);

        return array_map([$this, 'parseParameterizedTypes'], $unionTypes);
    }

    /**
     *
     * @param string $typeString
     * @return string[]
     */
    protected function parseUnionTypes(string $typeString): array
    {
        $matches = [];
        //simple, psalm-array or class-string, intersection
        $pattern = '(\([a-z|\[\]<>\-&\\\]+\)\[\])|(array<[-\(\) a-z|,<\[\]]+>+)|([a-z-<\[\]&\\\]+>*)';
        preg_match_all(
            "/$pattern/i",
            $typeString,
            $matches
        );
        /**
         * @psalm-suppress MixedArgument
         */
        return $this->processParamPatternMatches($matches[0]);
    }

    /**
     *
     * @param array $matches
     * @return string[]
     */
    private function processParamPatternMatches(array $matches): array
    {
        //echo "Raw:\n", var_dump($matches[0]);
        //We want to combine split parameter types back together if the are part of a
        //compound psalm array type (whew)
        $concat = false;
        /**  @var string[] $return */
        $return = array_reduce($matches, function (array $carry, string $param) use (&$concat): array {
            if (!$concat) {
                $carry[] = $param;
            } else {
                /** @psalm-suppress MixedOperand */
                $carry[array_key_last($carry) ?? 0] .= "|$param";
            }
            /** @var string $newCarry */
            $newCarry = $carry[array_key_last($carry) ?? 0];
            $concat = substr_count($newCarry, '<') !== substr_count($newCarry, '>');
            return $carry;
        }, []);
        return $return;
        //echo "Reduced:\n", var_dump($return);
    }

    /**
     *
     * @param string $typeString
     * @return ArrayParameter|TypedClassStringParameter|string
     */
    protected function parseParameterizedTypes(string $typeString)
    {
        /** @var int $openingArrow */
        $openingArrow = strpos_default($typeString, '<', self::MAX_LENGTH);
        /** @var int $openingParenthises */
        $openingParenthises = strpos_default($typeString, '(', self::MAX_LENGTH);
        if (str_contains_chars('<>', $typeString)
            && $openingArrow < $openingParenthises
        ) {
            if (str_contains_str($typeString, 'array')) {
                $types = $this->parsePsalmArrayType($typeString);
                return new ArrayParameter('array', $types[0], $types[1]);
            }
            if (str_contains_str($typeString, 'class-string')) {
                //Parse class-string types
                return new TypedClassStringParameter(
                    'class-string',
                    $this->parsePsalmClassStringTypes($typeString)
                );
            }
        }
        if (str_contains_str($typeString, '[]')) {
            $types = $this->parsePsrArrayType($typeString);
            return new ArrayParameter('array', 'int', $types);
        }
        return $typeString;
    }

    /**
     *
     * @param string $typeString
     * @return string[]
     */
    private function parsePsalmClassStringTypes(string $typeString): array
    {
        $classTypes = explode('|', substr($typeString, (int)strpos($typeString, '<') + 1, -1));
        return array_map(function ($type) {
            $rawClass = (strpos($type, '\\') === 0) ? substr($type, 1): $type;
            return $this->useContext->getUsedClass($rawClass);
        }, $classTypes);
    }

    /**
     *
     * @param string $typeString
     * @return array<array-key, ParameterInterface|string>
     */
    private function parsePsrArrayType(string $typeString): array
    {
        $openingParenth = strpos($typeString, '(');
        $closingParenth = (int)strpos($typeString, ')');
        if ($openingParenth !== false) {
            return $this->parseTypes(
                substr(
                    $typeString,
                    $openingParenth + 1,
                    $closingParenth - $openingParenth - 1
                )
            );
        }
        return [substr($typeString, 0, -2)];
    }

    /**
     *
     * @param string $typeString
     * @return array{0: string, 1: array<array-key, ParameterInterface|string>}
     */
    private function parsePsalmArrayType(string $typeString): array
    {
        $openingArrow = (int)strpos($typeString, '<');
        $arrayTypeString = substr($typeString, $openingArrow + 1, -1);
        $types = preg_split('`,(?![\w \[\]]+>)`', $arrayTypeString);
        //echo "Split Psalm\n", var_dump($types);
        if (count($types) === 1) {
            return ['array-key', $this->parseTypes($types[0])];
        }
        $keyType = array_shift($types);
        return [$keyType, $this->parseTypes($types[0])];
    }

    /**
     *
     * @param array<int, string> $params
     * @return array<int, array{name: string, type: string, required: bool}>
     */
    protected function getParamsInParts(array $params, int $numberOfRequired): array
    {
        $return = [];
        foreach ($params as $index => $param) {
            $raw = array_slice(array_filter(preg_split('/(?<!,) /', $param)), 1, 2);
            $raw[] = $numberOfRequired > $index;
            /** @var array{name: string, type: string, required: bool} */
            $return[] = array_combine(['type', 'name', 'required'], $raw);
        }
        return $return;
    }
}
