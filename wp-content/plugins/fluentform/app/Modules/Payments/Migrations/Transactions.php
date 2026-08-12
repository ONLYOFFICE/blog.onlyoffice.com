<?php

namespace FluentForm\App\Modules\Payments\Migrations;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Transactions
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

        $table = $wpdb->prefix . 'fluentform_transactions';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Migration file, direct query needed
        if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) != $table) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange -- Migration file, schema change is the purpose
            $sql = "CREATE TABLE $table (
			    id int(11) NOT NULL AUTO_INCREMENT,
			    transaction_hash varchar(255) NULL,
			    payer_name varchar(255) NULL,
			    payer_email varchar(255) NULL,
			    billing_address varchar(255) NULL,
			    shipping_address varchar(255) NULL,
				form_id int(11) NOT NULL,
				user_id int(11) DEFAULT NULL,
				submission_id int(11) NULL,
				subscription_id int(11) NULL,
				transaction_type varchar(255) DEFAULT 'onetime',
				payment_method varchar(255),
				card_last_4 int(4),
				card_brand varchar(255),
				charge_id varchar(255),
				payment_total BIGINT UNSIGNED DEFAULT 1,
				status varchar(255),
				currency varchar(255),
				payment_mode varchar(255),
				payment_note longtext,
				created_at timestamp NULL,
				updated_at timestamp NULL,
				PRIMARY  KEY  (id),
				KEY  ff_txn_created_at  (created_at),
				KEY  ff_txn_form_created  (form_id, created_at)
			  ) $charsetCollate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';

            dbDelta($sql);
        } else {
            self::maybeAlterColumns();
        }

        self::maybeAddIndexes();
    }

    public static function maybeAddIndexes()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'fluentform_transactions';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Migration file, reading index metadata, %1s is for identifier
        $indexes = $wpdb->get_results($wpdb->prepare('SHOW INDEX FROM %1s', $table));
        $existing = [];
        if (is_array($indexes)) {
            foreach ($indexes as $index) {
                $existing[$index->Key_name] = true;
            }
        }

        // Reports and the MCP payment/trend tools filter transactions by created_at
        // (cross-form) and by (form_id, created_at) (form-scoped); neither had an
        // index, so every summary scanned the whole table. Additive + idempotent.
        if (!isset($existing['ff_txn_created_at'])) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Migration file, schema change is the purpose, %1s is for identifier
            $wpdb->query($wpdb->prepare('ALTER TABLE %1s ADD INDEX ff_txn_created_at (created_at)', $table));
        }

        if (!isset($existing['ff_txn_form_created'])) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Migration file, schema change is the purpose, %1s is for identifier
            $wpdb->query($wpdb->prepare('ALTER TABLE %1s ADD INDEX ff_txn_form_created (form_id, created_at)', $table));
        }
    }

    public static function maybeAlterColumns()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'fluentform_transactions';

        // find the data types of each column
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Migration file, checking table structure, %1s is for identifier
        $results = $wpdb->get_results($wpdb->prepare('DESCRIBE %1s', $table));
        $items = [];
        foreach ($results as $result) {
            $items[$result->Field] = $result->Type;
        }

        $paymentTotalMigrated = strpos($items['payment_total'], 'bigint') !== false;
        if (!$paymentTotalMigrated) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Migration file, schema change is the purpose, %1s is for identifier
            $wpdb->query($wpdb->prepare('ALTER TABLE %1s MODIFY payment_total BIGINT UNSIGNED DEFAULT 1', $table));
        }
    }
}
