<?php

namespace FluentForm\App\Modules\Payments\Migrations;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class OrderItems
{
    /**
     * Migrate the table.
     *
     * @return void
     */
    public static function migrate()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();

        $table = $wpdb->prefix . 'fluentform_order_items';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Migration file, direct query needed
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) != $table) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange -- Migration file, schema change is the purpose
            $sql = "CREATE TABLE $table (
				id int(11) NOT NULL AUTO_INCREMENT,
				form_id int(11) NOT NULL,
				submission_id int(11) NOT NULL,
				type varchar(255) DEFAULT 'single',
				parent_holder varchar(255),
				billing_interval varchar(255),
				item_name varchar(255),
				quantity int(11) DEFAULT 1,
				item_price BIGINT UNSIGNED,
				line_total BIGINT UNSIGNED,
				created_at timestamp NULL,
				updated_at timestamp NULL,
				PRIMARY  KEY  (id)
			  ) $charsetCollate;";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        } else {
            self::maybeAlterColumns();
        }
    }


    public static function maybeAlterColumns()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'fluentform_order_items';
        // find the data types of each column
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Migration file, checking table structure, %1s is for identifier
        $results = $wpdb->get_results($wpdb->prepare("DESCRIBE %1s", $table));
        $items = [];
        foreach ($results as $result) {
            $items[$result->Field] = $result->Type;
        }

        $isBigInItemPrice = strpos($items['item_price'], 'bigint') !== false;;
        $isBigInitPrice = strpos($items['line_total'], 'bigint') !== false;;

        // if not big int convert to big int
        if (!$isBigInItemPrice) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Migration file, schema change is the purpose, %1s is for identifier
            $wpdb->query($wpdb->prepare("ALTER TABLE %1s MODIFY item_price BIGINT UNSIGNED", $table));
        }

        if (!$isBigInitPrice) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Migration file, schema change is the purpose, %1s is for identifier
            $wpdb->query($wpdb->prepare("ALTER TABLE %1s MODIFY line_total BIGINT UNSIGNED", $table));
        }
    }
}
