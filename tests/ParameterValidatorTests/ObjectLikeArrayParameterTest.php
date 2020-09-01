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

use Aesonus\Paladin\ParameterValidators\ObjectLikeArrayParameter;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class ObjectLikeArrayParameterTest extends ParameterInterfaceTestCase
{
    /**
     * @test
     */
    public function validateReturnsTrueIfKeysEqualGivenKeysAndValueParametersValidateMethodsSucceed()
    {
        $testObj = new ObjectLikeArrayParameter(
            [
                0 => $this->expectMockParameterInterfaceValidateCall($this->once(), [['int-key']], true),
                'string' => $this->expectMockParameterInterfaceValidateCall($this->once(), [['string-key']], true)
            ]
        );
        $this->assertTrue($testObj->validate([0 => 'int-key', 'string' => 'string-key']));
    }

    /**
     * @test
     */
    public function validateReturnsFalseIfGivenValueIsNotArray()
    {
        $testObj = new ObjectLikeArrayParameter([]);
        $this->assertFalse($testObj->validate('32'));
    }

    /**
     * @test
     */
    public function validateReturnsTrueIfOptionalParameterIsOmitted()
    {
        $testObj = new ObjectLikeArrayParameter(
            [
                0 => $this->expectMockParameterInterfaceValidateCall($this->once(), [['int-key']], true),
                'string' => $this->expectMockParameterInterfaceValidateCall($this->once(), [['string-key']], true)
            ],
            [
                'optional' => $this->expectMockParameterInterfaceValidateCall($this->never(), [[null]], true),
            ]
        );
        $this->assertTrue($testObj->validate([0 => 'int-key', 'string' => 'string-key']));
    }

    /**
     * @test
     */
    public function validateReturnsTrueIfOptionalParameterIsNotOmittedAndItsValidateMethodReturnsTrue()
    {
        $testObj = new ObjectLikeArrayParameter(
            [
                0 => $this->expectMockParameterInterfaceValidateCall($this->once(), [['int-key']], true),
                'string' => $this->expectMockParameterInterfaceValidateCall($this->once(), [['string-key']], true)
            ],
            [
                'optional' => $this->expectMockParameterInterfaceValidateCall($this->once(), [[32.4]], true),
            ]
        );
        $this->assertTrue($testObj->validate([0 => 'int-key', 'string' => 'string-key', 'optional' => 32.4]));
    }

    /**
     * @test
     */
    public function validateReturnsFalseIfRequiredKeyIsNotInGivenValue()
    {
        $testObj = new ObjectLikeArrayParameter(
            [
                0 => $this->expectMockParameterInterfaceValidateCall($this->once(), [['int-key']], true),
                'string' => $this->expectMockParameterInterfaceValidateCall($this->never(), [['string-key']], true)
            ]
        );
        $this->assertFalse($testObj->validate([0 => 'int-key']));
    }

    /**
     * @test
     */
    public function validateReturnsFalseIfRequiredValueValidateReturnsFalse()
    {
        $testObj = new ObjectLikeArrayParameter(
            [
                0 => $this->expectMockParameterInterfaceValidateCall($this->once(), [['int-key']], true),
                'string' => $this->expectMockParameterInterfaceValidateCall($this->once(), [['string-key']], false)
            ]
        );
        $this->assertFalse($testObj->validate([0 => 'int-key', 'string' => 'string-key']));
    }

    /**
     * @test
     */
    public function validateReturnsFalseIfOptionalParameterIsNotOmittedAndItsValidateMethodReturnsFalse()
    {
        $testObj = new ObjectLikeArrayParameter(
            [
                0 => $this->expectMockParameterInterfaceValidateCall($this->once(), [['int-key']], true),
                'string' => $this->expectMockParameterInterfaceValidateCall($this->once(), [['string-key']], true)
            ],
            [
                'optional' => $this->expectMockParameterInterfaceValidateCall($this->once(), [[32.4]], false),
            ]
        );
        $this->assertFalse($testObj->validate([0 => 'int-key', 'string' => 'string-key', 'optional' => 32.4]));
    }

    /**
     * @test
     */
    public function toStringReturnsStringRepresentationOfObjectLikeArrayParameterWithoutOptional()
    {
        $expected = 'array{0: int, string: float}';
        $required[0] = $this->getMockParameterInterface();
        $required[0]->expects($this->once())->method('__toString')
            ->willReturn('int');
        $required['string'] = $this->getMockParameterInterface();
        $required['string']->expects($this->once())->method('__toString')
            ->willReturn('float');

        $testObj = new ObjectLikeArrayParameter($required);

        $this->assertSame($expected, (string)$testObj);
    }

    /**
     * @test
     */
    public function toStringReturnsStringRepresentationOfObjectLikeArrayParameterWithOptional()
    {
        $expected = 'array{0: int, string: float, optional?: object}';
        $required[0] = $this->getMockParameterInterface();
        $required[0]->expects($this->once())->method('__toString')
            ->willReturn('int');
        $required['string'] = $this->getMockParameterInterface();
        $required['string']->expects($this->once())->method('__toString')
            ->willReturn('float');
        $optional['optional'] = $this->getMockParameterInterface();
        $optional['optional']->expects($this->once())->method('__toString')
            ->willReturn('object');

        $testObj = new ObjectLikeArrayParameter($required, $optional);

        $this->assertSame($expected, (string)$testObj);
    }
}
