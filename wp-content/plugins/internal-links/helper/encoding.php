<?php

namespace ILJ\Helper;

/**
 * Encoding toolset
 *
 * Methods for encoding / decoding of strings for the application
 *
 * @package ILJ\Helper
 * @since   1.0.0
 */
class Encoding
{
    /**
     * Masks (back-)slashes for saving into the postmeta table through WordPress sanitizing methods
     *
     * @since  1.0.0
     * @param  string $regex_string The full regex pattern
     * @return string
     */
    public static function maskSlashes($regex_string)
    {
        return str_replace("\\", "|", $regex_string);
    }

    /**
     * Unmasks sanitized (back-)slashes for retreiving a pattern from the postmeta table
     *
     * @since  1.0.0
     * @param  string $masked_string The masked regex pattern
     * @return string
     */
    public static function unmaskSlashes($masked_string)
    {
        return str_replace("|", "\\", $masked_string);
    }

    /**
     * Translates a pseudo selection rule to its regex pattern
     *
     * @since  1.0.0
     * @param  string $pseudo The given pseudo pattern
     * @return string
     */
    public static function translatePseudoToRegex($pseudo)
    {
        $word_pattern = '(?:\b\w+\b\s*)';
        $regex        = preg_replace('/\s*{(\d+)}\s*/', ' ' . $word_pattern . '{\1} ', $pseudo);
        $regex        = preg_replace('/\s*{\+(\d+)}\s*/', ' ' . $word_pattern . '{\1,} ', $regex);
        $regex        = preg_replace('/\s*{\-(\d+)}\s*/', ' ' . $word_pattern . '{1,\1} ', $regex);
        $regex        = preg_replace('/^\s*(.+?)\s*$/', '\1', $regex);
        return $regex;
    }

    /**
     * Translates a regex pattern to its equivalent pseudo pattern
     *
     * @since  1.0.0
     * @param  string $regex The given regex pattern
     * @return string
     */
    public static function translateRegexToPseudo($regex)
    {
        $pseudo = preg_replace('/\(\?\:\\\b\\\w\+\\\b\\\s\*\)\{(\d+)\}/', '{\1}', $regex);
        $pseudo = preg_replace('/\(\?\:\\\b\\\w\+\\\b\\\s\*\){(\d+),}/', '{+\1}', $pseudo);
        $pseudo = preg_replace('/\(\?\:\\\b\\\w\+\\\b\\\s\*\){(\d+),(\d+)}/', '{-\2}', $pseudo);
        return $pseudo;
    }

    /**
     * Decorates and manipulates a given pattern for matching optimization
     *
     * @since  1.1.5
     * @param  $pattern
     * @return string
     */
    public static function maskPattern($pattern)
    {
        $phrase = '(?<phrase>%1$s%2$s%1$s)';

        $has_dot = strpos($pattern, '.') !== false;
        $masked_pattern = sprintf($phrase, ($has_dot ? '' : '\b'), wptexturize($pattern));

        return $masked_pattern;
    }
}
