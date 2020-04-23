<?php
class YOP_Poll_Settings {
    private static $errors_present = false,
        $error_text,
        $settings;
    public static function create_settings() {
        $settings = array(
            'general' => array(
                'i-date' => current_time( 'mysql' ),
                'show-guide' => 'yes'
            ),
            'email'        => array(
                'from-name'  => 'Your Name Here',
                'from-email' => 'Your Email Address Here',
                'recipients' => '',
                'subject'    => 'Your Subject Here',
                'message'    => 'Your Message Here'
            ),
            'integrations' => array(
                'reCaptcha' => array(
                    'enabled' => 'no',
                    'site-key' => '',
                    'secret-key' => ''
                ),
                'reCaptchaV2Invisible' => array(
					'enabled' => 'no',
					'site-key' => '',
					'secret-key' => ''
				),
                'facebook' => array(
                    'enabled' => 'no',
                    'app-id'      => '',
                ),
                'google'   => array(
                    'enabled' => 'no',
                    'app-id'      => '',
                    'app-secret'  => ''
                )
            ),
            'messages' => array(
                'captcha' => array(
                    'accessibility-alt' => 'Sound icon',
                    'accessibility-title' => 'Accessibility option: listen to a question and answer it!',
                    'accessibility-description' => 'Type below the [STRONG]answer[/STRONG] to what you hear. Numbers or words:',
                    'explanation' => 'Click or touch the [STRONG]ANSWER[/STRONG]',
                    'refresh-alt' => 'Refresh/reload icon',
                    'refresh-title' => 'Refresh/reload: get new images and accessibility option!'
                ),
                'buttons' => array(
                    'anonymous' => 'Anonymous Vote',
                    'wordpress' => 'Sign in with Wordpress',
                    'facebook' => 'Sign in with Facebook',
                    'google' => 'Sign in with Google'
                ),
                'voting' => array(
                    'poll-ended' => 'This poll is no longer accepting votes',
                    'poll-not-started' => 'This poll is not accepting votes yet',
                    'already-voted-on-poll' => 'Thank you for your vote',
                    'invalid-poll' => 'Invalid Poll',
                    'no-answers-selected' => 'No answer selected',
                    'min-answers-required' => 'At least {min_answers_allowed} answer(s) required',
                    'max-answers-required' => 'A max of {max_answers_allowed} answer(s) accepted',
                    'no-answer-for-other' => 'No other answer entered',
                    'no-value-for-custom-field' => '{custom_field_name} is required',
                    'consent-not-checked' => 'You must agree to our terms and conditions',
                    'no-captcha-selected' => 'Captcha is required',
                    'not-allowed-by-ban' => 'Vote not allowed',
                    'not-allowed-by-block' => 'Vote not allowed',
                    'not-allowed-by-limit' => 'Vote not allowed',
                    'thank-you' => 'Thank you for your vote'
                ),
                'results' => array(
                    'single-vote' => 'vote',
                    'multiple-votes' => 'votes',
                    'single-answer' => 'answer',
                    'multiple-answers' => 'answers'
                )
            )
        );
        return serialize( $settings );
    }
    public static function import_settings_from_5x ( $old_settings ) {
        $new_settings = array(
            'general' => array(
                'i-date' => current_time( 'mysql' )
            ),
            'email'        => array(
                'from-name'  => isset( $old_settings['email_notifications_from_name'] ) ? $old_settings['email_notifications_from_name']: 'Your Name Here',
                'from-email' => isset( $old_settings['email_notifications_from_email'] ) ? $old_settings['email_notifications_from_email']: 'Your Email Address Here',
                'recipients' => isset( $old_settings['email_notifications_recipients'] ) ? $old_settings['email_notifications_recipients']: '',
                'subject'    => isset( $old_settings['email_notifications_subject'] ) ? $old_settings['email_notifications_subject']: 'Your Subject Here',
                'message'    => isset( $old_settings['email_notifications_body'] ) ? $old_settings['email_notifications_body'] : 'Your Message Here'
            ),
            'integrations' => array(
                'reCaptcha' => array(
                    'enabled' => 'no',
                    'site-key' => '',
                    'secret-key' => ''
                ),
                'reCaptchaV2Invisible' => array(
					'enabled' => 'no',
					'site-key' => '',
					'secret-key' => ''
				),
                'facebook' => array(
                    'enabled'  => isset( $old_settings['facebook_integration'] ) ? $old_settings['facebook_integration'] : 'no',
                    'app-id'     => isset( $old_settings['facebook_appID'] ) ? $old_settings['facebook_appID'] : '',
                ),
                'google'   => array(
                    'enabled' => isset( $old_settings['google_integration'] ) ? $old_settings['google_integration'] : 'no',
                    'app-id'      => isset( $old_settings['google_appID'] ) ? $old_settings['google_appID']: '',
                    'app-secret'  => isset( $old_settings['google_appSecret'] ) ? $old_settings['google_appSecret']: ''
                )
            ),
            'messages' => array(
                'captcha' => array(
                    'accessibility-alt' => 'Sound icon',
                    'accessibility-title' => 'Accessibility option: listen to a question and answer it!',
                    'accessibility-description' => 'Type below the [STRONG]answer[/STRONG] to what you hear. Numbers or words:',
                    'explanation' => 'Click or touch the [STRONG]ANSWER[/STRONG]',
                    'refresh-alt' => 'Refresh/reload icon',
                    'refresh-title' => 'Refresh/reload: get new images and accessibility option!'
                ),
                'buttons' => array(
                    'anonymous' => 'Anonymous Vote',
                    'wordpress' => 'Sign in with Wordpress',
                    'facebook' => 'Sign in with Facebook',
                    'google' => 'Sign in with Google'
                ),
                'voting' => array(
                    'poll-ended' => 'This poll is no longer accepting votes',
                    'poll-not-started' => 'This poll is not accepting votes yet',
                    'already-voted-on-poll' => 'Thank you for your vote',
                    'invalid-poll' => 'Invalid Poll',
                    'no-answers-selected' => 'No answer selected',
                    'min-answers-required' => 'At least {min_answers_allowed} answer(s) required',
                    'max-answers-required' => 'A max of {max_answers_allowed} answer(s) accepted',
                    'no-answer-for-other' => 'No other answer entered',
                    'no-value-for-custom-field' => '{custom_field_name} is required',
                    'consent-not-checked' => 'You must agree to our terms and conditions',
                    'no-captcha-selected' => 'Captcha is required',
                    'not-allowed-by-ban' => 'Vote not allowed',
                    'not-allowed-by-block' => 'Vote not allowed',
                    'not-allowed-by-limit' => 'Vote not allowed',
                    'thank-you' => 'Thank you for your vote'
                ),
                'results' => array(
                    'single-vote' => 'vote',
                    'multiple-votes' => 'votes',
                    'single-answer' => 'answer',
                    'multiple-answers' => 'answers'
                )
            )
        );
        return serialize( $new_settings );
    }
    public static function update_settings_to_version_6_0_4() {
        $current_settings = self::get_all_settings();
        $current_settings_decoded = unserialize( $current_settings );
        $captcha_enabled = 'no';
        if ( true === isset( $current_settings_decoded['integrations']['reCaptcha']['enabled'] ) ) {
            $captcha_enabled = $current_settings_decoded['integrations']['reCaptcha']['enabled'];
        } else {
            if ( true === isset( $current_settings_decoded['integrations']['reCaptcha']['integration'] ) ) {
                $captcha_enabled = $current_settings_decoded['integrations']['reCaptcha']['integration'];
            }
        }
        $new_settings = array(
            'general' => array(
                'i-date' => isset( $current_settings_decoded['general']['idate'] ) ? $current_settings_decoded['general']['idate'] : current_time( 'mysql' )
            ),
            'email'        => array(
                'from-name'  => isset( $current_settings_decoded['email']['from_name'] ) ? $current_settings_decoded['email']['from_name'] : '',
                'from-email' => isset( $current_settings_decoded['email']['from_email'] ) ?$current_settings_decoded['email']['from_email'] : '',
                'recipients' => isset( $current_settings_decoded['email']['recipients'] ) ? $current_settings_decoded['email']['recipients'] : '',
                'subject'    => isset( $current_settings_decoded['email']['subject'] ) ? $current_settings_decoded['email']['subject'] : '',
                'message'    => isset( $current_settings_decoded['email']['message'] ) ? $current_settings_decoded['email']['message'] : ''
            ),
            'integrations' => array(
                'reCaptcha' => array(
                    'enabled' => $captcha_enabled,
                    'site-key' => isset( $current_settings_decoded['integrations']['reCaptcha']['site_key'] ) ? $current_settings_decoded['integrations']['reCaptcha']['site_key'] : '',
                    'secret-key' => isset( $current_settings_decoded['integrations']['reCaptcha']['secret_key'] ) ? $current_settings_decoded['integrations']['reCaptcha']['secret_key']: '',
                ),
                'facebook' => array(
                    'enabled' => isset( $current_settings_decoded['integrations']['facebook']['integration'] ) ? $current_settings_decoded['integrations']['facebook']['integration'] : 'no',
                    'app-id' => isset( $current_settings_decoded['integrations']['facebook']['app_id'] ) ? $current_settings_decoded['integrations']['facebook']['app_id'] : '',
                ),
                'google'   => array(
                    'enabled' => isset( $current_settings_decoded['integrations']['google']['integration'] ) ? $current_settings_decoded['integrations']['google']['integration'] : 'no',
                    'app-id' => isset( $current_settings_decoded['integrations']['google']['app_id'] ) ? $current_settings_decoded['integrations']['google']['app_id'] : '',
                    'app-secret' => isset( $current_settings_decoded['integrations']['google']['app_secret'] ) ? $current_settings_decoded['integrations']['google']['app_secret'] : '',
                )
            ),
            'messages' => array(
                'captcha' => array(
                    'accessibility-alt' => 'Sound icon',
                    'accessibility-title' => 'Accessibility option: listen to a question and answer it!',
                    'accessibility-description' => 'Type below the [STRONG]answer[/STRONG] to what you hear. Numbers or words:',
                    'explanation' => 'Click or touch the [STRONG]ANSWER[/STRONG]',
                    'refresh-alt' => 'Refresh/reload icon',
                    'refresh-title' => 'Refresh/reload: get new images and accessibility option!'
                ),
                'buttons' => array(
                    'anonymous' => 'Anonymous Vote',
                    'wordpress' => 'Sign in with Wordpress',
                    'facebook' => 'Sign in with Facebook',
                    'google' => 'Sign in with Google'
                ),
                'voting' => array(
                    'poll-ended' => 'This poll is no longer accepting votes',
                    'poll-not-started' => 'This poll is not accepting votes yet',
                    'already-voted-on-poll' => 'Thank you for your vote',
                    'invalid-poll' => 'Invalid Poll',
                    'no-answers-selected' => 'No answer selected',
                    'min-answers-required' => 'At least {min_answers_allowed} answer(s) required',
                    'max-answers-required' => 'A max of {max_answers_allowed} answer(s) accepted',
                    'no-answer-for-other' => 'No other answer entered',
                    'no-value-for-custom-field' => '{custom_field_name} is required',
                    'consent-not-checked' => 'You must agree to our terms and conditions',
                    'no-captcha-selected' => 'Captcha is required',
                    'not-allowed-by-ban' => 'Vote not allowed',
                    'not-allowed-by-block' => 'Vote not allowed',
                    'not-allowed-by-limit' => 'Vote not allowed',
                    'thank-you' => 'Thank you for your vote'
                ),
                'results' => array(
                    'single-vote' => 'vote',
                    'multiple-votes' => 'votes',
                    'single-answer' => 'answer',
                    'multiple-answers' => 'answers'
                )
            )
        );
        update_option('yop_poll_settings', serialize( $new_settings ) );
    }
    public static function get_all_settings() {
        if ( ( false === isset( self::$settings ) ) || ( '' === self::$settings ) ) {
            self::$settings = get_option( 'yop_poll_settings' );
        }
        return self::$settings;
    }
    public static function get_install_date() {
        $install_date = '';
        $settings = self::get_all_settings();
        if ( '' !== $settings ) {
            $unserialized_settings = unserialize( $settings );
            $install_date = $unserialized_settings['general']['i-date'];
        }
        return $install_date;
    }
    public static function get_show_guide() {
        $show_guide = '';
        $settings = self::get_all_settings();
        if ( '' !== $settings ) {
            $unserialized_settings = unserialize( $settings );
            if ( isset( $unserialized_settings['general']['show-guide'] ) ) {
                $show_guide = $unserialized_settings['general']['show-guide'];
            } else {
                $show_guide = 'yes';
            }
        }
        return $show_guide;
    }
    public static function update_show_guide( $show_guide  ) {
        $settings = self::get_all_settings();
        if ( '' !== $settings ) {
            $unserialized_settings = unserialize( $settings );
            $unserialized_settings['general']['show-guide'] = $show_guide;
            $serialized_settings = serialize( $unserialized_settings );
            update_option('yop_poll_settings', $serialized_settings  );
            self::$settings = $serialized_settings;
        }
    }
    public static function get_email_settings() {
        $email_settings = array();
        $settings = self::get_all_settings();
        if ( '' !== $settings ) {
            $unserialized_settings = unserialize( $settings );
            $email_settings = array(
                'from-name' => $unserialized_settings['email']['from-name'],
                'from-email' => $unserialized_settings['email']['from-email'],
                'recipients' => $unserialized_settings['email']['recipients'],
                'subject' => $unserialized_settings['email']['subject'],
                'message' => $unserialized_settings['email']['message']
            );
        }
        return $email_settings;
    }
    public static function get_integrations() {
        $integrations_settings = array();
        $settings = self::get_all_settings();
        if ( '' !== $settings ) {
            $unserialized_settings = unserialize( $settings );
            $integrations_settings = array(
                'reCaptcha' => array(
                    'enabled' => ( isset( $unserialized_settings['integrations'] ) && isset( $unserialized_settings['integrations']['reCaptcha'] ) && isset( $unserialized_settings['integrations']['reCaptcha']['enabled'] ) ) ? $unserialized_settings['integrations']['reCaptcha']['enabled'] : '',
                    'site-key' => ( isset( $unserialized_settings['integrations'] ) && isset( $unserialized_settings['integrations']['reCaptcha'] ) && isset( $unserialized_settings['integrations']['reCaptcha']['site-key'] ) ) ? $unserialized_settings['integrations']['reCaptcha']['site-key'] : '',
                    'secret-key' => ( isset( $unserialized_settings['integrations'] ) && isset( $unserialized_settings['integrations']['reCaptcha'] ) && isset( $unserialized_settings['integrations']['reCaptcha']['secret-key'] ) ) ? $unserialized_settings['integrations']['reCaptcha']['secret-key'] : ''
                ),
                'reCaptchaV2Invisible' => array(
					'enabled' => ( isset( $unserialized_settings['integrations'] ) && isset( $unserialized_settings['integrations']['reCaptchaV2Invisible'] ) && isset( $unserialized_settings['integrations']['reCaptchaV2Invisible']['enabled'] ) ) ? $unserialized_settings['integrations']['reCaptchaV2Invisible']['enabled'] : '',
	                'site-key' => ( isset( $unserialized_settings['integrations'] ) && isset( $unserialized_settings['integrations']['reCaptchaV2Invisible'] ) && isset( $unserialized_settings['integrations']['reCaptchaV2Invisible']['site-key'] ) ) ? $unserialized_settings['integrations']['reCaptchaV2Invisible']['site-key'] : '',
					'secret-key' => ( isset( $unserialized_settings['integrations'] ) && isset( $unserialized_settings['integrations']['reCaptchaV2Invisible'] ) && isset( $unserialized_settings['integrations']['reCaptchaV2Invisible']['secret-key'] ) ) ? $unserialized_settings['integrations']['reCaptchaV2Invisible']['secret-key'] : ''
				),
                'facebook' => array(
                    'enabled' => ( isset( $unserialized_settings['integrations'] ) && isset( $unserialized_settings['integrations']['facebook'] ) && isset( $unserialized_settings['integrations']['facebook']['enabled'] ) ) ? $unserialized_settings['integrations']['facebook']['enabled'] : '',
                    'app-id' => ( isset( $unserialized_settings['integrations'] ) && isset( $unserialized_settings['integrations']['facebook'] ) && isset( $unserialized_settings['integrations']['facebook']['app-id'] ) ) ? $unserialized_settings['integrations']['facebook']['app-id'] : ''
                ),
                'google' => array(
                    'enabled' => ( isset( $unserialized_settings['integrations'] ) && isset( $unserialized_settings['integrations']['google'] ) && isset( $unserialized_settings['integrations']['google']['enabled'] ) ) ? $unserialized_settings['integrations']['google']['enabled'] : '',
                    'app-id' => ( isset( $unserialized_settings['integrations'] ) && isset( $unserialized_settings['integrations']['google'] ) && isset( $unserialized_settings['integrations']['google']['app-id'] ) ) ? $unserialized_settings['integrations']['google']['app-id'] : '',
                    'app-secret' => ( isset( $unserialized_settings['integrations'] ) && isset( $unserialized_settings['integrations']['google'] ) && isset( $unserialized_settings['integrations']['google']['app-secret'] ) ) ? $unserialized_settings['integrations']['google']['app-secret'] : ''
                )
            );
        }
        return $integrations_settings;
    }
    public static function get_messages() {
        $messages = array();
        $settings = self::get_all_settings();
        if ( '' !== $settings ) {
            $unserialized_settings = unserialize( $settings );
            $messages = $unserialized_settings['messages'];
        }
        return $messages;
    }
    public static function validate_data( $settings ) {
        if ( false === is_object( $settings ) ) {
            self::$errors_present = true;
            self::$error_text = __( 'Invalid data', 'yop-poll' );
        } else {
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->email->{'from-name'} ) ||
                    ( '' === trim( $settings->email->{'from-name'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "From Name" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->email->{'from-email'} ) ||
                    ( '' === trim( $settings->email->{'from-email'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "From Email" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->email->{'recipients'} ) ||
                    ( '' === trim( $settings->email->{'recipients'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Recipients" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->email->{'subject'} ) ||
                    ( '' === trim( $settings->email->{'subject'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Subject" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->email->{'message'} ) ||
                    ( '' === trim( $settings->email->{'message'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Message" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->integrations->reCaptcha->{'enabled'} ) ||
                    ( '' === trim( $settings->integrations->reCaptcha->{'enabled'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Use Google reCaptcha" is invalid', 'yop-poll' );
            }
            if ( 'yes' === $settings->integrations->reCaptcha->{'enabled'} ) {
                if ( ( false === isset( $settings->integrations->reCaptcha->{'site-key'} ) ) || ( '' === trim( $settings->integrations->reCaptcha->{'site-key'} ) ) ) {
                    self::$errors_present = true;
                    self::$error_text = __( 'Data for "Site Key" is invalid', 'yop-poll' );
                }
                if ( ( false === isset( $settings->integrations->reCaptcha->{'secret-key'} ) ) || ( '' === trim( $settings->integrations->reCaptcha->{'secret-key'} ) ) ) {
                    self::$errors_present = true;
                    self::$error_text = __( 'Data for "Secret Key" is invalid', 'yop-poll' );
                }
            }
            if ( 'yes' === $settings->integrations->reCaptchaV2Invisible->{'enabled'} ) {
				if ( ( false === isset( $settings->integrations->reCaptchaV2Invisible->{'site-key'} ) ) || ( '' === trim( $settings->integrations->reCaptchaV2Invisible->{'site-key'} ) ) ) {
					self::$errors_present = true;
					self::$error_text = __( 'Data for "Site Key" is invalid', 'yop-poll' );
				}
				if ( ( false === isset( $settings->integrations->reCaptchaV2Invisible->{'secret-key'} ) ) || ( '' === trim( $settings->integrations->reCaptchaV2Invisible->{'secret-key'} ) ) ) {
					self::$errors_present = true;
					self::$error_text = __( 'Data for "Secret Key" is invalid', 'yop-poll' );
				}
			}
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->integrations->facebook->{'enabled'} ) ||
                    ( '' === trim( $settings->integrations->facebook->{'enabled'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Use Facebook integration" is invalid', 'yop-poll' );
            }
            if ( 'yes' === $settings->integrations->facebook->{'enabled'} ) {
                if ( ( false === isset( $settings->integrations->facebook->{'app-id'} ) ) || ( '' === trim( $settings->integrations->facebook->{'app-id'} ) ) ) {
                    self::$errors_present = true;
                    self::$error_text = __( 'Data for "App ID" is invalid', 'yop-poll' );
                }
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->integrations->google->{'enabled'} ) ||
                    ( '' === trim( $settings->integrations->google->{'enabled'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Use Google integration" is invalid', 'yop-poll' );
            }
            if ( 'yes' === $settings->integrations->google->enabled ) {
                if ( ( false === isset( $settings->integrations->google->{'app-id'} ) ) || ( '' === trim( $settings->integrations->google->{'app-id'} ) ) ) {
                    self::$errors_present = true;
                    self::$error_text = __( 'Data for "App ID" is invalid', 'yop-poll' );
                }
                if ( ( false === isset( $settings->integrations->google->{'app-secret'} ) ) || ( '' === trim( $settings->integrations->google->{'app-secret'} ) ) ) {
                    self::$errors_present = true;
                    self::$error_text = __( 'Data for "App Secret" is invalid', 'yop-poll' );
                }
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->buttons->{'anonymous'} ) ||
                    ( '' === trim( $settings->messages->buttons->{'anonymous'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Vote as anonymous" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->buttons->{'wordpress'} ) ||
                    ( '' === trim( $settings->messages->buttons->{'wordpress'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Vote with your wordpress account" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->buttons->{'facebook'} ) ||
                    ( '' === trim( $settings->messages->buttons->{'facebook'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Vote with your facebook account" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->buttons->{'google'} ) ||
                    ( '' === trim( $settings->messages->buttons->{'google'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Vote with your google account" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->voting->{'poll-ended'} ) ||
                    ( '' === trim( $settings->messages->voting->{'poll-ended'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Poll Ended" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->voting->{'poll-not-started'} ) ||
                    ( '' === trim( $settings->messages->voting->{'poll-not-started'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Poll Not Started" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->voting->{'already-voted-on-poll'} ) ||
                    ( '' === trim( $settings->messages->voting->{'already-voted-on-poll'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Already voted on poll" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->voting->{'invalid-poll'} ) ||
                    ( '' === trim( $settings->messages->voting->{'invalid-poll'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Invalid Poll" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->voting->{'no-answers-selected'} ) ||
                    ( '' === trim( $settings->messages->voting->{'no-answers-selected'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "No Answer(s) selected" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->voting->{'min-answers-required'} ) ||
                    ( '' === trim( $settings->messages->voting->{'min-answers-required'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Minimum answers required" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->voting->{'max-answers-required'} ) ||
                    ( '' === trim( $settings->messages->voting->{'max-answers-required'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Maximum answers required" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->voting->{'no-answer-for-other'} ) ||
                    ( '' === trim( $settings->messages->voting->{'no-answer-for-other'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "No value for other" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->voting->{'no-value-for-custom-field'} ) ||
                    ( '' === trim( $settings->messages->voting->{'no-value-for-custom-field'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "No value for custom field" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->voting->{'consent-not-checked'} ) ||
                    ( '' === trim( $settings->messages->voting->{'consent-not-checked'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Consent not checked" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->voting->{'no-captcha-selected'} ) ||
                    ( '' === trim( $settings->messages->voting->{'no-captcha-selected'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Captcha missing" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->voting->{'not-allowed-by-ban'} ) ||
                    ( '' === trim( $settings->messages->voting->{'not-allowed-by-ban'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Vote not allowed by ban setting" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->voting->{'not-allowed-by-block'} ) ||
                    ( '' === trim( $settings->messages->voting->{'not-allowed-by-block'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Vote not allowed by block setting" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->voting->{'not-allowed-by-limit'} ) ||
                    ( '' === trim( $settings->messages->voting->{'not-allowed-by-limit'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Vote not allowed by limit setting" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->voting->{'thank-you'} ) ||
                    ( '' === trim( $settings->messages->voting->{'thank-you'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Thank you for your vote" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->results->{'single-vote'} ) ||
                    ( '' === trim( $settings->messages->results->{'single-vote'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Single Vote" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->results->{'multiple-votes'} ) ||
                    ( '' === trim( $settings->messages->results->{'multiple-votes'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Multiple Votes" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->results->{'single-answer'} ) ||
                    ( '' === trim( $settings->messages->results->{'single-answer'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Single Answer" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->results->{'multiple-answers'} ) ||
                    ( '' === trim( $settings->messages->results->{'multiple-answers'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Multiple Answers" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->captcha->{'accessibility-alt'} ) ||
                    ( '' === trim( $settings->messages->captcha->{'accessibility-alt'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Accessibility Alt" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->captcha->{'accessibility-title'} ) ||
                    ( '' === trim( $settings->messages->captcha->{'accessibility-title'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Accessibility Title" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->captcha->{'accessibility-description'} ) ||
                    ( '' === trim( $settings->messages->captcha->{'accessibility-description'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Accessibility Description" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->captcha->{'explanation'} ) ||
                    ( '' === trim( $settings->messages->captcha->{'explanation'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Accessibility Explanation" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->captcha->{'refresh-alt'} ) ||
                    ( '' === trim( $settings->messages->captcha->{'refresh-alt'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Refresh Alt" is invalid', 'yop-poll' );
            }
            if (
                ( false === self::$errors_present ) &&
                ( !isset( $settings->messages->captcha->{'refresh-title'} ) ||
                    ( '' === trim( $settings->messages->captcha->{'refresh-title'} ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Refresh Title" is invalid', 'yop-poll' );
            }
        }
    }
    public static function save_settings( $settings ) {
        self::validate_data( $settings );
        if ( false === self::$errors_present ) {
            $yop_poll_settings = array(
                'general' => array(
                    'i-date' => self::get_install_date(),
                    'show-guide' => self::get_show_guide()
                ),
                'email'        => array(
                    'from-name'  => $settings->email->{'from-name'},
                    'from-email' => $settings->email->{'from-email'},
                    'recipients' => $settings->email->{'recipients'},
                    'subject'    => $settings->email->{'subject'},
                    'message'    => $settings->email->{'message'}
                ),
                'integrations' => array(
                    'reCaptcha' => array(
                        'enabled' => $settings->integrations->reCaptcha->{'enabled'},
                        'site-key' => $settings->integrations->reCaptcha->{'site-key'},
                        'secret-key' => $settings->integrations->reCaptcha->{'secret-key'}
                    ),
                    'reCaptchaV2Invisible' => array(
						'enabled' => $settings->integrations->reCaptchaV2Invisible->{'enabled'},
						'site-key' => trim( $settings->integrations->reCaptchaV2Invisible->{'site-key'} ),
						'secret-key' => trim( $settings->integrations->reCaptchaV2Invisible->{'secret-key'} )
					),
                    'facebook' => array(
                        'enabled' => $settings->integrations->facebook->{'enabled'},
                        'app-id'      => $settings->integrations->facebook->{'app-id'},
                    ),
                    'google'   => array(
                        'enabled' => $settings->integrations->google->{'enabled'},
                        'app-id'      => $settings->integrations->google->{'app-id'},
                        'app-secret'  => $settings->integrations->google->{'app-secret'}
                    )
                ),
                'messages' => array(
                    'captcha' => array(
                        'accessibility-alt' => $settings->messages->captcha->{'accessibility-alt'},
                        'accessibility-title' => $settings->messages->captcha->{'accessibility-title'},
                        'accessibility-description' => $settings->messages->captcha->{'accessibility-description'},
                        'explanation' => $settings->messages->captcha->{'explanation'},
                        'refresh-alt' => $settings->messages->captcha->{'refresh-alt'},
                        'refresh-title' => $settings->messages->captcha->{'refresh-title'},
                    ),
                    'buttons'=> array(
                        'anonymous' => $settings->messages->buttons->{'anonymous'},
                        'wordpress' => $settings->messages->buttons->{'wordpress'},
                        'facebook' => $settings->messages->buttons->{'facebook'},
                        'google' => $settings->messages->buttons->{'google'}
                    ),
                    'voting' => array(
                        'poll-ended' => $settings->messages->voting->{'poll-ended'},
                        'poll-not-started' => $settings->messages->voting->{'poll-not-started'},
                        'already-voted-on-poll' => $settings->messages->voting->{'already-voted-on-poll'},
                        'invalid-poll' => $settings->messages->voting->{'invalid-poll'},
                        'no-answers-selected' => $settings->messages->voting->{'no-answers-selected'},
                        'min-answers-required' => $settings->messages->voting->{'min-answers-required'},
                        'max-answers-required' => $settings->messages->voting->{'max-answers-required'},
                        'no-answer-for-other' => $settings->messages->voting->{'no-answer-for-other'},
                        'no-value-for-custom-field' => $settings->messages->voting->{'no-value-for-custom-field'},
                        'consent-not-checked' => $settings->messages->voting->{'consent-not-checked'},
                        'no-captcha-selected' => $settings->messages->voting->{'no-captcha-selected'},
                        'not-allowed-by-ban' => $settings->messages->voting->{'not-allowed-by-ban'},
                        'not-allowed-by-block' => $settings->messages->voting->{'not-allowed-by-block'},
                        'not-allowed-by-limit' => $settings->messages->voting->{'not-allowed-by-limit'},
                        'thank-you' => $settings->messages->voting->{'thank-you'}
                    ),
                    'results' => array(
                        'single-vote' => $settings->messages->results->{'single-vote'},
                        'multiple-votes' => $settings->messages->results->{'multiple-votes'},
                        'single-answer' => $settings->messages->results->{'single-answer'},
                        'multiple-answers' => $settings->messages->results->{'multiple-answers'}
                    )
                )
            );
            update_option('yop_poll_settings', serialize( $yop_poll_settings ) );
            self::$settings = serialize( $yop_poll_settings );
        }
        return array(
            'success' => !self::$errors_present,
            'error' => self::$error_text
        );
    }
}
