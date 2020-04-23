<div id="yop-main-area" class="bootstrap-yop wrap add-edit-poll">
    <h1>
        <?php _e( 'Poll Settings', 'yop-poll' );?>
    </h1>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content" style="position:relative">
                <form id="yop-poll-settings-form" action="">
                    <input type="hidden" id="_token" value="<?php echo wp_create_nonce( 'yop-poll-update-settings' ); ?>" name="_token">
                    <div class="meta-box-sortables ui-sortable">
                        <div id="titlediv">
                            <div class="inside"></div>
                        </div>
                        <div class="container-fluid yop-poll-hook">
                            <div class="tabs-container">
                                <!-- Nav tabs -->
                                <ul class="main nav nav-tabs settings-steps" role="tablist">
                                    <li role="presentation" id="tab-notifications"  class="active">
                                        <a href="#settings-notifications" aria-controls="notifications" role="tab" data-toggle="tab">
                                            <?php _e( 'Notifications', 'yop-poll' );?>
                                        </a>
                                    </li>
                                    <li role="presentation" id="tab-integrations">
                                        <a href="#settings-integrations" aria-controls="integrations" role="tab" data-toggle="tab">
                                            <?php _e( 'Integrations', 'yop-poll' );?>
                                        </a>
                                    </li>
                                    <li role="presentation" id="tab-messages">
                                        <a href="#settings-messages" aria-controls="messages" role="tab" data-toggle="tab">
                                            <?php _e( 'Messages', 'yop-poll' );?>
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content settings-steps-content">
                                    <div role="tabpanel" class="tab-pane active" id="settings-notifications">
                                        <div class="row submenu" style="padding-top: 20px;">
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <div class="form-group">
                                                    <label for="email-from-name">
                                                        <?php _e( 'From Name', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="email-from-name" id="email-from-name" value="<?php echo isset( $settings['email']['from-name'] ) ? esc_html ( $settings['email']['from-name'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="email-from-email">
                                                        <?php _e( 'From Email', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="email-from-email" id="email-from-email" value="<?php echo isset( $settings['email']['from-email'] ) ? esc_html ( $settings['email']['from-email'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="email-recipients">
                                                        <?php _e( 'Recipients', 'yop-poll' ); ?>
                                                    </label>
                                                    <div><?php _e( 'Use comma separated email addresses: email@xmail.com,email2@ymail.com', 'yop-poll' ) ?></div>
                                                    <input class="form-control settings-required-field" name="email-recipients" id="email-recipients" value="<?php echo isset( $settings['email']['recipients'] ) ? esc_html ( $settings['email']['recipients'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="email-subject">
                                                        <?php _e( 'Subject', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="email-subject" id="email-subject" value="<?php echo isset( $settings['email']['subject'] ) ? esc_html ( $settings['email']['subject'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="email-message">
                                                        <?php _e( 'Body', 'yop-poll' ); ?>
                                                    </label>
                                                    <textarea class="form-control settings-required-field" name="email-message" id="email-message" rows="15"><?php echo isset( $settings['email']['message'] ) ? esc_html ( $settings['email']['message'] ) : ''; ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="settings-integrations">
                                        <br><br>
                                        <div class="row submenu">
                                            <div class="row submenu" style="padding-top: 20px;">
                                                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
                                                    <?php _e( 'Use Google reCaptcha:', 'yop-poll' ); ?>
                                                </div>
                                                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-9">
                                                    <?php
                                                    $reCaptcha_integration_yes = '';
                                                    $reCaptcha_integration_no = '';
                                                    $reCaptcha_data_section = '';
                                                    if ( ( true === isset( $settings['integrations']['reCaptcha']['enabled'] ) ) && ( 'yes' === $settings['integrations']['reCaptcha']['enabled'] ) ) {
                                                        $reCaptcha_integration_yes = 'selected';
                                                    } else {
                                                        $reCaptcha_integration_no = 'selected';
                                                        $reCaptcha_data_section = 'hide';
                                                    }
                                                    ?>
                                                    <select name="integrations-reCaptcha-enabled" id="integrations-reCaptcha-enabled" class="integrations-reCaptcha-enabled admin-select" style="width:100%">
                                                        <option value="yes" <?php echo $reCaptcha_integration_yes;?>><?php _e( 'Yes', 'yop-poll' );?></option>
                                                        <option value="no" <?php echo $reCaptcha_integration_no;?>><?php _e( 'No', 'yop-poll' );?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row submenu integrations-reCaptcha-section <?php echo $reCaptcha_data_section;?>" style="padding-top: 20px; margin-left: 20px;">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 input-caption">
                                                            <?php _e( '- Site Key:', 'yop-poll' ); ?>
                                                        </div>
                                                        <div class="col-lg-9 col-md-9 col-sm-9 col-xs-9">
                                                            <input name="integrations-reCaptcha-site-key" id ="integrations-reCaptcha-site-key" class="form-control settings-required-field" value="<?php echo isset( $settings['integrations']['reCaptcha']['site-key'] ) ? esc_html ( $settings['integrations']['reCaptcha']['site-key'] ) : ''; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="row" style="padding-top: 10px;">
                                                        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 input-caption">
                                                            <?php _e( '- Secret Key:', 'yop-poll' ); ?>
                                                        </div>
                                                        <div class="col-lg-9 col-md-9 col-sm-9 col-xs-9">
                                                            <input name="integrations-reCaptcha-secret-key" id ="integrations-reCaptcha-secret-key" class="form-control settings-required-field" value="<?php echo isset( $settings['integrations']['reCaptcha']['secret-key'] ) ? esc_html ( $settings['integrations']['reCaptcha']['secret-key'] ) : ''; ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row submenu" style="padding-top: 20px;">
                                                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
                                                    <?php _e( 'Use Invisible reCaptcha v2:', 'yop-poll' ); ?>
                                                </div>
                                                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-9">
                                                    <?php
                                                    $reCaptcha_v2_invisible_integration_yes = '';
                                                    $reCaptcha_v2_invisible_integration_no = '';
                                                    $reCaptcha_v2_invisible_data_section = '';
                                                    if ( ( true === isset( $settings['integrations']['reCaptchaV2Invisible']['enabled'] ) ) && ( 'yes' === $settings['integrations']['reCaptchaV2Invisible']['enabled'] ) ) {
                                                        $reCaptcha_v2_invisible_integration_yes = 'selected';
                                                    } else {
                                                        $reCaptcha_v2_invisible_integration_no = 'selected';
                                                        $reCaptcha_v2_invisible_data_section = 'hide';
                                                    }
                                                    ?>
                                                    <select name="integrations-reCaptchaV2Invisible-enabled" id="integrations-reCaptchaV2Invisible-enabled" class="integrations-reCaptchaV2Invisible-enabled admin-select" style="width:100%">
                                                        <option value="yes" <?php echo $reCaptcha_v2_invisible_integration_yes;?>><?php _e( 'Yes', 'yop-poll' );?></option>
                                                        <option value="no" <?php echo $reCaptcha_v2_invisible_integration_no;?>><?php _e( 'No', 'yop-poll' );?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row submenu integrations-reCaptchaV2Invisible-section <?php echo $reCaptcha_v2_invisible_data_section;?>" style="padding-top: 20px; margin-left: 20px;">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 input-caption">
                                                            <?php _e( '- Site Key:', 'yop-poll' ); ?>
                                                        </div>
                                                        <div class="col-lg-9 col-md-9 col-sm-9 col-xs-9">
                                                            <input name="integrations-reCaptchaV2Invisible-site-key" id ="integrations-reCaptchaV2Invisible-site-key" class="form-control settings-required-field" value="<?php echo isset( $settings['integrations']['reCaptchaV2Invisible']['site-key'] ) ? esc_html ( $settings['integrations']['reCaptchaV2Invisible']['site-key'] ) : ''; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="row" style="padding-top: 10px;">
                                                        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 input-caption">
                                                            <?php _e( '- Secret Key:', 'yop-poll' ); ?>
                                                        </div>
                                                        <div class="col-lg-9 col-md-9 col-sm-9 col-xs-9">
                                                            <input name="integrations-reCaptchaV2Invisible-secret-key" id ="integrations-reCaptchaV2Invisible-secret-key" class="form-control settings-required-field" value="<?php echo isset( $settings['integrations']['reCaptchaV2Invisible']['secret-key'] ) ? esc_html ( $settings['integrations']['reCaptchaV2Invisible']['secret-key'] ) : ''; ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row submenu" style="padding-top: 20px;">
                                                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
                                                    <a href="#" class="upgrade-to-pro" data-screen="media-integration">
                                                        <img src="<?php echo YOP_POLL_URL;?>admin/assets/images/pro-horizontal.svg" class="responsive" />
                                                    </a>
                                                    <?php _e( 'Use Facebook integration:', 'yop-poll' ); ?>
                                                </div>
                                                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-9">
                                                    <?php
                                                    $facebook_integration_yes = '';
                                                    $facebook_integration_no = '';
                                                    $facebook_data_section = '';
                                                    if ( ( true === isset( $settings['integrations']['facebook']['enabled'] ) ) && ( 'yes' === $settings['integrations']['facebook']['enabled'] ) ) {
                                                        $facebook_integration_yes = 'selected';
                                                    } else {
                                                        $facebook_integration_no = 'selected';
                                                        $facebook_data_section = 'hide';
                                                    }
                                                    ?>
                                                    <select name="integrations-facebook-enabled" id="integrations-facebook-enabled" class="integrations-facebook-enabled admin-select" style="width:100%">
                                                        <option value="yes" <?php echo $facebook_integration_yes;?>><?php _e( 'Yes', 'yop-poll' );?></option>
                                                        <option value="no" <?php echo $facebook_integration_no;?>><?php _e( 'No', 'yop-poll' );?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row submenu integrations-facebook-section <?php echo $facebook_data_section?>" style="padding-top: 20px; margin-left: 20px;">
                                                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 input-caption">
                                                    <?php _e( '- App ID:', 'yop-poll' ); ?>
                                                </div>
                                                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-9">
                                                    <input name="integrations-facebook-app-id" id ="integrations-facebook-app-id" class="form-control settings-required-field" value="<?php echo isset( $settings['integrations']['facebook']['app-id'] ) ? esc_html ( $settings['integrations']['facebook']['app-id'] ) : ''; ?>">
                                                </div>
                                            </div>
                                            <div class="row submenu" style="padding-top: 20px;">
                                                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
                                                    <a href="#" class="upgrade-to-pro" data-screen="media-integration">
                                                        <img src="<?php echo YOP_POLL_URL;?>admin/assets/images/pro-horizontal.svg" class="responsive" />
                                                    </a>
                                                    <?php _e( 'Use Google integration:', 'yop-poll' ); ?>
                                                </div>
                                                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-9">
                                                    <?php
                                                    $google_integration_yes = '';
                                                    $google_integration_no = '';
                                                    $google_data_section = '';
                                                    if ( ( true === isset( $settings['integrations']['google']['enabled'] ) ) && ( 'yes' === $settings['integrations']['google']['enabled'] ) ) {
                                                        $google_integration_yes = 'selected';
                                                    } else {
                                                        $google_integration_no = 'selected';
                                                        $google_data_section = 'hide';
                                                    }
                                                    ?>
                                                    <select name="integrations-google-enabled" id="integrations-google-enabled" class="integrations-google-enabled admin-select" style="width:100%">
                                                        <option value="yes" <?php echo $google_integration_yes;?>><?php _e( 'Yes', 'yop-poll' );?></option>
                                                        <option value="no" <?php echo $google_integration_no;?>><?php _e( 'No', 'yop-poll' );?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row submenu integrations-google-section <?php echo $google_data_section;?>" style="padding-top: 20px; margin-left: 20px;">
                                                <div class="row">
                                                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 input-caption">
                                                        <?php _e( '- App ID:', 'yop-poll' ); ?>
                                                    </div>
                                                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-9">
                                                        <input name="integrations-google-app-id" id ="integrations-google-app-id" class="form-control settings-required-field" value="<?php echo isset( $settings['integrations']['google']['app-id'] ) ? esc_html ( $settings['integrations']['google']['app-id'] ) : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="row" style="padding-top: 10px;">
                                                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 input-caption">
                                                        <?php _e( '- App Secret:', 'yop-poll' ); ?>
                                                    </div>
                                                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-9">
                                                        <input name="integrations-google-app-secret" id ="integrations-google-app-secret" class="form-control settings-required-field" value="<?php echo isset( $settings['integrations']['google']['app-secret'] ) ? esc_html ( $settings['integrations']['google']['app-secret'] ) : ''; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="settings-messages">
                                        <br><br>
                                        <div class="row submenu">
                                            <div class="col-md-3">
                                                <a class="btn btn-link btn-block submenu-item submenu-item-active btn-underline" data-content="settings-messages-buttons">
                                                    <?php _e( 'Vote Buttons', 'yop-poll' );?>
                                                </a>
                                            </div>
                                            <div class="col-md-3">
                                                <a class="btn btn-link btn-block submenu-item" data-content="settings-messages-voting">
                                                    <?php _e( 'Voting', 'yop-poll' );?>
                                                </a>
                                            </div>
                                            <div class="col-md-3">
                                                <a class="btn btn-link btn-block submenu-item" data-content="settings-messages-results">
                                                    <?php _e( 'Results', 'yop-poll' );?>
                                                </a>
                                            </div>
                                            <div class="col-md-3">
                                                <a class="btn btn-link btn-block submenu-item" data-content="settings-messages-captcha">
                                                    <?php _e( 'Captcha', 'yop-poll' );?>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="row submenu-content settings-messages-buttons">
                                            <div class="col-md-12">
                                                <div><br /><br /></div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-voting-buttons-anonymous" class="input-caption">
                                                        <?php _e( 'Vote as anonymous', 'yop-poll' );?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-buttons-anonymous" id="messages-buttons-anonymous"
                                                           value="<?php echo isset( $settings['messages']['buttons']['anonymous'] ) ? esc_html ( $settings['messages']['buttons']['anonymous'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-voting-buttons-wordpress" class="input-caption">
                                                        <?php _e( 'Vote with your wordpress account', 'yop-poll' );?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-buttons-wordpress" id="messages-buttons-wordpress"
                                                           value="<?php echo isset( $settings['messages']['buttons']['wordpress'] ) ? esc_html ( $settings['messages']['buttons']['wordpress'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-voting-buttons-facebook" class="input-caption">
                                                        <?php _e( 'Vote with your facebook account', 'yop-poll' );?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-buttons-facebook" id="messages-buttons-facebook"
                                                           value="<?php echo isset( $settings['messages']['buttons']['facebook'] ) ? esc_html ( $settings['messages']['buttons']['facebook'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-voting-buttons-google" class="input-caption">
                                                        <?php _e( 'Vote with your google account', 'yop-poll' );?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-buttons-google" id="messages-buttons-google"
                                                           value="<?php echo isset( $settings['messages']['buttons']['google'] ) ? esc_html ( $settings['messages']['buttons']['google'] ) : ''; ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row submenu-content settings-messages-voting hide">
                                            <div class="col-md-12">
                                                <div><br /><br /></div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-voting-poll-ended" class="input-caption">
                                                        <?php _e( 'Poll Ended', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-voting-poll-ended" id="messages-voting-poll-ended"
                                                           value="<?php echo isset( $settings['messages']['voting']['poll-ended'] ) ? esc_html ( $settings['messages']['voting']['poll-ended'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-voting-poll-not-started" class="input-caption">
                                                        <?php _e( 'Poll Not Started', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-voting-poll-not-started" id="messages-voting-poll-not-started"
                                                           value="<?php echo isset( $settings['messages']['voting']['poll-not-started'] ) ? esc_html ( $settings['messages']['voting']['poll-not-started'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-voting-already-voted-on-poll" class="input-caption">
                                                        <?php _e( 'Already voted on poll', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-voting-already-voted-on-poll" id="messages-voting-already-voted-on-poll"
                                                           value="<?php echo isset( $settings['messages']['voting']['already-voted-on-poll'] ) ? esc_html ( $settings['messages']['voting']['already-voted-on-poll'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-voting-invalid-poll" class="input-caption">
                                                        <?php _e( 'Invalid Poll', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-voting-invalid-poll" id="messages-voting-invalid-poll"
                                                           value="<?php echo isset( $settings['messages']['voting']['invalid-poll'] ) ? esc_html ( $settings['messages']['voting']['invalid-poll'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-voting-no-answer-selected" class="input-caption">
                                                        <?php _e( 'No Answer(s) selected', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-voting-no-answer-selected" id="messages-voting-no-answer-selected"
                                                           value="<?php echo isset( $settings['messages']['voting']['no-answers-selected'] ) ? esc_html ( $settings['messages']['voting']['no-answers-selected'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-voting-min-answers-required" class="input-caption">
                                                        <?php _e( 'Minimum answers required', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-voting-min-answers-required" id="messages-voting-min-answers-required"
                                                           value="<?php echo isset( $settings['messages']['voting']['min-answers-required'] ) ? esc_html ( $settings['messages']['voting']['min-answers-required'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-voting-max-answers-required" class="input-caption">
                                                        <?php _e( 'Maximum answers required', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-voting-max-answers-required" id="messages-voting-max-answers-required"
                                                           value="<?php echo isset( $settings['messages']['voting']['max-answers-required'] ) ? esc_html ( $settings['messages']['voting']['max-answers-required'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-voting-no-value-for-other" class="input-caption">
                                                        <?php _e( 'No value for other', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-voting-no-value-for-other" id="messages-voting-no-value-for-other"
                                                           value="<?php echo isset( $settings['messages']['voting']['no-answer-for-other'] ) ? esc_html ( $settings['messages']['voting']['no-answer-for-other'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-voting-no-value-for-custom-field" class="input-caption">
                                                        <?php _e( 'No value for custom field', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-voting-no-value-for-custom-field" id="messages-voting-no-value-for-custom-field"
                                                           value="<?php echo isset( $settings['messages']['voting']['no-value-for-custom-field'] ) ? esc_html ( $settings['messages']['voting']['no-value-for-custom-field'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-voting-consent-not-checked" class="input-caption">
                                                        <?php _e( 'Consent not checked', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-voting-consent-not-checked" id="messages-voting-consent-not-checked"
                                                           value="<?php echo isset( $settings['messages']['voting']['consent-not-checked'] ) ? esc_html ( $settings['messages']['voting']['consent-not-checked'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-voting-captcha-not-checked" class="input-caption">
                                                        <?php _e( 'Captcha missing', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-voting-captcha-not-checked" id="messages-voting-captcha-not-checked"
                                                           value="<?php echo isset( $settings['messages']['voting']['no-captcha-selected'] ) ? esc_html ( $settings['messages']['voting']['no-captcha-selected'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-voting-vote-not-allowed-by-ban" class="input-caption">
                                                        <?php _e( 'Vote not allowed by ban setting', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-voting-vote-not-allowed-by-ban" id="messages-voting-vote-not-allowed-by-ban"
                                                           value="<?php echo isset( $settings['messages']['voting']['not-allowed-by-ban'] ) ? esc_html ( $settings['messages']['voting']['not-allowed-by-ban'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-voting-vote-not-allowed-by-block" class="input-caption">
                                                        <?php _e( 'Vote not allowed by block setting', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-voting-vote-not-allowed-by-block" id="messages-voting-vote-not-allowed-by-block"
                                                           value="<?php echo isset( $settings['messages']['voting']['not-allowed-by-block'] ) ? esc_html ( $settings['messages']['voting']['not-allowed-by-block'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-voting-vote-not-allowed-by-limit" class="input-caption">
                                                        <?php _e( 'Vote not allowed by limit setting', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-voting-vote-not-allowed-by-limit" id="messages-voting-vote-not-allowed-by-limit"
                                                           value="<?php echo isset( $settings['messages']['voting']['not-allowed-by-limit'] ) ? esc_html ( $settings['messages']['voting']['not-allowed-by-limit'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-voting-thank-you" class="input-caption">
                                                        <?php _e( 'Thank you for your vote', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-voting-thank-you" id="messages-voting-thank-you"
                                                           value="<?php echo isset( $settings['messages']['voting']['thank-you'] ) ? esc_html ( $settings['messages']['voting']['thank-you'] ) : ''; ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row submenu-content settings-messages-results hide">
                                            <div class="col-md-12">
                                                <div><br /><br /></div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-results-single-vote" class="input-caption">
                                                        <?php _e( 'Single Vote', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-results-single-vote" id="messages-results-single-vote"
                                                           value="<?php echo isset( $settings['messages']['results']['single-vote'] ) ? esc_html ( $settings['messages']['results']['single-vote'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-results-multiple-votes" class="input-caption">
                                                        <?php _e( 'Multiple Votes', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-results-multiple-votes" id="messages-results-multiple-votes"
                                                           value="<?php echo isset( $settings['messages']['results']['multiple-votes'] ) ? esc_html ( $settings['messages']['results']['multiple-votes'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-results-single-answer" class="input-caption">
                                                        <?php _e( 'Single Answer', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-results-single-answer" id="messages-results-single-answer"
                                                           value="<?php echo isset( $settings['messages']['results']['single-answer'] ) ? esc_html ( $settings['messages']['results']['single-answer'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-results-multiple-answers" class="input-caption">
                                                        <?php _e( 'Multiple Answers', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-results-multiple-answers" id="messages-results-multiple-answers"
                                                           value="<?php echo isset( $settings['messages']['results']['multiple-answers'] ) ? esc_html ( $settings['messages']['results']['multiple-answers'] ) : ''; ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row submenu-content settings-messages-captcha hide">
                                            <div class="col-md-12">
                                                <div><br /><br /></div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-captcha-accessibility-alt" class="input-caption">
                                                        <?php _e( 'Accessibility Alt', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-captcha-accessibility-alt" id="messages-captcha-accessibility-alt"
                                                           value="<?php echo isset( $settings['messages']['captcha']['accessibility-alt'] ) ? $settings['messages']['captcha']['accessibility-alt'] : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-captcha-accessibility-title" class="input-caption">
                                                        <?php _e( 'Accessibility Title', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-captcha-accessibility-title" id="messages-captcha-accessibility-title"
                                                           value="<?php echo isset( $settings['messages']['captcha']['accessibility-title'] ) ? esc_html ( $settings['messages']['captcha']['accessibility-title'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-captcha-accessibility-description" class="input-caption">
                                                        <?php _e( 'Accessibility Description', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-captcha-accessibility-description" id="messages-captcha-accessibility-description"
                                                           value="<?php echo isset( $settings['messages']['captcha']['accessibility-description'] ) ? esc_html ( $settings['messages']['captcha']['accessibility-description'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-captcha-accessibility-explanation" class="input-caption">
                                                        <?php _e( 'Accessibility Explanation', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-captcha-accessibility-explanation" id="messages-captcha-accessibility-explanation"
                                                           value="<?php echo isset( $settings['messages']['captcha']['explanation'] ) ? esc_html ( $settings['messages']['captcha']['explanation'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-captcha-refresh-alt" class="input-caption">
                                                        <?php _e( 'Refresh Alt', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-captcha-refresh-alt" id="messages-captcha-refresh-alt"
                                                           value="<?php echo isset( $settings['messages']['captcha']['refresh-alt'] ) ? esc_html ( $settings['messages']['captcha']['refresh-alt'] ) : ''; ?>">
                                                </div>
                                                <div class="form-group messages-fields">
                                                    <label for="messages-captcha-refresh-title" class="input-caption">
                                                        <?php _e( 'Refresh Title', 'yop-poll' ); ?>
                                                    </label>
                                                    <input class="form-control settings-required-field" name="messages-captcha-refresh-title" id="messages-captcha-refresh-title"
                                                           value="<?php echo isset( $settings['messages']['captcha']['refresh-title'] ) ? esc_html ( $settings['messages']['captcha']['refresh-title'] ) : ''; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- /.container -->
                    </div>
                </form>
            </div>
            <div id="postbox-container-1" class="postbox-container">
                <div id="side-sortables" class="meta-box-sortables ui-sortable">
                    <div class="postbox" id="submitdiv">
                        <button type="button" class="handlediv button-link" aria-expanded="true">
                            <span class="screen-reader-text">
                                <?php _e( 'Toggle panel: Publish', 'yop-poll' );?>
                            </span>
                            <span class="toggle-indicator" aria-hidden="true"></span>
                        </button>
                        <h2 class="hndle ui-sortable-handle">
                            <span>
                                <?php _e( 'Publish', 'yop-poll' );?>
                            </span>
                        </h2>
                        <div class="inside">
                            <div id="submitpoll" class="submitbox">
                                <div id="minor-publishing">
                                    <div class="clear"></div>
                                    <div id="major-publishing-actions">
                                        <div id="publishing-action">
                                            <span class="spinner publish"></span>
                                            <button name="save_settings" class="button button-primary button-large save-settings" type="button">
                                                <?php _e( 'Save settings', 'yop-poll' );?>
                                            </button>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            if ( 1 === rand( 1, 2 ) ) {
                include( YOP_POLL_PATH . 'admin/views/general/upgrade-long.php' );
            } else {
                include( YOP_POLL_PATH . 'admin/views/general/upgrade-short.php' );
            }
            ?>
        </div>
    </div>
</div>
<!-- begin live preview -->
<div class="bootstrap-yop">
    <div id="yop-poll-preview" class="hide">
    </div>
</div>
<!-- end live preview -->
