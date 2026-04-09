<?php

namespace FluentForm\App\Modules\Form;

use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper;

class HoneyPot
{
    private $app;

    public function __construct(Application $application)
    {
        $this->app = $application;
    }

    public function renderHoneyPot($form)
    {
        if (!$this->isEnabled($form->id)) {
            return;
        }

        $fieldName = $this->getFieldName($form->id);
        $fieldId = 'ff_' . $form->id . '_item_sf' ;
        $labels = ['Newsletter', 'Updates', 'Contact', 'Subscribe', 'Notify'];
        $randomLabel = $labels[array_rand($labels)];
        ?>
        <div
                style="display: none!important; position: absolute!important; transform: translateX(1000%)!important;"
                class="ff-el-group ff-hpsf-container"
        >
            <div class="ff-el-input--label asterisk-right">
                <label for="<?php echo esc_attr($fieldId); ?>" aria-label="<?php echo esc_attr($randomLabel); ?>">
                    <?php echo esc_html($randomLabel); ?>
                </label>
            </div>
            <div class="ff-el-input--content">
                <input type="text"
                       name="<?php echo esc_attr($fieldName); ?>"
                       class="ff-el-form-control"
                       id="<?php echo esc_attr($fieldId); ?>"
                />
            </div>
        </div>
        <?php
    }

    public function verify($insertData, $requestData, $formId)
    {
        if (!$this->isEnabled($formId) || (
                Helper::isConversionForm($formId) &&
                ArrayHelper::isTrue($requestData, 'isFFConversational')
            )) {
            return;
        }

        $honeyPotName = $this->getFieldName($formId);

        if (
                !ArrayHelper::exists($requestData, $honeyPotName) ||
                !empty(ArrayHelper::get($requestData, $honeyPotName))
        ) {
            wp_send_json(
                [
                    'errors' => __('Sorry! You can not submit this form at this moment!', 'fluentform'),
                ],
                422
            );
        }
        return;
    }

    public function isEnabled($formId = false)
    {
        $option = get_option('_fluentform_global_form_settings');
        $status = 'yes' == ArrayHelper::get($option, 'misc.honeypotStatus');
        return apply_filters('fluentform/honeypot_status', $status, $formId);
    }

    private function getFieldName($formId)
    {
        $honeyPotName = 'item_' . $formId . '__fluent_sf';
        return apply_filters('fluentform/honeypot_name', $honeyPotName, $formId);
    }
}
