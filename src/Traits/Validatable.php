<?php
/*
 * This software is licensed under the MIT License. Please see LICENSE for more details.
 */

namespace Aesonus\Paladin\Traits;

use Aesonus\Paladin\Exceptions\ValidatorMethodNotFoundException;
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
    private $validatableTypes = ['mixed', 'int', 'integer', 'string', 'float', 'array', 'null'];
    private $validatorMappings;

    /**
     *
     * @var $customTypes array Custom validation types. Must be an array of strings.
     */
    protected $customTypes = [];

    /**
     *
     * @var \ReflectionMethod
     */
    protected $reflector;

    /**
     * Should always be called like:
     * $this->v(__METHOD__, func_get_args());
     * @param string $method_name
     * @param array $args
     */
    final protected function v($method_name, array $args)
    {
        if (!is_string($method_name) && isset($method_name)) {
            //TODO: Write a test for this
            $this->throwException('method_name', ['string', 'null'], $method_name);
        }
        //This also initializes the reflector
        $this->getReflector($method_name);
        $params = $this->getParamNames();
        $validators = $this->getParamTypes();
        //Cycle thru all $paramTypes
        foreach ($validators as $param_name => $ruleset) {
            $index = array_search($param_name, $params);
            $param_value = $args[$index];
            $this->callValidator($ruleset, $param_name, $param_value);
        }
    }

    /**
     * Allows using more than one validator to use one validation method
     * @param string $type
     * @param string $mapTo
     * @return $this
     * @throws \InvalidArgumentException
     */
    final public function mapType($type, $mapTo)
    {
        foreach(func_get_args() as $i => $value) {
            $names = ['type', 'mapTo'];
            if (!is_string($value)) {
                $this->throwException($names, ['string'], $type);
            }
        }
        $this->validatorMappings[$type] = $mapTo;
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
            // Add a filtering option so that we can validate namespaces
            $types = array_map(function($value) {
                return str_replace('\\', '', $value);
            }, $types);
            // Strip the '$'
            $key = substr(trim($param_doc[2]), 1);
            $arg_types[$key] = $types;
        }
        //Output like: [param_name => [index => 'type', index => 'type', ...], ...]
        return $arg_types;
    }

    private function isValidatable($param_type)
    {
        return in_array(strtolower($param_type), array_merge($this->validatableTypes, $this->customTypes));
    }

    private function callValidator(array $ruleset, $param_name, $param_value)
    {
        $hasFailed = false;
        foreach ($ruleset as $rule) {
            //Search for and find mappings
            $callable = [$this, 'validate' . ucfirst($rule)];
            if (!is_callable($callable)) {
                throw new ValidatorMethodNotFoundException(sprintf("Validatable type %s needs a validator method named '%s'", $rule, $callable[1]));
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

    private function throwException($param_name, $ruleset, $param_value)
    {
        throw new \InvalidArgumentException(sprintf("$%s should be of type(s) %s, %s given", $param_name, implode('|', $ruleset), gettype($param_value)));
    }

    protected function validateInteger($param_value)
    {
        return $this->validateInt($param_value);
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

    final protected function validateMixed($param_value)
    {
        return TRUE;
    }
}
