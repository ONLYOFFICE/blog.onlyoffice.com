<?php

namespace FluentForm\App\Modules\MCP\Tools;

defined('ABSPATH') || exit;

use FluentForm\App\Models\Form;
use FluentForm\App\Modules\MCP\Support\ErrorCodes;
use FluentForm\App\Modules\MCP\Support\FormAccess;
use FluentForm\App\Modules\MCP\Support\FormCreator;
use FluentForm\App\Modules\MCP\Support\MCPHelper;
use FluentForm\App\Modules\MCP\Support\Mutation;
use FluentForm\App\Modules\MCP\Support\WriteGuard;
use FluentForm\App\Services\Form\FormService;

/**
 * Form tools (read).
 *
 * The list-forms tool is the catalogue; get-form loads one form's field schema
 * so the agent knows the exact field keys before reading entries. Both respect the
 * user's form scope: a "specific forms" manager never sees a form outside their
 * assignment, even by id.
 */
class FormTools
{
    public static function definitions()
    {
        return [
            'fluentform/list-forms' => [
                'label'       => __('List Forms', 'fluentform'),
                'group'       => __('Forms', 'fluentform'),
                'description' => __('Find and filter forms with compact rows (id, title, status, type, entry count). Use this to discover the form_id you need for list-submissions and get-form-stats.', 'fluentform'),
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'search'    => ['type' => 'string', 'description' => 'Matches form title.'],
                        'status'    => ['type' => 'string', 'enum' => ['published', 'unpublished']],
                        'sort_by'   => ['type' => 'string', 'enum' => ['ASC', 'DESC'], 'default' => 'DESC'],
                        'page'      => ['type' => 'integer', 'default' => 1],
                        'per_page'  => ['type' => 'integer', 'default' => 15, 'description' => 'Max 100.'],
                    ],
                ],
                'execute_callback'    => [self::class, 'listForms'],
                'capability'          => ['fluentform_forms_manager', 'fluentform_dashboard_access'],
                'annotations' => ['readonly' => true],
            ],

