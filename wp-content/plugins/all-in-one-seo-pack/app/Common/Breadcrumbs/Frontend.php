<?php
namespace AIOSEO\Plugin\Common\Breadcrumbs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Frontend.
 *
 * @since 4.1.1
 */
class Frontend {
	/**
	 * A local 'cached' crumb array.
	 *
	 * @since 4.1.1
	 *
	 * @var array
	 */
	public $breadcrumbs = [];

	/**
	 * Gets the current page's breadcrumbs.
	 *
	 * @since 4.1.1
	 *
	 * @return array
	 */
	public function getBreadcrumbs() {
		if ( ! empty( $this->breadcrumbs ) ) {
			return $this->breadcrumbs;
		}

		$type      = '';
		$reference = get_queried_object();

		if ( is_single() ) {
			$type = 'single';
		}

		if ( is_singular( 'post' ) ) {
			$type = 'post';
		}

		if ( is_page() && ! is_front_page() ) {
			$type = 'page';
		}

		if ( is_category() || is_tag() ) {
			$type = 'category';
		}

		if ( is_tax() ) {
			$type = 'taxonomy';
		}

		if ( is_post_type_archive() ) {
			$type = 'postTypeArchive';
		}

		if ( is_author() ) {
			$type = 'author';
		}

		if ( is_home() ) {
			$type = 'blog';
		}

		if ( is_date() ) {
			$type = 'date';
			$reference = [
				'year'  => get_query_var( 'year' ),
				'month' => get_query_var( 'monthnum' ),
				'day'   => get_query_var( 'day' )
			];
		}

		if ( is_search() ) {
			$type      = 'search';
			$reference = get_search_query();
		}

		if ( is_404() ) {
			$type = 'notFound';
		}

		$paged = false;
		if ( is_paged() || ( is_singular() && 1 < get_query_var( 'page' ) ) ) {
			global $wp;
			$paged = [
				'paged' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' ),
				'link'  => home_url( $wp->request )
			];
		}

		return aioseo()->breadcrumbs->buildBreadcrumbs( $type, $reference, $paged );
	}

	/**
	 * Helper function to display breadcrumbs for a specific page.
	 *
	 * @since 4.1.1
	 *
	 * @param  bool        $echo      Print out the breadcrumb.
	 * @param  string      $type      The type for the breadcrumb.
	 * @param  string      $reference A reference to be used for rendering the breadcrumb.
	 * @return string|void            A html breadcrumb.
	 */
	public function sideDisplay( $echo = true, $type = '', $reference = '' ) {
		$this->breadcrumbs = aioseo()->breadcrumbs->buildBreadcrumbs( $type, $reference );

		return $this->display( $echo );
	}

	/**
	 * Display the breadcrumb in the frontend.
	 *
	 * @since 4.1.1
	 *
	 * @param  bool        $echo Print out the breadcrumb.
	 * @return string|void       A html breadcrumb.
	 */
	public function display( $echo = true ) {
		if ( ! is_object( get_queried_object() ) || ! aioseo()->options->breadcrumbs->enable ) {
			return;
		}

		$breadcrumbs = $this->getBreadcrumbs();
		if ( empty( $breadcrumbs ) ) {
			return;
		}

		$breadcrumbsCount = count( $breadcrumbs );

		$display = '<div class="aioseo-breadcrumbs">';
		foreach ( $breadcrumbs as $breadcrumb ) {
			-- $breadcrumbsCount;

			$breadcrumbDisplay = $this->breadcrumbToDisplay( $breadcrumb );

			// Strip link from Last crumb.
			if (
				0 === $breadcrumbsCount &&
				aioseo()->options->breadcrumbs->showCurrentItem &&
				! aioseo()->options->breadcrumbs->linkCurrentItem &&
				'default' === $breadcrumbDisplay['templateType']
			) {
				$breadcrumbDisplay['template'] = $this->stripLink( $breadcrumbDisplay['template'] );
			}

			$display .= $breadcrumbDisplay['template'];

			if ( 0 < $breadcrumbsCount ) {
				$display .= $this->getSeparator();
			}
		}
		$display .= '</div>';

		$display = wp_kses_post( $display );

		if ( $echo ) {
			echo $display; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $display;
	}

	/**
	 * Turns a crumb array into a rendered html crumb.
	 *
	 * @since 4.1.1
	 *
	 * @param  array       $item The crumb array.
	 * @return string|void       The crumb html.
	 */
	protected function breadcrumbToDisplay( $item ) {
		$templateItem = $this->getCrumbTemplate( $item );

		if ( empty( $templateItem['template'] ) ) {
			return;
		}

		// Do tags.
		$templateItem['template'] = aioseo()->breadcrumbs->tags->replaceTags( $templateItem['template'], $item );

		// Restore html.
		$templateItem['template'] = aioseo()->helpers->decodeHtmlEntities( $templateItem['template'] );

		// Remove html link if it comes back from the template but we passed no links to it.
		if ( empty( $item['link'] ) ) {
			$templateItem['template'] = $this->stripLink( $templateItem['template'] );
		}

		// Allow shortcodes to run in the final html.
		$templateItem['template'] = do_shortcode( $templateItem['template'] );

		// Final security cleaning.
		$templateItem['template'] = wp_kses_post( $templateItem['template'] );

		return $templateItem;
	}

	/**
	 * Helper function to get a crumb's template.
	 *
	 * @since 4.1.1
	 *
	 * @param  array $crumb The crumb array.
	 * @return string       The html template.
	 */
	protected function getTemplate( $crumb ) {
		return $this->getDefaultTemplate( $crumb );
	}

	/**
	 * Helper function to get a crumb's template.
	 *
	 * @since 4.1.1
	 *
	 * @param  array $crumb The crumb array.
	 * @return array        The template type and html.
	 */
	protected function getCrumbTemplate( $crumb ) {
		return [
			'templateType' => 'default',
			'template'     => $this->getTemplate( $crumb )
		];
	}

	/**
	 * Default html template.
	 *
	 * @since 4.1.1
	 *
	 * @param  string $type      The crumb's type.
	 * @param  mixed  $reference The crumb's reference.
	 * @return string            The default crumb template.
	 */
	public function getDefaultTemplate( $type = '', $reference = '' ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return <<<TEMPLATE
<span class="aioseo-breadcrumb">
	<a href="#breadcrumb_link" title="#breadcrumb_label">#breadcrumb_label</a>
</span>
TEMPLATE;
	}

	/**
	 * Helper function to strip a html link from the crumb.
	 *
	 * @since 4.1.1
	 *
	 * @param  string $html The crumb's html.
	 * @return string       A crumb html without links.
	 */
	public function stripLink( $html ) {
		return preg_replace( '/<a\s.*?>|<\/a>/is', '', $html );
	}

	/**
	 * Get the breadcrumb configured separator.
	 *
	 * @since 4.1.1
	 *
	 * @return string The separator html.
	 */
	public function getSeparator() {
		return apply_filters( 'aioseo_breadcrumbs_separator', '<span class="aioseo-breadcrumb-separator">' . esc_html( aioseo()->options->breadcrumbs->separator ) . '</span>' );
	}
}