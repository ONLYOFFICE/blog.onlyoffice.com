<?php
namespace ILJ\Backend\MenuPage;

use ILJ\Helper\Help;
use ILJ\Helper\Statistic;
use ILJ\Backend\AdminMenu;
use ILJ\Helper\IndexAsset;
use ILJ\Backend\Environment;
use ILJ\Backend\MenuPage\Tour;
use ILJ\Backend\MenuPage\AbstractMenuPage;
use ILJ\Backend\MenuPage\Includes\Sidebar;
use ILJ\Backend\MenuPage\Includes\Headline;

/**
 * The dashboard page
 *
 * Responsible for displaying the dashboard
 *
 * @package ILJ\Backend\Menupage
 * @since   1.0.0
 */
class Dashboard extends AbstractMenuPage
{
    use Headline;
    use Sidebar;

    public function __construct()
    {
        $this->page_title = __('Dashboard', 'ILJ');
    }

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->addSubMenuPage(true);
        $this->addAssets(
            [
                'tipso'         => ILJ_URL . 'admin/js/tipso.js',
                'ilj_statistic' => ILJ_URL . 'admin/js/ilj_statistic.js',
                'ilj_promo'     => ILJ_URL . 'admin/js/ilj_promo.js'
            ],
            [
                'tipso'         => ILJ_URL . 'admin/css/tipso.css',
                'ilj_ui'        => ILJ_URL . 'admin/css/ilj_ui.css',
                'ilj_grid'      => ILJ_URL . 'admin/css/ilj_grid.css',
                'ilj_statistic' => ILJ_URL . 'admin/css/ilj_statistic.css'
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        echo '<div class="wrap ilj-menu-dashboard">';
        $this->renderHeadline(__('Dashboard', 'ILJ'));
        echo '<div class="row">';
        echo '<div class="col-9">';
        echo '  <div class="postbox">';
        echo '      <h2>' . __('Plugin related', 'ILJ') . '</h2>';
        echo '      <div class="inside">';
        echo '          <p><strong>' . __('Installed version', 'ILJ') . ':</strong> ' . $this->getVersion() . '</p>';
        $this->renderHelpRessources();
        echo '      </div>';
        echo '  </div>';
        echo '  <div class="postbox">';
        echo '      <h2>' . __('Linkindex info', 'ILJ') . '</h2>';
        echo '      <div class="inside">';
        $this->renderIndexMeta();
        echo '      </div>';
        echo '  </div>';
        echo '  <div class="postbox ilj-statistic-wrap">';
        $this->renderStatistics();
        echo '  </div>';
        echo '</div>';
        echo '<div class="col-3">';
        $this->renderSidebar();
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Renders the help links
     *
     * @since  1.1.0
     * @return void
     */
    protected function renderHelpRessources()
    {
        $url_manual        = Help::getLinkUrl(null, null, 'docs', 'dashboard');
        $url_tour          = add_query_arg(['page' => AdminMenu::ILJ_MENUPAGE_SLUG . '-' . Tour::ILJ_MENUPAGE_TOUR_SLUG], admin_url('admin.php'));
        $url_plugins_forum = 'https://wordpress.org/support/plugin/internal-links/';

        echo '<ul class="ilj-ressources divide">';
        echo '<li>';
        echo '<span class="dashicons dashicons-book-alt"></span>';
        echo '<a href="' . $url_manual . '" target="_blank" rel="noopener"><strong>' . __('Docs & How To', 'ILJ') . '</strong><br>' . __('Learn how to use the plugin', 'ILJ') . '</a>';
        echo '</li>';
        echo '<li>';
        echo '<span class="dashicons dashicons-welcome-learn-more"></span>';
        echo '<a href="' . $url_tour . '"><strong>' . __('Interactive Tour', 'ILJ') . '</strong><br>' . __('A quick guided tutorial', 'ILJ') . '</a>';
        echo '</li>';
        echo '<li>';
        echo '<span class="dashicons dashicons-testimonial"></span>';
        echo '<a href="' . $url_plugins_forum . '" target="_blank" rel="noopener"><strong>' . __('Request support', 'ILJ') . '</strong><br>' . __('Get help through our forum', 'ILJ') . '</a>';
        echo '</li>';
        echo '</ul>';
    }

    /**
     * Renders the statistic section
     *
     * @since  1.0.0
     * @return void
     */
    protected function renderStatistics()
    {
        $top_inlinks = Statistic::getAggregatedCount(
            ["type" => "link_to"]
        );
        $top_outlinks = Statistic::getAggregatedCount(
            ["type" => "link_from"]
        );
        $top_anchors = Statistic::getAggregatedCount(
            ["type" => "anchor"]
        );

        echo '<h2>' . __('Statistics', 'ILJ') . '</h2>';
        echo '<div class="inside">';

        if (empty($top_anchors)) {
            echo '<p>' . __('There are no statistics to display', 'ILJ') . '.</p>';
            echo '</div>';
            return;
        }

        echo '<div class="row">';
        echo '<div class="col-4 no-top-padding">';
        echo '<h3>' . __('Top 10 incoming links', 'ILJ') . '</h3>';
        $this->renderLinkList($top_inlinks, 'link_to');
        echo '</div>';
        echo '<div class="col-4 no-top-padding">';
        echo '<h3>' . __('Top 10 outgoing links', 'ILJ') . '</h3>';
        $this->renderLinkList($top_outlinks, 'link_from');
        echo '</div>';
        echo '<div class="col-4 no-top-padding"> ';
        echo '<h3>' . __('Top 10 anchor texts', 'ILJ') . '</h3>';
        $this->renderKeywordList($top_anchors, 'anchor');
        echo '</div>';
        echo '<div class="row"></div>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Renders all index related meta data
     *
     * @since  1.0.0
     * @return void
     */
    private function renderIndexMeta()
    {
        $linkindex_info = Environment::get('linkindex');

        if ($linkindex_info['last_update']['entries'] == "") {
            $help_url = Help::getLinkUrl('editor/', null, 'editor onboarding', 'dashboard');
            echo '<p>' . __('Index has no entries yet', 'ILJ') . '.</p>';
            echo '<p class="divide"><span class="dashicons dashicons-arrow-right-alt"></span> <strong>' . __('Start to set some keywords to your posts', 'ILJ') . ' - <a href="' . $help_url . '" target="_blank" rel="noopener">' . __('learn how it works', 'ILJ') . '</a></strong></p>';
            return;
        }

        $hours   = (int) get_option('gmt_offset');
        $minutes = ($hours - floor($hours)) * 60;
        $date    = $linkindex_info['last_update']['date']->setTimezone(new \DateTimeZone(sprintf('%+03d:%02d', $hours, $minutes)));

        echo '<p><strong>' . __('Amount of links in the index', 'ILJ') . '</strong>: ' . $linkindex_info['last_update']['entries'] . '</p>';
        echo '<p><strong>' . __('Amount of configured keywords', 'ILJ') . '</strong>: ' . Statistic::getConfiguredKeywordsCount() . '</p>';
        echo '<p><strong>' . __('Last built', 'ILJ') . '</strong>: ' . $date->format(get_option('date_format')) . ' ' . __('at', 'ILJ') . ' ' . $date->format(get_option('time_format')) . '</p>';
        echo '<p><strong>' . __('Duration for construction', 'ILJ') . '</strong>: ' . $linkindex_info['last_update']['duration'] . ' ' . __('seconds', 'ILJ');
    }

    /**
     * Renders a list of keywords
     *
     * @since  1.0.0
     * @param  array  $data         Bag of objects
     * @param  string $keyword_node The name of the keyword property in single object
     * @return void
     */
    private function renderKeywordList(array $data, $keyword_node)
    {
        $render_header = [__('Keyword', 'ILJ'), __('Count', 'ILJ')];
        $render_data   = [];

        if (!isset($data[0]) || !property_exists($data[0], $keyword_node)) {
            return;
        }

        foreach ($data as $row) {
            $keyword       = $row->{$keyword_node};
            $render_data[] = [$keyword, $row->elements];
        }

        $this->renderList($render_header, $render_data);
    }

    /**
     * Renders a list of post ids as post links
     *
     * @since  1.0.0
     * @param  array $data          Bag of objects
     * @param  int   $asset_id_node The name of the post id property in single object
     * @return void
     */
    private function renderLinkList(array $data, $asset_id_node)
    {

        $render_header = [__('Page', 'ILJ'), __('Count', 'ILJ'), __('Action', 'ILJ')];
        $render_data   = [];

        if (!isset($data[0]) || !property_exists($data[0], $asset_id_node)) {
            return;
        }

        foreach ($data as $row) {
            $asset_id = (int) $row->{$asset_id_node};

            if ($asset_id < 1 || $row->type != 'post') {
                continue;
            }

            $asset_data = IndexAsset::getMeta($asset_id, 'post');

            $edit_link     = sprintf('<a href="%s" title="' . __('Edit', 'ILJ') . '" class="tip">%s</a>', $asset_data->url_edit, '<span class="dashicons dashicons-edit"></span>');
            $post_link     = sprintf('<a href="%s" title="' . __('Open', 'ILJ') . '" class="tip" target="_blank" rel="noopener">%s</a>', $asset_data->url, '<span class="dashicons dashicons-external"></span>');
            $render_data[] = [$asset_data->title, $row->elements, $post_link . $edit_link];
        }

        $this->renderList($render_header, $render_data);
    }

    /**
     * Generic method for rendering a list
     *
     * @since  1.0.0
     * @param  array $header
     * @param  array $data
     * @return void
     */
    private function renderList(array $header, array $data)
    {
        echo '<table class="wp-list-table widefat striped ilj-statistic-table">';
        echo '<thead>';
        echo '<tr>';

        foreach ($header as $title) {
            echo '<th scope="col">' . $title . '</th>';
        }

        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($data as $row) {
            echo '<tr>';

            foreach ($row as $col) {
                echo '<td>' . $col . '</td>';
            }

            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    }

    /**
     * Returns the version including the subscription type
     *
     * @since  1.1.0
     * @return string
     */
    protected function getVersion()
    {
        return ILJ_VERSION . ' <span class="badge basic">Basic</span>';
    }
}
