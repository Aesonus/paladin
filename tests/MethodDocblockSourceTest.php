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
namespace Aesonus\Tests;

use Aesonus\Paladin\MethodDocblockSource;
use Aesonus\TestLib\BaseTestCase;
use Aesonus\Tests\Fixtures\MethodDocCommentTestFixture;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class MethodDocblockSourceTest extends BaseTestCase
{
    /**
     * @test
     */
    public function getGetsTheDocCommentForThisClassIfNotADescendant()
    {
        $testObj = new MethodDocblockSource(MethodDocCommentTestFixture::class . '::testMethod');
        $expected = "/**\n"
            . "     *\n"
            . "     * @param string \$testString Is a string scalar type\n"
            . "     */";
        $this->assertEquals($expected, $testObj->get());
    }

    /**
     * @test
     */
    public function getGetsTheDocCommentFromAncestorClassIfGivenIsADescendant()
    {
        $mockTestClass = $this->getMockBuilder(MethodDocCommentTestFixture::class)
            ->setMethodsExcept()
            ->setMockClassName('TestClassDescendant')
            ->getMock();
        $testObj = new MethodDocblockSource('TestClassDescendant::testMethod');
        $expected = "/**\n"
            . "     *\n"
            . "     * @param string \$testString Is a string scalar type\n"
            . "     */";
        $this->assertEquals($expected, $testObj->get());
    }

    /**
     * @test
     */
    public function getGetsTheDocCommentFromInterface()
    {
        $testObj = new MethodDocblockSource(MethodDocCommentTestFixture::class . '::testInterfaceMethod');
        $expected = "/**\n"
            . "     *\n"
            . "     * @param string \$param\n"
            . "     */";
        $this->assertEquals($expected, $testObj->get());
    }

    /**
     * @test
     */
    public function getGetsTheDocCommentFromInterfaceOnDescendantOfClassImplementingSaidInterface()
    {
        $mockTestClass = $this->getMockBuilder(MethodDocCommentTestFixture::class)
            ->setMethodsExcept()
            ->setMockClassName('TestClassDescendant')
            ->getMock();
        $testObj = new MethodDocblockSource('TestClassDescendant::testInterfaceMethod');
        $expected = "/**\n"
            . "     *\n"
            . "     * @param string \$param\n"
            . "     */";
        $this->assertEquals($expected, $testObj->get());
    }
}
