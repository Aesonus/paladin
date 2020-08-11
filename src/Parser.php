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

use Aesonus\Paladin\Contracts\UseContextInterface;
use Exception;
use function Aesonus\Paladin\Utilities\str_contains_chars;
use function Aesonus\Paladin\Utilities\str_contains_str;
use function Aesonus\Paladin\Utilities\str_count;
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

    public function __construct(?UseContextInterface $useContext = null)
    {
        $this->useContext = $useContext;
    }

    /**
     *
     * @param string $docblock
     * @param int $numberOfRequiredParameters
     * @return DocBlockParameter[]
     * @throws Exception
     */
    public function getDocBlock(string $docblock, int $numberOfRequiredParameters): array
    {
        preg_match_all('/@param.+/', $docblock, $matches);
        $params = $this->getParamsInParts($matches[0], $numberOfRequiredParameters);
        return $this->constructDocBlockParameters($params);
    }

    protected function constructDocBlockParameters(array $paramParts): array
    {
        $return = [];
        foreach ($paramParts as $param) {
            $return[] = new DocBlockParameter(
                $param['name'],
                $this->parseTypes($param['type']),
                $param['required']
            );
        }
        return $return;
    }

    protected function parseTypes(string $typeString): array
    {
        $unionTypes = $this->parseUnionTypes($typeString);

        return array_map([$this, 'parseParameterizedTypes'], $unionTypes);
    }

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
        return $this->processParamPatternMatches($matches[0]);
    }

    private function processParamPatternMatches(array $matches): array
    {
        //echo "Raw:\n", var_dump($matches[0]);
        //We want to combine split parameter types back together if the are part of a
        //compound psalm array type (whew)
        $concat = false;
        return array_reduce($matches, function ($carry, $param) use (&$concat) {
            if (!$concat) {
                $carry[] = $param;
            } else {
                $carry[array_key_last($carry)] .= "|$param";
            }
            $new_carry = $carry[array_key_last($carry)];
            $concat = str_count('<', $new_carry) !== str_count('>', $new_carry);
            return $carry;
        }, []);
        //echo "Reduced:\n", var_dump($return);
    }

    protected function parseParameterizedTypes(string $typeString)
    {
        $openingArrow = strpos_default($typeString, '<', self::MAX_LENGTH);
        $openingParenthises = strpos_default($typeString, '(', self::MAX_LENGTH);
        if (
            str_contains_chars('<>', $typeString)
            && $openingArrow < $openingParenthises
        )
        {
            if (str_contains_str($typeString, 'array')) {
                $types = $this->parsePsalmArrayType($typeString);
                return new DocBlockArrayParameter('array', ...$types);
            }
            if (str_contains_str($typeString, 'class-string')) {
                //Parse class-string types
                return new DocBlockTypedClassStringParameter(
                    'class-string',
                    $this->parsePsalmClassStringTypes($typeString)
                );
            }
        }
        if (str_contains_str($typeString, '[]')) {
            $types = $this->parsePsrArrayType($typeString);
            return new DocBlockArrayParameter('array', 'int', $types);
        }
        return $typeString;
    }

    private function parsePsalmClassStringTypes(string $typeString): array
    {
        $classTypes = explode('|', substr($typeString, strpos($typeString, '<') + 1, -1));
        return array_map(function ($type) {
            $rawClass = (strpos($type, '\\') === 0) ? substr($type, 1): $type;
            return $this->useContext->getUsedClass($rawClass);
        }, $classTypes);
    }

    private function parsePsrArrayType(string $typeString): array
    {
        $openingParenth = strpos($typeString, '(');
        if ($openingParenth !== false) {
            return $this->parseTypes(
                substr(
                    $typeString,
                    $openingParenth + 1,
                    strpos($typeString, ')') - $openingParenth - 1
                )
            );
        } else {
            return [substr($typeString, 0, -2)];
        }
    }

    private function parsePsalmArrayType(string $typeString): array
    {
        $openingArrow = strpos($typeString, '<');
        $arrayTypeString = substr($typeString, $openingArrow + 1, -1);
        $types = preg_split('`,(?![\w \[\]]+>)`', $arrayTypeString);
        //echo "Split Psalm\n", var_dump($types);
        if (count($types) === 1) {
            return ['array-key', $this->parseTypes($types[0])];
        }
        $keyType = array_shift($types);
        return [$keyType, array_map('trim', $this->parseTypes($types[0]))];
    }

    /**
     *
     * @param array $params
     * @return string[]
     */
    protected function getParamsInParts(array $params, int $numberOfRequired): array
    {
        $return = [];
        foreach ($params as $index => $param) {
            $raw = array_slice(array_filter(preg_split('/(?<!,) /', $param)), 1, 2);
            $raw[] = $numberOfRequired > $index;
            $return[] = array_combine(['type', 'name', 'required'], $raw);
        }
        return $return;
    }

}
