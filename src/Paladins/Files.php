<?php
/*
 * This software is licensed under the MIT License. Please see LICENSE for more details.
 */

namespace Aesonus\Paladin\Paladins;

/**
 * Contains custom types for files. Compatible with streams.
 * @author Aesonus <corylcomposinger at gmail.com>
 */
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
        return is_string($param_value) && is_dir($param_value);
    }
}
