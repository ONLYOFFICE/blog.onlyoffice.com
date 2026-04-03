<?php

namespace FluentForm\App\Modules;

defined('ABSPATH') or die;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Models\Submission;

class DashboardWidgetModule
{
    /**
     * Constructor - automatically registers cache invalidation hooks
     */
    public function __construct()
    {
        $app = \FluentForm\Framework\Foundation\App::getInstance();

        // Clear dashboard widget cache when a new submission is created or status changes
        $app->addAction('fluentform/submission_inserted', function () {
            delete_transient('fluentform_dashboard_stats_v1');
        });

        $app->addAction('fluentform/before_submission_status_change', function () {
            delete_transient('fluentform_dashboard_stats_v1');
        });
    }

    public function showStat()
    {
        // Check cache first to avoid expensive query on every dashboard load
        $cacheKey = 'fluentform_dashboard_stats_v1';
        $stats = get_transient($cacheKey);

        if (false === $stats) {
            global $wpdb;

            $stats = Submission::select([
                'fluentform_forms.title',
                'fluentform_submissions.form_id',
                wpFluent()->raw('COUNT(' . $wpdb->prefix . 'fluentform_submissions.id) as total'),
                wpFluent()->raw('MAX(' . $wpdb->prefix . 'fluentform_submissions.id) as max_id'),
                wpFluent()->raw("SUM(CASE WHEN {$wpdb->prefix}fluentform_submissions.status = 'unread' THEN 1 ELSE 0 END) as unread_count"),
            ])
                   ->join('fluentform_forms', 'fluentform_forms.id', '=', 'fluentform_submissions.form_id')
                   ->groupBy('fluentform_submissions.form_id')
                   ->orderBy('max_id', 'DESC')
                   ->limit(10)
                   ->get();

            // Cache for 5 minutes to reduce database load
            set_transient($cacheKey, $stats, 10 * MINUTE_IN_SECONDS);
        }

        if (!$stats || count($stats) === 0) {
            echo 'You can see your submission stats here';
            return;
        }

        $this->printStats($stats);
    }


    private function printStats($stats)
    {
        ?>
        <ul class="ff_dashboard_stats">
            <?php foreach ($stats as $stat): ?>
            <li>
                <a
                    href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms&route=entries&form_id=' . $stat->form_id)); ?>">
                    <?php echo esc_html($stat->title); ?>
                    <span class="ff_total"><?php echo esc_attr($stat->unread_count); ?>/<?php echo esc_attr($stat->total); ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php if (!defined('FLUENTCRM') && !defined('FLUENTFORMPRO')) : ?>
        <div class="ff_recommended_plugin">
            Recommended Plugin: <b>FluentCRM - Email Marketing Automation For WordPress</b> <br />
            <a
                href="<?php echo esc_url($this->getInstallUrl('fluent-crm')); ?>">Install</a>
            | <a target="_blank" rel="noopener" href="https://wordpress.org/plugins/fluent-crm/">Learn More</a>
        </div>
        <?php endif; ?>
        <style>
            ul.ff_dashboard_stats {
                margin: 0;
                padding: 0;
                list-style: none;
            }

            ul.ff_dashboard_stats li {
                padding: 8px 12px;
                border-bottom: 1px solid #eeeeee;
                margin: 0 -12px;
                cursor: pointer;
            }

            ul.ff_dashboard_stats li:hover {
                background: #fafafa;
                border-bottom: 1px solid #eeeeee;
            }

            ul.ff_dashboard_stats li:hover a {
                color: black;
            }

            ul.ff_dashboard_stats li:nth-child(2n+2) {
                background: #f9f9f9;
            }

            ul.ff_dashboard_stats li span.ff_total {
                float: right;
            }

            ul.ff_dashboard_stats li a {
                display: block;
                color: #0073aa;
                font-weight: 500;
                font-size: 105%;
            }

            .ff_recommended_plugin {
                padding: 15px 0px 0px;
            }
            
            .ff_recommended_plugin a {
                font-weight: bold;
                font-size: 110%;
            }
        </style>
        <?php
    }

    private function getInstallUrl($plugin)
    {
        return wp_nonce_url(
            self_admin_url('update.php?action=install-plugin&plugin=' . $plugin),
            'install-plugin_' . $plugin
        );
    }
}
