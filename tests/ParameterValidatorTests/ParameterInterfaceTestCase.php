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
namespace Aesonus\Tests\ParameterValidatorTests;

use Aesonus\Paladin\Contracts\ParameterValidatorInterface;
use Aesonus\TestLib\BaseTestCase;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
abstract class ParameterInterfaceTestCase extends BaseTestCase
{
    protected function getMockParameterInterface(): ParameterValidatorInterface
    {
        return $this->getMockBuilder(ParameterValidatorInterface::class)
            ->getMockForAbstractClass();
    }

    protected function expectMockParameterInterfaceValidateCall(
        InvocationOrder $invocationRule,
        array $withValue,
        bool $willReturn
    ): ParameterValidatorInterface {
        $mock = $this->getMockParameterInterface();
        $mock->expects($invocationRule)->method('validate')
            ->withConsecutive(...$withValue)
            ->willReturn($willReturn);
        return $mock;
    }
}
