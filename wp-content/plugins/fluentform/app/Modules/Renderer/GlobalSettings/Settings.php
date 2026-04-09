<?php

namespace FluentForm\App\Modules\Renderer\GlobalSettings;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Modules\Form\AkismetHandler;
use FluentForm\App\Modules\Form\CleanTalkHandler;
use FluentForm\App\Modules\Registerer\TranslationString;
use FluentForm\Framework\Foundation\Application;

class Settings
{
    /**
     * App instance
     *
     * @var \FluentForm\Framework\Foundation\Application
     */
    protected $app;

    /**
     * GlobalSettings constructor.
     *
     * @param \FluentForm\Framework\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Render the page for native global settings components
     *
     * @throws \Exception
     */
    public function render()
    {
        $this->enqueue();

        $this->app->view->render('admin.globalSettings.settings');
    }

    /**
     * Enqueue necessary resources.
     *
     * @throws \Exception
     */
    public function enqueue()
    {
        wp_enqueue_script('fluentform-global-settings-js');
        
        $globalSettingAppData = [
            'plugin'                => $this->app->config->get('app.slug'),
            'akismet_activated'     => AkismetHandler::isPluginEnabled(),
            'cleantalk_activated'   => CleanTalkHandler::isPluginEnabled(),
            'has_pro'               => Helper::hasPro(),
            'is_payment_compatible' => Helper::isPaymentCompatible(),
            'form_settings_str'     => TranslationString::getGlobalSettingsI18n(),
            'ace_path_url'          => fluentformMix('libs/ace'),
        ];
        if (Helper::isPaymentCompatible()) {
            $globalSettingAppData = apply_filters('fluentform/global_settings_component_settings_data', $globalSettingAppData);
        }
        wp_localize_script('fluentform-global-settings-js', 'FluentFormApp', $globalSettingAppData);
    }
}
