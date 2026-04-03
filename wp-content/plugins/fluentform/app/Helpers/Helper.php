<?php

namespace FluentForm\App\Helpers;

use FluentForm\App\Models\EntryDetails;
use FluentForm\App\Models\Form;
use FluentForm\App\Models\FormMeta;
use FluentForm\App\Models\Submission;
use FluentForm\App\Models\SubmissionMeta;
use FluentForm\App\Services\FormBuilder\Components\SelectCountry;
use FluentForm\App\Services\FormBuilder\ShortCodeParser;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Helpers\Traits\GlobalDefaultMessages;
use FluentForm\Framework\Support\Arr;

class Helper
{
    use GlobalDefaultMessages;

    public static $tabIndex = 0;

    public static $formInstance = 0;

    public static $loadedForms = [];

    public static $tabIndexStatus = 'na';

    protected static $formMetaCache = [];


    /**
     * Sanitize form inputs recursively.
     *
     * @param $input
     *
     * @return string $input
     */
    public static function sanitizer($input, $attribute = null, $fields = [])
    {
        if (is_string($input)) {
            if ('textarea' === ArrayHelper::get($fields, $attribute . '.element')) {
                $input = sanitize_textarea_field($input);
            } else {
                $input = sanitize_text_field($input);
            }
        } elseif (is_array($input)) {
            foreach ($input as $key => &$value) {
                $attribute = $attribute ? $attribute . '[' . $key . ']' : $key;

                $value = static::sanitizer($value, $attribute, $fields);

                $attribute = null;
            }
        }

        return $input;
    }

    public static function makeMenuUrl($page = 'fluent_forms_settings', $component = null)
    {
        $baseUrl = admin_url('admin.php?page=' . $page);

        $hash = ArrayHelper::get($component, 'hash', '');
        if ($hash) {
            $baseUrl = $baseUrl . '#' . $hash;
        }

        $query = ArrayHelper::get($component, 'query');

        if ($query) {
            $paramString = http_build_query($query);
            if ($hash) {
                $baseUrl .= '?' . $paramString;
            } else {
                $baseUrl .= '&' . $paramString;
            }
        }

        return $baseUrl;
    }

    public static function getHtmlElementClass($value1, $value2, $class = 'active', $default = '')
    {
        return $value1 === $value2 ? $class : $default;
    }

    /**
     * Determines if the given string is a valid json.
     *
     * @param $string
     *
     * @return bool
     */
    public static function isJson($string)
    {
        json_decode($string);

        return JSON_ERROR_NONE === json_last_error();
    }

    public static function isSlackEnabled()
    {
        $globalModules = get_option('fluentform_global_modules_status');

        return $globalModules && isset($globalModules['slack']) && 'yes' == $globalModules['slack'];
    }

