<?php

namespace ILJ\Core;

use  ILJ\Core\Options ;
use  ILJ\Type\Ruleset ;
use  ILJ\Helper\Keyword ;
use  ILJ\Helper\Encoding ;
use  ILJ\Database\Postmeta ;
use  ILJ\Helper\IndexAsset ;
use  ILJ\Database\Linkindex ;
use  ILJ\Helper\Replacement ;
use  ILJ\Backend\Environment ;
use  ILJ\Enumeration\KeywordOrder ;
/**
 * IndexBuilder
 *
 * This class is responsible for the creation of the links
 *
 * @package ILJ\Core
 *
 * @since 1.0.0
 */
class IndexBuilder
{
    const  ILJ_ACTION_AFTER_INDEX_BUILT = 'ilj_after_index_built' ;
    /**
     * @var   array
     * @since 1.0.0
     */
    private  $posts = array() ;
    /**
     * @var   Ruleset
     * @since 1.0.0
     */
    private  $link_rules = null ;
    /**
     * @var   array
     * @since 1.0.1
     */
    private  $link_options = array() ;
    public function __construct()
    {
        $this->link_rules = new Ruleset();
        $this->link_options['multi_keyword_mode'] = (bool) Options::getOption( \ILJ\Core\Options\MultipleKeywords::getKey() );
        $this->link_options['links_per_page'] = ( $this->link_options['multi_keyword_mode'] === false ? Options::getOption( \ILJ\Core\Options\LinksPerPage::getKey() ) : 0 );
        $this->link_options['links_per_target'] = ( $this->link_options['multi_keyword_mode'] === false ? Options::getOption( \ILJ\Core\Options\LinksPerTarget::getKey() ) : 0 );
    }
    
    /**
     * Executes all processes for building a new index
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function buildIndex()
    {
        $start = microtime( true );
        $this->loadPosts();
        $this->loadLinkConfigurations();
        $this->removeIndices();
        $entries_count = $this->setIndices();
        $duration = round( microtime( true ) - $start, 2 );
        $offset = get_option( 'gmt_offset' );
        $hours = (int) $offset;
        $minutes = ($offset - floor( $offset )) * 60;
        $feedback = [
            "last_update" => [
            "date"     => new \DateTime( 'now', new \DateTimeZone( sprintf( '%+03d:%02d', $hours, $minutes ) ) ),
            "entries"  => $entries_count,
            "duration" => $duration,
        ],
        ];
        Environment::update( 'linkindex', $feedback );
        return $feedback;
    }
    
    /**
     * Loads all allowed posts for linking by the configured settings
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function loadPosts()
    {
        $whitelist = Options::getOption( \ILJ\Core\Options\Whitelist::getKey() );
        if ( !count( $whitelist ) ) {
            return;
        }
        $args = [
            'posts_per_page'   => -1,
            'post__not_in'     => Options::getOption( \ILJ\Core\Options\Blacklist::getKey() ),
            'post_type'        => $whitelist,
            'post_status'      => [ 'publish' ],
            'suppress_filters' => true,
        ];
        $query = new \WP_Query( $args );
        $this->posts = $query->posts;
    }
    
    /**
     * Flushes the existing linkindex database table
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function removeIndices()
    {
        Linkindex::flush();
    }
    
    /**
     * Picks up all meta definitions for configured keywords and adds them to internal ruleset
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function loadLinkConfigurations()
    {
        global  $ilj_fs ;
        $post_definitions = Postmeta::getAllLinkDefinitions();
        foreach ( $post_definitions as $definition ) {
            $type = 'post';
            $anchors = unserialize( $definition->meta_value );
            if ( !$anchors ) {
                continue;
            }
            $keyword_order = Options::getOption( \ILJ\Core\Options\KeywordOrder::getKey() );
            switch ( $keyword_order ) {
                case KeywordOrder::HIGH_WORDCOUNT_FIRST:
                    usort( $anchors, function ( $a, $b ) {
                        return Keyword::gapWordCount( $b ) - Keyword::gapWordCount( $a );
                    } );
                    break;
                case KeywordOrder::LOW_WORDCOUNT_FIRST:
                    usort( $anchors, function ( $a, $b ) {
                        return Keyword::gapWordCount( $a ) - Keyword::gapWordCount( $b );
                    } );
                    break;
            }
            foreach ( $anchors as $anchor ) {
                $anchor = Encoding::unmaskSlashes( $anchor );
                if ( !$this->isValidRegex( $anchor ) ) {
                    continue;
                }
                $pattern = str_replace( '.', '\\.', $anchor );
                $this->link_rules->addRule( $pattern, $definition->post_id, $type );
            }
        }
        return;
    }
    
    /**
     * Validates if a regex pattern is valid
     *
     * @since  1.1.0
     * @param  string $pattern The regular expression
     * @return bool
     */
    protected function isValidRegex( $pattern )
    {
        if ( @preg_match( '/' . $pattern . '/', null ) === false ) {
            return false;
        }
        return true;
    }
    
