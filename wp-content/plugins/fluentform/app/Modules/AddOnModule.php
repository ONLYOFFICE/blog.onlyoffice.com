<?php

namespace FluentForm\App\Modules;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Modules\Registerer\TranslationString;

class AddOnModule
{
    /**
     * The number of days we'll cached the add-ons got from remote server.
     *
     * @var integer
     */
    protected $cacheDays = 1;

    /**
     * The URL to fetch the add-ons
     *
     * @var string
     */
    protected $addOnsFetchUrl = 'https://wpmanageninja.com/add-ons.json';

    /**
     * Render the add-ons list page.
     */
    public function render()
    {
        $extraMenus = [];
    
        $extraMenus = apply_filters_deprecated(
            'fluentform_addons_extra_menu',
            [
                $extraMenus
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/addons_extra_menu',
            'Use fluentform/addons_extra_menu instead of fluentform_addons_extra_menu'
        );

        // Add suggested plugins tab
        $extraMenus['suggested_plugins'] = __('Suggested Plugins', 'fluentform');

        $extraMenus = apply_filters('fluentform/addons_extra_menu', $extraMenus);

        $current_menu_item = 'fluentform_add_ons';

        $subPage = wpFluentForm('request')->get('sub_page');

        if ($subPage) {
            $current_menu_item = sanitize_key($subPage);
        }

        wpFluentForm('view')->render('admin.addons.index', [
            'hasPro'            => defined('FLUENTFORMPRO'),
            'menus'             => $extraMenus,
            'base_url'          => admin_url('admin.php?page=fluent_forms_add_ons'),
            'current_menu_item' => $current_menu_item,
        ]);
    }

    /**
     * Show the add-ons list.
     */
    public function showFluentAddOns()
    {
        wp_enqueue_script('fluentform-modules');
    
        $addOns = apply_filters_deprecated(
            'fluentform_global_addons',
            [
                []
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/global_addons',
            'Use fluentform/global_addons instead of fluentform_global_addons'
        );
        $addOns = apply_filters('fluentform/global_addons', $addOns);

        $addOns['slack'] = [
            'title'       => __('Slack', 'fluentform'),
            'description' => __('Get realtime notification in slack channel when a new submission will be added.', 'fluentform'),
            'logo'        => fluentFormMix('img/integrations/slack.png'),
            'enabled'     => Helper::isSlackEnabled() ? 'yes' : 'no',
            'config_url'  => '',
            'category'    => 'crm',
        ];

        if (!defined('FLUENTFORMPRO')) {
            $addOns = array_merge($addOns, $this->getPremiumAddOns());
        }

        wp_localize_script('fluentform-modules', 'fluent_addon_modules', [
            'addons'          => $addOns,
            'has_pro'         => defined('FLUENTFORMPRO'),
            'addOnModule_str' => TranslationString::getAddOnModuleI18n(),
        ]);

        wpFluentForm('view')->render('admin.addons.list', []);
    }
    
    public function getPremiumAddOns()
    {
        $purchaseUrl = fluentform_upgrade_url();
        return [
            'paypal' => [
                'title'        => __('PayPal', 'fluentform'),
                'description'  => __('Accept Payments via paypal as a part of your form submission', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/paypal.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'payment',
            ],
            'stripe' => [
                'title'        => __('Stripe', 'fluentform'),
                'description'  => __('Accept Payments via stripe as a part of your form submission', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/stripe.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'payment',
            ],
            'UserRegistration' => [
                'title'        => __('User Registration', 'fluentform'),
                'description'  => __('Create WordPress user when when a form is submitted', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/user_registration.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'wp_core',
            ],
            'PostFeeds' => [
                'title'        => __('Advanced Post/CPT Creation', 'fluentform'),
                'description'  => __('Create post/any cpt on form submission. It will enable many new features including dedicated post fields.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/post-creation.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'wp_core',
            ],
            'sharePages' => [
                'title'        => __('Landing Pages', 'fluentform'),
                'description'  => __('Create completely custom "distraction-free" form landing pages to boost conversions', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/landing_pages.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'wp_core',
            ],
            'webhook' => [
                'title'        => __('WebHooks', 'fluentform'),
                'description'  => __('Broadcast your Fluent Forms Submission to any web api endpoint with the powerful webhook module.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/webhook.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'zapier' => [
                'title'        => __('Zapier', 'fluentform'),
                'description'  => __('Connect your Fluent Forms data with Zapier and push data to thousands of online softwares.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/zapier.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'trello' => [
                'title'        => __('Trello', 'fluentform'),
                'description'  => __('Fluent Forms Trello Module allows you to create Trello card from submiting forms.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/trello.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'google_sheet' => [
                'title'        => __('Google Sheet', 'fluentform'),
                'description'  => __('Add Fluent Forms Submission to Google sheets when a form is submitted.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/google-sheets.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'activecampaign' => [
                'title'        => __('ActiveCampaign', 'fluentform'),
                'description'  => __('Fluent Forms ActiveCampaign Module allows you to create ActiveCampaign list signup forms in WordPress, so you can grow your email list.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/activecampaign.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'campaign_monitor' => [
                'title'        => __('Campaign Monitor', 'fluentform'),
                'description'  => __('Fluent Forms Campaign Monitor module allows you to create Campaign Monitor newsletter signup forms in WordPress, so you can grow your email list.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/campaignmonitor.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'constatantcontact' => [
                'title'        => __('Constant Contact', 'fluentform'),
                'description'  => __('Connect Constant Contact with Fluent Forms and create subscriptions forms right into WordPress and grow your list.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/constantcontact.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'convertkit' => [
                'title'        => __('ConvertKit', 'fluentform'),
                'description'  => __('Connect ConvertKit with Fluent Forms and create subscription forms right into WordPress and grow your list.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/convertkit.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'getresponse' => [
                'title'        => __('GetResponse', 'fluentform'),
                'description'  => __('Fluent Forms GetResponse module allows you to create GetResponse newsletter signup forms in WordPress, so you can grow your email list.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/getresponse.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'hubspot' => [
                'title'        => __('Hubspot', 'fluentform'),
                'description'  => __('Connect HubSpot with Fluent Forms and subscribe a contact when a form is submitted.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/hubspot.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'icontact' => [
                'title'        => __('iContact', 'fluentform'),
                'description'  => __('Connect iContact with Fluent Forms and subscribe a contact when a form is submitted.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/icontact.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'platformly' => [
                'title'        => __('Platformly', 'fluentform'),
                'description'  => __('Connect Platform.ly with Fluent Forms and subscribe a contact when a form is submitted.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/platformly.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'moosend' => [
                'title'        => __('MooSend', 'fluentform'),
                'description'  => __('Connect MooSend with Fluent Forms and subscribe a contact when a form is submitted.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/moosend_logo.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'sendfox' => [
                'title'        => __('SendFox', 'fluentform'),
                'description'  => __('Connect SendFox with Fluent Forms and subscribe a contact when a form is submitted.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/sendfox.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'mailerlite' => [
                'title'        => __('MailerLite', 'fluentform'),
                'description'  => __('Connect your Fluent Forms with MailerLite and add subscribers easily.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/mailerlite.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'sms_notifications' => [
                'title'        => __('SMS Notification', 'fluentform'),
                'description'  => __('Send SMS in real time when a form is submitted with Twilio.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/twilio.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'get_gist' => [
                'title'        => __('Gist', 'fluentform'),
                'description'  => __('GetGist is Easy to use all-in-one software for live chat, email marketing automation, forms, knowledge base, and more for a complete 360° view of your contacts.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/getgist.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'sendinblue' => [
                'title'        => __('Brevo (formerly SendInBlue)', 'fluentform'),
                'description'  => __('Fluent Forms Brevo (formerly SendInBlue) Module allows you to create contacts on your list, so you can grow your email list.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/brevo.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'drip' => [
                'title'        => __('Drip', 'fluentform'),
                'description'  => __('Fluent Forms Drip Module allows you to create contacts on your Drip list, so you can grow your email list.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/drip.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'discord' => [
                'title'        => __('Discord', 'fluentform'),
                'description'  => __('Send notification with form data to your Discord channel when a form is submitted', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/discord.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'telegram' => [
                'title'        => __('Telegram', 'fluentform'),
                'description'  => __('Send notification to Telegram channel or group when a form is submitted', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/telegram.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'affiliateWp' => [
                'title'        => __('AffiliateWP', 'fluentform'),
                'description'  => __('Generate AffiliateWP referrals automatically when a customer is referred to your site via an affiliate link', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/affiliatewp.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'clicksend_sms_notification' => [
                'title'        => __('ClickSend', 'fluentform'),
                'description'  => __('Send SMS in real time when a form is submitted with ClickSend', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/clicksend.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'zohocrm' => [
                'title'        => __('Zoho CRM', 'fluentform'),
                'description'  => __('Zoho CRM is an online Sales CRM software that manages your sales, marketing and support in one CRM platform.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/zohocrm.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'cleverreach' => [
                'title'        => __('CleverReach', 'fluentform'),
                'description'  => __('CleverReach is web-based email marketing software for managing email campaigns and contacts. Use Fluent Forms to grow your CleverReach subscriber list', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/clever_reach.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'salesflare' => [
                'title'        => __('Salesflare', 'fluentform'),
                'description'  => __('Create Salesflare contact from WordPress, so you can grow your contact list', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/salesflare.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'automizy' => [
                'title'        => __('Automizy', 'fluentform'),
                'description'  => __('Connect Automizy with Fluent Forms and subscribe a contact when a form is submitted.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/automizy.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'salesforce' => [
                'title'        => __('Salesforce', 'fluentform'),
                'description'  => __('Salesforce helps your marketing, sales, commerce, service and IT teams work as one from anywhere — so you can keep your customers happy everywhere.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/salesforce.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'airtable' => [
                'title'        => __('Airtable', 'fluentform'),
                'description'  => __('Airtable is a low-code platform for building collaborative apps. Customize your workflow, collaborate, and achieve ambitious outcomes.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/airtable.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'mailjet' => [
                'title'        => __('Mailjet', 'fluentform'),
                'description'  => __('Mailjet is an easy-to-use all-in-one e-mail platform.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/mailjet.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
            'quiz_addon' => [
                'title'        => __('Quiz Module', 'fluentform'),
                'description'  => __('With this module, you can create quizzes and show scores with grades, points, fractions, or percentages', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/quiz-icon.svg'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'wp_core',
            ],
            'notion' => [
                'title'        => __('Notion', 'fluentform'),
                'description'  => __('Capture Fluent Forms Submission to your Notion workspaces — and do it exactly the way you want.', 'fluentform'),
                'logo'         => fluentFormMix('img/integrations/notion.png'),
                'enabled'      => 'no',
                'purchase_url' => $purchaseUrl,
                'category'     => 'crm',
            ],
        ];
    }

    /**
     * Show the suggested plugins list.
     */
    public function showSuggestedPlugins()
    {
        wp_enqueue_script('fluentform-modules');

        $suggestedPlugins = $this->getSuggestedPlugins();

        wp_localize_script('fluentform-modules', 'fluent_suggested_plugins', [
            'plugins' => $suggestedPlugins,
            'nonce' => wp_create_nonce('fluent_forms_suggested_plugins'),
            'assets_url' => fluentFormMix('img/suggested-plugins/'),
        ]);

        wpFluentForm('view')->render('admin.addons.suggested_plugins', []);
    }

    /**
     * Get the list of suggested WordPress plugins.
     *
     * @return array
     */
    public function getSuggestedPlugins()
    {
        $plugins = [
            'fluent-cart' => [
                'title'       => __('FluentCart A New Era of eCommerce', 'fluentform'),
                'description' => __('It is a performance-first, self-hosted eCommerce platform for WordPres', 'fluentform'),
                'logo'        => 'fcart.svg',
                'slug'        => 'fluent-cart/fluent-cart.php',
                'basename'    => 'fluent-cart',
                'badge_type'  => 'official',
                'wporg_url'   => 'https://wordpress.org/plugins/fluent-cart/',
            ],
            'multilingual-forms-fluent-forms-wpml' => [
                'title'       => __('Multilingual Forms for Fluent Forms (WPML)', 'fluentform'),
                'description' => __('Make Fluent Forms multilingual with WPML integration', 'fluentform'),
                'logo'        => 'wpml-ff.png',
                'slug'        => 'multilingual-forms-fluent-forms-wpml/multilingual-forms-fluent-forms-wpml.php',
                'basename'    => 'multilingual-forms-fluent-forms-wpml',
                'badge_type'  => 'official',
                'wporg_url'   => 'https://wordpress.org/plugins/multilingual-forms-fluent-forms-wpml/',
            ],
            'cloud-storage-manager'                => [
                'title'       => __('Cloud Storage Manager for Fluent Forms', 'fluentform'),
                'description' => __('Cloud Storage Manager bridges the gap between your fluent forms and popular cloud storage platforms.',
                    'fluentform'),
                'logo'        => 'cloud-storage-manager.png',
                'slug'        => 'cloud-storage-manager/cloud-storage-manager.php',
                'basename'    => 'cloud-storage-manager',
                'badge_type'  => 'verified',
                'wporg_url'   => 'https://wordpress.org/plugins/cloud-storage-manager/',
            ],
            'fluent-pdf'                           => [
                'title'       => __('Fluent Forms PDF', 'fluentform'),
                'description' => __('Generate PDF from Fluent Forms Submissions and Send via Email or Download',
                    'fluentform'),
                'logo'        => 'fluent-pdf.svg',
                'slug'        => 'fluentforms-pdf/fluentforms-pdf.php',
                'basename'    => 'fluentforms-pdf',
                'badge_type'  => 'official',
                'wporg_url'   => 'https://wordpress.org/plugins/fluentforms-pdf/',
            ],
            'fluent-community'                     => [
                'title'       => __('Fluent Community', 'fluentform'),
                'description' => __('Build Your Own Community & Membership Site with Fluent Community', 'fluentform'),
                'logo'        => 'fluent-community.svg',
                'slug'        => 'fluent-community/fluent-community.php',
                'basename'    => 'fluent-community',
                'badge_type'  => 'official',
                'wporg_url'   => 'https://wordpress.org/plugins/fluent-community/',
            ],
            'fluent-support'                       => [
                'title'       => __('Fluent Support', 'fluentform'),
                'description' => __('WordPress Helpdesk and Customer Support Ticket Plugin', 'fluentform'),
                'logo'        => 'fluent-support.svg',
                'slug'        => 'fluent-support/fluent-support.php',
                'basename'    => 'fluent-support',
                'badge_type'  => 'official',
                'wporg_url'   => 'https://wordpress.org/plugins/fluent-support/',
            ],
            'wp-social-reviews'                    => [
                'title'       => __('WP Social Ninja', 'fluentform'),
                'description' => __('Best Social Media Plugin for WordPress to Showcase Social Feeds, Reviews, and Chat Widgets',
                    'fluentform'),
                'logo'        => 'wp-social-reviews.gif',
                'slug'        => 'wp-social-reviews/wp-social-reviews.php',
                'basename'    => 'wp-social-reviews',
                'badge_type'  => 'official',
                'wporg_url'   => 'https://wordpress.org/plugins/wp-social-reviews/',
            ],
            'fluent-crm'                           => [
                'title'       => __('FluentCRM', 'fluentform'),
                'description' => __('Email Marketing Automation and CRM Plugin for WordPress', 'fluentform'),
                'logo'        => 'fluent-crm.svg',
                'slug'        => 'fluent-crm/fluent-crm.php',
                'basename'    => 'fluent-crm',
                'badge_type'  => 'official',
                'wporg_url'   => 'https://wordpress.org/plugins/fluent-crm/',
            ],
        ];

        // Add status to each plugin
        foreach ($plugins as $key => &$plugin) {
            $plugin['status'] = $this->getPluginStatus($plugin['slug']);
        }

        return $plugins;
    }

    /**
     * Get the installation and activation status of a plugin.
     *
     * @param string $pluginSlug The plugin slug (e.g., 'fluent-crm/fluent-crm.php')
     * @return string 'active', 'inactive', or 'not_installed'
     */
    private function getPluginStatus($pluginSlug)
    {
        // Check if plugin file exists
        $pluginFile = WP_PLUGIN_DIR . '/' . $pluginSlug;
        
        if (!file_exists($pluginFile)) {
            return 'not_installed';
        }

        // Ensure is_plugin_active function is available
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        // Check if plugin is active
        if (is_plugin_active($pluginSlug)) {
            return 'active';
        }

        return 'inactive';
    }
}
