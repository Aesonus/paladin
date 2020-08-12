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

use Aesonus\Paladin\Exceptions\TypeLintException;
use function Aesonus\Paladin\Utilities\array_last;
use function Aesonus\Paladin\Utilities\sign;
use function Aesonus\Paladin\Utilities\str_contains_str;
use function Aesonus\Paladin\Utilities\strpos_all;

/**
 * Checks parameter types defined in a doc block for errors
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class TypeLinter
{
    const MISSING_OPENING_PARENTHESIS = 'Missing an opening parenthesis';
    const MISSING_CLOSING_PARENTHESIS = 'Missing a closing parenthesis';
    const MISSING_OPENING_ARROW = 'Missing an opening arrow';
    const MISSING_CLOSING_ARROW = 'Missing a closing arrow';
    const MISSING_OPENING_BRACKET = 'Missing an opening bracket';
    const MISSING_CLOSING_BRACKET = 'Missing a closing bracket';
    const LEADING_BAR = 'A leading bar was found';
    const TRAILING_BAR = 'A trailing bar was found';
    const TRAILING_COMMA_IN_ARRAY_TYPE = 'Missing type after comma in array type';
    const LEADING_COMMA_IN_ARRAY_TYPE = 'Missing type before comma in array type';
    const INVALID_ARRAY_KEY_TYPE_TEMPLATE = 'Unexpected array type \'%s\'; '
        . 'expects array-key, int, or string';

    /**
     *
     * @var string
     */
    private $typeString = '';

    /**
     *
     * @var string
     */
    private $originalTypeString = '';

    /**
     *
     * @var string
     */
    private $paramName = '';

    /**
     *
     * @param string $paramName
     * @param string $typeString
     * @return void
     * @throws TypeLintException
     */
    public function lintCheck(string $paramName, string $typeString): void
    {
        $this->originalTypeString = $typeString;
        $this->typeString = str_replace(' ', '', $this->originalTypeString);
        $this->paramName = $paramName;

        $this->checkParenthesis();
        $this->checkArrows();
        $this->checkBrackets();
        $this->checkBars();
        $this->checkCommas();
        $this->checkPsalmArrayTypes();
    }

    /**
     *
     * @return void
     * @throws TypeLintException
     */
    private function checkParenthesis(): void
    {
        $message = [
            -1 => self::MISSING_OPENING_PARENTHESIS,
            1 => self::MISSING_CLOSING_PARENTHESIS,
        ];
        $result = $this->checkForPairs('(', ')');
        if ($result !== 0) {
            $this->throwExceptionWithMessage(
                $message[$result]
            );
        }
    }

    /**
     *
     * @return void
     * @throws TypeLintException
     */
    private function checkArrows(): void
    {
        $message = [
            -1 => self::MISSING_OPENING_ARROW,
            1 => self::MISSING_CLOSING_ARROW
        ];
        $result = $this->checkForPairs('<', '>');
        if ($result !== 0) {
            $this->throwExceptionWithMessage(
                $message[$result]
            );
        }
    }

    /**
     *
     * @return void
     * @throws TypeLintException
     */
    private function checkBrackets(): void
    {
        $message = [
            -1 => self::MISSING_OPENING_BRACKET,
            1 => self::MISSING_CLOSING_BRACKET,
        ];
        $result = $this->checkForPairs('[', ']');
        if ($result !== 0) {
            $this->throwExceptionWithMessage(
                $message[$result]
            );
        }
    }

    /**
     *
     * @return void
     * @throws TypeLintException
     */
    private function checkBars(): void
    {
        if (str_contains_str($this->typeString, '|)')
            || str_contains_str($this->typeString, '|>')
            || strpos($this->typeString, '|') === strlen($this->typeString) - 1
        ) {
            $this->throwExceptionWithMessage(self::TRAILING_BAR);
        }
        if (str_contains_str($this->typeString, '(|')
            || str_contains_str($this->typeString, '<|')
            || strpos($this->typeString, '|') === 0
        ) {
            $this->throwExceptionWithMessage(self::LEADING_BAR);
        }
    }

    /**
     *
     * @return void
     * @throws TypeLintException
     */
    private function checkCommas(): void
    {
        if (str_contains_str($this->typeString, ',>')) {
            $this->throwExceptionWithMessage(self::TRAILING_COMMA_IN_ARRAY_TYPE);
        }
        if (str_contains_str($this->typeString, '<,')) {
            $this->throwExceptionWithMessage(self::LEADING_COMMA_IN_ARRAY_TYPE);
        }
    }

    /**
     *
     * @return void
     * @throws TypeLintException
     */
    private function checkPsalmArrayTypes(): void
    {
        $typeDefLength = strlen('array<');
        $arrayTypePositions = strpos_all($this->typeString, 'array<');
        //var_dump($arrayTypePositions);
        $commaPositions = strpos_all($this->typeString, ',');
        $keyedArrayPositions = [];
        $commaPositionsCopy = $commaPositions;
        foreach (array_reverse($arrayTypePositions) as $arrPos) {
            /** @var int $commaPos */
            $commaPos = array_last($commaPositionsCopy);
            if ($arrPos < $commaPos) {
                array_unshift($keyedArrayPositions, $arrPos);
                array_pop($commaPositionsCopy);
            }
        }
        //echo $this->typeString . "\n";
        //echo "\n", var_dump($keyTypeArrayPositions, $commaPositions);
        foreach ($keyedArrayPositions as $arrayTypePosition) {
            $commaPos = array_shift($commaPositions);
            $foundType = substr(
                $this->typeString,
                $arrayTypePosition + $typeDefLength,
                $commaPos - ($arrayTypePosition + $typeDefLength)
            );
            //echo $foundType . "\n";
            if (!in_array($foundType, ['array-key', 'int', 'string'])) {
                $this->throwExceptionWithMessage(sprintf(self::INVALID_ARRAY_KEY_TYPE_TEMPLATE, $foundType));
            }
        }
    }

    /**
     *
     * @param string $opening
     * @param string $closing
     * @return int
     */
    private function checkForPairs(string $opening, string $closing): int
    {
        return sign(
            substr_count(
                $this->typeString,
                $opening
            )
            - substr_count($this->typeString, $closing)
        );
    }

    /**
     *
     * @param string $info
     * @return void
     * @throws TypeLintException
     */
    private function throwExceptionWithMessage(string $info): void
    {
        throw new TypeLintException(
            "Declared type is not valid for @param "
            . "{$this->originalTypeString} {$this->paramName}: $info"
        );
    }
}
