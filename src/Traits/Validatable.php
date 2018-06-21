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
    use Core;
    
    protected $validatorMappings;

    /**
     *
     * @var $customTypes array Custom validation types. Must be an array of strings.
     * @deprecated since version 1.0
     */
    protected $customTypes = [];
    
    /**
     * This method validates the arguments against the doc comment of
     * the method given. This also will use documentation inheritance if
     * the inherit doc annotation is used for the doc comment
     * Should always be called like:
     * $this->v(__METHOD__, func_get_args());
     * @param string $method_name
     * @param array $args
     * @throws \InvalidArgumentException
     * @return void
     */
    final protected function v($method_name, array $args)
    {
        if (!is_string($method_name)) {
            $this->throwException('method_name', ['string'], $method_name);
        }
        //This also initializes the reflector
        $this->getReflector($method_name);
        $params = $this->getParamNames();
        $validators = $this->getParamTypes();
        $defaults = $this->getParamDefaults();
        //Cycle thru all $paramTypes
        foreach ($validators as $param_name => $ruleset) {
            $index = array_search($param_name, $params);
            //If the argument was not provided, then the default is
            //okay to accept
            if (array_key_exists($index, $args)) {
                $param_value = $args[$index];
            } else {
                $param_value = $defaults[$index];
            }
            $this->callValidator($ruleset, $param_name, $param_value);
        }
    }

    /**
     * Allows the use of an alias in doc comments to validate as if it were of 
     * the type given.
     * @param string $alias
     * @param string $type
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function mapType($alias, $type)
    {
        //Validate Parameters
        foreach(func_get_args() as $i => $value) {
            $names = ['alias', 'type'];
            if (!is_string($value)) {
                $this->throwException($names[$i], ['string'], $value);
            }
        }
        //Make sure type exists
        if (!$this->isValidatable($type)) {
            throw new \Aesonus\Paladin\Exceptions\TypeNotDefinedException("$type has no validation method");
        }
        $mappings = $this->getValidatorMappings();
        $mappings[$alias] = $type;
        
        $this->validatorMappings = $mappings;
        return $this;
    }
    /**
     * Returns a keyed array of aliases to types.
     * @return array
     */
    public function getValidatorMappings()
    {
        if (!isset($this->validatorMappings)) {
            $this->validatorMappings = [
                'integer' => 'int',
                'boolean' => 'bool'
            ];
        }
        return $this->validatorMappings;
    }

    /**
     * Adds a type for Paladin to validate. 
     * Doesn't actually do anything anymore except return $this
     * To add a custom type, simply define a validateTypename method
     * @param string $type
     * @return $this
     * @deprecated since version 1.0
     */
    public function addCustomParameterType($type)
    {
        return $this;
    }



}
