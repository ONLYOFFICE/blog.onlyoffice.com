<?php

namespace FluentForm\App\Services\Transfer;

defined('ABSPATH') or die;

use Exception;
use FluentForm\App\Helpers\Helper;
use FluentForm\App\Models\Form;
use FluentForm\App\Models\FormMeta;
use FluentForm\App\Models\Submission;
use FluentForm\App\Models\SubmissionMeta;
use FluentForm\App\Modules\Acl\Acl;
use FluentForm\App\Modules\Form\FormDataParser;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\App\Services\FormBuilder\ShortCodeParser;
use FluentForm\Framework\Foundation\App;
use FluentForm\Framework\Http\Request\File;
use FluentForm\Framework\Support\Arr;

class TransferService
{
    public static function sanitizeImportedMetaValue($metaKey, $metaValue)
    {
        if (!is_string($metaValue)) {
            return $metaValue;
        }

        if ($metaKey === '_custom_form_css') {
            return fluentformSanitizeCSS($metaValue);
        }

        if ($metaKey === '_custom_form_js') {
            return fluentform_kses_js($metaValue);
        }

        $decoded = json_decode($metaValue);
        if (is_array($decoded) || is_object($decoded)) {
            self::sanitizeJsonNode($decoded);
            $encoded = wp_json_encode($decoded);
            return $encoded ?: $metaValue;
        }

        return wp_kses_post($metaValue);
    }

    private static function sanitizeJsonNode(&$node)
    {
        if (is_array($node)) {
            foreach ($node as &$value) {
                self::sanitizeJsonNode($value);
            }
            unset($value);
        } elseif (is_object($node)) {
            foreach (get_object_vars($node) as $key => $value) {
                self::sanitizeJsonNode($value);
                $node->{$key} = $value;
            }
        } elseif (is_string($node)) {
            $node = wp_kses_post($node);
        }
    }

