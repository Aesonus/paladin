<?php
/*
 * This software is licensed under the MIT License. Please see LICENSE for more details.
 */

namespace Aesonus\Tests;

/**
 * Description of CoreTestHelperParent
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class CoreTestHelperParent extends CoreTestHelperParentParent
{
    /**
     * 
     * @param scalar $scalar
     * @param object $object
     */
    public function testDocInheritance($scalar, $object)
    {
        
    }

    /**
     * 
     * @param type $param
     */
    public function testParentDoc($param)
    {
        
    }
    
    /**
     * {@inheritdoc}
     */
    public function testRecursiveDocInheritance($test, $param)
    {
        parent::testRecursiveDocInheritance($test, $param);
    }
}
