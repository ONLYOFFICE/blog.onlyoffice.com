<?php

namespace FluentForm\App\Modules\Ai;

defined('ABSPATH') || die;

use Exception;
use FluentForm\App\Helpers\Helper;
use FluentForm\App\Models\Form;
use FluentForm\App\Models\FormMeta;
use FluentForm\App\Modules\Acl\Acl;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\App\Modules\Payments\PaymentHelper;
use FluentForm\App\Services\FluentConversational\Classes\Converter\Converter;
use FluentForm\App\Services\Form\FormService;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;
use FluentForm\Framework\Support\Sanitizer;

class AiFormBuilder extends FormService
{
    private $allDefaultFields = [];

    public function __construct()
    {
        parent::__construct();
        add_action('wp_ajax_fluentform_ai_create_form', [$this, 'buildForm'], 11, 0);
    }

    public function buildForm()
    {
        try {
            Acl::verify('fluentform_forms_manager');
            $form = $this->generateForm($this->app->request->all());
            $form = $this->prepareAndSaveForm($form);
            wp_send_json_success([
                'formId'       => $form->id,
                'redirect_url' => admin_url(
                    'admin.php?page=fluent_forms&form_id=' . $form->id . '&route=editor'
                ),
                'message'      => __('Successfully created a form.', 'fluentform'),
            ], 200);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Map the AI-generated field list into a persisted form.
     *
     * @param array $form
     * @return Form|\FluentForm\Framework\Database\Query\Builder
     * @throws Exception
     */
    protected function prepareAndSaveForm($form)
    {
        $allFields = $this->getDefaultFields();
        $fluentFormFields = [];
        $fields = Arr::get($form, 'fields', []);
        $isConversational = Arr::isTrue($form, 'is_conversational');
        $customCss = Arr::get($form, 'custom_css', '');
        $hasStep = false;
        $lastFieldIndex = count($fields) - 1;

        $disableFields = array_keys($this->getDisabledComponents());
        foreach ($fields as $index => $field) {
            if (count($field) == 1) {
                $field = reset($field);
            }
            if ($element = $this->resolveInput($field)) {
                if (in_array($element, $disableFields)) {
                    continue;
                }
                if (!$hasStep && 'form_step' === $element) {
                    if (0 === $index || $lastFieldIndex === $index) {
                        continue;
                    }
                    $hasStep = true;
                }
                $fluentFormFields[] = $this->processField($element, $field, $allFields);
            }
        }
        $fluentFormFields = array_filter($fluentFormFields);
        if (!$fluentFormFields) {
            throw new Exception(esc_html__('Empty form. Please try again!', 'fluentform'));
        }
        $title = Arr::get($form, 'title', '');
        return $this->saveForm($fluentFormFields, $title, $hasStep, $isConversational, $customCss);
    }

    /**
     * Send the prompt to the AI service and return the decoded form fields.
     *
     * @param array $args
     * @return array response form fields
     * @throws Exception
     */
    protected function generateForm($args)
    {
        $aiModel = Arr::get($args, 'ai_model', 'default');
        $isUsingChatGpt = Helper::hasPro() && 'chat_gpt' == $aiModel && class_exists('FluentFormPro\classes\Chat\ChatFormBuilder');

        if ($isUsingChatGpt) {
            (new \FluentFormPro\classes\Chat\ChatFormBuilder())->buildForm();
        }

        $paymentSetting = PaymentHelper::getPaymentSettings();
        $queryArgs = [
            'user_prompt'    => $this->getUserPrompt($args),
            'site_url'       => site_url(),
            'site_title'     => get_bloginfo('name'),
            'site_locale'    => determine_locale(),
            'has_pro'        => Helper::hasPro(),
            'has_payment'    => 'yes' == $paymentSetting['status'],
            'request_id'     => uniqid('ff_ai_'),
            'save_usage'     => apply_filters('fluentform/ai_save_usage', true),
        ];

        $result = (new FluentFormAIAPI())->makeRequest($queryArgs);

        if (is_wp_error($result)) {
            throw new Exception(esc_html($result->get_error_message()));
        }

        $response = trim(Arr::get($result, 'response', ''), '"');
        // preg_match() returns 0 when there is no fence and false only on error,
        // so this must test for an actual match — otherwise an unfenced (and
        // perfectly valid) JSON reply is replaced with an empty string.
        if (1 === preg_match('/```json(.*?)```/s', $response, $matches)) {
            $response = trim($matches[1]);
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($decoded) || empty($decoded['fields'])) {
            throw new Exception(esc_html__('Invalid response: Please try again!', 'fluentform'));
        }
        return $this->applyPromptHints($decoded, $args);
    }

    protected function getDefaultFields()
    {
        if ($this->allDefaultFields) {
            return $this->allDefaultFields;
        }
        $components = $this->app->make('components');
        $this->app->doAction('fluentform/editor_init', $components);
        $editorComponents = $components->toArray();
        // Re-key by element name. The palette groups are keyed by element in
        // DefaultElements.php, but Components::sort() renumbers them 0..n for
        // the editor's JSON contract - so whether these arrive keyed or as a
        // list depends on whether anything rendered the palette earlier in the
        // request. resolveInput() matches on the key, so normalise here.
        $general = array_column(Arr::get($editorComponents, 'general', []), null, 'element');
        $advanced = array_column(Arr::get($editorComponents, 'advanced', []), null, 'element');
        $container = Arr::get($editorComponents, 'container', []);

        // Apply filter to get additional components
        // The second parameter (true) is passed to prevent field loss when form ID is falsy loss some field
        $editorComponents = apply_filters('fluentform/editor_components', [], true);
        if ($generalExtra = Arr::get($editorComponents, 'general')) {
            $generalExtra = array_column($generalExtra, null, 'element');
            $general = array_merge($general, $generalExtra);
        }

        if ($advancedExtra = Arr::get($editorComponents, 'advanced')) {
            $advancedExtra = array_column($advancedExtra, null, 'element');
            $advanced = array_merge($advanced, $advancedExtra);
        }

        $payments = Arr::get($editorComponents, 'payments', []);
        $payments = array_column($payments, null, 'element');
        $this->allDefaultFields = array_merge($general, $payments, $advanced, ['container' => $container]);
        return $this->allDefaultFields;
    }

    protected function processField($element, $field, $allFields)
    {
        if ('container' == $element) {
            return $this->resolveContainerFields($field, $allFields);
        }

        $matchedField = Arr::get($allFields, $element);
        if (!$matchedField) {
            return [];
        }
        $formatField = $matchedField;
        if ($settings = Arr::get($field, 'settings')) {
            // Replace 'label' with 'admin_field_label' if 'label' is shorter
            if (isset($settings['label']) && $adminFieldLabel = Arr::get($settings, 'admin_field_label')) {
                if (strlen($settings['label']) < strlen($adminFieldLabel)) {
                    $settings['label'] = $adminFieldLabel;
                }
            }
            $formatField['settings'] = wp_parse_args($settings, $matchedField['settings']);
        }
        if ($attributes = Arr::get($field, 'attributes')) {
            $formatField['attributes'] = wp_parse_args($attributes, $matchedField['attributes']);
        }

        $formatField['uniqElKey'] = 'el_' . uniqid();

        if ('form_step' === $element) {
            return $formatField;
        }

        if ($fieldName = Arr::get($field, 'attributes.name')) {
            $formatField['attributes']['name'] = $fieldName;
        }

        if ($options = $this->getOptions(Arr::get($field, 'options'))) {
            if (isset($formatField['settings']['advanced_options'])) {
                $formatField['settings']['advanced_options'] = $options;
            }
            if ('ratings' == $element) {
                $formatField['options'] = array_column($options, 'label', 'value');
            }
        }

        if ('rangeslider' == $element) {
            if ($min = Arr::get($field, 'min')) {
                $formatField['attributes']['min'] = intval($min);
            }
            if ($max = intval(Arr::get($field, 'max', 10))) {
                $formatField['attributes']['max'] = $max;
            }
        }

        if (in_array($element, ['input_name', 'address']) && $fields = Arr::get($field, 'fields')) {
            foreach ($formatField['fields'] as $name => &$field) {
                if ($targetAttributes = Arr::get($fields, "$name.attributes")) {
                    $field['attributes'] = wp_parse_args($targetAttributes, $field['attributes']);
                }
                if ($targetSettings = Arr::get($fields, "$name.settings")) {
                    $field['settings'] = wp_parse_args($targetSettings, $field['settings']);
                }
            }
        }

        return $formatField;
    }

    protected function resolveInput($field)
    {
        if (!is_array($field)) {
            return false;
        }
        $element = Arr::get($field, 'element');
        $allElements = array_keys($this->getDefaultFields());
        if (in_array($element, $allElements)) {
            return $element;
        }

        $type = Arr::get($field, 'type');
        if (!$type) {
            return false;
        }

        $searchTags = fluentformLoadFile('Services/FormBuilder/ElementSearchTags.php');
        $form = ['type' => ''];
        $form = json_decode(json_encode($form));
        $searchTags = apply_filters('fluentform/editor_element_search_tags', $searchTags, $form);
        foreach ($searchTags as $inputKey => $tags) {
            if (array_search($type, $tags) !== false) {
                return $inputKey;
            } else {
                foreach ($tags as $tag) {
                    if (strpos($tag, $type) !== false) {
                        return $inputKey;
                    }
                }
            }
        }
        return false;
    }

    protected function getOptions($options = [])
    {
        $formattedOptions = [];
        if (empty($options) || !is_array($options)) {
            return $options;
        }
        foreach ($options as $key => $option) {
            if (is_string($option) || is_numeric($option)) {
                $value = $label = $option;
            } elseif (is_array($option)) {
                $label = Arr::get($option, 'label');
                $value = Arr::get($option, 'value');
            } else {
                continue;
            }
            if (!$value || !$label) {
                $value = $value ?? $label;
                $label = $label ?? $value;
            }
            if (!$value || !$label) {
                continue;
            }
            $formattedOptions[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return $formattedOptions;
    }

    protected function getBlankFormConfig()
    {
        $attributes = ['type' => 'form', 'predefined' => 'blank_form'];
        $customForm = Form::resolvePredefinedForm($attributes);
        $customForm['form_fields'] = json_decode($customForm['form_fields'], true);
        $customForm['form_fields']['submitButton'] = $customForm['form']['submitButton'];
        $customForm['form_fields'] = json_encode($customForm['form_fields']);
        return $customForm;
    }

    protected function saveForm($formattedInputs, $title, $isStepForm = false, $isConversational = false, $customCss = '')
    {
        $customForm = $this->prepareCustomForm($formattedInputs, $isStepForm);
        $data = Form::prepare($customForm);

        $form = $this->model->create($data);
        $form->title = $title ? $title : $form->title . ' (ChatGPT#' . $form->id . ')';

        $formData = (object) $form->toArray();
        if (FormFieldsParser::hasPaymentFields($formData)) {
            $form->has_payment = 1;
        }

        if ($isConversational) {
            $formMeta = FormMeta::prepare(['type' => 'form', 'predefined' => 'conversational'], $customForm);
            $form->fill([
                'form_fields' => Converter::convertExistingForm($form),
            ])->save();
        } else {
            $form->save();
            $formMeta = FormMeta::prepare(['type' => 'form', 'predefined' => 'blank_form'], $customForm);
        }

        FormMeta::store($form, $formMeta);

        if ($customCss = fluentformSanitizeCSS($customCss)) {
            Helper::setFormMeta($form->id, '_custom_form_css', $customCss);
        }

        do_action('fluentform/inserted_new_form', $form->id, $data);
        return $form;
    }

    protected function prepareCustomForm($formattedInputs, $isStepForm)
    {
        $formattedInputs = fluentFormSanitizer($formattedInputs);
        $customForm = $this->getBlankFormConfig();
        $fields = json_decode($customForm['form_fields'], true);

        $fields['form_fields']['fields'] = $formattedInputs;
        $fields['form_fields']['submitButton'] = Arr::get($customForm, 'form.submitButton');

        if ($isStepForm) {
            $fields['form_fields']['stepsWrapper'] = $this->getStepWrapper();
        }

        $customForm['form_fields'] = json_encode($fields['form_fields']);

        return $customForm;
    }

    protected function resolveContainerFields($field, $allFields)
    {
        $columns = Arr::get($field, 'columns');
        $columnsCount = count($columns);
        if (!$columnsCount || $columnsCount > 6) {
            return [];
        }
        $matchedField = Arr::get($allFields, 'container.container_' . $columnsCount . '_col');
        if (!$matchedField) {
            return [];
        }
        $columnWidth = round(100 / $columnsCount, 2);
        foreach ($columns as &$column) {
            $formatedFields = [];
            $fields = Arr::get($column, 'fields', []);
            $columnWidth = Arr::get($column, 'width', $columnWidth);
            foreach ($fields as $colField) {
                $element = Arr::get($colField, 'element');
                if ($columnField = $this->processField($element, $colField, $allFields)) {
                    $formatedFields[] = $columnField;
                }
            }
            if ($formatedFields) {
                $column['fields'] = $formatedFields;
                $column['width'] = $columnWidth;
            }
        }
        $matchedField['columns'] = $columns;
        return $matchedField;
    }

    /**
     * Build the step-wrapper skeleton used to wrap a multi-step form.
     *
     * @return array
     */
    protected function getStepWrapper()
    {
        return [
            'stepStart' => [
                'element'        => 'step_start',
                'attributes'     => [
                    'id'    => '',
                    'class' => '',
                ],
                'settings'       => [
                    'progress_indicator'           => 'progress-bar',
                    'step_titles'                  => [],
                    'disable_auto_focus'           => 'no',
                    'enable_auto_slider'           => 'no',
                    'enable_step_data_persistency' => 'no',
                    'enable_step_page_resume'      => 'no',
                ],
                'editor_options' => [
                    'title' => 'Start Paging',
                ],
            ],
            'stepEnd'   => [
                'element'        => 'step_end',
                'attributes'     => [
                    'id'    => '',
                    'class' => '',
                ],
                'settings'       => [
                    'prev_btn' => [
                        'type'    => 'default',
                        'text'    => 'Previous',
                        'img_url' => '',
                    ],
                ],
                'editor_options' => [
                    'title' => 'End Paging',
                ],
            ],
        ];
    }

    private function getUserPrompt($args)
    {
        $startingQuery = 'Create a form for ';
        $query = Sanitizer::sanitizeTextField(Arr::get($args, 'query'));
        if (empty($query)) {
            throw new Exception(esc_html__('Query is empty!', 'fluentform'));
        }

        // Validate query length to prevent abuse (filterable; default 12000 characters)
        $maxQueryLength = (int) apply_filters('fluentform/ai_query_max_length', 12000);
        if (mb_strlen($query) > $maxQueryLength) {
            throw new Exception(esc_html(sprintf(
                /* translators: %d is the maximum allowed number of characters */
                __('Query is too long. Please limit your prompt to %d characters.', 'fluentform'),
                $maxQueryLength
            )));
        }

        $additionalQuery = Sanitizer::sanitizeTextField(Arr::get($args, 'additional_query'));

        // Validate additional query length (filterable; default 6000 characters)
        $maxAdditionalQueryLength = (int) apply_filters('fluentform/ai_additional_query_max_length', 6000);
        if ($additionalQuery && mb_strlen($additionalQuery) > $maxAdditionalQueryLength) {
            throw new Exception(esc_html(sprintf(
                /* translators: %d is the maximum allowed number of characters */
                __('Additional query is too long. Please limit to %d characters.', 'fluentform'),
                $maxAdditionalQueryLength
            )));
        }

        if ($additionalQuery) {
            $query .= "\n including questions for information like  " . $additionalQuery . '.';
        }
        return $startingQuery . $query . $this->getPromptContractInstructions();
    }

    private function getPromptContractInstructions()
    {
        return "\n\nReturn strict JSON only. The user's instructions may be written in any language. "
            . "Preserve labels and help text in the user's original language, but always return machine-readable field settings. "
            . 'For every field, explicitly set whether it is required in settings.validation_rules.required.value when the prompt marks it as required or optional. '
            . 'If the prompt lists allowed upload extensions, map them into settings.validation_rules.allowed_file_types.value. '
            . 'If the prompt asks for a multi-step form with sections, include form_step elements between sections.';
    }

    private function applyPromptHints(array $form, array $args)
    {
        $query = (string) Arr::get($args, 'query', '');
        $additionalQuery = (string) Arr::get($args, 'additional_query', '');
        $hints = $this->extractPromptFieldHints(trim($query . "\n" . $additionalQuery));

        if (!$hints) {
            return $form;
        }

        $hintIndex = 0;
        $form['fields'] = $this->applyHintsToFields(Arr::get($form, 'fields', []), $hints, $hintIndex);

        return $form;
    }

    private function applyHintsToFields(array $fields, array $hints, &$hintIndex)
    {
        foreach ($fields as &$field) {
            if (!is_array($field)) {
                continue;
            }

            if (count($field) === 1) {
                $field = reset($field);
            }

            $element = Arr::get($field, 'element');

            if ('container' === $element) {
                $columns = Arr::get($field, 'columns', []);
                foreach ($columns as &$column) {
                    $column['fields'] = $this->applyHintsToFields(Arr::get($column, 'fields', []), $hints, $hintIndex);
                }
                $field['columns'] = $columns;
                continue;
            }

            if (in_array($element, ['form_step', 'section_break'])) {
                continue;
            }

            $hint = Arr::get($hints, $hintIndex);
            ++$hintIndex;

            if (!$hint) {
                continue;
            }

            $field = $this->mergeFieldHint($field, $hint);
        }

        return $fields;
    }

    private function mergeFieldHint(array $field, array $hint)
    {
        if (array_key_exists('required', $hint)) {
            Arr::set($field, 'settings.validation_rules.required.value', (bool) $hint['required']);
        }

        if (!empty($hint['help_message'])) {
            Arr::set($field, 'settings.help_message', $hint['help_message']);
        }

        if (!empty($hint['allowed_file_types']) && in_array(Arr::get($field, 'element'), ['input_file', 'input_image'])) {
            Arr::set($field, 'settings.validation_rules.allowed_file_types.value', array_values(array_unique($hint['allowed_file_types'])));
        }

        return $field;
    }

    private function extractPromptFieldHints($prompt)
    {
        if (!$prompt) {
            return [];
        }

        $lines = preg_split('/\R/u', $prompt);
        $hints = [];

        foreach ($lines as $line) {
            $line = trim(wp_strip_all_tags($line));
            $line = preg_replace('/^[\-\*\d\.\)\s]+/u', '', $line);

            if (!$line || !$this->looksLikeFieldLine($line)) {
                continue;
            }

            $attributesText = '';
            if (preg_match('/\(([^()]*)\)/u', $line, $matches)) {
                $attributesText = trim($matches[1]);
            }

            $hint = [];
            $normalizedLine = function_exists('mb_strtolower') ? mb_strtolower($line, 'UTF-8') : strtolower($line);

            if ($this->containsAnyPhrase($normalizedLine, $this->getRequiredKeywords())) {
                $hint['required'] = true;
            } elseif ($this->containsAnyPhrase($normalizedLine, $this->getOptionalKeywords())) {
                $hint['required'] = false;
            }

            $helpMessage = $this->extractHelpMessage($attributesText);
            if ($helpMessage) {
                $hint['help_message'] = $helpMessage;
            }

            if ($this->containsAnyPhrase($normalizedLine, $this->getFileUploadKeywords())) {
                $allowedTypes = $this->extractAllowedFileTypes($line);
                if ($allowedTypes) {
                    $hint['allowed_file_types'] = $allowedTypes;
                }
            }

            $hints[] = $hint;
        }

        return $hints;
    }

    private function looksLikeFieldLine($line)
    {
        if (preg_match('/^(create|erstelle|crée|crear|criar|crea|maak|utw[oó]rz|oluştur|创建|作成)\b/ui', $line)) {
            return false;
        }

        if (preg_match('/^(section|abschnitt|secci[oó]n|sectione|seção|sectie|sekcja|b[oö]l[uü]m|章节|セクション)\b/ui', $line)) {
            return false;
        }

        if (false === strpos($line, '(')) {
            return false;
        }

        preg_match('/\(([^()]*)\)/u', $line, $matches);
        $attributesText = isset($matches[1]) ? $matches[1] : '';

        return $this->containsAnyPhrase(
            function_exists('mb_strtolower') ? mb_strtolower($attributesText, 'UTF-8') : strtolower($attributesText),
            $this->getFieldDescriptorKeywords()
        );
    }

    private function extractHelpMessage($attributesText)
    {
        if (!$attributesText) {
            return '';
        }

        if (preg_match('/(?:note|hint|hinweis|remarque|nota|nota bene|opmerking|uwaga|not|说明|備考)\s*:\s*["“]?(.+?)["”]?$/ui', $attributesText, $matches)) {
            return trim($matches[1], " \t\n\r\0\x0B\"'“”");
        }

        return '';
    }

    private function extractAllowedFileTypes($line)
    {
        preg_match_all('/\b(jpe?g|gif|png|tiff?|bmp|webp|svg|pdf|docx?|xlsx?|xls|csv|zip|rar|txt)\b/ui', $line, $matches);

        if (empty($matches[1])) {
            return [];
        }

        $types = array_map(function ($type) {
            $type = strtolower($type);

            if ('jpeg' === $type) {
                return 'jpg';
            }

            if ('tif' === $type) {
                return 'tiff';
            }

            return $type;
        }, $matches[1]);

        return array_values(array_unique($types));
    }

    private function containsAnyPhrase($text, array $phrases)
    {
        foreach ($phrases as $phrase) {
            if (false !== strpos($text, $phrase)) {
                return true;
            }
        }

        return false;
    }

    private function getRequiredKeywords()
    {
        return [
            'required',
            'mandatory',
            'pflichtfeld',
            'erforderlich',
            'obligatoire',
            'requis',
            'obligatorio',
            'obrigatório',
            'obbligatorio',
            'verplicht',
            'wymagane',
            'zorunlu',
            '必填',
            '必須',
        ];
    }

    private function getOptionalKeywords()
    {
        return [
            'optional',
            'facultatif',
            'facoltativo',
            'opcjonalne',
            'isteğe bağlı',
            'opcional',
            'opcionales',
            '选填',
            '任意',
        ];
    }

    private function getFileUploadKeywords()
    {
        return [
            'file upload',
            'upload',
            'datei',
            'datei upload',
            'foto upload',
            'image upload',
            'upload de fichier',
            'carga de archivo',
            'subida de archivo',
            'carregamento de arquivo',
            'caricamento file',
            'bestandsupload',
            'yükleme',
            '上传',
        ];
    }

    private function getFieldDescriptorKeywords()
    {
        return [
            'text',
            'textarea',
            'email',
            'date',
            'file',
            'upload',
            'phone',
            'number',
            'radio',
            'checkbox',
            'select',
            'dropdown',
            'image',
            'foto',
            'bild',
            'datei',
            'telefon',
            'mobil',
            'correo',
            'texte',
            'texto',
            'testo',
            'fecha',
            'fichier',
            'archivo',
            'caricamento',
            'ficheiro',
            'bestand',
            'yükleme',
            '上传',
        ];
    }
}
