<?php
/*
 * This software is licensed under the MIT License. Please see LICENSE for more details.
 */

namespace Aesonus\Paladin\Traits;

use \Aesonus\Paladin\Exceptions\DocBlockSyntaxException;

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
    protected $validatableTypes = ['mixed', 'scalar', 'int', 'string', 'float', 'array', 'null', 'bool'];
    protected $validatorMappings;

    /**
     *
     * @var $customTypes array Custom validation types. Must be an array of strings.
     */
    protected $customTypes = [];

    /**
     *
     * @var \ReflectionMethod
     */
    private $reflector;

    /**
     * Should always be called like:
     * $this->v(__METHOD__, func_get_args());
     * @param string $method_name
     * @param array $args
     */
    final protected function v($method_name, array $args)
    {
        if (!is_string($method_name) && isset($method_name)) {
            $this->throwException('method_name', ['string', 'null'], $method_name);
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
     * Allows using more than one validator to use one validation method
     * @param string $alias
     * @param string $type
     * @return $this
     * @throws \InvalidArgumentException
     */
    final public function mapType($alias, $type)
    {
        foreach(func_get_args() as $i => $value) {
            $names = ['type', 'mapToType'];
            if (!is_string($value)) {
                $this->throwException($names[$i], ['string'], $alias);
            }
        }
        $mappings = $this->getValidatorMappings();
        $mappings[$alias] = $type;
        
        $this->validatorMappings = $mappings;
        return $this;
    }

    /**
     * Adds a type for Paladin to validate. 
     * @param string $type
     * @return $this
     * @throws \InvalidArgumentException
     */
    final public function addCustomParameterType($type)
    {
        if (!is_string($type)) {
            $this->throwException('type', ['string'], $type);
        }
        $this->customTypes[] = $type;
        return $this;
    }

    /**
     * 
     * @param string|null $method_name
     * @return \ReflectionMethod
     */
    protected function getReflector($method_name = NULL)
    {
        if (isset($method_name)) {
            $this->reflector = new \ReflectionMethod($method_name);
        }
        return $this->reflector;
    }
    
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

    private function getParamNames()
    {
        $reflector = $this->getReflector();
        $params = [];
        foreach ($reflector->getParameters() as $param) {
            $params[] = $param->getName();
        }
        return $params;
    }

    private function getParamTypes()
    {
        $docs = $this->getRawDocs();
        if (FALSE === $docs) {
            return [];
        }
        //Get all @param docblock tags
        $param_docs = $this->getParamDocs($docs);

        $arg_types = $this->parseParamDocs($param_docs);
        return $arg_types;
    }
    
    protected function getParamDefaults()
    {
        $reflector = $this->getReflector();
        $defaults = [];
        foreach ($reflector->getParameters() as $param) {
            //if (!$param->isOptional()) continue;
            $defaults[] = $param->getDefaultValue();
        }
        return $defaults;
    }

    /**
     * Performs validation on doc comments
     * @return string
     * @throws DocBlockSyntaxException
     * @todo Allow for doc block inheritance
     */
    private function getRawDocs()
    {
        $docs = $this->getReflector()->getDocComment();
        //Check for syntax Error
        if (preg_match("/([[:blank:]]+[|]+)|([|]+[[:blank:]]+)/", $docs) === 1) {
            throw new DocBlockSyntaxException("Syntax error in doc block:\n$docs");
        }
        return $docs;
    }

    /**
     * 
     * @param string $docs
     * @return array
     */
    private function getParamDocs($docs)
    {
        preg_match_all("/@param [a-z0-9\\ |$]+/i", $docs, $preg_matches);
        return $preg_matches[0];
    }

    /**
     * 
     * @param array $param_docs
     * @return array
     */
    private function parseParamDocs($param_docs)
    {
        $arg_types = [];
        foreach ($param_docs as $raw_param_doc) {
            $param_doc = preg_split("/[[:blank:]]+/", $raw_param_doc);
            $types = array_filter(explode('|', trim($param_doc[1])), function ($param_type) {
                return $this->isValidatable($param_type);
            });
            
            //Strips out any backslashes so functions can be called using the type
            $types = $this->sanitizeParamDocs($types);
            
            // Strip the '$'
            $key = substr(trim($param_doc[2]), 1);
            $arg_types[$key] = $types;
        }
        //Output like: [param_name => [index => 'type', index => 'type', ...], ...]
        return $arg_types;
    }

    private function isValidatable($param_type)
    {
        $type = $this->sanitizeParamDocs([$param_type])[0];
        return in_array($type, 
        array_merge($this->customTypes, array_keys($this->getValidatorMappings()))) || in_array(strtolower($type), $this->validatableTypes);
    }
    
    private function callValidator(array $ruleset, $param_name, $param_value)
    {
        $hasFailed = false;
        foreach ($ruleset as $original_rule) {
            //Get our mapping
            $mapped_rule = $this->getMapping($original_rule);
            
            $rule = $mapped_rule === NULL ? $original_rule : $mapped_rule;
            $callable = [$this, 'validate' . ucfirst($rule)];
            if (!is_callable($callable)) {
                throw new \BadMethodCallException(sprintf("Validatable type %s needs a validator method named '%s'", $original_rule, $callable[1]));
            }
            if ($callable($param_value)) {
                $hasFailed = false;
                break;
            }
            $hasFailed = true;
        }
        if ($hasFailed) {
            $this->throwException($param_name, $ruleset, $param_value);
        }
    }
    
    /**
     * 
     * @param string $param_name
     * @return string|null Returns null if no mapping exists
     */
    private function getMapping($param_name)
    {
        if (!key_exists($param_name, $this->getValidatorMappings())) {
            return NULL;
        }
        return $this->getValidatorMappings()[$param_name];
    }
    
    private function sanitizeParamDocs(array $types)
    {
        return array_map(function($value) {
            return str_replace("\\", '', $value);
        }, $types);
    }

    private function throwException($param_name, $ruleset, $param_value)
    {
        throw new \InvalidArgumentException(sprintf("$%s should be of type(s) %s, %s given", $param_name, implode('|', $ruleset), gettype($param_value)));
    }

    protected function validateInt($param_value)
    {
        return is_numeric($param_value) === TRUE
            // We use this trick to ensure we really are getting an int without
            // having to write another function
            && is_int($param_value - (int) $param_value) === TRUE;
    }

    protected function validateString($param_value)
    {
        return is_string($param_value) === TRUE;
    }

    protected function validateFloat($param_value)
    {
        return is_numeric($param_value) === TRUE && is_float((float) $param_value) === TRUE;
    }

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
    
    protected function validateBool($param_value)
    {
        return is_bool($param_value);
    }

    final protected function validateMixed($param_value)
    {
        return TRUE;
    }
}
