<?php

defined('ABSPATH') or die;

/**
 * Add all ajax hooks
 */

use FluentForm\App\Modules\Acl\Acl;

/**
 * App instance
 *
 * @var $app \FluentForm\Framework\Foundation\Application
 */

$app->addAction('wp_ajax_nopriv_fluentform_submit', function () use ($app) {
    (new \FluentForm\App\Modules\SubmissionHandler\SubmissionHandler($app))->submit();
});

$app->addAction('wp_ajax_fluentform_submit', function () use ($app) {
    //    (new \FluentForm\App\Modules\Form\FormHandler($app))->onSubmit();
    (new \FluentForm\App\Modules\SubmissionHandler\SubmissionHandler($app))->submit();
});

/*
 * We are using this ajax call for updating form fields
 * REST API seems not working for some servers with Mod Security Enabled
 */
$app->addAction('wp_ajax_fluentform-form-update', function () use ($app) {
    Acl::verify('fluentform_forms_manager', $app->request->get('form_id'));
    try {
        $data = $app->request->all();
        $isValidJson = (!empty($data['formFields'])) && json_decode($data['formFields'], true);

        if(!$isValidJson) {
            wp_send_json([
                'message' => 'Looks like the provided JSON is invalid. Please try again or contact support',
                'reason' => 'formFields JSON validation failed'
            ], 422);
        }

        $formService = new \FluentForm\App\Services\Form\FormService();
        $form = $formService->update($data);
        wp_send_json([
            'message' => __('The form is successfully updated.', 'fluentform')
        ], 200);
    } catch (\Exception $exception) {
        wp_send_json([
            'message' => $exception->getMessage(),
        ], 422);
    }
});

/*
 * This ajax endpoint is used to update form general settings
 * Mod-Security also block this request
 */
$app->addAction('wp_ajax_fluentform-save-settings-general-formSettings', function () use ($app) {
    Acl::verify('fluentform_forms_manager');
    try {
        $settingsService = new \FluentForm\App\Services\Settings\SettingsService();
        $settingsService->saveGeneral($app->request->all());
        wp_send_json([
            'message' => __('Settings has been saved.', 'fluentform'),
        ]);
    } catch (\FluentForm\Framework\Validator\ValidationException $exception) {
        wp_send_json($exception->errors(), 422);
    }
});

/*
 * This ajax endpoint is used to update form email notifications settings
 * Mod-Security also block this request
 */
$app->addAction('wp_ajax_fluentform-save-form-email-notification', function () use ($app) {
    Acl::verify('fluentform_forms_manager');
    try {
        $settingsService = new \FluentForm\App\Services\Settings\SettingsService();
        [$settingsId, $settings] = $settingsService->store($app->request->all());

        wp_send_json([
            'message'  => __('Settings has been saved.', 'fluentform'),
            'id'       => $settingsId,
            'settings' => $settings,
        ]);
    } catch (\FluentForm\Framework\Validator\ValidationException $exception) {
        wp_send_json($exception->errors(), 422);
    }
});


// Legacy AJAX handlers removed — these routes are handled by the REST API.
// Kept: fluentform-form-find-shortcode-locations (still in active use)
$app->addAdminAjaxAction('fluentform-form-find-shortcode-locations', function () use ($app) {
    Acl::verify('fluentform_forms_manager');
    (new \FluentForm\App\Modules\Form\Form($app))->findFormLocations();
});

// Legacy AJAX handlers removed — these routes are now handled by the REST API.



$app->addAction('wp_ajax_fluentform-form-entries-export', function () use ($app) {
    Acl::verify('fluentform_entries_viewer');
    (new \FluentForm\App\Modules\Transfer\Transfer())->exportEntries();
});

$app->addAction('wp_ajax_fluentform-update-entry-user', function () use ($app) {
    $submissionId = intval($app->request->get('submission_id'));
    $formId = null;
    if ($submissionId) {
        $submission = \FluentForm\App\Models\Submission::select('form_id')->find($submissionId);
        $formId = $submission ? $submission->form_id : null;
    }
    Acl::verify('fluentform_manage_entries', $formId);
    $userId = intval($app->request->get('user_id'));
    try {
        $result = (new \FluentForm\App\Services\Submission\SubmissionService())->updateSubmissionUser($userId, $submissionId);
        wp_send_json_success($result);
    } catch (\Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()], 423);
    }
});

