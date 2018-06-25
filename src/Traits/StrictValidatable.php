<?php
/*
 * This software is licensed under the MIT License. Please see LICENSE for more details.
 */

namespace Aesonus\Paladin\Traits;

/**
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
trait StrictValidatable
{
    use Validatable;
    
    protected function validateInt($param_value)
    {
        return is_int($param_value) === TRUE;
    }

    protected function validateFloat($param_value)
    {
        return is_float($param_value) === TRUE;
    }
    
    protected function isClassOf($param_value, $type)
    {
        return is_a($param_value, $type, FALSE);
    }
}
