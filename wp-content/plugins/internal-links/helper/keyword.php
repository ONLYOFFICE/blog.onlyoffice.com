<?php

namespace ILJ\Helper;

/**
 * Toolset for keywords
 *
 * Methods for keyword (-phrases)
 *
 * @package ILJ\Helper
 * @since   1.0.4
 */
class Keyword
{
    /**
     * Calculates an effective word count value with respect to configured gaps
     *
     * @since  1.0.4
     * @param  string $keyword The (keyword-) phrase where words get counted
     * @return int
     */
    public static function gapWordCount($keyword)
    {
        $word_count = count(explode(' ', $keyword));

        preg_match_all('/{(?:1,)?(\d),?}/', $keyword, $matches);

        if (isset($matches[1])) {
            $word_count -= count($matches[1]);

            foreach ($matches[1] as $match) {
                $word_count += (int) $match;
            }
        }

        return $word_count;
    }
}