$app->addAction('wp_ajax_fluentform-get-users', function () use ($app) {
    Acl::verify('fluentform_entries_viewer');
    $search = sanitize_text_field($app->request->get('search'));
    $users = get_users([
        'search' => "*{$search}*",
        'number' => 50,
    ]);
    $formattedUsers = [];
    foreach ($users as $user) {
        $formattedUsers[] = [
            'ID'    => $user->ID,
            'label' => $user->display_name . ' - ' . $user->user_email,
        ];
    }
    wp_send_json_success(['users' => $formattedUsers]);
});


// Legacy log AJAX handlers removed — these routes are now handled by the REST API.

$app->addAction('wp_ajax_fluentform-change-entry-status', function () use ($app) {
    Acl::verify('fluentform_manage_entries');
    $attributes = [
        'entry_id' => intval($app->request->get('entry_id')),
        'status'   => sanitize_text_field($app->request->get('status')),
    ];
    $newStatus = (new \FluentForm\App\Services\Submission\SubmissionService())->updateStatus($attributes);
    wp_send_json_success([
        // translators: %s is the submission status name
        'message' => sprintf(__('Item has been marked as %s', 'fluentform'), $newStatus),
        'status'  => $newStatus,
    ], 200);
});


$app->addAction('wp_ajax_fluentform_notice_action_track_yes', function () {
    Acl::verify('fluentform_settings_manager');
    (new FluentForm\App\Modules\Track\TrackModule())->sendInitialInfo();
});

$app->addAction('wp_ajax_fluentform_install_fluentsmtp', function () {
    Acl::verify('fluentform_settings_manager');
    (new FluentForm\App\Modules\Track\SetupModule())->installPlugin('fluent-smtp');
});

// Export forms
$app->addAction('wp_ajax_fluentform-export-forms', function () use ($app) {
    Acl::verify('fluentform_settings_manager', $app->request->get('forms'));
    (new \FluentForm\App\Modules\Transfer\Transfer())->exportForms();
});

// Import forms
$app->addAction('wp_ajax_fluentform-import-forms', function () use ($app) {
    Acl::verify('fluentform_settings_manager');
    (new \FluentForm\App\Modules\Transfer\Transfer())->importForms();
});

/*
 * Background Process Receiver
 */

// $this refers to the Application instance (included via Application::requireCommonFiles → includes.php)
$app->addAction('wp_ajax_fluentform_background_process', function () {
    $this->app['fluentFormAsyncRequest']->handleBackgroundCall();
});

$app->addAction('wp_ajax_nopriv_fluentform_background_process', function () {
    $this->app['fluentFormAsyncRequest']->handleBackgroundCall();
});

/*
 * Background Report Data Migration
 */
$app->addAction('wp_ajax_fluentform_report_data_migrate', function () {
    if (!wp_verify_nonce(sanitize_text_field(wpFluentForm('request')->get('nonce')), 'fluentform_report_data_migrate')) {
        die('invalid');
    }
    $formId = intval(wpFluentForm('request')->get('form_id'));
    if ($formId && Acl::hasPermission('fluentform_entries_viewer', $formId)) {
        \FluentForm\App\Services\Report\ReportHelper::runMigrationBatch($formId);
    }
    die('done');
});

/*
 * For REST API Nonce Renewal
 */
$app->addAction('wp_ajax_fluentform_renew_rest_nonce', function () {
    if (!Acl::getCurrentUserPermissions()) {
        wp_send_json([
            'error' => 'You do not have permission to do this',
        ], 403);
    }
    
    wp_send_json([
        'nonce' => wp_create_nonce('wp_rest'),
    ], 200);
});
/*
 * For selectGroup Component Grouped Options
 * Use this filter to pass data to component
 */

add_action('wp_ajax_fluentform_select_group_ajax_data', function () {
    Acl::verify('fluentform_dashboard_access');
    $requestData = wpFluentForm('request')->all();
    $ajaxList = apply_filters('fluentform/select_group_component_ajax_options', [], $requestData);
    wp_send_json_success($ajaxList);
});
