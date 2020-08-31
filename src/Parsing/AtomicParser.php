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
use Aesonus\Paladin\DocblockParameters\ArrayKeyParameter;
use Aesonus\Paladin\DocblockParameters\ArrayParameter;
use Aesonus\Paladin\DocblockParameters\BoolParameter;
use Aesonus\Paladin\DocblockParameters\CallableParameter;
use Aesonus\Paladin\DocblockParameters\CallableStringParameter;
use Aesonus\Paladin\DocblockParameters\ClassStringParameter;
use Aesonus\Paladin\DocblockParameters\FalseParameter;
use Aesonus\Paladin\DocblockParameters\FloatParameter;
use Aesonus\Paladin\DocblockParameters\IntParameter;
use Aesonus\Paladin\DocblockParameters\IterableParameter;
use Aesonus\Paladin\DocblockParameters\ListParameter;
use Aesonus\Paladin\DocblockParameters\MixedParameter;
use Aesonus\Paladin\DocblockParameters\NullParameter;
use Aesonus\Paladin\DocblockParameters\NumericParameter;
use Aesonus\Paladin\DocblockParameters\NumericStringParameter;
use Aesonus\Paladin\DocblockParameters\ObjectParameter;
use Aesonus\Paladin\DocblockParameters\ResourceParameter;
use Aesonus\Paladin\DocblockParameters\ScalarParameter;
use Aesonus\Paladin\DocblockParameters\StringParameter;
use Aesonus\Paladin\DocblockParameters\TraitStringParameter;
use Aesonus\Paladin\DocblockParameters\TrueParameter;
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

    public function parse(Parser $parser, string $typeString): ParameterInterface
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
