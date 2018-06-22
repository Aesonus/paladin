<?php
/*
 * This software is licensed under the MIT License. Please see LICENSE for more details.
 */

namespace Aesonus\Paladin\Traits;

/**
 * Contains methods
 * @author Aesonus <corylcomposinger at gmail.com>
 */
trait Core
{

    /**
     *
     * @var \ReflectionMethod
     */
    private $reflector;

    /**
     * Contains valid data types for phpdocs. You can make your own if you wish.
     * [TODO: Link to documentation]
     * @var array 
     */
    protected $validatableTypes = ['mixed', 'scalar', 'int', 'string', 'float', 'array', 'null', 'bool', 'object', 'callable'];

        
    /**
     * 
     * @param string $method
     * @param array $args
     * @throws \InvalidArgumentException
     */
    abstract protected function v($method, array $args);

    /**
     * 
     * @return array
     */
    abstract protected function getValidatorMappings();
    
    /**
     * 
     * @param string $alias
     * @param string $type
     * @throws \InvalidArgumentException
     * @return $this Allows for fluent usage
     */
    abstract public function mapType($alias, $type);

    /**
     * Factory/getter to get a reflection method class. Either creates a new instance
     * or returns the current.
     * @param string|null $method_name [optional] Leave out to get the current reflector
     * instance
     * @throws \ReflectionException
     * @return \ReflectionMethod
     */
    protected function getReflector($method_name = NULL)
    {
        if (isset($method_name) || !isset($this->reflector)) {
            $this->reflector = new \ReflectionMethod($method_name);
        }
        return $this->reflector;
    }

    protected function getParamNames()
    {
        $reflector = $this->getReflector();
        $params = [];
        foreach ($reflector->getParameters() as $param) {
            $params[] = $param->getName();
        }
        return $params;
    }

    protected function getParamTypes()
    {
        if (!$docs = $this->getRawDocs()) {
            return [];
        }
        //Get all @param docblock tags
        $param_docs = $this->getParamDocs($docs);

        $arg_types = $this->parseParamTypes($param_docs);
        return $arg_types;
    }

    protected function getParamDefaults()
    {
        $reflector = $this->getReflector();
        $defaults = [];
        foreach ($reflector->getParameters() as $i => $param) {
            if (!$param->isOptional()) {
                continue;
            }
            $defaults[$i] = $param->getDefaultValue();
        }
        return $defaults;
    }

    /**
     * Gets doc comments. If the docblock uses inheritdoc tag, this method will
     * get the parent doc block
     * @return string|false
     */
    protected function getRawDocs()
    {
        $reflector = $this->getReflector();
        $docs = $reflector->getDocComment();
        while (stripos($docs, '{@inheritdoc}') !== FALSE) {
            $reflector = $reflector
                ->getDeclaringClass()
                ->getParentClass()
                ->getMethod($reflector->getName());
            $docs = $reflector->getDocComment();
        }

        /*
          if (preg_match("/([[:blank:]]+[|]+)|([|]+[[:blank:]]+)/", $docs) === 1) {
          throw new \Aesonus\Paladin\Exceptions\DocBlockSyntaxException("Syntax error in doc block:\n$docs");
          }
         */
        return $docs;
    }

    /**
     * 
     * @param string $docs
     * @return array
     */
    protected function getParamDocs($docs)
    {
        preg_match_all("/@param [a-z0-9_\\\ |$]+/i", $docs, $preg_matches);
        return $preg_matches[0];
    }

    /**
     * 
     * @param array $param_docs
     * @return array
     */
    protected function parseParamTypes($param_docs)
    {
        $arg_types = [];
        foreach ($param_docs as $i => $raw_param_doc) {
            $param_doc = preg_split("/[[:blank:]]+/", $raw_param_doc);
            $types = array_filter(
                explode('|', trim($param_doc[1])), [$this, 'isValidatable']
            );

            //Strips out any backslashes so functions can be called using the type
            $types = $this->sanitizeParamTypes($types);

            $key = $this->getParamNames()[$i];
            // Strip the '$'
            //$key = substr(trim($param_doc[2]), 1);
            $arg_types[$key] = $types;
        }
        //Output like: [param_name => [index => 'type', index => 'type', ...], ...]
        return $arg_types;
    }

    /**
     * Returns whether the parameter type is validatable
     */
    protected function isValidatable($param_type)
    {
        $type = $this->sanitizeParamTypes([$param_type])[0];
        return in_array($type, array_keys($this->getValidatorMappings())) ||
            in_array(strtolower($type), $this->validatableTypes) ||
            is_callable($this->getValidatorCallable($type));
    }

    protected function getValidatorCallable($type)
    {
        return [$this, 'validate' . ucfirst($type)];
    }

    protected function callValidator(array $valid_types, $param_name, $param_value)
    {
        while ($original_type = array_shift($valid_types)) {
            //Get our mapping
            $mapped_type = $this->getMapping($original_type);

            $type = $mapped_type === NULL ? $original_type : $mapped_type;
            $callable = $this->getValidatorCallable($type);
            $has_failed = !$callable($param_value);
            if (!$has_failed) {
                return ;
            }
        }
        if (isset($has_failed)) {
            $this->throwException($param_name, $valid_types, $param_value);
        }
    }

    /**
     * 
     * @param string $param_name
     * @return string|null Returns null if no mapping exists
     */
    protected function getMapping($param_name)
    {
        if (!key_exists($param_name, $this->getValidatorMappings())) {
            return NULL;
        }
        return $this->getValidatorMappings()[$param_name];
    }

    protected function sanitizeParamTypes(array $types)
    {
        return array_map(function($value) {
            return str_replace("\\", '', $value);
        }, $types);
    }

    protected function throwException($param_name, array $ruleset, $param_value)
    {
        throw new \InvalidArgumentException(sprintf("$%s should be of type(s) %s, %s given", $param_name, implode('|', $ruleset), gettype($param_value))
        );
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

    protected function validateObject($param_value)
    {
        return is_object($param_value);
    }

    protected function validateCallable($param_value)
    {
        return is_callable($param_value);
    }

    final protected function validateMixed($param_value)
    {
        return TRUE;
    }

}
