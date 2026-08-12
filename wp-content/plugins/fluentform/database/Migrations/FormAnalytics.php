<?php

namespace FluentForm\Database\Migrations;

class FormAnalytics
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

        $table = $wpdb->prefix . 'fluentform_form_analytics';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Migration file, direct query needed
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) != $table) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange -- Migration file, schema change is the purpose
            $sql = "CREATE TABLE $table (
			  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			  `form_id` INT UNSIGNED NULL,
			  `user_id` INT UNSIGNED NULL,
			  `source_url` TEXT NOT NULL,
			  `platform` CHAR(30) NULL,
			  `browser` CHAR(30) NULL,
			  `city` VARCHAR (100) NULL,
			  `country` VARCHAR (100) NULL,
			  `ip` CHAR(15) NULL,
			  `count` INT DEFAULT 1,
			  `created_at` TIMESTAMP NULL,
			  PRIMARY KEY (`id`),
			  KEY `form_id_ip` (`form_id`, `ip`),
			  KEY `created_at` (`created_at`)) $charsetCollate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';

            dbDelta($sql);
        } else {
            // increase column type of source_url from varchar to text - for already installed sites
            $column_name = 'source_url';
            $dataType = $wpdb->get_col_length($table, $column_name);
            $type = $dataType['type'] ?? false;
            if ($type == 'char') {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Migration file, table/column names cannot be prepared
                $sql = "ALTER TABLE {$table} MODIFY {$column_name} TEXT NOT NULL";
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Migration file, schema change is the purpose, table/column names are safe
                $wpdb->query($sql);
            }

            // Add indexes to existing tables
            self::maybeAddIndexes();
        }
    }

    /**
     * Add indexes to existing tables for better query performance.
     *
     * @return void
     */
    private static function maybeAddIndexes()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'fluentform_form_analytics';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Migration file, checking indexes, %1s is for identifier
        $indexes = $wpdb->get_results($wpdb->prepare("SHOW INDEX FROM %1s", $table), ARRAY_A);

        $existingIndexes = [];
        foreach ($indexes as $index) {
            $existingIndexes[] = $index['Key_name'];
        }

        // Add composite index form_id + ip if it doesn't exist
        if (!in_array('form_id_ip', $existingIndexes)) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Migration file, adding composite index for performance, %1s is for identifier
            $wpdb->query($wpdb->prepare("ALTER TABLE %1s ADD KEY `form_id_ip` (`form_id`, `ip`)", $table));
        }

        // Add created_at index if it doesn't exist
        if (!in_array('created_at', $existingIndexes)) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Migration file, adding index for performance, %1s is for identifier
            $wpdb->query($wpdb->prepare("ALTER TABLE %1s ADD KEY `created_at` (`created_at`)", $table));
        }
    }
}
