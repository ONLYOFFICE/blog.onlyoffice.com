<?php

namespace FluentForm\App\Modules\Entries;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\App\Modules\Registerer\TranslationString;
use FluentForm\Framework\Helpers\ArrayHelper;

class EntryViewRenderer
{
    public function renderEntries($form_id)
    {
        wp_enqueue_script('fluentform_form_entries');

        $forms = wpFluent()
            ->table('fluentform_forms')
            ->select(['id', 'title'])
            ->orderBy('id', 'DESC')
            ->get();

        $emailNotifications = wpFluent()
            ->table('fluentform_form_meta')
            ->where('form_id', $form_id)
            ->where('meta_key', 'notifications')
            ->get();

        $formattedNotification = [];

        foreach ($emailNotifications as $notification) {
            $value = \json_decode($notification->value, true);
            $formattedNotification[] = [
                'id'   => $notification->id,
                'name' => ArrayHelper::get($value, 'name'),
            ];
        }

        $form = wpFluent()->table('fluentform_forms')->find($form_id);
        $submissionShortcodes = \FluentForm\App\Services\FormBuilder\EditorShortCode::getSubmissionShortcodes();
        $submissionShortcodes['shortcodes']['{submission.ip}'] = __('Submitter IP', 'fluentform');
        if ($form->has_payment) {
            $submissionShortcodes['shortcodes']['{payment.payment_status}'] = __('Payment Status','fluentform');
            $submissionShortcodes['shortcodes']['{payment.payment_total}'] = __('Payment Total','fluentform');
        }
        $formInputs = FormFieldsParser::getEntryInputs($form, ['admin_label', 'raw']);
        $inputLabels = FormFieldsParser::getAdminLabels($form, $formInputs);
        $data = [
            'all_forms_url'       => admin_url('admin.php?page=fluent_forms'),
            'forms'               => $forms,
            'form_id'             => $form->id,
            'enabled_auto_delete' => Helper::isEntryAutoDeleteEnabled($form_id),
            'current_form_title'  => $form->title,
            'entry_statuses'      => Helper::getEntryStatuses($form_id),
            'entries_url_base'    => admin_url('admin.php?page=fluent_forms&route=entries&form_id='),
            'no_found_text'       => __('Sorry! No entries found. All your entries will be shown here once you start getting form submissions',
                'fluentform'),
            'has_pro'             => defined('FLUENTFORMPRO'),
            'printStyles'         => [fluentformMix('css/settings_global.css')],
            'email_notifications' => $formattedNotification,
            'available_countries' => getFluentFormCountryList(),
            'upgrade_url'         => fluentform_upgrade_url(),
            'form_entries_str'    => TranslationString::getEntriesI18n(),
            'editor_shortcodes'   => $submissionShortcodes['shortcodes'],
            'input_labels'        => $inputLabels,
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This is just passing URL parameter to frontend for display
            'update_status'       => isset($_REQUEST['update_status']) ? sanitize_text_field(wp_unslash($_REQUEST['update_status'])) : '',
            'address_fields'       => array_keys(FormFieldsParser::getAddressFields($form)),
        ];

        $data = apply_filters_deprecated(
            'fluent_form_entries_vars',
            [
                $data,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/entries_vars',
            'Use fluentform/entries_vars instead of fluent_form_entries_vars.'
        );

        $fluentFormEntriesVars = apply_filters('fluentform/entries_vars', $data, $form);

        wp_localize_script(
            'fluentform_form_entries',
            'fluent_form_entries_vars',
            $fluentFormEntriesVars
        );

        wpFluentForm('view')->render('admin.form.entries', [
            'form_id' => $form_id,
            'has_pdf' => defined('FLUENTFORM_PDF_VERSION') ? 'true' : 'false',
        ]);
    }
}
