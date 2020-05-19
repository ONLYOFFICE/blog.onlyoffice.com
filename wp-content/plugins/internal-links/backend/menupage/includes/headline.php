<?php
namespace ILJ\Backend\MenuPage\Includes;

/**
 * Backend Headline
 *
 * Responsible for displaying the headline on backend pages
 *
 * @package ILJ\Backend\Menupage
 * @since   1.1.0
 */
trait Headline
{
    /**
     * Renders the headline
     *
     * @since  1.1.0
     * @param  string $page_title The title for the headline
     * @return void
     */
    private function renderHeadline($page_title)
    {
        echo '<hr class="wp-header-end" />';
        echo '<section class="row ilj-admin-headline">';
        echo '<h1>Internal Link Juicer - ' . $page_title . '</h1>';
        echo '<div class="clear"></div>';
        echo '</section>';
    }
}
