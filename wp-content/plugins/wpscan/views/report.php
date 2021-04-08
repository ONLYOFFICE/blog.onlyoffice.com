<?php
// File Security Check
defined( 'ABSPATH' ) or die( "No script kiddies please!" );
?>

<div class="wrap">

  <h1><?php echo file_get_contents(self::$plugin_url . 'assets/svg/logo.svg'); ?></h1>

  <?php if ( get_transient( self::WPSCAN_TRANSIENT_CRON ) ) : ?>
  <div class="notice notice-info">
    <p><?php _e( 'The task is running in the background. This page will be reloaded once finished to display the results.', 'wpscan' ) ?></p>
  </div>
  <?php endif; ?>

  <?php settings_errors(); ?>

  <div id="poststuff">

    <div id="post-body" class="metabox-holder columns-2">

      <div id="postbox-body" class="postbox-container">

        <div class="wpscan-report-section">

          <h3><?php _e( 'WordPress', 'wpscan' ) ?></h3>

          <table class="wp-list-table widefat striped plugins">
          <thead>
            <tr>
              <td scope="col" class="manage-column check-column">&nbsp;</td>
              <th scope="col" class="manage-column column-name column-primary" width="250"><?php _e( 'Name', 'wpscan' ) ?></th>
              <th scope="col" class="manage-column column-description"><?php _e( 'Vulnerabilities', 'wpscan' ) ?></th>
            </tr>
          </thead>
          <tbody id="report-wordpress">
            <tr>
              <th scope="row" class="check-column" style="text-align: center"><?php echo self::get_status( 'wordpress', get_bloginfo( 'version' ) ) ?></span></th>
              <td class="plugin-title column-primary">
                <strong>WordPress</strong> 
                <span class='item-version'><?php echo sprintf( __( 'Version <span>%s</span>', 'wpscan' ), get_bloginfo( 'version' ) ) ?></span>
              </td>
              <td class="vulnerabilities"><?php self::list_vulnerabilities( 'wordpress', get_bloginfo( 'version' ) ) ?></td>
            </tr>
          </tbody>
          </table>        
        </div>

        <div class="wpscan-report-section">

          <h3><?php _e( 'Plugins', 'wpscan' ) ?></h3>

          <table class="wp-list-table widefat striped plugins">
            <thead>
              <tr>
                <td scope="col" class="manage-column check-column">&nbsp;</td>
                <th scope="col" class="manage-column column-name column-primary" width="250"><?php _e( 'Name', 'wpscan' ) ?></th>
                <th scope="col" class="manage-column column-description"><?php _e( 'Vulnerabilities', 'wpscan' ) ?></th>
              </tr>
            </thead>
            <tbody id="report-plugins">
              <?php 
                foreach ( get_plugins() as $name => $details ) {
                  $name = self::sanitize_plugin_name( $name, $details );
                  $is_closed = self::is_item_closed('plugins', $name);
              ?>
              <tr>
                <th scope="row" class="check-column" style="text-align: center"><?php echo self::get_status( 'plugins', $name ) ?></span></th>
                
                <td class="plugin-title column-primary">
                  <strong><?php echo esc_html($details['Name']) ?></strong>
                  <span class='item-version'><?php echo sprintf( __( 'Version <span>%s</span>', 'wpscan' ), esc_html($details['Version']) ) ?></span>
                  <?php if ($is_closed) { ?>
                    <span class='item-closed'>Plugin Closed</span>
                  <?php } ?>
                </td>
                <td class="vulnerabilities"><?php self::list_vulnerabilities( 'plugins', $name ) ?></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>

        </div>
  
        <div class="wpscan-report-section">

          <h3><?php _e( 'Themes', 'wpscan' ) ?></h3>

          <table class="wp-list-table widefat striped plugins">
            <thead>
              <tr>
                <td scope="col" class="manage-column check-column">&nbsp;</td>
                <th scope="col" class="manage-column column-name column-primary" width="250"><?php _e( 'Name', 'wpscan' ) ?></th>
                <th scope="col" class="manage-column column-description"><?php _e( 'Vulnerabilities', 'wpscan' ) ?></th>
              </tr>
            </thead>
            <tbody id="report-themes">
              <?php foreach ( wp_get_themes() as $name => $details ):
                $name = self::sanitize_theme_name( $name, $details );
                $is_closed = self::is_item_closed('themes', $name);
              ?>
              <tr>
                <th scope="row" class="check-column" style="text-align: center"><?php echo self::get_status( 'themes', $name ) ?></span></th>
                <td class="plugin-title column-primary">
                  <strong><?php echo esc_html($details['Name']) ?></strong>
                  <?php if ($is_closed) { ?>
                    <span class='item-closed'>Plugin Closed</span>
                  <?php } ?>
                  <span class='item-version'><?php echo sprintf( __( 'Version <span>%s</span>', 'wpscan' ), esc_html($details['Version']) ) ?></span>
                </td>
                <td class="vulnerabilities"><?php self::list_vulnerabilities( 'themes', $name ) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

        </div>

        <?php if ( get_option( self::OPT_API_TOKEN ) ) { ?>
          <a href="#" class='button button-secondary download-report'><?php _e( 'Download as PDF', 'wpscan' ) ?></a>
        <?php } ?>
      </div>

      <div id="postbox-container-1" class="postbox-container">

        <?php do_meta_boxes( 'wpscan', 'side', null ); ?>

      </div>

    </div>

    <br class="clear">

  </div>

</div>