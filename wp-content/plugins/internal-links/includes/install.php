<?php

/**
 * @package ILJ\Includes
 */
use  ILJ\Database\Usermeta ;
use  ILJ\Database\Linkindex ;
use  ILJ\Database\Postmeta ;
use  ILJ\Backend\Environment ;
use  ILJ\Core\Options ;
/**
 * Responsible for creating the database tables
 *
 * @since  1.0.0
 * @return void
 */
function ilj_install_db()
{
    global  $wpdb ;
    $charset_collate = $wpdb->get_charset_collate();
    $query_linkindex = "CREATE TABLE " . $wpdb->prefix . Linkindex::ILJ_DATABASE_TABLE_LINKINDEX . " (\n        `link_from` BIGINT(20) NULL,\n        `link_to` BIGINT(20) NULL,\n        `type_from` VARCHAR(45) NULL,\n        `type_to` VARCHAR(45) NULL,\n        `anchor` TEXT NULL,\n        INDEX `link_from` (`link_from` ASC),\n        INDEX `type_from` (`type_from` ASC),\n        INDEX `type_to` (`type_to` ASC),\n        INDEX `link_to` (`link_to` ASC))" . $charset_collate . ";";
    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $query_linkindex );
    Environment::update( "last_version", ILJ_VERSION );
}

function ilj_uninstall_db()
{
    global  $wpdb ;
    $query_linkindex = "DROP TABLE IF EXISTS " . $wpdb->prefix . Linkindex::ILJ_DATABASE_TABLE_LINKINDEX . ";";
    $wpdb->query( $query_linkindex );
}

function ilj_remove_db_data()
{
    global  $ilj_fs ;
    $keep_settings = Options::getOption( \ILJ\Core\Options\KeepSettings::getKey() );
    if ( $keep_settings ) {
        return;
    }
    Options::removeAllOptions();
    Postmeta::removeAllLinkDefinitions();
    Usermeta::removeAllUsermeta();
}

register_activation_hook( ILJ_FILE, '\\ilj_install_db' );
register_activation_hook( ILJ_FILE, [ 'ILJ\\Core\\Options', 'setOptionsDefault' ] );
register_deactivation_hook( ILJ_FILE, '\\ilj_uninstall_db' );
register_deactivation_hook( ILJ_FILE, '\\ilj_remove_db_data' );