<?php

namespace FluentForm\Database\Migrations;

class FormMeta
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

        $table = $wpdb->prefix . 'fluentform_form_meta';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Migration file, direct query needed
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) != $table) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange -- Migration file, schema change is the purpose
            $sql = "CREATE TABLE $table (
			  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			  `form_id` INT UNSIGNED NULL,
			  `meta_key` VARCHAR(255) NOT NULL,
			  `value` LONGTEXT NULL,
			  PRIMARY KEY (`id`),
			  KEY `form_id_meta_key` (`form_id`, `meta_key`(191)),
			  KEY `meta_key` (`meta_key`(191))) $charsetCollate;";

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

        $table = $wpdb->prefix . 'fluentform_form_meta';

        // Check if indexes already exist
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Migration file, checking indexes, %1s is for identifier
        $indexes = $wpdb->get_results($wpdb->prepare("SHOW INDEX FROM %1s", $table), ARRAY_A);

        $existingIndexes = [];
        foreach ($indexes as $index) {
            $existingIndexes[] = $index['Key_name'];
        }

        // Add composite index if it doesn't exist
        if (!in_array('form_id_meta_key', $existingIndexes)) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Migration file, adding composite index for performance, %1s is for identifier
            $wpdb->query($wpdb->prepare("ALTER TABLE %1s ADD KEY `form_id_meta_key` (`form_id`, `meta_key`(191))", $table));
        }

        // Add meta_key index if it doesn't exist (needed for queries that filter by meta_key alone)
        if (!in_array('meta_key', $existingIndexes)) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Migration file, adding index for performance, %1s is for identifier
            $wpdb->query($wpdb->prepare("ALTER TABLE %1s ADD KEY `meta_key` (`meta_key`(191))", $table));
        }
    }
}