    public static function exportForms($formIds)
    {
        $result = Form::with(['formMeta'])
            ->whereIn('id', $formIds)
            ->get();
        $forms = [];
        foreach ($result as $item) {
            $form = json_decode($item);
            $formMetaFiltered = array_filter($form->form_meta, function ($item) {
                return ($item->meta_key !== '_total_views');
            });
            $form->metas = $formMetaFiltered;
            $form->form_fields = json_decode($form->form_fields);
            $forms[] = $form;
        }

        $fileName = 'fluentform-export-forms-' . count($forms) . '-' . date('d-m-Y') . '.json';

        header('Content-disposition: attachment; filename=' . $fileName);

        header('Content-type: application/json');

        echo json_encode(array_values($forms)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $forms is escaped before being passed in.

        die();
    }

    /**
     * Build the notice shown when imported custom JS/CSS was skipped because the
     * importer lacks unfiltered_html. Returns an empty string when nothing was skipped.
     *
     * @param int $skippedForms
     * @param int $totalForms
     * @return string
     */
    protected static function restrictedCodeNotice($skippedForms, $totalForms)
    {
        if (!$skippedForms) {
            return '';
        }

        if ($totalForms < 2) {
            return __('Custom JS and CSS were not imported because your account cannot add custom code. Ask an administrator to add it.', 'fluentform');
        }

        return sprintf(
            /* translators: 1: number of forms whose custom code was skipped, 2: total number of imported forms */
            __('Custom JS and CSS were not imported for %1$d of %2$d forms because your account cannot add custom code. Ask an administrator to add it.', 'fluentform'),
            $skippedForms,
            $totalForms
        );
    }

    /**
     * @param File $file The uploaded JSON file
     * @param bool $applyDefaultStyle Whether to apply default style settings to imported forms
     * @throws Exception
     */
    public static function importForms($file, $applyDefaultStyle = false)
    {
        if ($file instanceof File) {
            $forms = \json_decode($file->getContents(), true);
            $insertedForms = [];
            $restrictedCodeForms = 0;
            if ($forms && is_array($forms)) {
                foreach ($forms as $formItem) {
                    $formFields = json_encode([]);
                    if ($fields = Arr::get($formItem, 'form', '')) {
                        $formFields = json_encode($fields);
                    } elseif ($fields = Arr::get($formItem, 'form_fields', '')) {
                        $formFields = json_encode($fields);
                    } else {
                        throw new Exception(esc_html__('You have a faulty JSON file, please export the Fluent Forms again.', 'fluentform'));
                    }

                    // SECURITY (FINDING-07): the editor save path routes form_fields through
                    // Updater::sanitizeFields (skipped only for unfiltered_html users), but import
                    // stored them verbatim, so an importer without unfiltered_html could plant
                    // stored XSS (e.g. a field label of <img onerror=...>). Apply the same
                    // recursive HTML sanitizer used for imported meta values unless the importer
                    // may author raw HTML.
                    if (!fluentformCanUnfilteredHTML()) {
                        $decodedFields = json_decode($formFields, true);
                        if (is_array($decodedFields)) {
                            static::sanitizeJsonNode($decodedFields);
                            $formFields = wp_json_encode($decodedFields) ?: $formFields;
                        }
                    }

                    $formTitle = sanitize_text_field(Arr::get($formItem, 'title'));
                    $form = [
                        'title'       => $formTitle ?: 'Blank Form',
                        'form_fields' => $formFields,
                        'status'      => sanitize_text_field(Arr::get($formItem, 'status', 'published')),
                        'has_payment' => sanitize_text_field(Arr::get($formItem, 'has_payment', 0)),
                        'type'        => sanitize_text_field(Arr::get($formItem, 'type', 'form')),
                        'created_by'  => get_current_user_id(),
                    ];

                    if (Arr::get($formItem, 'conditions')) {
                        $form['conditions'] = Arr::get($formItem, 'conditions');
                    }

                    if (isset($formItem['appearance_settings'])) {
                        $form['appearance_settings'] = Arr::get($formItem, 'appearance_settings');
                    }

                    $formId = Form::insertGetId($form);
                    $insertedForms[$formId] = [
                        'title'    => $form['title'],
                        'edit_url' => admin_url('admin.php?page=fluent_forms&route=editor&form_id=' . $formId),
                    ];

                    $skippedCustomCode = false;

                    if (isset($formItem['metas'])) {
                        foreach ($formItem['metas'] as $metaData) {
                            $metaKey = sanitize_text_field(Arr::get($metaData, 'meta_key'));
                            $metaValue = Arr::get($metaData, 'value');
                            // SECURITY (FINDING-08): Customizer::store() refuses to save custom
                            // JS/CSS without unfiltered_html; import must honor the same boundary.
                            // Sanitizing _custom_form_js via fluentform_kses_js is insufficient
                            // because the value is JS *code* executed inside a <script> block (kses
                            // only strips <script> tags), so skip these keys entirely for importers
                            // who cannot author raw JS/CSS.
                            if (
                                in_array($metaKey, ['_custom_form_js', '_custom_form_css'], true)
                                && !fluentformCanUnfilteredHTML()
                            ) {
                                $skippedCustomCode = true;
                                continue;
                            }
                            if ('ffc_form_settings_generated_css' == $metaKey || 'ffc_form_settings_meta' == $metaKey) {
                                $metaValue = str_replace('ff_conv_app_' . Arr::get($formItem, 'id'), 'ff_conv_app_' . $formId, $metaValue);
                            }
                            $metaValue = static::sanitizeImportedMetaValue($metaKey, $metaValue);
                            $settings = [
                                'form_id'  => $formId,
                                'meta_key' => $metaKey,
                                'value'    => $metaValue,
                            ];
                            FormMeta::insert($settings);
                        }
                    } else {
                        $oldKeys = [
                            'formSettings',
                            'notifications',
                            'mailchimp_feeds',
                            'slack',
                        ];
                        foreach ($oldKeys as $key) {
                            if (isset($formItem[$key])) {
                                FormMeta::persist($formId, $key, json_encode(Arr::get($formItem, $key)));
                            }
                        }
                    }

                    if ($skippedCustomCode) {
                        $restrictedCodeForms++;
                    }

                    do_action('fluentform/form_imported', $formId);

                    // Apply default style if requested
                    if ($applyDefaultStyle) {
                        do_action('fluentform/inserted_new_form', $formId, $form);
                    }
                }

                return ([
                    'message'                 => __('You form has been successfully imported.', 'fluentform'),
                    'inserted_forms'          => $insertedForms,
                    'restricted_code_notice'  => static::restrictedCodeNotice($restrictedCodeForms, count($insertedForms)),
                ]);
            }
        }
        throw new Exception(esc_html__('You have a faulty JSON file, please export the Fluent Forms again.', 'fluentform'));
    }

    public static function exportEntries($args)
    {
        if (!defined('FLUENTFORM_EXPORTING_ENTRIES')) {
            define('FLUENTFORM_EXPORTING_ENTRIES', true);
        }

        $formId = Acl::verifyFormId(Arr::get($args, 'form_id'));
        Acl::verify('fluentform_entries_viewer', $formId);

        $tableName = Arr::get($args, 'table');
        try {
            $form = Form::findOrFail($formId);
        } catch (Exception $e) {
            exit('No Form Found');
        }
        $type = sanitize_key(Arr::get($args, 'format', 'csv'));
        if (!in_array($type, ['csv', 'ods', 'xlsx', 'json'])) {
            exit('Invalid requested format');
        }
        if ('json' == $type) {
            self::exportAsJSON($form, $args);
        }
        if (!defined('FLUENTFORM_DOING_CSV_EXPORT')) {
            define('FLUENTFORM_DOING_CSV_EXPORT', true);
        }
        $formInputs = FormFieldsParser::getEntryInputs($form, ['admin_label', 'raw']);
        $inputLabels = FormFieldsParser::getAdminLabels($form, $formInputs);
        $selectedLabels = Arr::get($args, 'fields_to_export');
        if (is_string($selectedLabels) && Helper::isJson($selectedLabels)) {
            $selectedLabels = \json_decode($selectedLabels, true);
        }
        $selectedLabels = fluentFormSanitizer($selectedLabels);

        $withNotes = isset($args['with_notes']);

        //filter out unselected fields
        if (!empty($selectedLabels)) {
            foreach ($inputLabels as $key => $value) {
                if (!in_array($key, $selectedLabels) && isset($inputLabels[$key])) {
                    unset($inputLabels[$key]);
                }
            }
        }

        $submissions = self::getSubmissions($args);
        $submissions = FormDataParser::parseFormEntries($submissions, $form, $formInputs);
        $parsedShortCodes = [];
        $exportData = [];
        $selectedShortcodes = self::getSelectedExportShortcodes($args, $form);
        $legacyShortcodeHeaders = self::getLegacyExportShortcodeHeaders();

        // Preload notes for all submissions in a single query to avoid N+1
        $notesMap = [];
        if ($withNotes && count($submissions)) {
            $submissionIds = array_map(function ($s) {
                return is_object($s) ? $s->id : $s['id'];
            }, $submissions->toArray());
            $allNotes = SubmissionMeta::whereIn('response_id', $submissionIds)
                ->where('meta_key', '_notes')
                ->get();
            foreach ($allNotes as $note) {
                $notesMap[$note->response_id][] = $note->value;
            }
        }

        foreach ($submissions as $submission) {

            $submission->response = json_decode($submission->response, true);

            $temp = [];
            foreach ($inputLabels as $field => $label) {

                //format tabular grid data for CSV/XLSV/ODS export
                if (isset($formInputs[$field]['element']) && 'tabular_grid' === $formInputs[$field]['element']) {
                    $gridRawData = Arr::get($submission->response, $field);
                    $content = Helper::getTabularGridFormatValue($gridRawData, Arr::get($formInputs, $field), ' | ');
                } elseif (isset($formInputs[$field]['element']) && 'subscription_payment_component' === $formInputs[$field]['element']) {
                    //resolve plane name for subscription field
                    $planIndex = Arr::get($submission->user_inputs, $field);
                    $planLabel = Arr::get($formInputs, "{$field}.raw.settings.subscription_options.{$planIndex}.name");
                    if ($planLabel) {
                        $content = $planLabel;
                    } else {
                        $content = self::getFieldExportContent($submission, $field);
                    }
                } else {
                    $content = self::getFieldExportContent($submission, $field);
                    if (Arr::get($formInputs, $field . '.element') === 'input_number' && is_numeric($content)) {
                        $content = $content + 0;
                    }
                }
                $temp[] = Helper::sanitizeForCSV($content);
            }

            if (!empty($selectedShortcodes)) {
                $regularShortcodes = self::getRegularExportShortcodes($selectedShortcodes, $legacyShortcodeHeaders);

                if (!empty($regularShortcodes)) {
                    $parsedShortCodes = ShortCodeParser::parse(
                        $regularShortcodes,
                        $submission->id,
                        $submission->response,
                        $form,
                        false,
                        true
                    );
                }

                // SECURITY (FINDING-17): shortcode-export values (which include submitter-controlled
                // {inputs.*} content) bypassed the CSV formula guard applied to regular columns.
                // Sanitize each so a leading = - + @ etc. cannot execute when opened in a spreadsheet.
                $shortcodeValues = self::getSelectedShortcodeExportValues(
                    $selectedShortcodes,
                    $parsedShortCodes,
                    $legacyShortcodeHeaders,
                    $submission
                );
                $shortcodeValues = array_map(function ($v) {
                    return is_scalar($v) ? Helper::sanitizeForCSV((string) $v) : $v;
                }, $shortcodeValues);
                $temp = array_merge($temp, $shortcodeValues);
            }
            if ($withNotes) {
                $noteValues = isset($notesMap[$submission->id]) ? $notesMap[$submission->id] : [];
                if (!empty($noteValues)) {
                    // SECURITY (FINDING-17): notes are submitter-influenceable and were exported raw.
                    $temp[] = Helper::sanitizeForCSV(implode(", ", $noteValues));
                }
            }

            $temp = apply_filters('fluentform/export_entry_metadata', $temp, $submission, $form, $args);

            $exportData[] = $temp;
        }

        $extraLabels = [];

        $extraLabels = self::getSelectedShortcodeExportLabels(
            $selectedShortcodes,
            $parsedShortCodes,
            $legacyShortcodeHeaders
        );

        $inputLabels = array_merge($inputLabels, $extraLabels);
        if ($withNotes) {
            $inputLabels[] = __('Notes', 'fluentform');
        }
        $inputLabels = apply_filters('fluentform/export_entry_metadata_labels', $inputLabels, $form, $args);

        // SECURITY (FINDING-17): sanitize the header row too — field/shortcode labels can start with
        // a formula lead character (=, +, -, @) and were exported unguarded.
        $headerRow = array_map(function ($v) {
            return is_scalar($v) ? Helper::sanitizeForCSV((string) $v) : $v;
        }, array_values($inputLabels));
        $data = array_merge([$headerRow], $exportData);

        $data = apply_filters('fluentform/export_data', $data, $form, $exportData, $inputLabels);
        $fileName = self::getReadableExportFileName($form->title);
        self::downloadOfficeDoc($data, $type, $fileName);
    }

    private static function getFieldExportContent($submission, $fieldName)
    {
        return trim(
            wp_strip_all_tags(
                FormDataParser::formatValue(
                    Arr::get($submission->user_inputs, $fieldName)
                )
            )
        );
    }

    private static function getSelectedExportShortcodes($args, $form)
    {
        $selectedShortcodes = fluentFormSanitizer(Arr::get($args, 'shortcodes_to_export', []));

        if (!Arr::has($args, 'shortcodes_to_export_defined') && empty($selectedShortcodes)) {
            return self::getDefaultExportShortcodes($form);
        }

        return $selectedShortcodes;
    }

    private static function getDefaultExportShortcodes($form)
    {
        $defaults = [
            [
                'label' => __('Submission ID', 'fluentform'),
                'value' => '{submission.id}',
            ],
            [
                'label' => __('Submission Create Date', 'fluentform'),
                'value' => '{submission.created_at}',
            ],
            [
                'label' => __('Submission Status', 'fluentform'),
                'value' => '{submission.status}',
            ],
        ];

        if ($form->has_payment) {
            $defaults[] = [
                'label' => __('Payment Status', 'fluentform'),
                'value' => '{payment.payment_status}',
            ];
            $defaults[] = [
                'label' => __('Payment Total', 'fluentform'),
                'value' => '{payment.payment_total}',
            ];
            $defaults[] = [
                'label' => __('Currency', 'fluentform'),
                'value' => '{submission.currency}',
            ];
        }

        return $defaults;
    }

    private static function getLegacyExportShortcodeHeaders()
    {
        return [
            '{submission.id}'             => 'entry_id',
            '{submission.status}'         => 'entry_status',
            '{submission.created_at}'     => 'created_at',
            '{payment.payment_status}'    => 'payment_status',
            '{submission.payment_status}' => 'payment_status',
            '{payment.payment_total}'     => 'payment_total',
            '{submission.payment_total}'  => 'payment_total',
            '{submission.currency}'       => 'currency',
        ];
    }

    private static function getRegularExportShortcodes($selectedShortcodes, $legacyShortcodeHeaders)
    {
        $regularShortcodes = [];

        foreach ($selectedShortcodes as $index => $shortcode) {
            if (!isset($legacyShortcodeHeaders[Arr::get($shortcode, 'value')])) {
                $regularShortcodes[$index] = $shortcode;
            }
        }

        return $regularShortcodes;
    }

    private static function getSelectedShortcodeExportValues($selectedShortcodes, $parsedShortCodes, $legacyShortcodeHeaders, $submission)
    {
        $values = [];

        foreach ($selectedShortcodes as $index => $shortcode) {
            $shortcodeValue = Arr::get($shortcode, 'value');

            if (!isset($legacyShortcodeHeaders[$shortcodeValue])) {
                $values[] = Arr::get($parsedShortCodes, $index . '.value');
                continue;
            }

            $values[] = self::getLegacyExportValue($legacyShortcodeHeaders[$shortcodeValue], $submission);
        }

        return $values;
    }

    private static function getSelectedShortcodeExportLabels($selectedShortcodes, $parsedShortCodes, $legacyShortcodeHeaders)
    {
        $labels = [];

        foreach ($selectedShortcodes as $index => $shortcode) {
            $shortcodeValue = Arr::get($shortcode, 'value');

            if (isset($legacyShortcodeHeaders[$shortcodeValue])) {
                $labels[] = $legacyShortcodeHeaders[$shortcodeValue];
                continue;
            }

            $labels[] = Arr::get($parsedShortCodes, $index . '.label');
        }

        return $labels;
    }

    private static function getLegacyExportValue($header, $submission)
    {
        $legacyValueResolvers = [
            'entry_id' => function ($submission) {
                return $submission->id ?? '';
            },
            'entry_status' => function ($submission) {
                return $submission->status ?? '';
            },
            'created_at' => function ($submission) {
                return $submission->created_at ?? '';
            },
            'payment_status' => function ($submission) {
                return $submission->payment_status ?? '';
            },
            'payment_total' => function ($submission) {
                return round(($submission->payment_total ?? 0) / 100, 1);
            },
            'currency' => function ($submission) {
                return $submission->currency ?? '';
            },
        ];

        if (!isset($legacyValueResolvers[$header])) {
            return '';
        }

        return $legacyValueResolvers[$header]($submission);
    }

    private static function exportAsJSON($form, $args)
    {
        $formInputs = FormFieldsParser::getEntryInputs($form, ['admin_label', 'raw']);
        $submissions = self::getSubmissions($args);
        $submissions = FormDataParser::parseFormEntries($submissions, $form, $formInputs);
        foreach ($submissions as $submission) {
            $submission->response = json_decode($submission->response, true);
        }
        self::sendDownloadHeaders('application/json', self::getReadableExportFileName($form->title) . '.json');
        echo json_encode($submissions); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $submissions is escaped before being passed in.
        exit();
    }

    private static function getReadableExportFileName($formTitle)
    {
        $sanitizedTitle = sanitize_file_name(wp_strip_all_tags((string) $formTitle));

        if (!$sanitizedTitle) {
            $sanitizedTitle = 'export';
        }

        return $sanitizedTitle . '-' . date('Y-m-d');
    }

    private static function sendDownloadHeaders($contentType, $fileName)
    {
        $safeFileName = basename((string) $fileName);
        $encodedFileName = rawurlencode($safeFileName);

        header('Content-Type: ' . $contentType);
        header(
            'Content-Disposition: attachment; ' .
            'filename="' . $encodedFileName . '"; ' .
            'filename*=UTF-8\'\'' . $encodedFileName
        );
    }

    private static function getSubmissions($args)
    {
        $tableName = Arr::get($args, 'table');

        if ($tableName) {
            $allowedTables = [
                'fluentform_submissions',
                'fluentform_draft_submissions',
            ];
            if (!in_array($tableName, $allowedTables, true)) {
                wp_send_json([
                    'message' => __('Invalid table name for export.', 'fluentform'),
                ], 422);
            }
            $query = wpFluent()->table($tableName)
                ->where('form_id', (int) Arr::get($args, 'form_id'))
                ->orderBy('id', Helper::sanitizeOrderValue(Arr::get($args, 'sort_by', 'DESC')));

            $searchString = Arr::get($args, 'search');
            if ($searchString) {
                global $wpdb;
                $escaped = $wpdb->esc_like($searchString);
                $query->where(function ($q) use ($escaped) {
                    $q->where('id', 'LIKE', "%{$escaped}%")
                        ->orWhere('response', 'LIKE', "%{$escaped}%");
                });
            }
        } else {
            $query = (new Submission())->customQuery($args);
        }

        $entries = fluentFormSanitizer(Arr::get($args, 'entries', []));
        $query->when(is_array($entries) && (count($entries) > 0), function ($q) use ($entries) {
            return $q->whereIn('id', $entries);
        });

        if (Arr::get($args, 'advanced_filter')) {
            $query = apply_filters('fluentform/apply_entries_advance_filter', $query, $args);
        }

        return $query->get();
    }

    private static function downloadOfficeDoc($data, $type = 'csv', $fileName = null)
    {
        $data = array_map(function ($item) {
            return array_map(function ($itemValue) {
                if (is_array($itemValue)) {
                    $itemValue = implode(', ', $itemValue);
                }

                return is_string($itemValue)
                    ? Helper::sanitizeForCSV($itemValue)
                    : $itemValue;
            }, $item);
        }, $data);
        // Load Composer autoloader for OpenSpout
        require_once FLUENTFORM_DIR_PATH . '/vendor/autoload.php';
        $fileName = ($fileName) ? $fileName . '.' . $type : 'export-data-' . date('d-m-Y') . '.' . $type;

        // Create writer based on type
        switch (strtolower($type)) {
            case 'csv':
                $writer = \OpenSpout\Writer\Common\Creator\WriterEntityFactory::createCSVWriter();
                break;
            case 'xlsx':
                $writer = \OpenSpout\Writer\Common\Creator\WriterEntityFactory::createXLSXWriter();
                break;
            case 'ods':
                $writer = \OpenSpout\Writer\Common\Creator\WriterEntityFactory::createODSWriter();
                break;
            default:
                throw new \Exception(sprintf('Unsupported file type: %s', esc_html($type)));
        }
        $writer->openToBrowser($fileName);

        $rows = self::getOfficeDocRows($data, $type);

        $writer->addRows($rows);
        $writer->close();
        die();
    }

    private static function getOfficeDocRows($data, $type)
    {
        if (strtolower($type) !== 'xlsx') {
            return array_map(function ($rowData) {
                return \OpenSpout\Writer\Common\Creator\WriterEntityFactory::createRowFromArray($rowData);
            }, $data);
        }

        $dateStyle = (new \OpenSpout\Writer\Common\Creator\Style\StyleBuilder())
            ->setFormat('yyyy-mm-dd hh:mm:ss')
            ->build();

        return array_map(function ($rowData) use ($dateStyle) {
            $cells = array_map(function ($cellValue) use ($dateStyle) {
                if ($cellValue instanceof \DateTimeInterface) {
                    return \OpenSpout\Writer\Common\Creator\WriterEntityFactory::createCell($cellValue, $dateStyle);
                }

                return \OpenSpout\Writer\Common\Creator\WriterEntityFactory::createCell($cellValue);
            }, $rowData);

            return \OpenSpout\Writer\Common\Creator\WriterEntityFactory::createRow($cells);
        }, $data);
    }
}
