<?php
/*
 * This software is licensed under the MIT License. Please see LICENSE for more details.
 */

namespace Aesonus\Paladin\Traits;

use Aesonus\Paladin\Exceptions\ValidatorNotFoundException;

/**
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
trait Validatable
{
    /**
     * Contains valid data types for phpdocs. You can make your own if you wish.
     * [TODO: Link to documentation]
     * @var array 
     */
    protected $validatableTypes = ['int', 'integer', 'string', 'float', 'null'];

    /**
     * Should always be called like:
     * $this->v(__METHOD__, func_get_args());
     * @param string $method_name
     * @param array $args
     */
    protected function v($method_name, array $args)
    {
        $reflector = new \ReflectionMethod($method_name);
        foreach ($reflector->getParameters() as $param) {
            $params[] = $param->name;
        }
        $validators = $this->getParamTypes($reflector);
        //Cycle thru all $paramTypes
        foreach ($validators as $param_name => $ruleset) {
            $index = array_search($param_name, $params);
            $param_value = $args[$index];
            $this->callValidator($ruleset, $param_name, $param_value);
        }
    }

    private function getParamTypes(\ReflectionMethod $reflector)
    {
        $docs = $reflector->getDocComment();
        if ($docs === FALSE) {
            return [];
        }
        preg_match_all("/@param [a-z0-9 |$]+/i", $docs, $preg_matches);

        $arg_types = [];
        foreach ($preg_matches[0] as $raw_param_doc) {
            $param_doc = explode(' ', $raw_param_doc);
            $types = array_filter(explode('|', trim($param_doc[1])), function ($value) {
                return in_array(strtolower($value), $this->validatableTypes);
            });
            // Strip the '$'
            $key = substr(trim($param_doc[2]), 1);
            $arg_types[$key] = $types;
        }
        //Output like: [param_name => [index => 'type', index => 'type', ...], ...]
        return $arg_types;
    }

    private function callValidator(array $ruleset, $param_name, $param_value)
    {
        $hasFailed = false;
        foreach ($ruleset as $rule) {
            $callable = [$this, 'validate' . ucfirst($rule)];
            if (!is_callable($callable)) {
                throw new ValidatorNotFoundException(sprintf("Validatable type %s needs a validator method named '%s'", $rule, $callable[1]));
            }
            if ($callable($param_value)) {
                $hasFailed = false;
                break;
            }
            $hasFailed = true;
        }
        if ($hasFailed) {
            throw new \InvalidArgumentException(sprintf("$%s should be of type(s) %s, %s given", $param_name, implode('|', $ruleset), gettype($param_value)));
        }
    }

    protected function validateInteger($param)
    {
        return $this->validateInt($param);
    }

    protected function validateInt($param)
    {
        return is_numeric($param) === TRUE 
        // We use this trick to ensure we really are getting an int without
        // having to write another function
        && is_int($param - (int)$param) === TRUE;
    }

    protected function validateString($param)
    {
        return is_string($param) === TRUE;
    }

    protected function validateFloat($param)
    {
        return is_numeric($param) === TRUE && is_float((float)$param) === TRUE;
    }

    protected function validateNull($param)
    {
        return $param === NULL;
    }
}
