<?php

namespace FluentForm\Database\Migrations;

class Submissions
{
    /**
     * Migrate the table.
     *
     * @return void
     */
    public static function migrate($force = false)
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();

        $table = $wpdb->prefix . 'fluentform_submissions';

        $sql = "CREATE TABLE $table (
			  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			  `form_id` INT UNSIGNED NULL,
			  `serial_number` INT UNSIGNED NULL,
			  `response` LONGTEXT NULL,
			  `source_url` VARCHAR(255) NULL,
			  `user_id` INT UNSIGNED NULL,
			  `status` VARCHAR(45) NULL DEFAULT 'unread' COMMENT 'possible values: read, unread, trashed',
			  `is_favourite` TINYINT(1) NOT NULL DEFAULT 0,
			  `browser` VARCHAR(45) NULL,
			  `device` VARCHAR(45) NULL,
			  `ip` VARCHAR(45) NULL,
			  `city` VARCHAR(45) NULL,
			  `country` VARCHAR(45) NULL,
			  `payment_status` VARCHAR(45) NULL,
			  `payment_method` VARCHAR(45) NULL,
			  `payment_type` VARCHAR(45) NULL,
			  `currency` VARCHAR(45) NULL,
			  `payment_total` FLOAT NULL,
			  `total_paid` FLOAT NULL,
			  `created_at` TIMESTAMP NULL,
			  `updated_at` TIMESTAMP NULL,
			  PRIMARY KEY (`id`),
			  KEY `form_id_status` (`form_id`, `status`),
			  KEY `form_id_created_at` (`form_id`, `created_at`),
			  KEY `user_id` (`user_id`),
			  KEY `serial_number` (`serial_number`)) $charsetCollate;";

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Migration file, direct query needed
        if ($force || $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) != $table) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        } else {
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

        $table = $wpdb->prefix . 'fluentform_submissions';

        // Check if indexes already exist
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Migration file, checking indexes, %1s is for identifier
        $indexes = $wpdb->get_results($wpdb->prepare("SHOW INDEX FROM %1s", $table), ARRAY_A);

        $existingIndexes = [];
        foreach ($indexes as $index) {
            $existingIndexes[] = $index['Key_name'];
        }

        // Add composite index form_id + status if it doesn't exist
        if (!in_array('form_id_status', $existingIndexes)) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Migration file, adding composite index for performance, %1s is for identifier
            $wpdb->query($wpdb->prepare("ALTER TABLE %1s ADD KEY `form_id_status` (`form_id`, `status`)", $table));
        }

        // Add composite index form_id + created_at if it doesn't exist
        if (!in_array('form_id_created_at', $existingIndexes)) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Migration file, adding composite index for performance, %1s is for identifier
            $wpdb->query($wpdb->prepare("ALTER TABLE %1s ADD KEY `form_id_created_at` (`form_id`, `created_at`)", $table));
        }

        // Add user_id index if it doesn't exist
        if (!in_array('user_id', $existingIndexes)) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Migration file, adding index for performance, %1s is for identifier
            $wpdb->query($wpdb->prepare("ALTER TABLE %1s ADD KEY `user_id` (`user_id`)", $table));
        }

        // Add serial_number index if it doesn't exist
        if (!in_array('serial_number', $existingIndexes)) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Migration file, adding index for performance, %1s is for identifier
            $wpdb->query($wpdb->prepare("ALTER TABLE %1s ADD KEY `serial_number` (`serial_number`)", $table));
        }
    }
}