    /**
     * Writes a set of data to the linkindex
     *
     * @since 1.0.1
     *
     * @param  array  $data      The data container
     * @param  string $data_type Type of the data inside the container
     * @param  array  $fields    Field settings for the container objects
     * @param  int    &$counter  Counts the written operations
     * @return void
     */
    protected function writeToIndex(
        $data,
        $data_type,
        array $fields,
        &$counter
    )
    {
        if ( !is_array( $data ) || !count( $data ) ) {
            return;
        }
        global  $ilj_fs ;
        $multi_keyword_mode = $this->link_options['multi_keyword_mode'];
        $links_per_page = $this->link_options['links_per_page'];
        $links_per_target = $this->link_options['links_per_target'];
        $fields = wp_parse_args( $fields, [
            'id'      => '',
            'content' => '',
        ] );
        foreach ( $data as $item ) {
            $linked_urls = [];
            $linked_anchors = [];
            $post_outlinks_count = 0;
            if ( !property_exists( $item, $fields['content'] ) || !property_exists( $item, $fields['id'] ) ) {
                continue;
            }
            try {
                $content = do_shortcode( $item->{$fields['content']} );
            } catch ( \Exception $e ) {
                continue;
            }
            Replacement::mask( $content );
            while ( $this->link_rules->hasRule() ) {
                $link_rule = $this->link_rules->getRule();
                if ( !isset( $linked_urls[$link_rule->value] ) ) {
                    $linked_urls[$link_rule->value] = 0;
                }
                
                if ( !$multi_keyword_mode && ($links_per_page > 0 && $post_outlinks_count >= $links_per_page || $links_per_target > 0 && $linked_urls[$link_rule->value] >= $links_per_target) ) {
                    $this->link_rules->nextRule();
                    continue;
                }
                
                
                if ( $link_rule->value != $item->{$fields['id']} ) {
                    $has_dot = strpos( $link_rule->pattern, '.' ) !== false;
                    $pattern = sprintf( '/(?<phrase>%1$s%2$s%1$s)/ui', ( $has_dot ? '' : '\\b' ), $link_rule->pattern );
                    preg_match( $pattern, $content, $rule_match );
                    
                    if ( isset( $rule_match['phrase'] ) ) {
                        $phrase = trim( $rule_match['phrase'] );
                        
                        if ( !$multi_keyword_mode && in_array( $phrase, $linked_anchors ) ) {
                            $this->link_rules->nextRule();
                            continue;
                        }
                        
                        Linkindex::addRule(
                            $item->{$fields['id']},
                            $link_rule->value,
                            $phrase,
                            $data_type,
                            $link_rule->type
                        );
                        $counter++;
                        $post_outlinks_count++;
                        $linked_urls[$link_rule->value]++;
                        $linked_anchors[] = $phrase;
                    }
                
                }
                
                $this->link_rules->nextRule();
            }
            $this->link_rules->reset();
        }
    }
    
    /**
     * Responsible for building the index and writing possible internal links to it
     *
     * @since 1.0.1
     *
     * @return int
     */
    public function setIndices()
    {
        global  $ilj_fs ;
        $index_count = 0;
        $this->writeToIndex(
            $this->posts,
            'post',
            [
            'id'      => 'ID',
            'content' => 'post_content',
        ],
            $index_count
        );
        /**
         * Fires after the index got built.
         *
         * @since 1.0.0
         */
        do_action( self::ILJ_ACTION_AFTER_INDEX_BUILT );
        return $index_count;
    }

}