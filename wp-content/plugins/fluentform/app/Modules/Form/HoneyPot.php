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
        // SECURITY (FINDING-25): do NOT skip the check for conversational forms based on the
        // client-supplied isFFConversational flag. The conversational renderer now injects the
        // honeypot field (empty) into the submission (getConversationalHoneypotInput via
        // extra_inputs), so the "present and empty" check passes for legitimate conversational
        // submissions and the control can no longer be bypassed with a single flag.
        if (!$this->isEnabled($formId)) {
            return;
        }

        $honeyPotName = $this->getFieldName($formId);

        if (
                !ArrayHelper::exists($requestData, $honeyPotName) ||
                !empty(ArrayHelper::get($requestData, $honeyPotName))
        ) {
            $message = apply_filters(
                'fluentform/honeypot_spam_message',
                __('Sorry! You can not submit this form at this moment!', 'fluentform'),
                $formId
            );
            wp_send_json(['errors' => $message], 422);
        }
        return;
    }

    public function isEnabled($formId = false)
    {
        $option = get_option('_fluentform_global_form_settings');
        $status = 'yes' == ArrayHelper::get($option, 'misc.honeypotStatus');
        return apply_filters('fluentform/honeypot_status', $status, $formId);
    }

    /**
     * SECURITY (FINDING-25): the conversational form is a JS app that never renders the DOM
     * honeypot field, so verify() previously had to be skipped for it (via the client-controlled
     * isFFConversational flag). Return the honeypot field pre-filled EMPTY so the conversational
     * JS carries it in the submission and the "present and empty" check passes for a legitimate
     * submission while the control is enforced server-side rather than bypassable by a flag.
     *
     * @param int $formId
     * @return array
     */
    public function getConversationalHoneypotInput($formId)
    {
        if (!$this->isEnabled($formId)) {
            return [];
        }

        return [$this->getFieldName($formId) => ''];
    }

    private function getFieldName($formId)
    {
        $honeyPotName = 'item_' . $formId . '__fluent_sf';
        return apply_filters('fluentform/honeypot_name', $honeyPotName, $formId);
    }
}
