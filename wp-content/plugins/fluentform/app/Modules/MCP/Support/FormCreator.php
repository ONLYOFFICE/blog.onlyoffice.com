<?php

namespace FluentForm\App\Modules\MCP\Support;

defined('ABSPATH') || exit;

use FluentForm\App\Modules\Acl\Acl;
use FluentForm\App\Modules\Ai\AiFormBuilder;
use FluentForm\App\Services\Form\FormService;
use FluentForm\Framework\Support\Arr;

/**
 * Builds a form from a simple field spec for the create-form MCP tool.
 *
 * Extends AiFormBuilder purely to reuse its (protected) field-mapping + save
 * pipeline, so the MCP concern stays out of the AI builder itself.
 */
class FormCreator extends AiFormBuilder
{
    /**
     * Set up the save pipeline without AiFormBuilder's constructor, which only
     * adds it to register an admin-ajax handler this REST/MCP path never uses.
     *
     * KNOWN COUPLING (accepted): naming the grandparent explicitly is only legal
     * because AiFormBuilder extends FormService, so this skips exactly one level.
     * If that chain ever changes, PHP will fatal here rather than fail quietly —
     * which is the reason this is written out rather than hidden behind
     * parent::__construct(), whose side effect is the thing being avoided.
     * Not worked around further because the alternative is editing
     * AiFormBuilder, which this PR deliberately leaves untouched.
     */
    public function __construct()
    {
        FormService::__construct();
    }

    /**
     * Create a form from a title + field spec.
     *
     * @param array $form { title: string, fields: array, is_conversational?: bool }.
     * @return \FluentForm\App\Models\Form
     * @throws \Exception
     */
    public function create(array $form)
    {
        Acl::verify('fluentform_forms_manager');

        $form['fields'] = $this->assignStorageNames($this->normalizeSpec(Arr::get($form, 'fields', [])));

        return $this->prepareAndSaveForm($form);
    }

    /**
     * Turn a simple field spec into FluentForm's formatted `fields` array — the
     * same mapping create() runs, minus the save. Reuses the AI builder's
     * per-element processing so an edited form matches a created one. Multi-step
     * (form_step) elements are skipped; the field-edit tool preserves the existing
     * form's step structure separately.
     *
     * @param array $fields Simple field spec (list of {element/attributes/settings}).
     * @return array Formatted fields ready to slot into form_fields['fields'].
     */
    public function formatFields(array $fields)
    {
        $fields    = $this->assignStorageNames($this->normalizeSpec($fields));
        $allFields = $this->getDefaultFields();
        $disabled  = array_keys($this->getDisabledComponents());

        $formatted = [];
        foreach ($fields as $field) {
            if (is_array($field) && 1 === count($field)) {
                $field = reset($field);
            }
            $element = $this->resolveInput($field);
            if (!$element || 'form_step' === $element || in_array($element, $disabled, true)) {
                continue;
            }
            $formatted[] = $this->processField($element, $field, $allFields);
        }

        return array_values(array_filter($formatted));
    }

    /**
     * Assign a unique attributes.name to every input field. Submission responses
     * are keyed by field name, and AiFormBuilder keeps the component default when
     * no name is supplied — so two fields of the same type would share one
     * storage key and overwrite each other's submitted values. Idempotent:
     * fields that already carry a unique name pass through unchanged, so the
     * tool boundary can assign names and create() re-running it is a no-op.
     */
    public function assignStorageNames(array $fields)
    {
        $used = [];

        foreach ($fields as &$field) {
            if (!is_array($field)) {
                continue;
            }

            $element = $this->resolveInput($field);
            if (!$element || 'container' === $element) {
                continue;
            }

            $current = Arr::get($field, 'attributes.name');
            $default = Arr::get($this->getDefaultFields(), $element . '.attributes.name');
            if (!$current && !$default) {
                // Element has no storage key (custom_html, section_break, …).
                continue;
            }

            $base = $current ? $current : $this->nameFromLabel(Arr::get($field, 'settings.label', ''), $default ? $default : $element);

            $name  = $base;
            $count = 1;
            while (isset($used[$name])) {
                $name = $base . '_' . $count;
                ++$count;
            }
            $used[$name] = true;

            $field['attributes']['name'] = $name;
        }
        unset($field);

        return $fields;
    }

    /**
     * Lift a top-level `label` (the simple shape both create-form and
     * update-form-fields accept) into settings.label, where assignStorageNames and
     * the builder read it. Without this, update-form-fields' {type,label} spec
     * lost its label, field names fell back to element defaults, and re-sending a
     * form's own fields read as a full removal. Idempotent: an explicit
     * settings.label is left untouched, so the full {element,attributes,settings}
     * shape passes through unchanged.
     */
    private function normalizeSpec(array $fields)
    {
        foreach ($fields as &$field) {
            if (!is_array($field)) {
                continue;
            }
            $label = Arr::get($field, 'label');
            if ('' === (string) $label || null !== Arr::get($field, 'settings.label')) {
                continue;
            }
            $label = sanitize_text_field($label);
            $field['settings']['label'] = $label;
            if (null === Arr::get($field, 'settings.admin_field_label')) {
                $field['settings']['admin_field_label'] = $label;
            }
        }
        unset($field);

        return $fields;
    }

    private function nameFromLabel($label, $fallback)
    {
        $name = sanitize_key(str_replace([' ', '-'], '_', (string) $label));
        $name = preg_replace('/_+/', '_', trim($name, '_'));
        if (strlen($name) > 40) {
            $name = rtrim(substr($name, 0, 40), '_');
        }

        return '' !== $name ? $name : $fallback;
    }
}
