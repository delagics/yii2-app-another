<?php

namespace console\helpers;

class Console extends \yii\helpers\Console
{
    /**
     * Gets input from STDIN and returns a string right-trimmed for EOLs.
     *
     * @param boolean $raw If set to true, returns the raw string without trimming
     * @return string the string read from stdin
     */
    public static function stdin($raw = false)
    {
        return parent::stdin($raw);
    }

    /**
     * Prints a string to STDOUT.
     *
     * @param string $string the string to print
     * @return int|boolean Number of bytes printed or false on error
     */
    public static function stdout($string)
    {
        $args = func_get_args();
        array_shift($args);
        $string = parent::ansiFormat($string, $args) . "\n";

        return parent::stdout($string);
    }

    /**
     * Prints a string to STDERR.
     *
     * @param string $string the string to print
     * @return int|boolean Number of bytes printed or false on error
     */
    public static function stderr($string)
    {
        $args = func_get_args();
        array_shift($args);
        $string = parent::ansiFormat($string, $args) . "\n";

        return parent::stderr($string);
    }
}
