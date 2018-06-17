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
     * @param int|string|null $param
     */
    public function testMethodSingleTypeParam($param = 'null')
    {
        $this->v(__METHOD__, func_get_args());
        return true;
    }
    
    /**
     * 
     * @param int|null $param
     * @param int|null $arg_number2
     */
    public function testMethodMulitpleArgsSingleTypeParam($param = null, $arg_number2 = null)
    {
        $this->v(__METHOD__, func_get_args());
        return true;
    }
}
