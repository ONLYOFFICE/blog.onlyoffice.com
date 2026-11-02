<?php

namespace FluentForm\App\Modules\MCP\Tools;

defined('ABSPATH') || exit;

use FluentForm\App\Modules\MCP\Support\FormAccess;
use FluentForm\App\Modules\MCP\Support\MCPHelper;
use FluentForm\App\Services\Integrations\FormIntegrationService;
use FluentForm\Framework\Support\Arr;

/**
 * Integration tools (read).
 *
 * The list-integrations tool reports the integration feeds configured on one
 * form (provider, name, enabled) so an agent can answer "where do this form's
 * entries go?". It reuses FormIntegrationService::get(), the same source the admin
 * integrations screen reads, and is form-scoped.
 */
class IntegrationTools
{
    public static function definitions()
    {
        return [
            'fluentform/list-integrations' => [
                'label'       => __('List Integrations', 'fluentform'),
                'group'       => __('Integrations', 'fluentform'),
                'description' => __('List the integration feeds configured on one form (provider, name, enabled state) — the notifications and third-party connections that fire when the form is submitted. Requires form_id.', 'fluentform'),
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'form_id' => ['type' => 'integer'],
                    ],
                    'required' => ['form_id'],
                ],
                'execute_callback'    => [self::class, 'listIntegrations'],
                'capability'          => ['fluentform_forms_manager', 'fluentform_settings_manager', 'fluentform_dashboard_access'],
                'annotations' => ['readonly' => true],
            ],
        ];
    }

    public static function listIntegrations($params = [])
    {
        $form = FormAccess::resolveForm($params);
        if (is_wp_error($form)) {
            return $form;
        }
        $formId = (int) $form->id;

        // FormIntegrationService::get() returns ['feeds' => [...], ...]; the
        // configured feeds live under the 'feeds' key, not at the top level.
        $result = (new FormIntegrationService())->get($formId);
        $feeds  = (isset($result['feeds']) && is_array($result['feeds'])) ? $result['feeds'] : [];

        $rows = [];
        foreach ($feeds as $feed) {
            if (!is_array($feed)) {
                continue;
            }
            $rows[] = [
                'id'       => isset($feed['id']) ? (int) $feed['id'] : null,
                'name'     => Arr::get($feed, 'name'),
                'provider' => Arr::get($feed, 'provider'),
                'enabled'  => !empty($feed['enabled']),
            ];
        }

        return MCPHelper::envelope(
            sprintf(
                /* translators: %d: number of integration feeds */
                _n('%d integration feed configured.', '%d integration feeds configured.', count($rows), 'fluentform'),
                count($rows)
            ),
            ['form_id' => $formId, 'integrations' => $rows]
        );
    }
}