            'fluentform/get-form' => [
                'label'       => __('Get Form', 'fluentform'),
                'group'       => __('Forms', 'fluentform'),
                'description' => __('Full detail for one form: status, type, timestamps, and the field schema (each input key, label, and element type) so you know the exact keys that appear in submission responses.', 'fluentform'),
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'form_id' => ['type' => 'integer', 'description' => 'The form id (from list-forms).'],
                    ],
                    'required' => ['form_id'],
                ],
                'execute_callback'    => [self::class, 'getForm'],
                'capability'          => ['fluentform_forms_manager', 'fluentform_dashboard_access'],
                'annotations' => ['readonly' => true],
            ],

            'fluentform/create-form' => [
                'label'       => __('Create Form', 'fluentform'),
                'group'       => __('Forms', 'fluentform'),
                'description' => __('Create a new form from a title and a list of fields. Each field needs a type (text, email, textarea, name, phone, number, url, dropdown, checkbox, radio, date) and a label. Omit fields to create a basic contact form (name, email, message). Call once with dry_run:true to preview the form that would be created and get a confirm_token, then call again with the same title and fields plus confirm_token to execute. Returns the new form id and editor URL.', 'fluentform'),
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => array_merge([
                        'title'  => ['type' => 'string', 'description' => 'The form title.'],
                        'fields' => [
                            'type'        => 'array',
                            'description' => 'Fields to add, in order. Omit for a basic contact form.',
                            'items'       => [
                                'type'       => 'object',
                                'properties' => [
                                    'type'  => ['type' => 'string', 'description' => 'Field type, e.g. text, email, textarea, name, phone, number, url, dropdown, checkbox, radio, date.'],
                                    'label' => ['type' => 'string', 'description' => 'Field label shown to the user.'],
                                ],
                                'required' => ['type'],
                            ],
                        ],
                        'is_conversational' => ['type' => 'boolean', 'description' => 'Create as a conversational form. Default false.'],
                    ], WriteGuard::schemaProps()),
                    'required' => ['title'],
                ],
                'execute_callback'    => [self::class, 'createForm'],
                'capability'          => 'fluentform_forms_manager',
            ],
        ];
    }

    public static function createForm($params = [])
    {
        $title = isset($params['title']) ? sanitize_text_field($params['title']) : '';
        if ('' === $title) {
            return MCPHelper::error(ErrorCodes::MISSING_PARAM, __('title is required.', 'fluentform'), ['fields' => ['title']]);
        }

        $specFields = [];
        $fieldsIn   = (isset($params['fields']) && is_array($params['fields'])) ? $params['fields'] : [];
        foreach ($fieldsIn as $field) {
            if (!is_array($field)) {
                continue;
            }
            $type = isset($field['type']) ? sanitize_text_field($field['type']) : '';
            if ('' === $type) {
                continue;
            }
            $label    = isset($field['label']) ? sanitize_text_field($field['label']) : '';
            $settings = [];
            if ('' !== $label) {
                $settings['label']             = $label;
                $settings['admin_field_label'] = $label;
            }
            $specFields[] = ['type' => $type, 'settings' => $settings];
        }

        // No usable fields supplied -> sensible default so the form is never empty.
        if (!$specFields) {
            $specFields = [
                ['type' => 'name', 'settings' => ['label' => __('Name', 'fluentform')]],
                ['type' => 'email', 'settings' => ['label' => __('Email', 'fluentform')]],
                ['type' => 'textarea', 'settings' => ['label' => __('Message', 'fluentform')]],
            ];
        }

        $isConversational = !empty($params['is_conversational']);

        // Nothing exists yet to fingerprint, so the token binds to the spec
        // itself: the form previewed to the operator is the only form it can
        // create. A different title or field list needs its own dry run.
        $specHash = md5(wp_json_encode([$title, $specFields, $isConversational]));

        return Mutation::runGuarded(
            'fluentform/create-form',
            $params,
            'form:new:' . $specHash,
            'spec:' . $specHash,
            function () use ($title, $specFields, $isConversational) {
                return [
                    'title'             => $title,
                    'is_conversational' => $isConversational,
                    'field_count'       => count($specFields),
                    'fields'            => array_map(function ($field) {
                        return [
                            'type'  => isset($field['type']) ? $field['type'] : null,
                            'label' => isset($field['settings']['label']) ? $field['settings']['label'] : null,
                        ];
                    }, $specFields),
                ];
            },
            function () use ($title, $specFields, $isConversational) {
                try {
                    $creator = new FormCreator();

                    // Submission responses are keyed by attributes.name; assign each
                    // field a unique one before saving so repeated field types never
                    // share a storage key (create() re-checks this, idempotently).
                    $specFields = $creator->assignStorageNames($specFields);

                    $form = $creator->create([
                        'title'             => $title,
                        'fields'            => $specFields,
                        'is_conversational' => $isConversational,
                    ]);
                } catch (\Throwable $e) {
                    return MCPHelper::error(ErrorCodes::CREATE_FAILED, $e->getMessage(), ['retryable' => false]);
                }

                return MCPHelper::envelope(
                    sprintf(
                        /* translators: 1: form title, 2: form id */
                        __('Form "%1$s" created (#%2$d).', 'fluentform'),
                        $form->title,
                        (int) $form->id
                    ),
                    [
                        'id'       => (int) $form->id,
                        'title'    => $form->title,
                        'status'   => $form->status,
                        'fields'   => count($specFields),
                        'edit_url' => admin_url('admin.php?page=fluent_forms&form_id=' . (int) $form->id . '&route=editor'),
                    ]
                );
            },
            function ($result) {
                return (is_array($result) && isset($result['data']['id'])) ? ['form_id' => (int) $result['data']['id']] : [];
            }
        );
    }

    public static function listForms($params = [])
    {
        $paging = MCPHelper::pagination($params, 15);

        $query = Form::query()->orderBy('id', strtoupper(isset($params['sort_by']) && 'ASC' === strtoupper($params['sort_by']) ? 'ASC' : 'DESC'));

        FormAccess::applyScope($query, 'id');
        if (!empty($params['search'])) {
            $query->where('title', 'LIKE', '%' . sanitize_text_field($params['search']) . '%');
        }
        if (!empty($params['status'])) {
            $query->where('status', sanitize_text_field($params['status']));
        }

        $paginator = $query->paginate($paging['per_page'], ['*'], 'page', $paging['page']);
        $total     = MCPHelper::paginatorTotal($paginator);

        $items  = MCPHelper::paginatorItems($paginator);
        $counts = FormAccess::entryCounts(array_map(function ($form) {
            return (int) $form->id;
        }, is_array($items) ? $items : iterator_to_array($items)));

        $rows = [];
        foreach ($items as $form) {
            $formId = (int) $form->id;
            $rows[] = [
                'id'         => $formId,
                'title'      => $form->title,
                'status'     => $form->status,
                'type'       => $form->type,
                'entries'    => isset($counts[$formId]) ? $counts[$formId] : null,
                'created_at' => MCPHelper::toIso8601($form->created_at),
            ];
        }

        return MCPHelper::envelope(
            sprintf(
                /* translators: %d: number of matching forms */
                _n('%d form found.', '%d forms found.', $total, 'fluentform'),
                $total
            ),
            ['forms' => $rows],
            MCPHelper::pagingMeta($paginator)
        );
    }

    public static function getForm($params = [])
    {
        $form = FormAccess::resolveForm($params);
        if (is_wp_error($form)) {
            return $form;
        }
        $formId = (int) $form->id;

        $fields = [];
        try {
            $service = new FormService();
            $schema  = $service->getInputsAndLabels($formId);
            $inputs  = isset($schema['inputs']) ? $schema['inputs'] : [];
            $labels  = isset($schema['labels']) ? $schema['labels'] : [];
            foreach ($inputs as $key => $input) {
                $fields[] = [
                    'key'     => $key,
                    'label'   => isset($labels[$key]) ? $labels[$key] : (isset($input['admin_label']) ? $input['admin_label'] : $key),
                    'element' => isset($input['element']) ? $input['element'] : null,
                ];
            }
        } catch (\Throwable $e) {
            $fields = [];
        }

        return MCPHelper::envelope(
            sprintf(
                /* translators: 1: form title, 2: field count */
                __('Form "%1$s" has %2$d fields.', 'fluentform'),
                $form->title,
                count($fields)
            ),
            [
                'id'         => (int) $form->id,
                'title'      => $form->title,
                'status'     => $form->status,
                'type'       => $form->type,
                'entries'    => FormAccess::entryCount($form->id),
                'created_at' => MCPHelper::toIso8601($form->created_at),
                'updated_at' => MCPHelper::toIso8601($form->updated_at),
                'fields'     => $fields,
            ]
        );
    }
}
