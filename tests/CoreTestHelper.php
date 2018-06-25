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
class CoreTestHelper extends CoreTestHelperParent
{
    use \Aesonus\Paladin\Traits\Core;
    use \Aesonus\Paladin\Traits\DefaultValidators;
    
    /**
     * 
     * @param \stdClass $stdClass
     * @param int $int
     * @param bool $bool
     */
    public function testMethod($stdClass, $int, $bool)
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
    
    protected function getValidatorMappings()
    {
        return [];
    }
    
    public function testMethodWithSomeDefaults($no_default, $pi = 3.141, $string = 'string')
    {
        
    }
    
    public function testMethodWithAllDefaults($default = 'default', $ten = 10)
    {
        
    }

    protected function v($method, array $args)
    {
        
    }

    public function mapType($alias, $type)
    {
        return $this;
    }
}
