[![Build Status](https://travis-ci.org/Aesonus/paladin.svg?branch=master)](https://travis-ci.org/Aesonus/paladin)

# Paladin
This library provides functionality to validate parameters for methods in classes using docblocks.

## Installation

```
composer require aesonus/paladin
```

## Usage

Use Validatable in your class:

```php
class MyClass {
    use Aesonus\Paladin\Validatable;
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

Validate arguments with the same line of code:
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

## Extending Paladin

### Adding a custom parameter type

To add additional validatable type, simply create a validate method in the class using
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
start with a lower case or upper case letter:

```php
    ...
    /**
     * @param myType $paramName
     * @throws \InvalidArgumentException
     * ...
     */
    public function myMethodWithCustomType($paramName) {
        $this->v(__METHOD__, func_get_args());
        ...
    }
    ...
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