# Paladin

This package allows for method arguments to be validated according to their docblocks

## Installation

Simply install with composer:

```
composer require aesonus/paladin
```

## Usage

Usage is simple. Just use the trait ValidatesParameters in your classes:

```php

use Aesonus\Paladin\ValidatesParameters;

```

To validate parameters, just call the protected method validate with the method name
and arguments:

```php

/**
 *
 * @param int[] $param
*/
public function myMethod(array $param)
{
    $this->validate(__METHOD__, func_get_args());
}

```

The previous example will throw an exception if the passed argument is not an
array of only int types.

You can use most psalm types as well. Refer to psalm documentation to see what types you can use.

Currently, templates and complex callables cannot be validated