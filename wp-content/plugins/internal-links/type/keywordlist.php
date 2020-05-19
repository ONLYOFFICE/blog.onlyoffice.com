<?php
namespace ILJ\Type;

use ILJ\Helper\Encoding;

/**
 * Keywordlist Datatype
 *
 * A container for keywords with a comprehensive toolset for handling keyword data
 *
 * @package ILJ\Type
 * @since   1.0.0
 */
class KeywordList
{
    /**
     * @var   int $keywords
     * @since 1.0.0
     */
    private $keywords = [];

    /**
     * Constructor of the keywordlist datatype
     *
     * @since  1.0.0
     * @param  array $keyword_list Initial list of keywords
     * @return void
     */
    public function __construct($keyword_list = [])
    {
        if (!is_array($keyword_list) || !count($keyword_list)) {
            return;
        }

        foreach ($keyword_list as $keyword) {
            $this->addKeyword($keyword);
        }
    }

    /**
     * Converts an user input of comma seperated values with placeholders to keywordlist datatype
     *
     * @since  1.0.0
     * @param  string $input The input string for object generation
     * @return self
     */
    public static function fromInput($input)
    {
        $keyword_list = new self(self::decoded($input));
        $keyword_list->clean();
        return $keyword_list;
    }

    /**
     * Adds a keyword to the internal list
     *
     * @since  1.0.0
     * @param  string $keyword The keyword that should get added
     * @return void
     */
    public function addKeyword($keyword)
    {
        $this->keywords[] = $keyword;
        $this->clean();
    }

    /**
     * Returns the keyword list
     *
     * @since  1.0.0
     * @return array
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Encodes an array of keywords to a string format
     *
     * @since  1.0.0
     * @return string
     */
    public function encoded()
    {
        $keywords = [];
        foreach ($this->keywords as $keyword) {
            $unmasked_keyword   = Encoding::unmaskSlashes($keyword);
            $translated_keyword = Encoding::translateRegexToPseudo($unmasked_keyword);
            $keywords[]         = esc_attr($translated_keyword);
        }
        return implode(",", $keywords);
    }

    /**
     * Encodes a comma seperated list of keywords to an array
     *
     * @since  1.0.0
     * @param  string $csv_list A comma seperated list of keywords
     * @return array
     */
    private static function decoded($csv_list)
    {
        $csv_list_clear      = preg_replace("/\s*,\s*/", ",", $csv_list);
        $keyword_list_pseudo = explode(",", $csv_list_clear);
        $keyword_list_regex  = [];
        foreach ($keyword_list_pseudo as $keyword) {
            $keyword              = Encoding::translatePseudoToRegex($keyword);
            $keyword              = Encoding::maskSlashes($keyword);
            $keyword_list_regex[] = $keyword;
        }
        return $keyword_list_regex;
    }

    /**
     * Cleans the keyword list from empty strings and makes all keywords unique
     *
     * @since  1.0.0
     * @return void
     */
    public function clean()
    {
        $unspace_keyword_list   = preg_replace('/\s{2,}/', ' ', $this->keywords);
        $unique_keyword_list    = array_unique($unspace_keyword_list);
        $no_empties_keywordlist = array_filter(
            $unique_keyword_list, function ($keyword) {
                return (preg_match('/^\s+$/', $keyword) || $keyword == '') ? false : $keyword;
            }
        );
        $this->keywords = array_values($no_empties_keywordlist);
    }
}
