<?php

namespace ILJ\Enumeration;

/**
 * Enum for TagExclusion
 *
 * @package ILJ\Enumerations
 * @since   1.1.1
 */
final class TagExclusion
{
    const  HEADLINE = 'tag_headlines' ;
    const  STRONG = 'tag_strong' ;
    const  DIV = 'tag_div' ;
    const  TABLE = 'tag_table' ;
    const  CAPTION = 'tag_caption' ;
    const  ORDERED_LIST = 'tag_ordered_list' ;
    const  UNORDERED_LIST = 'tag_unordered_list' ;
    const  BLOCKQUOTE = 'tag_blockquote' ;
    const  ITALIC = 'tag_italic' ;
    const  CITE = 'tag_cite' ;
    const  CODE = 'tag_code' ;
    /**
     * Returns all enumeration values
     *
     * @since  1.1.1
     * @return array
     */
    public static function getValues()
    {
        $reflectionClass = new \ReflectionClass( static::class );
        return $reflectionClass->getConstants();
    }
    
    /**
     * Translate enum to natural language
     *
     * @since  1.1.1
     * @param  string $value The enum value
     * @return string
     */
    public static function translate( $value )
    {
        switch ( $value ) {
            case self::HEADLINE:
                return htmlentities( __( 'Headlines', 'ILJ' ) . ' (<h1-6>)' );
            case self::STRONG:
                return htmlentities( __( 'Strong text', 'ILJ' ) . ' (<strong>, <b>)' );
            case self::DIV:
                return htmlentities( __( 'Div container', 'ILJ' ) . ' (<div>)' );
            case self::TABLE:
                return htmlentities( __( 'Tables', 'ILJ' ) . ' (<table>)' );
            case self::CAPTION:
                return htmlentities( __( 'Image captions', 'ILJ' ) . ' (<figcaption>)' );
            case self::ORDERED_LIST:
                return htmlentities( __( 'Ordered lists', 'ILJ' ) . ' (<ol>)' );
            case self::UNORDERED_LIST:
                return htmlentities( __( 'Unordered lists', 'ILJ' ) . ' (<ul>)' );
            case self::BLOCKQUOTE:
                return htmlentities( __( 'Blockquotes', 'ILJ' ) . ' (<blockquote>)' );
            case self::ITALIC:
                return htmlentities( __( 'Italic text', 'ILJ' ) . ' (<em>, <i>)' );
            case self::CITE:
                return htmlentities( __( 'Inline quotes', 'ILJ' ) . ' (<cite>)' );
            case self::CODE:
                return htmlentities( __( 'Sourcecode', 'ILJ' ) . ' (<code>)' );
        }
        return 'N/A';
    }
    
    /**
     * Returns the regex for the exclusion
     *
     * @since  1.1.1
     * @param  string $deputy The name of the html area
     * @return string|bool
     */
    public static function getRegex( $deputy )
    {
        global  $ilj_fs ;
        switch ( $deputy ) {
            case self::HEADLINE:
                return '/(?<parts><h[1-6].*>.*<\\/h[1-6]>)/sU';
            case self::STRONG:
                return '/(?<parts><strong.*>.*<\\/strong>|<b.*>.*<\\/b>)/sU';
        }
        return false;
    }

}