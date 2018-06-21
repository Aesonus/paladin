<?php
/*
 * This software is licensed under the MIT License. Please see LICENSE for more details.
 */

namespace Aesonus\Tests;

/**
 * Description of CoreTest
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class CoreTest extends \Aesonus\TestLib\BaseTestCase
{

    /**
     *
     * @var \PHPUnit_Framework_MockObject_MockObject 
     */
    protected $testObj;

    /**
     *
     * @var \PHPUnit_Framework_MockObject_MockBuilder 
     */
    protected $traitMockBuilder;

    protected function setUp()
    {
        $this->traitMockBuilder = $this->getMockBuilder(CoreTestHelper::class);
    }

    /////TEST GET REFLECTOR
    private $reflectionMethodName = CoreTestHelper::class . '::testMethod';

    private function reflectionMethod()
    {
        return new \ReflectionMethod($this->reflectionMethodName);
    }

    private function setUpGetReflectorTests()
    {
        $this->testObj = $this->traitMockBuilder->setMethods()->getMock();
    }

    /**
     * @test
     */
    public function getReflectorReturnsANewReflectionMethod()
    {
        $this->setUpGetReflectorTests();
        $expected = $this->reflectionMethod();
        $actual = $this
            ->invokeMethod($this->testObj, 'getReflector', [
            $this->reflectionMethodName
        ]);
        $this->assertEquals($expected, $actual);
        return $this->testObj;
    }

    /**
     * @test
     * @depends getReflectorReturnsANewReflectionMethod
     */
    public function getReflectorReturnsPreviouslySetReflectionMethod($coreTraitObject)
    {
        $this->setUpGetReflectorTests();
        $expected = $this->reflectionMethod();
        $actual = $this
            ->invokeMethod($coreTraitObject, 'getReflector');
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function invalidMethodName()
    {
        $this->setUpGetReflectorTests();
        $this->expectException(\ReflectionException::class);
        $this->invokeMethod($this->testObj, 'getReflector', ['badMethodName']);
    }

    ////TEST GET PARAM NAMES

    private function setUpGetParamNamesTests()
    {
        $this->testObj = $this->traitMockBuilder
                ->setMethods(['getReflector'])->getMock();
    }

    /**
     * @test
     */
    public function getParamNamesReturnsCorrectArrayOfNames()
    {
        $this->setUpGetParamNamesTests();
        $this->testObj
            ->expects($this->once())
            ->method('getReflector')
            ->willReturn($this->reflectionMethod());
        $expected = ['string', 'int', 'bool'];
        $actual = $this->invokeMethod($this->testObj, 'getParamNames');
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function getParamNamesWithNoParamsReturnsEmptyArray()
    {
        $this->setUpGetParamNamesTests();
        $this->testObj
            ->expects($this->once())
            ->method('getReflector')
            ->willReturn(new \ReflectionMethod('Exception::getCode'));
        $expected = [];
        $actual = $this->invokeMethod($this->testObj, 'getParamNames');
        $this->assertEquals($expected, $actual);
    }

    ////TEST GET RAW DOCS

    private function setUpGetRawDocsTest($method)
    {
        $this->testObj = $this->
            traitMockBuilder->setMethods(['getReflector'])->getMock();
        $this->testObj
            ->expects($this->atLeastOnce())
            ->method('getReflector')
            ->willReturn(new \ReflectionMethod($method));
    }

    /**
     * @test
     */
    public function getRawDocsReturnsTheDocs()
    {
        $testMethod = CoreTestHelper::class . '::testMethod';
        $this->setUpGetRawDocsTest($testMethod);
        $expected = (new \ReflectionMethod($this->reflectionMethodName))->getDocComment();

        $actual = $this->invokeMethod($this->testObj, 'getRawDocs');
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider rawDocsInheritanceDataProvider
     */
    public function getRawDocsReturnsInheritedMethodDocOnOveriddenMethod($methodname, $expectedMethodName)
    {
        $testMethod = CoreTestHelper::class . "::$methodname";
        $this->setUpGetRawDocsTest($testMethod);
        $expected = (new \ReflectionMethod($expectedMethodName))->getDocComment();
        $actual = $this->invokeMethod($this->testObj, 'getRawDocs');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function getRawDocsOnNonOveriddenMethod()
    {
        $testMethod = CoreTestHelper::class . '::testParentDoc';
        $this->setUpGetRawDocsTest($testMethod);
        $expected = (new \ReflectionMethod(CoreTestHelperParent::class . '::testParentDoc'))->getDocComment();
        $actual = $this->invokeMethod($this->testObj, 'getRawDocs');
        $this->assertEquals($expected, $actual);
    }

    public function rawDocsInheritanceDataProvider()
    {
        return [
            ['testDocInheritance',
                CoreTestHelperParent::class . "::testDocInheritance"],
            [
                'testRecursiveDocInheritance',
                CoreTestHelperParentParent::class .
                "::testRecursiveDocInheritance"
            ]
        ];
    }

    /**
     * @test
     */
    public function getRawDocsWithNoDocsReturnsFalse()
    {
        $testMethod = CoreTestHelper::class . '::noDocTestMethod';
        $this->setUpGetRawDocsTest($testMethod);
        $this->assertFalse($this->invokeMethod($this->testObj, 'getRawDocs'));
    }

    ////TEST GET PARAM DOCS
    private function setUpGetParamDocs()
    {
        $this->testObj = $this->
            traitMockBuilder->setMethods()->getMock();
    }

    /**
     * @test
     * @dataProvider paramDocsDataProvider
     */
    public function getParamDocsReturnsOnlyParamTaggedLinesInDoc($docs, $expected)
    {
        $this->setUpGetParamDocs();
        $actual = $this->invokeMethod($this->testObj, 'getParamDocs', [$docs]);
        $this->assertEquals($expected, $actual);
    }

    public function paramDocsDataProvider()
    {
        return [
            'testhelper' => [
                (new \ReflectionMethod(CoreTestHelper::class . "::testMethod"))->getDocComment(),
                [
                    '@param \stdClass $string',
                    '@param int $int',
                    '@param bool $bool',
                ]
            ],
            'notypeindoc' => [
                (new \ReflectionMethod(CoreTestHelper::class . '::testNoTypeInDoc'))->getDocComment(),
                [
                    '@param $param'
                ]
            ],
            'noparamdoc' => [
                (new \ReflectionMethod(CoreTestHelper::class . '::noParamDocs'))->getDocComment(),
                []
            ]
        ];
    }
    ////SANITIZE PARAMTYPES TESTS

    /**
     * @test
     * @dataProvider sanitizeParamTypesStripsSlashesDataProvider
     */
    public function sanitizeParamTypesStripsSlashes($data, $expected)
    {
        $this->testObj = $this->traitMockBuilder->setMethods()->getMock();
        $actual = $this->invokeMethod($this->testObj, 'sanitizeParamTypes', [$data]);
        $this->assertEquals($expected, $actual);
    }

    public function sanitizeParamTypesStripsSlashesDataProvider()
    {
        return [
            [['Test\Type'], ['TestType']],
            [['Yay \IT WOrks\\'], ['Yay IT WOrks']],
            [['Test\Type', 'Yay \IT WOrks\\'], ['TestType', 'Yay IT WOrks']]
        ];
    }

    ////TEST ISVALIDATABLE

    private function setUpIsValidatableTests()
    {
        $this->testObj = $this->traitMockBuilder
            ->setMethods(['validateCustom', 'validateStdClass', 'getValidatorMappings'])
            ->getMock();
        $this->testObj->expects($this->any())->method('validateCustom')->willReturn(true);
        $this->testObj->expects($this->any())->method('validateStdClass')->willReturn(true);
        $this->testObj->expects($this->any())->method('getValidatorMappings')->willReturn([
            'integer' => 'int'
        ]);
    }

    /**
     * @test
     * @dataProvider isValidatableValidTypeDataProvider
     */
    public function isValidatableIsTrueOnValidTypes($type)
    {
        $this->setUpIsValidatableTests();
        $actual = $this->invokeMethod($this->testObj, 'isValidatable', [
            $type
        ]);
        $this->assertTrue($actual);
    }

    /**
     * @test
     * @dataProvider isValidatableInvalidTypesDataProvider
     */
    public function isValidatableIsFalseOnTypesWithNoValidateMethod($type)
    {
        $this->setUpIsValidatableTests();
        $actual = $this->invokeMethod($this->testObj, 'isValidatable', [
            $type
        ]);
        $this->assertFalse($actual);
    }

    public function isValidatableInvalidTypesDataProvider()
    {
        return [
            ['badType'],
            ['OtherBadType']
        ];
    }

    public function isValidatableValidTypeDataProvider()
    {
        return [
            ['int'],
            'mapping is valid' => ['integer'],
            ['custom'],
            ['string'],
            ['object'],
            ['bool'],
            ['\stdClass']
        ];
    }

    ////GET VALIDATOR CALLABLE
    /**
     * @test
     * @dataProvider getValidatorCallableDataProvider
     */
    public function getValidatorCallableReturnsCallableInCorrectFormat($type, $expectedMethodName)
    {
        $testObj = $this->traitMockBuilder->setMethods()->getMock();
        $expected = [$testObj, $expectedMethodName];
        $actual = $this->invokeMethod($testObj, 'getValidatorCallable', [$type]);
        $this->assertEquals($expected, $actual);
    }

    public function getValidatorCallableDataProvider()
    {
        return [
            ['string', 'validateString'],
            ['stdClass', 'validateStdClass']
        ];
    }

    ////GET MAPPING TESTS
    private function setUpGetMappingTests()
    {
        $this->testObj = $this->traitMockBuilder
            ->setMethods(['getValidatorMappings'])
            ->getMock();
        $this->testObj->expects($this->atLeastOnce())
            ->method('getValidatorMappings')
            ->willReturn(['mapFrom' => 'mapTo', 'otherMapFrom' => 'otherMapTo']);
    }

    /**
     * @test
     */
    public function getMappingReturnsMappingForNameOnSuccess()
    {
        $this->setUpGetMappingTests();
        $expected = 'otherMapTo';
        $actual = $this->invokeMethod($this->testObj, 'getMapping', ['otherMapFrom']);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function getMappingReturnsNullOnNotFound()
    {
        $this->setUpGetMappingTests();
        $actual = $this->invokeMethod($this->testObj, 'getMapping', ['notFound']);
        $this->assertNull($actual);
    }
    ////THROW EXCEPTION TESTS

    /**
     * @test
     * @dataProvider throwExceptionDataProvider
     */
    public function throwExceptionOutputsCorrectMessage($param_name, $ruleset, $param_value, $expected)
    {
        $this->testObj = $this->traitMockBuilder
            ->setMethods()
            ->getMock();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expected);
        $this->invokeMethod($this->testObj, 'throwException', [$param_name, $ruleset, $param_value]);
    }

    public function throwExceptionDataProvider()
    {
        //paramname, ruleset, paramvalue, expected
        return [
            ['param',
                ['int'],
                'badval',
                '$param should be of type(s) int, string'
            ],
            ['param',
                ['string', 'int'],
                new \stdClass(),
                '$param should be of type(s) string|int, object'
            ]
        ];
    }

    ////TEST PARSE PARAM TYPES

    private function setUpParseParamTypesTests(array $param_names)
    {
        $this->testObj = $this->traitMockBuilder->setMethods(['validateStdClass', 'getParamNames'])->getMock();
        $this->testObj->expects($this->any())->method('validateStdClass')->willReturn(true);
        $this->testObj->expects($this->any())->method('getParamNames')->willReturn($param_names);
    }

    /**
     * @test
     * @dataProvider parseParamTypesDataProvider
     */
    public function parseParamTypesReturnsParamTypesInNestedArrays($param_docs, $expected)
    {
        $this->setUpParseParamTypesTests(array_keys($expected));
        $actual = $this->invokeMethod($this->testObj, 'parseParamTypes', [$param_docs]);
        $this->assertEquals($expected, $actual);
    }

    public function parseParamTypesDataProvider()
    {
        return [
            'single_types' => [
                ['@param string $stringvar', '@param int $intvar'],
                [
                    'stringvar' => [
                        'string'
                    ],
                    'intvar' => [
                        'int'
                    ]
                ]
            ],
            'multiple_types' => [
                ['@param string|int $stringintvar', '@param int|null $intnullvar'],
                [
                    'stringintvar' => [
                        'string',
                        'int'
                    ],
                    'intnullvar' => [
                        'int',
                        'null'
                    ]
                ]
            ],
            'single_types_with_objct' => [
                ['@param \stdClass $stdclassvar', '@param int $intvar'],
                [
                    'stdclassvar' => [
                        'stdClass'
                    ],
                    'intvar' => [
                        'int'
                    ]
                ]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider parseParamTypesFiltersInvalidTypesAndReturnsArrayDataProvider
     */
    public function parseParamTypesFiltersInvalidTypesAndReturnsArray($param_docs, $expected)
    {
        $this->setUpParseParamTypesTests(array_keys($expected));
        $actual = $this->invokeMethod($this->testObj, 'parseParamTypes', [$param_docs]);
        $this->assertEquals($expected, $actual);
    }

    public function parseParamTypesFiltersInvalidTypesAndReturnsArrayDataProvider()
    {
        return [
            'single_types_with_objct' => [
                ['@param \stdClass $stdclassvar', '@param badType $badvar'],
                [
                    'stdclassvar' => [
                        'stdClass'
                    ],
                    'badvar' => []
                ]
            ],
            'multi_types_with_objct' => [
                ['@param \stdClass $stdclassvar', '@param badType|int $badvar'],
                [
                    'stdclassvar' => [
                        'stdClass'
                    ],
                    'badvar' => [
                        1 => 'int'
                    ]
                ]
            ],
            'no_type_given' => [
                ['@param $noType'],
                [
                    'noType' => []
                ]
            ]
        ];
    }

    ////TEST GET PARAM DEFAULTS

    private function setUpGetParamDefaultsTests($method_name)
    {
        $this->testObj = $this->traitMockBuilder->setMethods(['getReflector'])->getMock();
        $this->testObj->expects($this->once())->method('getReflector')
            ->willReturn(new \ReflectionMethod($method_name));
    }

    /**
     * @test
     * @dataProvider getParamDefaultsReturnsDefaultsInArrayDataProvider
     */
    public function getParamDefaultsReturnsDefaultsInArray($method_name, $expected)
    {
        $this->setUpGetParamDefaultsTests($method_name);
        $actual = $this->invokeMethod($this->testObj, 'getParamDefaults');
        $this->assertEquals($expected, $actual);
    }

    public function getParamDefaultsReturnsDefaultsInArrayDataProvider()
    {
        $obj = CoreTestHelper::class;
        return [
            'some_defaults' => [
                "$obj::testMethodWithSomeDefaults",
                [
                    1 => 3.141,
                    2 => 'string'
                ]
            ],
            'all_defaults' => [
                "$obj::testMethodWithAllDefaults",
                [
                    'default',
                    10
                ]
            ]
        ];
    }

    ////TEST GET PARAM TYPES

    private function setUpGetParamTypesTests($reflector)
    {
        $this->testObj = $this->traitMockBuilder->setMethods([
                'getReflector'
            ])->getMock();
        $this->testObj->expects($this->any())->method('getReflector')->willReturn($reflector);
    }

    /**
     * @test
     */
    public function getParamTypesReturnsTheTypesInArray()
    {
        $this->setUpGetParamTypesTests(
            new \ReflectionMethod(CoreTestHelper::class . "::testMethod")
        );
        $expected = [
            'string' => [],
            'int' => ['int'],
            'bool' => ['bool']
        ];
        $actual = $this->invokeMethod($this->testObj, 'getParamTypes');
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function getParamTypesReturnsEmptyArrayIfNoRawDocs()
    {
        $this->setUpGetParamTypesTests(new \ReflectionMethod(CoreTestHelper::class . "::noDocTestMethod"));
        $actual = $this->invokeMethod($this->testObj, 'getParamTypes');
        $this->assertEmpty($actual);
    }

    ////TEST CALL VALIDATOR
    
    private function setUpCallValidatorTest($mappings = [])
    {
        $this->testObj = $this->traitMockBuilder->setMethods(['getValidatorMappings'])->getMock();
        $this->testObj->expects($this->any())->method('getValidatorMappings')->willReturn($mappings);
    }

    /**
     * @test
     * @dataProvider callValidatorDoesNothingOnSuccessfulValidationDataProvider
     */
    public function callValidatorDoesNothingOnSuccessfulValidation
    ($valid_types, $param_name, $param_value, $mappings)
    {
        $this->setUpCallValidatorTest($mappings);
        $this->invokeMethod($this->testObj, 'callValidator', [$valid_types, $param_name, $param_value]);
    }

    public function callValidatorDoesNothingOnSuccessfulValidationDataProvider()
    {
        return [
            'one_type' => [['int'], 'intparam', 5, []],
            'with_mapping' => [['integer'], 'intparam', 54, ['integer' => 'int']],
            'no_params' => [[], '', '', []],
            'multiple_types' => [['int', 'string'], 'intstringparam', 'hello', []],
        ];
    }

    /**
     * @test
     * @dataProvider callValidatorThrowsExceptionOnUnsuccessfulValidationDataProvider
     */
    public function callValidatorThrowsExceptionOnUnsuccessfulValidation
    ($valid_types, $param_name, $param_value, $mappings)
    {
        $this->setUpCallValidatorTest($mappings);
        $this->expectException(\InvalidArgumentException::class);
        $this->invokeMethod($this->testObj, 'callValidator', [$valid_types, $param_name, $param_value]);
    }

    public function callValidatorThrowsExceptionOnUnsuccessfulValidationDataProvider()
    {
        return [
            'one_type' => [
                ['int'],
                'intparam',
                5.6,
                []
            ],
            'with_mapping_and_multiple_types' => [
                ['null', 'boolean'],
                'boolparam', 'notaboolean',
                ['integer' => 'int', 'boolean' => 'bool']
            ],
            'multiple_types' => [
                ['int', 'string'],
                'intstringparam',
                false,
                []
            ],
        ];
    }
}
