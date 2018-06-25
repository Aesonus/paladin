<?php
/*
 * This software is licensed under the MIT License. Please see LICENSE for more details.
 */

namespace Aesonus\Tests;

/**
 * Description of ValidatableTestHelper
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class ValidatableTestHelper extends CoreTestHelperParent
{
    use \Aesonus\Paladin\Traits\StrictValidatable;
    
    /**
     * 
     * @param string $string
     * @param int $int
     * @param bool $bool
     */
    public function testMethod($string, $int, $bool)
    {
        
    }
    
    /**
     * 
     * @param \stdClass $object
     * @param \Aesonus\Paladin\Service\AbstractValidator $coreTest
     */
    public function testObjectOfClassType($object, $coreTest)
    {
        
    }
    
    public function noDocTestMethod($param)
    {
        
    }
    
    /**
     * 
     * @param $param
     */
    public function testNoTypeInDoc($param)
    {
        
    }
    
    /**
     * 
     * @returns null
     */
    public function noParamDocs()
    {
        return null;
    }
    
    /**
     * {@inheritdoc}
     */
    public function testDocInheritance($scalar, $object)
    {
        parent::testDocInheritance($scalar, $object);
    }
    
    /**
     * {@inheritdoc}
     */
    public function testRecursiveDocInheritance($test, $param)
    {
        parent::testRecursiveInheritedMethod($test, $param);
    }
    
    /**
     * 
     * @param integer $no_default_int
     * @param float $pi
     * @param string $string
     */
    public function testMethodWithSomeDefaults($no_default_int, $pi = 3.141, $string = 'string')
    {
        
    }
    
    public function testMethodWithAllDefaults($default = 'default', $ten = 10)
    {
        
    }
}
