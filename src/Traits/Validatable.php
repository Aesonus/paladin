<?php
/*
 * This software is licensed under the MIT License. Please see LICENSE for more details.
 */

namespace Aesonus\Paladin\Traits;

/**
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
trait Validatable
{
    private $reflector;
    
    public function initialize()
    {
        $this->reflector = new \ReflectionClass(get_class($this));
    }
    
    protected function validateInt($param)
    {
        return is_int($param) === TRUE;
    }
    
    protected function validateString($param)
    {
        return is_string($param) === TRUE;
    }
}
