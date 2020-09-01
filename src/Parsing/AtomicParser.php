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

use Aesonus\Paladin\Contracts\ParameterValidatorInterface;
use Aesonus\Paladin\Contracts\TypeStringParsingInterface;
use Aesonus\Paladin\ParameterValidators\ArrayKeyParameter;
use Aesonus\Paladin\ParameterValidators\ArrayParameter;
use Aesonus\Paladin\ParameterValidators\BoolParameter;
use Aesonus\Paladin\ParameterValidators\CallableParameter;
use Aesonus\Paladin\ParameterValidators\CallableStringParameter;
use Aesonus\Paladin\ParameterValidators\ClassStringParameter;
use Aesonus\Paladin\ParameterValidators\FalseParameter;
use Aesonus\Paladin\ParameterValidators\FloatParameter;
use Aesonus\Paladin\ParameterValidators\IntParameter;
use Aesonus\Paladin\ParameterValidators\IterableParameter;
use Aesonus\Paladin\ParameterValidators\ListParameter;
use Aesonus\Paladin\ParameterValidators\MixedParameter;
use Aesonus\Paladin\ParameterValidators\NullParameter;
use Aesonus\Paladin\ParameterValidators\NumericParameter;
use Aesonus\Paladin\ParameterValidators\NumericStringParameter;
use Aesonus\Paladin\ParameterValidators\ObjectParameter;
use Aesonus\Paladin\ParameterValidators\ResourceParameter;
use Aesonus\Paladin\ParameterValidators\ScalarParameter;
use Aesonus\Paladin\ParameterValidators\StringParameter;
use Aesonus\Paladin\ParameterValidators\TraitStringParameter;
use Aesonus\Paladin\ParameterValidators\TrueParameter;
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
        'list' => ListParameter::class,
    ];

    public function parse(Parser $parser, string $typeString): ParameterValidatorInterface
    {
        $this->assertThatStringCanBeParsed($typeString);
        if (is_class_string($typeString)) {
            return new ObjectParameter($parser->getUseContext()->getUsedClass($typeString));
        }
        $parameter = self::PARAMETER_TYPES[$typeString];
        return new $parameter;
    }

    public function assertThatStringCanBeParsed(string $typeString): void
    {
        if (!is_class_string($typeString) && !array_key_exists($typeString, self::PARAMETER_TYPES)) {
            throw new ParseException($typeString);
        }
    }
}
