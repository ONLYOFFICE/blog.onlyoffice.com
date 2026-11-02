<?php

namespace FluentForm\App\Services\Integrations\Slack;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Modules\Form\FormDataParser;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\App\Services\Integrations\LogResponseTrait;
use FluentForm\Framework\Helpers\ArrayHelper;

class Slack
{
    use LogResponseTrait;

    /**
     * The slack integration settings of the form.
     *
     * @var array $settings
     */
    protected $settings = [];

    /**
     * Handle slack notifier.
     *
     * @param $submissionId
     * @param $formData
     * @param $form
     */
    public static function handle($feed, $formData, $form, $entry)
    {
        $settings = $feed['processedValues'];

        $inputs = FormFieldsParser::getEntryInputs($form);

        $labels = FormFieldsParser::getAdminLabels($form, $inputs);

        $labels = apply_filters_deprecated(
            'fluentform_slack_field_label_selection',
            [
                $labels,
                $settings,
                $form,
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/slack_field_label_selection',
            'Use fluentform/slack_field_label_selection instead of fluentform_slack_field_label_selection.'
        );

        $labels = apply_filters('fluentform/slack_field_label_selection', $labels, $settings, $form);

        foreach ($inputs as $name => $input) {
            if (empty($formData[$name])) {
                continue;
            }
            if ('tabular_grid' == ArrayHelper::get($input, 'element', '')) {
                $formData[$name] = Helper::getTabularGridFormatValue($formData[$name], $input, '<br />', ', ', 'markdown');
            }
        }
        $formData = FormDataParser::parseData((object) $formData, $inputs, $form->id);

        $slackTitle = ArrayHelper::get($settings, 'textTitle');

        if ('' === $slackTitle) {
            $title = 'New submission on ' . $form->title;
        } else {
            $title = $slackTitle;
        }

        $footerText = ArrayHelper::get($settings, 'footerText');
        if ($footerText === '') {
            $footerText = 'fluentform';
        }

        $fields = [];

        foreach ($formData as $attribute => $value) {
            $value = str_replace('<br />', "\n", $value);
            $value = str_replace('&', '&amp;', $value);
            $value = str_replace('<', '&lt;', $value);
            $value = str_replace('>', '&gt;', $value);
            if (! isset($labels[$attribute]) || empty($value)) {
                continue;
            }
            $fields[] = [
                'title' => $labels[$attribute],
                'value' => $value,
                'short' => false,
            ];
        }
        $slackHook = ArrayHelper::get($settings, 'webhook');

        $titleLink = admin_url(
            'admin.php?page=fluent_forms&form_id='
            . $form->id
            . '&route=entries#/entries/'
            . $entry->id
        );

        $body = [
            'payload' => json_encode([
                'attachments' => [
                    [
                        'color'      => '#0078ff',
                        'fallback'   => $title,
                        'title'      => $title,
                        'title_link' => $titleLink,
                        'fields'     => $fields,
                        'footer'     => $footerText,
                        'ts'         => round(microtime(true) * 1000),
                    ],
                ],
            ]),
        ];

        // SECURITY (FINDING-15): the webhook URL is admin-supplied and was fetched with no egress
        // restriction — an SSRF with a logged status/error oracle (the response is written into the
        // feed log). wp_safe_remote_post (below) blocks private/loopback ranges; additionally pin
        // the host, since Slack incoming webhooks are always https://hooks.slack.com/... , so an
        // arbitrary external host cannot be used as the oracle target either.
        // COMPAT: filterable so a site using a Slack-compatible endpoint (Mattermost, Rocket.Chat,
        // an internal relay) can keep an existing feed working without patching. Site PHP only —
        // a form manager cannot reach it, so the default-deny posture is preserved.
        $defaultHookHosts = ['hooks.slack.com'];
        $allowedHookHosts = (array) apply_filters('fluentform/slack_allowed_webhook_hosts', $defaultHookHosts, $feed);
        $allowedHookHosts = array_map('strtolower', array_filter($allowedHookHosts, 'is_string'));
        // A filter returning nothing usable must not silently break every Slack feed — fall back
        // to the official host so the default keeps working no matter what the filter returns.
        if (!$allowedHookHosts) {
            $allowedHookHosts = $defaultHookHosts;
        }

        $parsedHook = wp_parse_url($slackHook);
        $hookHost = isset($parsedHook['host']) ? strtolower($parsedHook['host']) : '';
        $hookScheme = isset($parsedHook['scheme']) ? strtolower($parsedHook['scheme']) : '';
        if ('https' !== $hookScheme || !in_array($hookHost, $allowedHookHosts, true)) {
            $message = sprintf(
                // translators: %s is a comma-separated list of allowed webhook hosts.
                __('Invalid Slack webhook URL. It must be an https:// URL on: %s', 'fluentform'),
                implode(', ', $allowedHookHosts)
            );
            do_action('fluentform/integration_action_result', $feed, 'failed', $message);
            return [
                'status'  => 'failed',
                'message' => $message,
            ];
        }

        $result = wp_safe_remote_post($slackHook, [
            'method'      => 'POST',
            'timeout'     => 30,
            'redirection' => 5,
            'httpversion' => '1.0',
            'headers'     => [],
            'body'        => $body,
            'cookies'     => [],
        ]);

        if (is_wp_error($result)) {
            $status = 'failed';
            $message = $result->get_error_message();
        } else {
            $message = $result['response'];
            $status = 200 == $result['response']['code'] ? 'success' : 'failed';
        }

        if ('failed' == $status) {
            do_action('fluentform/integration_action_result', $feed, 'failed', $message);
        } else {
            do_action('fluentform/integration_action_result', $feed, 'success', 'Submission notification has been successfully delivered to slack channel');
        }

        return [
            'status'  => $status,
            'message' => $message,
        ];
    }
}
