<?php

/**
 * Plain-PHP fallbacks for mb_* functions when mbstring extension is missing.
 *
 * These handle ASCII correctly, which is sufficient for the framework's
 * internal use (route matching, snake/camel case, validator attribute parsing).
 *
 * WordPress already polyfills mb_strlen and mb_substr via wp-includes/compat.php.
 * This file covers the remaining functions the framework and plugin use.
 *
 * When mbstring IS loaded, this file does nothing.
 */

if (extension_loaded('mbstring')) {
    return;
}

if (!defined('MB_CASE_UPPER')) {
    define('MB_CASE_UPPER', 0);
}

if (!defined('MB_CASE_LOWER')) {
    define('MB_CASE_LOWER', 1);
}

if (!defined('MB_CASE_TITLE')) {
    define('MB_CASE_TITLE', 2);
}

if (!function_exists('mb_strlen')) {
    function mb_strlen($string, $encoding = null)
    {
        return strlen($string);
    }
}

if (!function_exists('mb_substr')) {
    function mb_substr($string, $start, $length = null, $encoding = null)
    {
        return $length === null ? substr($string, $start) : substr($string, $start, $length);
    }
}

if (!function_exists('mb_strtolower')) {
    function mb_strtolower($string, $encoding = null)
    {
        return strtolower($string);
    }
}

if (!function_exists('mb_strtoupper')) {
    function mb_strtoupper($string, $encoding = null)
    {
        return strtoupper($string);
    }
}

if (!function_exists('mb_strpos')) {
    function mb_strpos($haystack, $needle, $offset = 0, $encoding = null)
    {
        return strpos($haystack, $needle, $offset);
    }
}

if (!function_exists('mb_strrpos')) {
    function mb_strrpos($haystack, $needle, $offset = 0, $encoding = null)
    {
        return strrpos($haystack, $needle, $offset);
    }
}

if (!function_exists('mb_strwidth')) {
    function mb_strwidth($string, $encoding = null)
    {
        return strlen($string);
    }
}

if (!function_exists('mb_strimwidth')) {
    function mb_strimwidth($string, $start, $width, $trimmarker = '', $encoding = null)
    {
        $string = substr($string, $start);

        if (strlen($string) <= $width) {
            return $string;
        }

        $markerLen = strlen($trimmarker);

        return substr($string, 0, $width - $markerLen) . $trimmarker;
    }
}

if (!function_exists('mb_convert_case')) {
    function mb_convert_case($string, $mode, $encoding = null)
    {
        switch ($mode) {
            case MB_CASE_UPPER:
                return strtoupper($string);
            case MB_CASE_LOWER:
                return strtolower($string);
            case MB_CASE_TITLE:
                return ucwords(strtolower($string));
            default:
                return $string;
        }
    }
}

if (!function_exists('mb_str_split')) {
    function mb_str_split($string, $length = 1, $encoding = null)
    {
        return str_split($string, $length);
    }
}

if (!function_exists('mb_split')) {
    function mb_split($pattern, $string, $limit = -1)
    {
        return preg_split('/' . $pattern . '/u', $string, $limit);
    }
}
