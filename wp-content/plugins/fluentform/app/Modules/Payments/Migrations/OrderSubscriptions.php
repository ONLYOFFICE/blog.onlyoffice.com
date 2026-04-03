<?php

namespace FluentForm\App\Modules\Payments\Migrations;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class OrderSubscriptions
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

        $table = $wpdb->prefix . 'fluentform_subscriptions';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Migration file, direct query needed
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) != $table) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange -- Migration file, schema change is the purpose
            $sql = "CREATE TABLE $table (
				id int(20) NOT NULL AUTO_INCREMENT,
				submission_id int(11),
				form_id int(11),
				payment_total int(11) DEFAULT 0,
				item_name varchar(255),
				plan_name varchar(255),
				parent_transaction_id int(11),
				billing_interval varchar (50),
				trial_days int(11),
				initial_amount int(11),
				quantity int(11) DEFAULT 1,
				recurring_amount int(11),
				bill_times int(11),
				bill_count int(11) DEFAULT 0,
				vendor_customer_id varchar(255),
				vendor_subscription_id varchar(255),
				vendor_plan_id varchar(255),
				status varchar(255) DEFAULT 'pending',
				initial_tax_label varchar(255),
				initial_tax int(11),
				recurring_tax_label varchar(255),
				recurring_tax int(11),
				element_id varchar(255),
				note text,
				original_plan text,
				vendor_response longtext,
				expiration_at timestamp NULL,
				created_at timestamp NULL,
				updated_at timestamp NULL,
				PRIMARY  KEY  (id)
			  ) $charsetCollate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            dbDelta($sql);
        }
    }
}
