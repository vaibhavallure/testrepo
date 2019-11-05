<?php

namespace Elastica;

use Elastica\Exception\JSONParseException;

// vr added, missing from php 5.4 
if (!function_exists('json_last_error_msg')) {

    /**
     * Copied from http://php.net/manual/en/function.json-last-error-msg.php#117393 
     * @return string 
     */
    function json_last_error_msg()
    {
        static $errors = array(
            JSON_ERROR_NONE => 'No error',
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'State mismatch (invalid or malformed JSON)',
            JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
            JSON_ERROR_SYNTAX => 'Syntax error',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
        );
        $error = json_last_error();
        return isset($errors[$error]) ? $errors[$error] : 'Unknown error';
    }

}

/**
 * Elastica JSON tools.
 */
class JSON
{

    /**
     * Parse JSON string to an array.
     *
     * @link http://php.net/manual/en/function.json-decode.php
     * @link http://php.net/manual/en/function.json-last-error.php
     *
     * @param string $args,... JSON string to parse
     *
     * @throws JSONParseException
     *
     * @return array PHP array representation of JSON string
     */
    public static function parse($args/* inherit from json_decode */)
    {
        // extract arguments
        $args = func_get_args();

        // default to decoding into an assoc array
        if (count($args) === 1) {
            $args[] = true;
        }

        // run decode
        $array = call_user_func_array('json_decode', $args);

        // turn errors into exceptions for easier catching
        if ($error = self::getJsonLastErrorMsg()) {
            throw new JSONParseException($error);
        }

        // output
        return $array;
    }

    /**
     * Convert input to JSON string with standard options.
     *
     * @link http://php.net/manual/en/function.json-encode.php
     * @link http://php.net/manual/en/function.json-last-error.php
     *
     * @param mixed $args,... Target to stringify
     *
     * @throws JSONParseException
     *
     * @return string Valid JSON representation of $input
     */
    public static function stringify($args/* inherit from json_encode */)
    {
        // extract arguments
        $args = func_get_args();


        if (defined("JSON_PRESERVE_ZERO_FRACTION")) { // php >= 5.6.6
            if (isset($args[1])) {
                $args[1] |= JSON_PRESERVE_ZERO_FRACTION;
            } else {
                $args[1] = JSON_PRESERVE_ZEON_FRACTION;
            }
        }
        // run encode and output
        $string = call_user_func_array('json_encode', $args);

        // turn errors into exceptions for easier catching
        if ($error = self::getJsonLastErrorMsg()) {
            throw new JSONParseException($error);
        }

        // output
        return $string;
    }

    /**
     * Get Json Last Error.
     *
     * @link http://php.net/manual/en/function.json-last-error.php
     * @link http://php.net/manual/en/function.json-last-error-msg.php
     * @link https://github.com/php/php-src/blob/master/ext/json/json.c#L308
     *
     * @return string
     */
    private static function getJsonLastErrorMsg()
    {
        if (JSON_ERROR_NONE !== json_last_error()) {
            /**
             * Copied from http://php.net/manual/en/function.json-last-error-msg.php#117393 
             */
            static $errors = array(
                JSON_ERROR_NONE => 'No error',
                JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
                JSON_ERROR_STATE_MISMATCH => 'State mismatch (invalid or malformed JSON)',
                JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
                JSON_ERROR_SYNTAX => 'Syntax error',
                JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
            );
            $error = json_last_error();
            return isset($errors[$error]) ? $errors[$error] : 'Unknown error';
        } else {
            return false;
        }
    }

}
