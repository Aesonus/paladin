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
class ValidatableTestHelper
{
    use \Aesonus\Paladin\Traits\Validatable;
    
    /**
     * 
     * @param int $param
     */
    public function testMethodSingleTypeParam($param)
    {
        $this->v(__METHOD__, func_get_args());
    }
    
    /**
     * 
     * @param int|float $param
     */
    public function testMethodMultiTypeParam($param)
    {
        $this->v(__METHOD__, func_get_args());
    }
    
    /**
     * 
     * @param string $string
     * @param integer $int
     */
    public function testMethodSingleTypeMuliParams($string, $int)
    {
        $this->v(__METHOD__, func_get_args());
    }
    
    /**
     * 
     * @param integer|string $stringint
     * @param null|array $nullarray
     */
    public function testMethodMultiTypeMultiParams($stringint, $nullarray)
    {
        $this->v(__METHOD__, func_get_args());
    }
    
    /**
     * 
     * @param int $param
     */
    public function testManyParamMethod(...$param)
    {
        $this->v(__METHOD__, func_get_args());
    }
    
    public function testNoDocMethod($param)
    {
        $this->v(__METHOD__, func_get_args());
    }
    
    /**
     * 
     * @param invalid $param
     */
    public function testCustomValidatorMethod($param)
    {
        $this->v(__METHOD__, func_get_args());
    }
    
    /**
     * 
     * @param mixed $mixed
     */
    public function testMethodMixedType($mixed)
    {
        $this->v(__METHOD__, func_get_args());
    }
    
    /**
     * 
     * @param custom $param
     */
    public function testMethodCustomType($param)
    {
        $this->v(__METHOD__, func_get_args());
    }
}
