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

```php
/**
 * @param int|string|float|integer|null|mixed $paramName
 * ...
 */
public function myMethod($paramName, ...) {
    $this->v(__METHOD__, func_get_args());
    ...
}
```