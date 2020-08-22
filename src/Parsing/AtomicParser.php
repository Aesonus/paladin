<?php
/*
 * The MIT License
 *
 * Copyright 2020 Aesonus <corylcomposinger at gmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace Aesonus\Paladin\Parsing;

use Aesonus\Paladin\Contracts\ParameterInterface;
use Aesonus\Paladin\Contracts\TypeStringParsingInterface;
use Aesonus\Paladin\DocBlock\ArrayKeyParameter;
use Aesonus\Paladin\DocBlock\ArrayParameter;
use Aesonus\Paladin\DocBlock\BoolParameter;
use Aesonus\Paladin\DocBlock\CallableParameter;
use Aesonus\Paladin\DocBlock\CallableStringParameter;
use Aesonus\Paladin\DocBlock\ClassStringParameter;
use Aesonus\Paladin\DocBlock\FalseParameter;
use Aesonus\Paladin\DocBlock\FloatParameter;
use Aesonus\Paladin\DocBlock\IntParameter;
use Aesonus\Paladin\DocBlock\IterableParameter;
use Aesonus\Paladin\DocBlock\MixedParameter;
use Aesonus\Paladin\DocBlock\NullParameter;
use Aesonus\Paladin\DocBlock\NumericParameter;
use Aesonus\Paladin\DocBlock\NumericStringParameter;
use Aesonus\Paladin\DocBlock\ObjectParameter;
use Aesonus\Paladin\DocBlock\ResourceParameter;
use Aesonus\Paladin\DocBlock\ScalarParameter;
use Aesonus\Paladin\DocBlock\StringParameter;
use Aesonus\Paladin\DocBlock\TraitStringParameter;
use Aesonus\Paladin\DocBlock\TrueParameter;
use Aesonus\Paladin\Exceptions\ParseException;
use Aesonus\Paladin\Parser;
use function Aesonus\Paladin\is_class_string;

/**
 *
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class AtomicParser implements TypeStringParsingInterface
{
    const PARAMETER_TYPES = [
        'int' => IntParameter::class,
        'integer' => IntParameter::class,
        'float' => FloatParameter::class,
        'double' => FloatParameter::class,
        'string' => StringParameter::class,
        'class-string' => ClassStringParameter::class,
        'trait-string' => TraitStringParameter::class,
        'callable-string' => CallableStringParameter::class,
        'numeric-string' => NumericStringParameter::class,
        'bool' => BoolParameter::class,
        'boolean' => BoolParameter::class,
        'array-key' => ArrayKeyParameter::class,
        'numeric' => NumericParameter::class,
        'scalar' => ScalarParameter::class,
        'array' => ArrayParameter::class,
        'resource' => ResourceParameter::class,
        'object' => ObjectParameter::class,
        'callable' => CallableParameter::class,
        'null' => NullParameter::class,
        'true' => TrueParameter::class,
        'false' => FalseParameter::class,
        'mixed' => MixedParameter::class,
        'iterable' => IterableParameter::class,
    ];

    public function parse(Parser $parser, string $typeString): ParameterInterface
    {
        if (is_class_string($typeString)) {
            return new ObjectParameter($parser->getUseContext()->getUsedClass($typeString));
        }
        if (array_key_exists($typeString, self::PARAMETER_TYPES)) {
            $parameter = self::PARAMETER_TYPES[$typeString];
            return new $parameter;
        }
        throw new ParseException($typeString);
    }
}
