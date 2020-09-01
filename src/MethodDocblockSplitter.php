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

use Aesonus\Paladin\Contracts\DocblockSplitterInterface;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class MethodDocblockSplitter implements DocblockSplitterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDocblockParameters(string $docblock): array
    {
        return $this->getParamsInParts($this->getParamMatches($docblock));
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

    /**
     *
     * @param list<string> $params
     * @return list<array{name: string, type: string}>
     */
    private function getParamsInParts(array $params): array
    {
        $return = [];
        foreach ($params as $param) {
            $raw = array_slice(array_filter(preg_split('/(?<!,) /', $param)), 1, 2);
            /** @var array{name: string, type: string} */
            $return[] = array_combine(['type', 'name'], $raw);
        }
        return $return;
    }
}