    public static function getEntryStatuses($form_id = false)
    {
        $statuses = [
            'unread'    => __('Unread', 'fluentform'),
            'read'      => __('Read', 'fluentform'),
            'favorites' => __('Favorites', 'fluentform'),
        ];

        $statuses = apply_filters_deprecated(
            'fluentform_entry_statuses_core',
            [
                $statuses,
                $form_id
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/entry_statuses_core',
            'Use fluentform/entry_statuses_core instead of fluentform_entry_statuses_core.'
        );

        $statuses = apply_filters('fluentform/entry_statuses_core', $statuses, $form_id);

        $statuses['trashed'] = 'Trashed';

        return $statuses;
    }

    public static function getReportableInputs()
    {
        $data = [
            'select',
            'input_radio',
            'input_checkbox',
            'ratings',
            'net_promoter',
            'select_country',
            'net_promoter_score',
        ];

        $data = apply_filters_deprecated(
            'fluentform_reportable_inputs',
            [
                $data
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/reportable_inputs',
            'Use fluentform/reportable_inputs instead of fluentform_reportable_inputs.'
        );

        return apply_filters('fluentform/reportable_inputs', $data);
    }

    public static function getSubFieldReportableInputs()
    {
        $grid = apply_filters_deprecated(
            'fluentform_subfield_reportable_inputs',
            [
                ['tabular_grid']
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/subfield_reportable_inputs',
            'Use fluentform/subfield_reportable_inputs instead of fluentform_subfield_reportable_inputs.'
        );

        return apply_filters('fluentform/subfield_reportable_inputs', $grid);
    }

    public static function getFormMeta($formId, $metaKey, $default = '', $forced = false)
    {
        $formattedValues = self::$formMetaCache[$formId] ?? [];
        
        if (!isset(self::$formMetaCache[$formId]) || $forced) {
            $formMetas = FormMeta::where('form_id', $formId)
                ->get();

            $formattedValues = [];
            foreach ($formMetas as $formMeta) {
                $value = $formMeta->value;
                
                $decoded = json_decode($value ?? '', true);
                if (is_array($decoded)) {
                    $value = $decoded;
                }

                $formattedValues[$formMeta->meta_key] = $value;
            }
            self::$formMetaCache[$formId] = $formattedValues;
        }

        return Arr::get($formattedValues, $metaKey, $default);
    }


    public static function setFormMeta($formId, $metaKey, $value)
    {
        if ($meta = FormMeta::persist($formId, $metaKey, $value)) {
            // Update the cache with the new value
            if (!isset(self::$formMetaCache[$formId])) {
                self::$formMetaCache[$formId] = [];
            }
            self::$formMetaCache[$formId][$metaKey] = $value;

            return $meta->id;
        }
        return null;
    }

    public static function deleteFormMeta($formId, $metaKey)
    {
        try {
            FormMeta::remove($formId, $metaKey);
            return true;
        } catch (\Exception $ex) {
            return null;
        }
    }

    public static function getSubmissionMeta($submissionId, $metaKey, $default = false)
    {
        return SubmissionMeta::retrieve($metaKey, $submissionId, $default);
    }

    public static function setSubmissionMeta($submissionId, $metaKey, $value, $formId = false)
    {
        if ($meta = SubmissionMeta::persist($submissionId, $metaKey, $value, $formId)) {
            return $meta->id;
        }
        return null;
    }

    public static function setSubmissionMetaAsArrayPush($submissionId, $metaKey, $value, $formId = false)
    {
        if ($meta = SubmissionMeta::persistArray($submissionId, $metaKey, $value, $formId)) {
            return $meta->id;
        }
        return null;
    }

    public static function isEntryAutoDeleteEnabled($formId)
    {
        if (
            'yes' == ArrayHelper::get(static::getFormMeta($formId, 'formSettings', []), 'delete_entry_on_submission',
                '')
        ) {
            return true;
        }
        return false;
    }

    public static function formExtraCssClass($form)
    {
        if (!$form->settings) {
            $formSettings = static::getFormMeta($form->id, 'formSettings');
        } else {
            $formSettings = $form->settings;
        }

        if (!$formSettings) {
            return '';
        }

        if ($extraClass = ArrayHelper::get($formSettings, 'form_extra_css_class')) {
            return esc_attr($extraClass);
        }

        return '';
    }

    public static function getNextTabIndex($increment = 1)
    {
        if (static::isTabIndexEnabled()) {
            static::$tabIndex += $increment;

            return static::$tabIndex;
        }

        return '';
    }

    public static function getFormInstaceClass($formId)
    {
        static::$formInstance += 1;

        return 'ff_form_instance_' . $formId . '_' . static::$formInstance;
    }

    public static function resetTabIndex()
    {
        static::$tabIndex = 0;
    }

    public static function isFluentAdminPage()
    {
        $fluentPages = [
            'fluent_forms',
            'fluent_forms_all_entries',
            'fluent_forms_transfer',
            'fluent_forms_settings',
            'fluent_forms_add_ons',
            'fluent_forms_docs',
            'fluent_forms_payment_entries',
            'fluent_forms_smtp',
            'fluent_forms_reports'
        ];

        $status = true;

        $page = wpFluentForm('request')->get('page');

        if (!$page || !in_array($page, $fluentPages)) {
            $status = false;
        }

        $status = apply_filters_deprecated(
            'fluentform_is_admin_page',
            [
                $status
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/is_admin_page',
            'Use fluentform/is_admin_page instead of fluentform_is_admin_page.'
        );

        return apply_filters('fluentform/is_admin_page', $status);
    }

    public static function getShortCodeIds($content, $tag = 'fluentform', $selector = 'id')
    {
        if (false === strpos($content, '[')) {
            return [];
        }

        preg_match_all('/' . get_shortcode_regex() . '/', $content, $matches, PREG_SET_ORDER);
        if (empty($matches)) {
            return [];
        }

        $ids = [];
        $attributes = [];

        foreach ($matches as $shortcode) {
            if (count($shortcode) >= 2 && $tag === $shortcode[2]) {
                // Replace braces with empty string.
                $parsedCode = str_replace(['[', ']', '&#91;', '&#93;'], '', $shortcode[0]);

                $result = shortcode_parse_atts($parsedCode);

                if (!empty($result[$selector])) {
                    if ($tag == 'fluentform' && !empty($result['type']) && $result['type'] == 'conversational') {
                        continue;
                    }

                    $ids[$result[$selector]] = $result[$selector];

                    $theme = ArrayHelper::get($result, 'theme');

                    if ($theme) {
                        $attributes[] = [
                            'formId' => $result[$selector],
                            'theme'  => $theme
                        ];
                    }
                }
            }
        }

        if ($attributes) {
            $ids['attributes'] = $attributes;
        }

        return $ids;
    }

    public static function getFormsIdsFromBlocks($content)
    {
        $ids = [];
        $attributes = [];

        if (!function_exists('parse_blocks')) {
            return $ids;
        }

        $has_block = false !== strpos($content, '<!-- wp:fluentfom/guten-block' . ' ');

        if (!$has_block) {
            return $ids;
        }

        $parsedBlocks = parse_blocks($content);
        foreach ($parsedBlocks as $block) {
            if (!ArrayHelper::exists($block, 'blockName') || !ArrayHelper::get($block, 'attrs.formId')) {
                continue;
            }

            $hasBlock = strpos($block['blockName'], 'fluentfom/guten-block') === 0;
            if ($hasBlock) {
                $formId = (int)$block['attrs']['formId'];

                $ids[] = $formId;

                $theme = ArrayHelper::get($block, 'attrs.themeStyle');

                if ($theme) {
                    $attributes[] = [
                        'formId' => $formId,
                        'theme'  => $theme
                    ];
                }
            }
        }

        if ($attributes) {
            $ids['attributes'] = $attributes;
        }

        return $ids;
    }

    public static function isTabIndexEnabled()
    {
        if ('na' == static::$tabIndexStatus) {
            $globalSettings = get_option('_fluentform_global_form_settings');
            static::$tabIndexStatus = 'yes' == ArrayHelper::get($globalSettings, 'misc.tabIndex');
        }

        return static::$tabIndexStatus;
    }

    public static function isMultiStepForm($formOrId)
    {
        // Accept both form object and form ID to avoid re-querying
        if (is_object($formOrId)) {
            $form = $formOrId;
        } else {
            $form = Form::find($formOrId);
        }

        if (!$form) {
            return false;
        }

        $fieldsJson = (string)($form->form_fields ?? '');
        if ($fieldsJson === '') {
            return false;
        }

        $fields = json_decode($fieldsJson, true);
        if (!is_array($fields)) {
            return false;
        }

        return (bool)ArrayHelper::get($fields, 'stepsWrapper');
    }

    public static function hasFormElement($formId, $elementName)
    {
        $form = Form::find($formId);
        $fieldsJson = $form->form_fields;

        return false != strpos($fieldsJson, '"element":"' . $elementName . '"');
    }

    public static function isUniqueValidation($validation, $field, $formData, $fields, $form)
    {
        if ('yes' == ArrayHelper::get($field, 'raw.settings.is_unique')) {
            $fieldName = ArrayHelper::get($field, 'name');
            if ($inputValue = ArrayHelper::get($formData, $fieldName)) {
                $exist = EntryDetails::where('form_id', $form->id)
                    ->where('field_name', $fieldName)
                    ->where('field_value', $inputValue)
                    ->exists();

                // if form has pending payment then the value doesn't exist in EntryDetails table
                // further checking on Submission table if the value exists
                if (!$exist && $form->has_payment) {
                    $escapedKey = json_encode($fieldName);
                    $escapedValue = json_encode($inputValue);
                    $searchPattern = trim($escapedKey, '"') . '":' . $escapedValue;
                    $searchPattern = addcslashes($searchPattern, '%_');

                    $exist = Submission::where('form_id', $form->id)
                        ->where('response', 'LIKE', '%' . $searchPattern . '%')
                        ->exists();
                }

                if ($exist) {
                    $typeName = ArrayHelper::get($field, 'element', 'input_text');
                    return [
                        'unique' => apply_filters('fluentform/validation_message_unique_' . $typeName,
                            ArrayHelper::get($field, 'raw.settings.unique_validation_message'), $field),
                    ];
                }
            }
        }

        return $validation;
    }


    public static function hasPartialEntries($formId)
    {
        static $cache = [];
        if (isset($cache[$formId])) {
            return $cache[$formId];
        }

        $cache[$formId] = 'yes' == static::getFormMeta($formId, 'form_save_state_status');

        return $cache[$formId];
    }

    public static function getNumericFormatters()
    {
        $data = [
            'none'                 => [
                'value' => '',
                'label' => 'None',
            ],
            'comma_dot_style'      => [
                'value'    => 'comma_dot_style',
                'label'    => __('US Style with Decimal (EX: 123,456.00)', 'fluentform'),
                'settings' => [
                    'decimal'   => '.',
                    'separator' => ',',
                    'precision' => 2,
                    'symbol'    => '',
                ],
            ],
            'dot_comma_style_zero' => [
                'value'    => 'dot_comma_style_zero',
                'label'    => __('US Style without Decimal (Ex: 123,456,789)', 'fluentform'),
                'settings' => [
                    'decimal'   => '.',
                    'separator' => ',',
                    'precision' => 0,
                    'symbol'    => '',
                ],
            ],
            'dot_comma_style'      => [
                'value'    => 'dot_comma_style',
                'label'    => __('EU Style with Decimal (Ex: 123.456,00)', 'fluentform'),
                'settings' => [
                    'decimal'   => ',',
                    'separator' => '.',
                    'precision' => 2,
                    'symbol'    => '',
                ],
            ],
            'comma_dot_style_zero' => [
                'value'    => 'comma_dot_style_zero',
                'label'    => __('EU Style without Decimal (EX: 123.456.789)', 'fluentform'),
                'settings' => [
                    'decimal'   => ',',
                    'separator' => '.',
                    'precision' => 0,
                    'symbol'    => '',
                ],
            ],
        ];

        $data = apply_filters_deprecated(
            'fluentform_numeric_styles',
            [
                $data
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/numeric_styles',
            'Use fluentform/numeric_styles instead of fluentform_numeric_styles.'
        );

        return apply_filters('fluentform/numeric_styles', $data);
    }

    public static function getNumericValue($input, $formatterName)
    {
        $formatters = static::getNumericFormatters();
        if (empty($formatters[$formatterName]['settings'])) {
            return $input;
        }
        $settings = $formatters[$formatterName]['settings'];
        $number = floatval(str_replace($settings['decimal'], '.',
            preg_replace('/[^-?\d' . preg_quote($settings['decimal']) . ']/', '', $input)));

        return number_format($number, $settings['precision'], '.', '');
    }

    public static function getNumericFormatted($input, $formatterName)
    {
        if (!is_numeric($input)) {
            return $input;
        }
        $formatters = static::getNumericFormatters();
        if (empty($formatters[$formatterName]['settings'])) {
            return $input;
        }
        $settings = $formatters[$formatterName]['settings'];

        return number_format($input, $settings['precision'], $settings['decimal'], $settings['separator']);
    }

    public static function getDuplicateFieldNames($fields)
    {
        $fields = json_decode($fields, true);
        $items = $fields['fields'];
        $inputNames = static::getFieldNamesStatuses($items);
        $uniqueNames = array_unique($inputNames);

        if (count($inputNames) == count($uniqueNames)) {
            return [];
        }

        return array_diff_assoc($inputNames, $uniqueNames);
    }

    protected static function getFieldNamesStatuses($fields)
    {
        $names = [];

        foreach ($fields as $field) {
            if ('container' == ArrayHelper::get($field, 'element')) {
                $columns = ArrayHelper::get($field, 'columns', []);
                foreach ($columns as $column) {
                    $columnInputs = static::getFieldNamesStatuses(ArrayHelper::get($column, 'fields', []));
                    $names = array_merge($names, $columnInputs);
                }
            } else {
                if ($name = ArrayHelper::get($field, 'attributes.name')) {
                    $names[] = $name;
                }
            }
        }

        return $names;
    }

    public static function isConversionForm($formId)
    {
        static $cache = [];
        if (isset($cache[$formId])) {
            return $cache[$formId];
        }

        $cache[$formId] = 'yes' == static::getFormMeta($formId, 'is_conversion_form');

        return $cache[$formId];
    }

    public static function getPreviewUrl($formId, $type = '')
    {
        if ('conversational' == $type) {
            return static::getConversionUrl($formId);
        } elseif ('classic' == $type) {
            return site_url('?fluent_forms_pages=1&design_mode=1&preview_id=' . $formId) . '#ff_preview';
        } else {
            if (static::isConversionForm($formId)) {
                return static::getConversionUrl($formId);
            }
        }

        return site_url('?fluent_forms_pages=1&design_mode=1&preview_id=' . $formId) . '#ff_preview';
    }

    public static function getFormAdminPermalink($route, $form)
    {
        $baseUrl = admin_url('admin.php?page=fluent_forms');

        return $baseUrl . '&route=' . $route . '&form_id=' . $form->id;
    }

    public static function getFormSettingsUrl($form)
    {
        $baseUrl = admin_url('admin.php?page=fluent_forms');

        return $baseUrl . '&form_id=' . $form->id . '&route=settings&sub_route=form_settings#basic_settings';
    }

    private static function getConversionUrl($formId)
    {
        $meta = static::getFormMeta($formId, 'ffc_form_settings_meta', []);
        $key = ArrayHelper::get($meta, 'share_key', '');

        $slug = apply_filters_deprecated(
            'fluentform_conversational_url_slug',
            [
                'fluent-form'
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/conversational_url_slug',
            'Use fluentform/conversational_url_slug instead of fluentform_conversational_url_slug.'
        );

        $paramKey = apply_filters('fluentform/conversational_url_slug', $slug);

        if ('form' == $paramKey) {
            $paramKey = 'fluent-form';
        }
        if ($key) {
            return static::getFrontendFacingUrl('?' . $paramKey . '=' . $formId . '&form=' . $key);
        }
        return static::getFrontendFacingUrl('?' . $paramKey . '=' . $formId);
    }

    public static function fileUploadLocations()
    {
        $locations = [
            [
                'value' => 'default',
                'label' => __('Fluent Forms Default', 'fluentform'),
            ],
            [
                'value' => 'wp_media',
                'label' => __('Media Library', 'fluentform'),
            ],
        ];

        $locations = apply_filters_deprecated(
            'fluentform_file_upload_options',
            [
                $locations
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/file_upload_options',
            'Use fluentform/file_upload_options instead of fluentform_file_upload_options'
        );

        return apply_filters('fluentform/file_upload_options', $locations);
    }

    public static function unreadCount($formId)
    {
        return Submission::where('status', 'unread')
            ->where('form_id', $formId)
            ->count();
    }

    public static function getForms()
    {
        $ff_list = Form::select(['id', 'title'])->orderBy('id', 'DESC')->get();
        $forms = [];

        if (count($ff_list) > 0) {
            $forms[0] = esc_html__('Select a Fluent Forms', 'fluentform');
            foreach ($ff_list as $form) {
                $forms[$form->id] = esc_html($form->title) . ' (' . $form->id . ')';
            }
        } else {
            $forms[0] = esc_html__('Create a Form First', 'fluentform');
        }

        return $forms;
    }

    public static function replaceBrTag($content, $with = '')
    {
        if (is_array($content)) {
            foreach ($content as $key => $value) {
                $content[$key] = static::replaceBrTag($value, $with);
            }
        } elseif (static::hasBrTag($content)) {
            $content = str_replace('<br />', $with, $content);
        }

        return $content;
    }

    public static function hasBrTag($content)
    {
        return is_string($content) && false !== strpos($content, '<br />');
    }

    public static function sanitizeForCSV($content)
    {
        $formulas = ['=', '-', '+', '@', "\t", "\r"];

        $formulas = apply_filters('fluentform/csv_sanitize_formulas', $formulas);

        if (Str::startsWith($content, $formulas)) {
            $content = "'" . $content;
        }

        return $content;
    }

    public static function sanitizeOrderValue($orderType = '')
    {
        $orderBys = ['ASC', 'DESC'];

        $orderType = trim(strtoupper($orderType));

        return in_array($orderType, $orderBys) ? $orderType : 'DESC';
    }

    public static function getForm($id)
    {
        return Form::where('id', $id)->first();
    }

    public static function shouldHidePassword($formId)
    {
        $isTruncate = apply_filters_deprecated(
            'fluentform_truncate_password_values',
            [
                true,
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/truncate_password_values',
            'Use fluentform/truncate_password_values instead of fluentform_truncate_password_values.'
        );

        return apply_filters('fluentform/truncate_password_values', $isTruncate, $formId) &&
            (
                (defined('FLUENTFORM_RENDERING_ENTRIES') && FLUENTFORM_RENDERING_ENTRIES) ||
                (defined('FLUENTFORM_RENDERING_ENTRY') && FLUENTFORM_RENDERING_ENTRY) ||
                (defined('FLUENTFORM_EXPORTING_ENTRIES') && FLUENTFORM_EXPORTING_ENTRIES)
            );
    }

    // make tabular-grid value markdown format
    public static function getTabularGridFormatValue(
        $girdData,
        $field,
        $rowJoiner = '<br />',
        $colJoiner = ', ',
        $type = ''
    )
    {
        if (!$girdData || !$field) {
            return '';
        }
        $girdRows = ArrayHelper::get($field, 'raw.settings.grid_rows', []);
        $girdRows = fluentFormSanitizer($girdRows);
        $girdCols = ArrayHelper::get($field, 'raw.settings.grid_columns', []);
        $girdCols = fluentFormSanitizer($girdCols);

        $value = '';
        $lastRow = key(array_slice($girdData, -1, 1, true));
        foreach ($girdData as $row => $column) {
            $_row = $row;
            if ($girdRows && isset($girdRows[$row])) {
                $row = $girdRows[$row];
            }
            if ('markdown' === $type) {
                $value .= '- *' . $row . '* :  ';
            } else {
                $value .= $row . ': ';
            }
            if (is_array($column)) {
                foreach ($column as $index => $item) {
                    $_colJoiner = $colJoiner;
                    if ($girdCols && isset($girdCols[$item])) {
                        $item = $girdCols[$item];
                    }
                    if ($index == (count($column) - 1)) {
                        $_colJoiner = '';
                    }
                    $value .= $item . $_colJoiner;
                }
            } else {
                if ($girdCols && isset($girdCols[$column])) {
                    $column = $girdCols[$column];
                }
                $value .= $column;
            }
            if ($_row != $lastRow) {
                $value .= $rowJoiner;
            }
        }

        return $value;
    }

    public static function getInputNameFromShortCode($value)
    {
        preg_match('/{+(.*?)}/', $value, $matches);
        if ($matches && false !== strpos($matches[1], 'inputs.')) {
            return substr($matches[1], strlen('inputs.'));
        }

        return '';
    }

    public static function getRestInfo()
    {
        $config = wpFluentForm('config');

        $namespace = $config->get('app.rest_namespace');
        $version = $config->get('app.rest_version');
        $restUrl = rest_url($namespace . '/' . $version);
        $restUrl = rtrim($restUrl, '/\\');

        return [
            'base_url'  => esc_url_raw(rest_url()),
            'url'       => $restUrl,
            'nonce'     => wp_create_nonce('wp_rest'),
            'namespace' => $namespace,
            'version'   => $version,
        ];
    }

    public static function getLogInitiator($action, $type = 'log')
    {
        if ('log' === $type) {
            $title = ucwords(implode(' ', preg_split('/(?=[A-Z])/', $action)));
        } else {
            $title = ucwords(
                str_replace(
                    ['fluentform/integration_notify_', 'fluentform_', '_notification_feed', '_'],
                    ['', '', '', ' '],
                    $action
                )
            );
        }

        return $title;
    }

    public static function getIpinfo()
    {
        return ArrayHelper::get(get_option('_fluentform_global_form_settings'), 'misc.geo_provider_token');
    }

    public static function isAutoloadCaptchaEnabled()
    {
        return ArrayHelper::get(get_option('_fluentform_global_form_settings'), 'misc.autoload_captcha');
    }

    public static function isAutosaveEnabled()
    {
        $autosaveEnabled = ArrayHelper::get(get_option('_fluentform_global_form_settings'), 'misc.autosave_enabled', 'no');
        return $autosaveEnabled === 'yes';
    }

    public static function maybeDecryptUrl($url)
    {
        $uploadDir = str_replace('/', '\/', FLUENTFORM_UPLOAD_DIR . '/temp');
        $pattern = "/(?<={$uploadDir}\/).*$/";
        preg_match($pattern, $url, $match);
        if (!empty($match)) {
            $url = str_replace($match[0], Protector::decrypt($match[0]), $url);
        }
        return $url;
    }

    public static function arrayFilterRecursive($arrayItems)
    {
        foreach ($arrayItems as $key => $item) {
            is_array($item) && $arrayItems[$key] = self::arrayFilterRecursive($item);
            if (empty($arrayItems[$key])) {
                unset($arrayItems[$key]);
            }
        }
        return $arrayItems;
    }

    public static function isBlockEditor()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Checking REST API context
        return defined('REST_REQUEST') && REST_REQUEST && !empty($_REQUEST['context']) && $_REQUEST['context'] === 'edit';
    }

    public static function resolveValidationRulesGlobalOption(&$field)
    {
        if (isset($field['fields']) && is_array($field['fields'])) {
            foreach ($field['fields'] as &$subField) {
                static::resolveValidationRulesGlobalOption($subField);
            }
        } else {
            if (ArrayHelper::get($field, 'settings.validation_rules')) {
                foreach ($field['settings']['validation_rules'] as $key => &$rule) {
                    if (!isset($rule['global'])) {
                        $rule['global'] = false;
                    }
                    $rule['global_message'] = static::getGlobalDefaultMessage($key);
                }
            }
        }
    }

    /**
     * Validate form input value against database values
     *
     * @param $field array Form Field
     * @param $formData array From Data
     * @param $form object From
     * @param $fieldName string optional
     * @param $inputValue mixed optional
     *
     * @return string
     * Return Error message on fail. Otherwise, return empty string
     */
    public static function validateInput($field, $formData, $form, $fieldName = '', $inputValue = [])
    {
        $error = '';
        if (!$fieldName) {
            $fieldName = ArrayHelper::get($field, 'name');
        }
        if (!$fieldName) {
            return $error;
        }
        if (!$inputValue) {
            $inputValue = ArrayHelper::get($formData, $fieldName);
        }
        if ($inputValue) {
            $rawField = ArrayHelper::get($field, 'raw');
            if (!$rawField) {
                $rawField = $field;
            }
            $fieldType = ArrayHelper::get($rawField, 'element');
            $rawField = apply_filters('fluentform/rendering_field_data_' . $fieldType, $rawField, $form);
            $options = [];
            if ("net_promoter_score" === $fieldType) {
                $options = array_flip(ArrayHelper::get($rawField, 'options', []));
            } elseif ('ratings' == $fieldType) {
                $options = array_keys(ArrayHelper::get($rawField, 'options', []));
            } elseif ('gdpr_agreement' == $fieldType) {
                $options = ['on'];
            } elseif ('terms_and_condition' == $fieldType) {
                $options = ['on', 'off'];
            } elseif (in_array($fieldType, ['input_radio', 'select', 'input_checkbox'])) {
                if (ArrayHelper::isTrue($rawField, 'attributes.multiple')) {
                    $fieldType = 'multi_select';
                }
                $options = array_column(
                    ArrayHelper::get($rawField, 'settings.advanced_options', []),
                    'value'
                );
                
                // Add field-specific __ff_other__ to options if "Other" option is enabled
                if (in_array($fieldType, ['input_checkbox', 'input_radio']) &&
                    ArrayHelper::get($rawField, 'settings.enable_other_option') === 'yes') {
                    $fieldName = sanitize_key(str_replace(['[', ']'], '', ArrayHelper::get($rawField, 'attributes.name', '')));
                    $options[] = '__ff_other_' . $fieldName . '__';
                }
            } elseif ("dynamic_field" == $fieldType) {
                $dynamicFetchValue = 'yes' == ArrayHelper::get($rawField, 'settings.dynamic_fetch');
                if ($dynamicFetchValue) {
                    $rawField = apply_filters('fluentform/dynamic_field_re_fetch_result_and_resolve_value', $rawField);
                }
                $dfElementType = ArrayHelper::get($rawField, 'attributes.type');
                if (in_array($dfElementType, ['radio', 'select', 'checkbox'])) {
                    $fieldType = 'dynamic_field_options';
                    $options = array_column(
                        ArrayHelper::get($rawField, 'settings.advanced_options', []),
                        'value'
                    );
                }
            }

            if ($options) {
                $options = array_map('sanitize_text_field', $options);
            }

            $isValid = true;
            switch ($fieldType) {
                case 'input_radio':
                case 'select':
                case 'net_promoter_score':
                case 'ratings':
                case 'gdpr_agreement':
                case 'terms_and_condition':
                case 'input_checkbox':
                case 'multi_select':
                case 'dynamic_field_options':
            
                    $skipValidationInputsWithOptions = apply_filters('fluentform/skip_validation_inputs_with_options', false, $fieldType, $form, $formData);
                    if ($skipValidationInputsWithOptions) {
                        break;
                    }
                    if (is_array($inputValue)) {
                        // Handle field-specific "Other" options for checkboxes
                        $filteredValues = array_filter($inputValue, function($value) {
                            // Skip field-specific other values and processed other values
                            return !preg_match('/^__ff_other_.*__$/', $value) &&
                                   !preg_match('/^Other:\s/', $value);
                        });
                        $isValid = array_diff($filteredValues, $options);
                        $isValid = empty($isValid);
                    } else {
                        // Handle field-specific "Other" option for single values
                        if (preg_match('/^__ff_other_.*__$/', $inputValue) ||
                            preg_match('/^Other:\s/', $inputValue)) {
                            $isValid = true;
                        } else {
                            $isValid = in_array($inputValue, $options);
                        }
                    }
                    break;
                case 'input_number':
                    if (is_array($inputValue)) {
                        $hasNonNumricValue = in_array(false, array_map('is_numeric', $inputValue));
                        if ($hasNonNumricValue) {
                            $isValid = false;
                        }
                    } else {
                        $isValid = is_numeric($inputValue);
                    }
                    break;
                case 'select_country':
                    $fieldData = ArrayHelper::get($field, 'raw');
                    $data = (new SelectCountry())->loadCountries($fieldData);
                    $validCountries = ArrayHelper::get($fieldData, 'settings.country_list.priority_based', []);
                    $validCountries = array_merge($validCountries, array_keys(ArrayHelper::get($data, 'options')));
                    $isValid = in_array($inputValue, $validCountries);
                    break;
                case 'repeater_field':
                case 'repeater_container':
                    foreach (ArrayHelper::get($rawField, 'fields', []) as $index => $repeaterField) {
                        $repeaterFieldValue = array_filter(array_column($inputValue, $index));
                        if ($repeaterFieldValue && $error = static::validateInput($repeaterField, $formData, $form,
                                $fieldName, $repeaterFieldValue)) {
                            $isValid = false;
                            break;
                        }
                    }
                    break;
                case 'tabular_grid':
                    $rows = array_keys(ArrayHelper::get($rawField, 'settings.grid_rows', []));
                    $rows = array_map(function ($row) {
                        return trim(sanitize_text_field($row));
                    }, $rows);

                    $submittedRows = array_keys(ArrayHelper::get($formData, $fieldName, []));
                    $submittedRows = array_map('trim', $submittedRows);

                    $rowDiff = array_diff($submittedRows, $rows);

                    $isValid = empty($rowDiff);
                    if ($isValid) {
                        $columns = array_keys(ArrayHelper::get($rawField, 'settings.grid_columns', []));
                        $columns = array_map(function ($column) {
                            return trim(sanitize_text_field($column));
                        }, $columns);
                        $submittedCols = ArrayHelper::flatten(ArrayHelper::get($formData, $fieldName, []));
                        $submittedCols = array_map('trim', $submittedCols);
                        $colDiff = array_diff($submittedCols, $columns);
                        $isValid = empty($colDiff);
                    }
                    break;
                default:
                    break;
            }
            if (!$isValid) {
                $error = __('The given data was invalid', 'fluentform');
            }
        }
        return $error;
    }

    public static function getWhiteListedFields($formId)
    {
        $whiteListedFields = [
            '__fluent_form_embded_post_id',
            '_fluentform_' . $formId . '_fluentformnonce',
            '_wp_http_referer',
            'g-recaptcha-response',
            'h-captcha-response',
            'cf-turnstile-response',
            '__stripe_payment_method_id',
            '__ff_all_applied_coupons',
            '__entry_intermediate_hash',
            '__square_payment_method_id',
            '__square_verify_buyer_id',
            'ct_bot_detector_event_token',
            'ff_ct_form_load_time'
        ];

        return apply_filters('fluentform/white_listed_fields', $whiteListedFields, $formId);
    }

    /**
     * Shortcode parse on validation message
     * @param string $message
     * @param object $form
     * @param string $fieldName
     * @return string
     */
    public static function shortCodeParseOnValidationMessage($message, $form, $fieldName)
    {
        // Return early if form is null to prevent errors
        if ($form === null) {
            return $message;
        }

        // For validation message there is no entry & form data
        // Add 'current_field' name as data array to resolve {labels.current_field} shortcode if it has
        return ShortCodeParser::parse(
            $message,
            (object)['response' => "", 'form_id' => $form->id],
            ['current_field' => $fieldName],
            $form
        );
    }

    public static function getAjaxUrl()
    {
        return apply_filters('fluentform/ajax_url', admin_url('admin-ajax.php'));
    }

    public static function getDefaultDateTimeFormatForMoment()
    {
        $phpFormat = get_option('date_format') . ' ' . get_option('time_format');

        $replacements = [
            'A' => 'A',      // for the sake of escaping below
            'a' => 'a',      // for the sake of escaping below
            'B' => '',       // Swatch internet time (.beats), no equivalent
            'c' => 'YYYY-MM-DD[T]HH:mm:ssZ', // ISO 8601
            'D' => 'ddd',
            'd' => 'DD',
            'e' => 'zz',     // deprecated since version 1.6.0 of moment.js
            'F' => 'MMMM',
            'G' => 'H',
            'g' => 'h',
            'H' => 'HH',
            'h' => 'hh',
            'I' => '',       // Daylight Saving Time? => moment().isDST();
            'i' => 'mm',
            'j' => 'D',
            'L' => '',       // Leap year? => moment().isLeapYear();
            'l' => 'dddd',
            'M' => 'MMM',
            'm' => 'MM',
            'N' => 'E',
            'n' => 'M',
            'O' => 'ZZ',
            'o' => 'YYYY',
            'P' => 'Z',
            'r' => 'ddd, DD MMM YYYY HH:mm:ss ZZ', // RFC 2822
            'S' => 'o',
            's' => 'ss',
            'T' => 'z',      // deprecated since version 1.6.0 of moment.js
            't' => '',       // days in the month => moment().daysInMonth();
            'U' => 'X',
            'u' => 'SSSSSS', // microseconds
            'v' => 'SSS',    // milliseconds (from PHP 7.0.0)
            'W' => 'W',      // for the sake of escaping below
            'w' => 'e',
            'Y' => 'YYYY',
            'y' => 'YY',
            'Z' => '',       // time zone offset in minutes => moment().zone();
            'z' => 'DDD',
        ];

        // Converts escaped characters.
        foreach ($replacements as $from => $to) {
            $replacements['\\' . $from] = '[' . $from . ']';
        }

        $format = strtr($phpFormat, $replacements);

        return apply_filters('fluentform/moment_date_time_format', $format);
    }

    public static function isDefaultWPDateEnabled()
    {
        $globalSettings = get_option('_fluentform_global_form_settings');
        return 'wp_default' === ArrayHelper::get($globalSettings, 'misc.default_admin_date_time');
    }

    public static function isPaymentCompatible()
    {
        if (!self::hasPro()) {
            return true;
        } else {
            return version_compare(FLUENTFORMPRO_VERSION, FLUENTFORM_MINIMUM_PRO_VERSION, '>=');
        }
    }

    /**
     * Determine pro payment script is compatible or not
     * Script is compatible if pro version meets the minimum required version
     *
     * @return bool
     */
    public static function isProPaymentScriptCompatible()
    {
        if (self::hasPro()) {
            return version_compare(FLUENTFORMPRO_VERSION, FLUENTFORM_MINIMUM_PRO_VERSION, '>=');
        }
        return false;
    }

    public static function hasPro()
    {
        return defined('FLUENTFORMPRO');
    }

    public static function getLandingPageEnabledForms()
    {
        if (class_exists(\FluentFormPro\classes\SharePage\SharePage::class)) {
            if (method_exists(\FluentFormPro\classes\SharePage\SharePage::class, 'getLandingPageFormIds')) {
                $sharePage = new \FluentFormPro\classes\SharePage\SharePage();
                return $sharePage->getLandingPageFormIds();
            }
        }
        return [];
    }

    public static function sanitizeArrayKeysAndValues($values)
    {
        if (is_array($values)) {
            $sanitized = [];
            foreach ($values as $key => $value) {
                $trimmedKey = sanitize_text_field(trim($key));
                $trimmedValue = sanitize_text_field(trim($value));
                $sanitized[$trimmedKey] = $trimmedValue;
            }
            return $sanitized;
        }
        return sanitize_text_field(trim($values));
    }
    public static function getFrontendFacingUrl($args = '')
    {
        return home_url($args);
    }

    public static function getCountryCodeFromHeaders()
    {
        $headers = [
            // Cloudflare (most common)
            'HTTP_CF_IPCOUNTRY',
            'CF-IPCountry',

            // AWS CloudFront (widely used)
            'HTTP_CLOUDFRONT_VIEWER_COUNTRY',
            'CloudFront-Viewer-Country',

            // Common standard headers
            'HTTP_X_COUNTRY_CODE',
            'X-Country-Code',
            'HTTP_X_FORWARDED_COUNTRY',
            'X-Forwarded-Country',

            // GeoIP (used by many systems)
            'HTTP_GEOIP_COUNTRY_CODE',
            'GEOIP_COUNTRY_CODE',
            'HTTP_X_GEOIP_COUNTRY',
            'X-GeoIP-Country',

            // General purpose country headers
            'HTTP_X_COUNTRY',
            'X-Country',
            'HTTP_X_COUNTRY_ISO',
            'X-Country-ISO'
        ];

        foreach ($headers as $header) {
            // Try directly from $_SERVER
            if (isset($_SERVER[$header])) {
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- Country code from CDN/proxy header, validated below
                $code = trim(sanitize_text_field(wp_unslash($_SERVER[$header])));
            } // Try with HTTP_ prefix if not already present
            elseif (strpos($header, 'HTTP_') !== 0) {
                $httpHeader = 'HTTP_' . str_replace('-', '_', strtoupper($header));
                if (isset($_SERVER[$httpHeader])) {
                    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- Country code from CDN/proxy header, validated below
                    $code = trim(sanitize_text_field(wp_unslash($_SERVER[$httpHeader])));
                } else {
                    continue;
                }
            } else {
                continue;
            }

            // Basic validation - should be 2-letter country code
            if (!empty($code) && is_string($code) && strlen($code) === 2 && ctype_alpha($code) && $code !== 'XX') {
                return strtoupper($code);
            }
        }

        return null;
    }

    /**
     * Fixes PHP Object Injection Vulnerability
     * @param $data
     * @return mixed
     */
    public static function safeUnserialize($data)
    {
        if (is_serialized($data)) { // Don't attempt to unserialize data that wasn't serialized going in.
            return @unserialize(trim($data), ['allowed_classes' => false]);
        }
        return $data;
    }

	/**
	 * If elementor editor is open
	 * @return bool
	 */
    public static function isElementorEditor()
    {
        return defined('ELEMENTOR_VERSION') &&
            class_exists('\Elementor\Plugin') &&
            isset(\Elementor\Plugin::$instance) &&
            \Elementor\Plugin::$instance->editor->is_edit_mode();
    }

    /**
     * Check if we're in block editor context (Site Editor, Template Editor, or Post/Page Editor)
     * Covers all Gutenberg block editor contexts including mobile/tablet preview iframes
     * @return bool
     */
    public static function isSiteEditor()
    {
        if (!is_admin() || !function_exists('get_current_screen')) {
            return false;
        }

        $screen = get_current_screen();

        // Check for Site Editor and Template Editor contexts
        if ($screen && in_array($screen->base, ['site-editor', 'edit-site', 'appearance_page_gutenberg-edit-site'], true )) {
            return true;
        }

        // Check for Post/Page block editor contexts
        if ($screen && in_array($screen->base, ['post', 'page'], true) && $screen->is_block_editor()) {
            return true;
        }

        // Check for custom post types with block editor
        if ($screen && $screen->is_block_editor()) {
            return true;
        }

        // Fallback checks for various block editor contexts
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- REQUEST_URI used for string comparison only
        $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Checking block editor context
        return isset( $_GET['_wp-find-template'] ) ||
               strpos( $request_uri, 'site-editor.php' ) !== false ||
               // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Checking REST API context
               (defined('REST_REQUEST') && REST_REQUEST && !empty($_REQUEST['context']) && $_REQUEST['context'] === 'edit');
    }
}
