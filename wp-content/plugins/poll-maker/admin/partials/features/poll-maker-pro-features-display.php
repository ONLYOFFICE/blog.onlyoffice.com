<div class="wrap">
    <h1 class="wp-heading-inline">
		<?php echo __( esc_html( get_admin_page_title() ), $this->plugin_name ); ?>
    </h1>

    <div class="ays-poll-features-wrap">
        <div class="comparison">
            <table>
                <thead>
                <tr>
                    <th class="tl tl2"></th>
                    <th class="product" style="background:#69C7F1; border-top-left-radius: 5px; border-left:0px;">
                            <span style="display: block">
                                <?php echo __( 'Personal', $this->plugin_name ); ?></span>
                        <img src="<?php echo POLL_MAKER_AYS_ADMIN_URL . '/images/avatars/personal_avatar.png'; ?>"
                             alt="Free" title="Free" width="100"/>
                    </th>
                    <th class="product" style="background:#69C7F1;">
                            <span style="display: block">
                                <?php echo __( 'Business', $this->plugin_name ); ?></span>
                        <img src="<?php echo POLL_MAKER_AYS_ADMIN_URL . '/images/avatars/business_avatar.png'; ?>"
                             alt="Business" title="Business" width="100"/>
                    </th>
                    <th class="product" style="border-top-right-radius: 5px; border-right:0px; background:#69C7F1;">
                            <span style="display: block">
                                <?php echo __( 'Developer', $this->plugin_name ); ?></span>
                        <img src="<?php echo POLL_MAKER_AYS_ADMIN_URL . '/images/avatars/pro_avatar.png'; ?>"
                             alt="Developer"
                             title="Developer" width="100"/>
                    </th>
                </tr>
                <tr>
                    <th></th>
                    <th class="price-info">
                        <div class="price-now"><span>
                                    <?php echo __( 'Free', $this->plugin_name ); ?></span></div>
                    </th>
                    <th class="price-info">
                        <div class="price-now"><span>$29</span>
                        </div>
                    </th>
                    <th class="price-info">
                        <div class="price-now"><span>$89</span>
                        </div>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td></td>
                    <td colspan="4">
						<?php echo __( 'Support for', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
						<?php echo __( 'Support for', $this->plugin_name ); ?>
                    </td>
                    <td>
						<?php echo __( '1 site', $this->plugin_name ); ?>
                    </td>
                    <td>
						<?php echo __( '5 sites', $this->plugin_name ); ?>
                    </td>
                    <td>
						<?php echo __( 'Unlimited sites', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr>
                    <td> </td>
                    <td colspan="3">
		                <?php echo __( 'Upgrade for', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
		                <?php echo __( 'Upgrade for', $this->plugin_name ); ?>
                    </td>
                    <td>
		                <?php echo __( '3 months', $this->plugin_name ); ?>
                    </td>
                    <td>
		                <?php echo __( '12 months', $this->plugin_name ); ?>
                    </td>
                    <td>
		                <?php echo __( 'Lifetime', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
		                <?php echo __( 'Support for', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
		                <?php echo __( 'Support for', $this->plugin_name ); ?>
                    </td>
                    <td>
		                <?php echo __( '3 months', $this->plugin_name ); ?>
                    </td>
                    <td>
		                <?php echo __( '12 months', $this->plugin_name ); ?>
                    </td>
                    <td>
		                <?php echo __( 'Lifetime', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr>
                    <td> </td>
                    <td colspan="3">
						<?php echo __( 'Install on unlimited sites', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr>
                    <td>
						<?php echo __( 'Install on unlimited sites', $this->plugin_name ); ?>
                    </td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></i></td>
                </tr>
                <tr>
                    <td> </td>
                    <td colspan="3">
						<?php echo __( 'Reports in dashboard', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr>
                    <td>
						<?php echo __( 'Reports in dashboard', $this->plugin_name ); ?>
                    </td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="3">
                        <?php echo __( 'Allow multivote', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo __( 'Allow multivote', $this->plugin_name ); ?>
                    </td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td> </td>
                    <td colspan="3">
		                <?php echo __( 'VS type of poll (versus)', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
		                <?php echo __( 'VS type of poll (versus)', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></i></td>
                </tr>
                <tr>
                    <td> </td>
                    <td colspan="3">
		                <?php echo __( 'Ability to add custom option', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
		                <?php echo __( 'Ability to add custom option', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
						<?php echo __( 'Extra 4 themes', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
						<?php echo __( 'Extra 4 themes', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
						<?php echo __( 'Results with charts', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
						<?php echo __( 'Results with charts', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
						<?php echo __( 'Export results to CSV', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
						<?php echo __( 'Export results to CSV', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
						<?php echo __( 'Custom Form Fields', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
						<?php echo __( 'Custom Form Fields', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
		                <?php echo __( 'Vote reason option', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
		                <?php echo __( 'Vote reason option', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
		                <?php echo __( 'Text instead of results', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
		                <?php echo __( 'Text instead of results', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
						<?php echo __( 'Import/Export polls', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
						<?php echo __( 'Import/Export polls', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
						<?php echo __( 'Mailchimp integration', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
						<?php echo __( 'Mailchimp integration', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <?php echo __( 'Campaign Monitor integration', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
                        <?php echo __( 'Campaign Monitor integration', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <?php echo __( 'Zapier integration', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
                        <?php echo __( 'Zapier integration', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <?php echo __( 'ActiveCampaign integration', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
                        <?php echo __( 'ActiveCampaign integration', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <?php echo __( 'Slack integration', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
                        <?php echo __( 'Slack integration', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <?php echo __( 'Anonymous poll', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
                        <?php echo __( 'Anonymous poll', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <?php echo __( 'Password protected poll', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
                        <?php echo __( 'Password protected poll', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <?php echo __( 'Vote session', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
                        <?php echo __( 'Vote session', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <?php echo __( 'Send mail to user', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
                        <?php echo __( 'Send mail to user', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <?php echo __( 'Messages based on answers', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
                        <?php echo __( 'Messages based on answers', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <?php echo __( 'Display category shortcode', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
                        <?php echo __( 'Display category shortcode', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <?php echo __( 'User History shortcode', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
                        <?php echo __( 'User History shortcode', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <?php echo __( 'Leaderboard', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
                        <?php echo __( 'Leaderboard', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <?php echo __( 'Poll creation by user', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
                        <?php echo __( 'Poll creation by user', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <?php echo __( 'SendGrid integration', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
                        <?php echo __( 'SendGrid integration', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <?php echo __( 'GamiPress integration', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
                        <?php echo __( 'GamiPress integration', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <?php echo __( 'Mad Mimi integration', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
                        <?php echo __( 'Mad Mimi integration', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <?php echo __( 'ConvertKit integration', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
                        <?php echo __( 'ConvertKit integration', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <?php echo __( 'GetResponse integration', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
                        <?php echo __( 'GetResponse integration', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <?php echo __( 'Google sheet integration', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr class="compare-row">
                    <td>
                        <?php echo __( 'Google sheet integration', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td> </td>
                </tr>
                <tr class="compare-row">
                    <td>
                        <?php echo __( 'Summary emails', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td> </td>
                </tr>
                <tr class="compare-row">
                    <td>
                        <?php echo __( 'Copy protection', $this->plugin_name ); ?>
                    </td>
                    <td><span>–</span></td>
                    <td><span>–</span></td>
                    <td><i class="ays_poll_fas ays_poll_fa-check"></i></td>
                </tr>
                <tr>
                    <td> </td>
                </tr>
                <tr>
                    <td></td>
                    <td><a href="https://wordpress.org/plugins/poll-maker/" class="price-buy">
							<?php echo __( 'Download', $this->plugin_name ); ?><span class="hide-mobile"></span></a>
                    </td>
                    <td><a href="https://ays-pro.com/index.php/wordpress/poll-maker" class="price-buy">
							<?php echo __( 'Buy now', $this->plugin_name ); ?><span class="hide-mobile"></span></a></td>
                    <td><a href="https://ays-pro.com/index.php/wordpress/poll-maker" class="price-buy">
							<?php echo __( 'Buy now', $this->plugin_name ); ?><span class="hide-mobile"></span></a></td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="ays-poll-row">
            <div class="ays-poll-col-4">
                <a href="<?php echo POLL_MAKER_AYS_ADMIN_URL . '/images/features/screenshot-3.png'; ?>"
                   class="open-lightbox">
                    <img src="<?php echo POLL_MAKER_AYS_ADMIN_URL . '/images/features/screenshot-3.png'; ?>"
                         width="100%"
                         alt="Choosing poll" title="Choosing poll"/>
                </a>
            </div>
            <div class="ays-poll-col-4">
                <a href="<?php echo POLL_MAKER_AYS_ADMIN_URL . '/images/features/screenshot-2.png'; ?>"
                   class="open-lightbox">
                    <img src="<?php echo POLL_MAKER_AYS_ADMIN_URL . '/images/features/screenshot-2.png'; ?>"
                         width="100%"
                         alt="Choosing poll 2" title="Choosing poll 2"/>
                </a>
            </div>
            <div class="ays-poll-col-4">
                <a href="<?php echo POLL_MAKER_AYS_ADMIN_URL . '/images/features/Screenshot2.png'; ?>"
                   class="open-lightbox">
                    <img src="<?php echo POLL_MAKER_AYS_ADMIN_URL . '/images/features/Screenshot2.png'; ?>"
                         width="100%"
                         alt="Choosing poll 2" title="Choosing poll 2"/>
                </a>
            </div>
            <div class="ays-poll-col-4">
                <a href="<?php echo POLL_MAKER_AYS_ADMIN_URL . '/images/features/screenshot-4.png'; ?>"
                   class="open-lightbox">
                    <img src="<?php echo POLL_MAKER_AYS_ADMIN_URL . '/images/features/screenshot-4.png'; ?>"
                         width="100%"
                         alt="PRO Feature 3" title="PRO Feature 3"/>
                </a>
            </div>
            <div class="ays-poll-col-4">
                <a href="<?php echo POLL_MAKER_AYS_ADMIN_URL . '/images/features/screenshot-7.jpg'; ?>"
                   class="open-lightbox">
                    <img src="<?php echo POLL_MAKER_AYS_ADMIN_URL . '/images/features/screenshot-7.jpg'; ?>"
                         width="100%"
                         alt="PRO Feature 4" title="PRO Feature 4"/>
                </a>
            </div>
            <div class="ays-poll-col-4">
                <a href="<?php echo POLL_MAKER_AYS_ADMIN_URL . '/images/features/screenshot-8.jpg'; ?>"
                   class="open-lightbox">
                    <img src="<?php echo POLL_MAKER_AYS_ADMIN_URL . '/images/features/screenshot-8.jpg'; ?>"
                         width="100%"
                         alt="PRO Feature 4" title="PRO Feature 4"/>
                </a>
            </div>
        </div>
    </div>
</div>