<?php
/*
 * This software is licensed under the MIT License. Please see LICENSE for more details.
 */

namespace Aesonus\Paladin\Traits;

/**
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
trait DefaultValidators
{
    protected function validateInt($param_value)
    {
        return is_numeric($param_value) === TRUE
            // We use this trick to ensure we really are getting an int without
            // having to write another function
            && is_int($param_value - (int) $param_value) === TRUE;
    }

    protected function validateFloat($param_value)
    {
        return is_numeric($param_value) === TRUE && is_float((float) $param_value) === TRUE;
    }

    /**
     * 
     * @param mixed $param_value
     * @return boolean
     */
    protected function validateString($param_value)
    {
        return is_string($param_value) === TRUE;
    }

    /**
     * Returns true if $param_value is null
     * @param mixed $param_value
     * @return boolean
     */
    protected function validateNull($param_value)
    {
        return $param_value === NULL;
    }

    protected function validateArray($param_value)
    {
        return is_array($param_value) === TRUE;
    }

    protected function validateScalar($param_value)
    {
        return is_scalar($param_value) === TRUE;
    }

    /**
     * 
     * @param mixed $param_value
     * @return boolean
     */
    protected function validateBool($param_value)
    {
        return is_bool($param_value);
    }

    protected function validateObject($param_value)
    {
        return is_object($param_value);
    }

    protected function validateCallable($param_value)
    {
        return is_callable($param_value);
    }
    
    protected function isClassOf($param_value, $type)
    {
        return is_a($param_value, $type, TRUE);
    }
    
    final protected function validateMixed($param_value)
    {
        return TRUE;
    }
}
