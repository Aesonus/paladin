<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
