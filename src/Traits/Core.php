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
            //$types = $this->sanitizeParamTypes($types);

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

        $type = $param_type;
        return in_array($type, array_keys($this->getValidatorMappings())) ||
            is_callable($this->getValidatorCallable($type)) ||
            class_exists($type);
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
            $validate = $this->getValidatorCallable($type);
            $is_successful = method_exists($validate[0], $validate[1]) && $validate($param_value) || $this->isClassOf($param_value, $type);
            if ($is_successful) {
                return;
            }
        }
        //If the while loop has ran then $is_successful will be set
        if (isset($is_successful)) {
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

    abstract protected function validateInt($param);

    abstract protected function validateFloat($param);

    abstract protected function validateString($param);

    abstract protected function validateNull($param);

    abstract protected function validateArray($param);

    abstract protected function validateScalar($param);

    abstract protected function validateBool($param);

    abstract protected function validateObject($param);

    abstract protected function validateCallable($param);

    abstract protected function isClassOf($object, $type);
}
