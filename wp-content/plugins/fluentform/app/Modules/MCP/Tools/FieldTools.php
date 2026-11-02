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
use FluentForm\Framework\Support\Arr;

/**
 * Field-editing tool (write, destructive).
 *
 * The update-form-fields tool replaces an existing form's whole field set from a
 * simple spec (the same shape create-form accepts), reusing FormService::update so the
 * agent path shares the admin editor's duplicate-name validation and field
 * sanitization. It is destructive: a submission's answers are keyed by a field's
 * attributes.name, so removing or renaming a field orphans its stored values.
 * The dry-run preview lists exactly which stored field keys would disappear, and
 * a confirm_token bound to the form's current field state must round-trip before
 * anything is written. Multi-step forms are refused — their step wrappers make a
 * blind full-replace unsafe; edit those in the form builder.
 */
class FieldTools
{
    public static function definitions()
    {
        return [
            'fluentform/update-form-fields' => [
                'label'       => __('Update Form Fields', 'fluentform'),
                'group'       => __('Forms', 'fluentform'),
                'description' => __('Replace a form\'s fields from a spec (same shape as create-form: a list of {element, attributes, settings}). This overwrites the ENTIRE field set — include every field you want to keep, not just new ones. Entries are keyed by a field\'s name, so dropping or renaming a field orphans its stored answers. Call once with dry_run:true to preview which stored field keys would be removed and get a confirm_token, then call again with the same fields plus confirm_token to execute. If your new list drops any existing field you must ALSO pass allow_field_removal:true. Multi-step forms are not editable here. Requires form_id and fields.', 'fluentform'),
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => array_merge([
                        'form_id'             => ['type' => 'integer', 'description' => 'Required. The form to edit.'],
                        'fields'              => ['type' => 'array', 'items' => ['type' => 'object'], 'description' => 'Required. The full replacement field list (create-form spec shape).'],
                        'allow_field_removal' => ['type' => 'boolean', 'description' => 'Required true to execute if your new list drops any existing field key (which orphans that field\'s stored entries).'],
                    ], WriteGuard::schemaProps()),
                    'required' => ['form_id', 'fields'],
                ],
                'execute_callback'    => [self::class, 'updateFields'],
                'capability'          => 'fluentform_forms_manager',
                'annotations' => ['destructive' => true],
            ],
        ];
    }

    public static function updateFields($params = [])
    {
        $form = FormAccess::resolveForm($params);
        if (is_wp_error($form)) {
            return $form;
        }
        $formId = (int) $form->id;

        $spec = isset($params['fields']) ? $params['fields'] : null;
        if (!is_array($spec) || empty($spec)) {
            return MCPHelper::error(ErrorCodes::MISSING_PARAM, __('fields must be a non-empty array of field definitions.', 'fluentform'), ['fields' => ['fields']]);
        }

        $existing = json_decode($form->form_fields, true);
        if (!is_array($existing)) {
            $existing = ['fields' => [], 'submitButton' => []];
        }

        if (!empty($existing['stepsWrapper'])) {
            return MCPHelper::error(ErrorCodes::INVALID_PARAM, __('This form is multi-step; edit its fields in the form builder. update-form-fields does not support step wrappers.', 'fluentform'), ['fields' => ['form_id']]);
        }

        try {
            $newFields = (new FormCreator())->formatFields($spec);
        } catch (\Throwable $e) {
            return MCPHelper::error(ErrorCodes::INVALID_PARAM, $e->getMessage(), ['fields' => ['fields']]);
        }
        if (empty($newFields)) {
            return MCPHelper::error(ErrorCodes::INVALID_PARAM, __('None of the supplied fields resolved to a valid FluentForm element.', 'fluentform'), ['fields' => ['fields']]);
        }

        $oldNames = self::fieldNames(Arr::get($existing, 'fields', []));
        $newNames = self::fieldNames($newFields);
        $removed  = array_values(array_diff($oldNames, $newNames));

        // Dropping a field orphans its stored entries. dry_run still previews the
        // removed keys so the agent can learn what's at stake; execution is refused
        // unless the caller explicitly opts into removal.
        if (empty($params['dry_run']) && $removed && empty($params['allow_field_removal'])) {
            return MCPHelper::error(
                ErrorCodes::INVALID_PARAM,
                __('This change removes existing fields, which orphans their stored entries. Re-send with allow_field_removal:true (plus the confirm_token) to proceed.', 'fluentform'),
                ['fields' => ['allow_field_removal'], 'removed_field_keys' => $removed, 'next_step' => 'set allow_field_removal:true']
            );
        }

        $fingerprint = 'fields:' . md5((string) $form->form_fields);

        return Mutation::runGuarded(
            'fluentform/update-form-fields',
            $params,
            'form_fields:' . $formId,
            $fingerprint,
            function () use ($removed, $oldNames, $newNames) {
                return [
                    'removed_field_keys' => $removed,
                    'kept_field_keys'    => array_values(array_intersect($oldNames, $newNames)),
                    'new_field_keys'     => array_values(array_diff($newNames, $oldNames)),
                    'warning'            => $removed
                        ? __('Entries stored under the removed field keys will no longer display against those fields. To execute, re-send with confirm_token AND allow_field_removal:true.', 'fluentform')
                        : __('No stored field keys are removed.', 'fluentform'),
                ];
            },
            function () use ($formId, $newFields, $removed, $fingerprint) {
                global $wpdb;

                // Serialize against concurrent form saves (editor autosave, another
                // agent): lock the form row, then read-modify-write inside one
                // transaction so no edit lands between our read and the Updater
                // write (lost update). FOR UPDATE degrades to a plain fresh read on
                // engines without row locks. Mirrors the pattern the field-conditions
                // tool used before it was folded away.
                $wpdb->query('START TRANSACTION');

                try {
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from $wpdb->prefix, id is %d-prepared
                    $wpdb->query($wpdb->prepare("SELECT id FROM {$wpdb->prefix}fluentform_forms WHERE id = %d FOR UPDATE", $formId));

                    $fresh = Form::query()->find($formId);
                    if (!$fresh) {
                        $wpdb->query('ROLLBACK');
                        return MCPHelper::error(ErrorCodes::STATE_CHANGED, __('The form was deleted while this update was in flight.', 'fluentform'));
                    }

                    // Re-validate the locked row against the exact state the caller
                    // previewed and confirmed. The confirm_token check ran against a
                    // pre-lock read; an edit landing between that read and this lock
                    // would make the removed-keys preview wrong (a concurrently-added
                    // field would be dropped without consent). Refuse and force a
                    // fresh dry_run so removal is always previewed against what ships.
                    if ('fields:' . md5((string) $fresh->form_fields) !== $fingerprint) {
                        $wpdb->query('ROLLBACK');
                        return MCPHelper::error(ErrorCodes::STATE_CHANGED, __('The form changed while this update was in flight. Run a fresh dry_run to re-preview which fields would be removed, then execute.', 'fluentform'), ['next_step' => 'set dry_run:true']);
                    }

                    $decoded = json_decode($fresh->form_fields, true);
                    if (!is_array($decoded)) {
                        $decoded = ['fields' => [], 'submitButton' => []];
                    }
                    if (!empty($decoded['stepsWrapper'])) {
                        $wpdb->query('ROLLBACK');
                        return MCPHelper::error(ErrorCodes::STATE_CHANGED, __('The form became multi-step while this update was in flight; edit it in the form builder.', 'fluentform'), ['fields' => ['form_id']]);
                    }

                    // Replace only the fields; keep the fresh submitButton and any
                    // other top-level keys as they stand right now.
                    $decoded['fields'] = $newFields;

                    (new FormService())->update([
                        'form_id'    => $formId,
                        'formFields' => wp_json_encode($decoded),
                        'title'      => $fresh->title,
                        'status'     => $fresh->status,
                    ]);

                    $wpdb->query('COMMIT');
                } catch (\FluentForm\Framework\Validator\ValidationException $e) {
                    $wpdb->query('ROLLBACK');
                    return MCPHelper::error(ErrorCodes::INVALID_PARAM, $e->getMessage(), ['fields' => ['fields']]);
                } catch (\Throwable $e) {
                    $wpdb->query('ROLLBACK');
                    throw $e;
                }

                return MCPHelper::envelope(
                    sprintf(
                        /* translators: %s: form title */
                        __('Fields for "%s" updated.', 'fluentform'),
                        $fresh->title
                    ),
                    ['form_id' => $formId, 'removed_field_keys' => $removed]
                );
            },
            ['form_id' => $formId]
        );
    }

    /**
     * Every stored field key (attributes.name) in a fields array, recursing into
     * container columns — the keys entries are stored against.
     */
    private static function fieldNames($fields)
    {
        $names = [];
        if (!is_array($fields)) {
            return $names;
        }

        foreach ($fields as $field) {
            if (!is_array($field)) {
                continue;
            }
            $name = Arr::get($field, 'attributes.name');
            if ($name) {
                $names[] = $name;
            }
            $columns = Arr::get($field, 'columns', []);
            if (is_array($columns)) {
                foreach ($columns as $column) {
                    $names = array_merge($names, self::fieldNames(Arr::get($column, 'fields', [])));
                }
            }
        }

        return array_values(array_unique($names));
    }
}
