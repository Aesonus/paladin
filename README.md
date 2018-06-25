[![Build Status](https://travis-ci.org/Aesonus/paladin.svg?branch=master)](https://travis-ci.org/Aesonus/paladin)

# Paladin
This library provides functionality to validate parameters for methods in classes using docblocks.

## Installation

```
composer require aesonus/paladin
```

## Usage

Use Validatable or StrictValidatable in your class:

```php
class MyClass {
    use Aesonus\Paladin\Traits\Validatable;
    // OR
    use Aesonus\Paladin\Traits\StrictValidatable;
    ...
```

Create a docblock for your methods.
Use the pipe operator to allow for multiple types:
```php
    ...
    /**
     * @param int|string|float|integer|boolean|bool|scalar|array|callable|object|null|mixed $paramName
     * @throws \InvalidArgumentException
     * ...
     */
    public function myMethod($paramName, ...) {
    ...
```

You can also validate instances of a class:
```php
    /**
     * @param \stdClass
     * @throws \InvalidArgumentException
     * ...
     */
     public function myMethod($paramName, ...) {
    ...
```

Validate arguments with a single line of code:
```php
        ...
        $this->v(__METHOD__, func_get_args());
    }
} //End Class
```

Paladin throws an exception when an argument does not validate

Note that when arguments are omitted they are also validated. Consider:

```php
    /**
     * @param int $paramName
     * @throws \InvalidArgumentException
     * ...
     */
    public function myMethod($paramName = 'randomdefault', ...) {...}

```

When the function is called:

```php
    $this->myMethod();
```

The default value will throw an exception because it isn't a string

### Strict Validation

The StrictValidator trait does not allow strings to validate as ints, floats, or
classes of.

## Extending Paladin

### Adding a custom parameter type

You may add additional validatable types; simply create a validate method in the class using
validateCamelCaseTypeName of the type name where CamelCaseTypeName is the name
you wish to use:

```php
    ...
    protected function validateMyType($param_value) {
        ...
        return true; // If $param_value is valid, return true, otherwise false.
    }
    ...
```

Use your new parameter type in the docblock. Please note that the type name can
start with a lower case or upper case letter but are otherwise case sensitive:

```php
    ...
    /**
     * @param myType|MyType $paramName
     * @throws \InvalidArgumentException
     * ...
     */
    public function myMethodWithCustomType($paramName) {
        $this->v(__METHOD__, func_get_args());
        ...
    }
    ...
```

### Paladins

Grouping validators together in their own trait is a great way to re-use types

```php
// Use the *\Paladins namespace paradigm
namespace Aesonus\Paladin\Paladins;

trait Files
{
    protected function validateFile($param_value)
    {
        return is_string($param_value) &&
            file_exists($param_value) &&
            !is_dir($param_value);
    }

    protected function validateDir($param_value)
    {
        return $this->validateFile($param_value) && is_dir($param_value);
    }
}
```

Then use it in your class:

```php
use Aesonus\Paladin\Paladins;

class MyClass {
    use Aesonus\Paladin\Traits\Validatable;
    use Paladins\Files;

    /**
     * 
     * @param dir $param
     */
    public function myFunction($param) {
        $this->v(__METHOD__, func_get_args());
        ...
    }
```

### Overriding default validators

You may override the default validators:

```php
    ...
    protected function validateArray($param_value) {
        //Custom validation code
        return true; // If validation passes
    }
} //End Class
```

These are the types that have override-able methods associated with them:
int, string, float, array, scalar, bool, object, callable and null

### Mapping a type to a docblock alias

You can define multiple aliases to validate as one type. Internally, Paladin maps
integer to int:

```php
    public function __construct() {
        //mapToType($alias, $type)
        $this->mapToType('integer','int');
    }
```

You can do this for your own types:
```php
    public function __construct() {
        //mapToType($alias, $type)
        $this->mapToType('NamespaceMyType','MyType');
    }
```

## Tests

```
phpunit
```