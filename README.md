# Paladin
This library includes functionality to validate parameters for methods in classes

## Usage

Use Validatable in your class

```php
class MyClass {
    use Aesonus\Paladin\Validatable;
    ...
}
```

Create a docblock for your methods. Use the pipe operator to define multiple types.
Validate arguments with the same line of code.

```php
/**
 * @param int|string|float|integer|array|null|mixed $paramName
 * @throws \InvalidArgumentException
 * ...
 */
public function myMethod($paramName, ...) {
    $this->v(__METHOD__, func_get_args());
    ...
}
```

Paladin throws an exception when an argument does not validate

## Extending Paladin

### Adding a custom parameter type

Add the parameter type, usually you should do this in the construct of the class using
Paladin\Validator

```php
$this->addCustomParameterType('myType');
```

Create a validate method in the class with Validatable.

```php
protected function validateMyType($param_value) {
    ...
    return true; // If $param_value is valid, return true, otherwise false.
}
```

Use your new parameter type.

```php
/**
 * @param myType $paramName
 * @throws \InvalidArgumentException
 * ...
 */
public function myMethodWithCustomType($paramName) {
    $this->v(__METHOD__, func_get_args());
    ...
}
```

### Overriding default validators

They use the same rules as custom types and validators.

```php
protected function validateArray($param_value) {
    //Custom validation code
    return true; // If validation passes
}
```

These are the types that have methods associated with them:
int, string, float, integer, array, null, mixed


## Tests

```
phpunit
```