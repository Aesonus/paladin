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

use Aesonus\Paladin\Contracts\ParameterInterface;
use Aesonus\Paladin\DocblockParameters\IntersectionParameter;
use Aesonus\Paladin\DocblockParameters\ObjectParameter;
use Aesonus\Paladin\DocblockParameters\UnionParameter;
use Aesonus\Paladin\Exceptions\TypeException;
use Aesonus\Paladin\TypeExceptionVisitor;
use Aesonus\TestLib\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class TypeExceptionVisitorTest extends BaseTestCase
{
    /**
     *
     * @param array $types
     * @return MockObject|UnionParameter
     */
    private function getMockUnionParameter($types)
    {
        return $this->getMockBuilder(UnionParameter::class)
            ->setMethodsExcept(['getTypes'])
            ->setConstructorArgs(['$param', $types])
            ->getMock();
    }

    private function getMockParameterInterface(): ParameterInterface
    {
        return $this->getMockBuilder(ParameterInterface::class)
            ->getMockForAbstractClass();
    }

    /**
     * @test
     */
    public function visitParameterCallsValidateOnUnionParameter()
    {
        $parameter = $this->getMockUnionParameter([]);
        $parameter->expects($this->once())->method('validate')->willReturn(true);

        $testObj = new TypeExceptionVisitor(23);
        $testObj->visitParameter($parameter);
    }

    /**
     * @test
     */
    public function visitParameterThrowsExceptionIfValidateFails()
    {
        $parameter = $this->getMockUnionParameter([]);

        $parameter->expects($this->any())->method('validate')->willReturn(false);
        $this->expectException(TypeException::class);

        $testObj = new TypeExceptionVisitor(23);
        $testObj->visitParameter($parameter);
    }

    /**
     * @test
     */
    public function visitParameterCallsParameterToStringMethodsToGenerateExceptionMessage()
    {
        $types = [
            $this->getMockParameterInterface(),
            $this->getMockParameterInterface(),
        ];
        $types[0]->expects($this->any())->method('__toString')->willReturn('int');
        $types[1]->expects($this->any())->method('__toString')->willReturn('float');

        $parameter = $this->getMockUnionParameter($types);
        $parameter->expects($this->any())->method('validate')->willReturn(false);

        $this->expectExceptionMessage('of type int, or of type float;');
        $testObj = new TypeExceptionVisitor(23);
        $testObj->visitParameter($parameter);
    }

    /**
     * @test
     * @dataProvider visitParameterExceptionMessageHasCorrectGivenValueTypeDataProvider
     */
    public function visitParameterExceptionMessageHasCorrectGivenValueType($givenValue, $expected)
    {
        $parameter = $this->getMockUnionParameter([]);

        $parameter->expects($this->any())->method('validate')->willReturn(false);

        $this->expectExceptionMessage("$expected given");
        $testObj = new TypeExceptionVisitor($givenValue);
        $testObj->visitParameter($parameter);
    }

    /**
     * Data Provider
     */
    public function visitParameterExceptionMessageHasCorrectGivenValueTypeDataProvider()
    {
        return [
            'int' => [3, 'int'],
            'stdClass' => [new stdClass(), 'instance of stdClass'],
            'array with int keys' => [['test', 'array'], 'array<int, string>'],
            'array with string keys' => [['test' => 3, 'array' => 4], 'array<string, int>'],
            'array with array-key keys' => [['test' => 3, 4 => 'array'], 'array<array-key, int|string>'],
        ];
    }

    /**
    * @test
    */
    public function visitParameterExceptionMessageHasCorrectClauseForInstanceOf()
    {
        $instanceOfParameter = $this->getMockBuilder(ObjectParameter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $instanceOfParameter->expects($this->any())->method('__toString')
                    ->willReturn(stdClass::class);
        $parameter = $this->getMockUnionParameter([$instanceOfParameter]);
        $parameter->expects($this->any())->method('validate')->willReturn(false);


        $this->expectExceptionMessage('instance of stdClass');
        $testObj = new TypeExceptionVisitor(23);
        $testObj->visitParameter($parameter);
    }

    /**
    * @test
    */
    public function visitParameterExceptionMessageHasCorrectClauseForGenericObject()
    {
        $objectParameter = $this->getMockBuilder(ObjectParameter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectParameter->expects($this->any())->method('__toString')
                    ->willReturn('object');
        $parameter = $this->getMockUnionParameter([$objectParameter]);
        $parameter->expects($this->any())->method('validate')->willReturn(false);


        $this->expectExceptionMessage('an object');
        $testObj = new TypeExceptionVisitor(23);
        $testObj->visitParameter($parameter);
    }

    /**
    * @test
    */
    public function visitParameterExceptionMessageHasCorrectClauseForIntersection()
    {
        $intersectionParameter = $this->getMockBuilder(IntersectionParameter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $intersectionParameter->expects($this->any())->method('__toString')
                    ->willReturn('stdClass&ArrayObject');
        $parameter = $this->getMockUnionParameter([$intersectionParameter]);
        $parameter->expects($this->any())->method('validate')->willReturn(false);


        $this->expectExceptionMessage('an intersection of stdClass and ArrayObject');
        $testObj = new TypeExceptionVisitor(23);
        $testObj->visitParameter($parameter);
    }
}
