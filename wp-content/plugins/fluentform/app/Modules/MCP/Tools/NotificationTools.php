<?php

namespace FluentForm\App\Modules\MCP\Tools;

defined('ABSPATH') || exit;

use FluentForm\App\Models\FormMeta;
use FluentForm\App\Modules\MCP\Support\ErrorCodes;
use FluentForm\App\Modules\MCP\Support\FormAccess;
use FluentForm\App\Modules\MCP\Support\MCPHelper;
use FluentForm\App\Modules\MCP\Support\Mutation;
use FluentForm\App\Modules\MCP\Support\WriteGuard;
use FluentForm\App\Services\Settings\SettingsService;
use FluentForm\Framework\Support\Arr;

/**
 * Email-notification tools (read + write).
 *
 * A form's email notifications live as individual fluentform_form_meta rows under
 * meta_key "notifications"; each row's id IS the notification id. Reads and writes
 * reuse SettingsService::store — the same path the admin Email Notifications screen
 * uses — so the agent inherits its validation (sendTo/subject/message required) and
 * sanitization (the HTML body runs through fluentform_sanitize_html). Writes are
 * form-scoped and a supplied notification_id is verified to belong to the form
 * before update (IDOR-safe), never silently forked onto a new row.
 *
 * upsert is the highest-leverage write in the module: repointing sendTo
 * redirects every future submission of the form to a new mailbox, persistently
 * and with no signal in the admin UI. It is therefore annotated destructive and
 * routed through WriteGuard, with the old and new recipient spelled out in the
 * dry-run preview so an operator can catch a redirect before it happens.
 */
class NotificationTools
{
    const META_KEY = 'notifications';

    public static function definitions()
    {
        return [
            'fluentform/list-email-notifications' => [
                'label'       => __('List Email Notifications', 'fluentform'),
                'group'       => __('Notifications', 'fluentform'),
                'description' => __('List a form\'s email notifications with the id you need to edit one: id, name, enabled state, recipient (sendTo), subject, and a short body preview. Requires form_id.', 'fluentform'),
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'form_id' => ['type' => 'integer', 'description' => 'Required. The form to read notifications for.'],
                    ],
                    'required' => ['form_id'],
                ],
                'execute_callback'    => [self::class, 'listNotifications'],
                'capability'          => ['fluentform_forms_manager', 'fluentform_settings_manager', 'fluentform_dashboard_access'],
                'annotations' => ['readonly' => true],
            ],

            'fluentform/upsert-email-notification' => [
                'label'       => __('Create or Update Email Notification', 'fluentform'),
                'group'       => __('Notifications', 'fluentform'),
                'description' => __('Create a new email notification (omit notification_id) or update an existing one (pass its notification_id from list-email-notifications). On update, only the keys you pass change; the rest are kept. subject and message are required to create; message is HTML and is sanitized on save. Recipient: send_to_type "email" with send_to_email (a fixed address or a {smartcode}), or "field" with send_to_field (a form field key). CHANGING THE RECIPIENT REDIRECTS EVERY FUTURE SUBMISSION OF THIS FORM — call once with dry_run:true to preview the old and new recipient and get a confirm_token, then call again with the same values plus confirm_token to execute. Requires form_id.', 'fluentform'),
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => array_merge([
                        'form_id'         => ['type' => 'integer', 'description' => 'Required. The form the notification belongs to.'],
                        'notification_id' => ['type' => 'integer', 'description' => 'Omit to create; pass an id from list-email-notifications to update.'],
                        'name'            => ['type' => 'string', 'description' => 'Admin label for the notification.'],
                        'subject'         => ['type' => 'string', 'description' => 'Email subject (required to create).'],
                        'message'         => ['type' => 'string', 'description' => 'Email body, HTML allowed (required to create). Sanitized on save.'],
                        'send_to_type'    => ['type' => 'string', 'enum' => ['email', 'field'], 'description' => 'How to resolve the recipient. Default email.'],
                        'send_to_email'   => ['type' => 'string', 'description' => 'Recipient when send_to_type=email; a fixed address or a {smartcode}.'],
                        'send_to_field'   => ['type' => 'string', 'description' => 'Recipient field key when send_to_type=field.'],
                        'from_name'       => ['type' => 'string'],
                        'from_email'      => ['type' => 'string'],
                        'reply_to'        => ['type' => 'string'],
                        'enabled'         => ['type' => 'boolean', 'description' => 'Whether this notification fires. Default true on create.'],
                    ], WriteGuard::schemaProps()),
                    'required' => ['form_id'],
                ],
                'execute_callback'    => [self::class, 'upsertNotification'],
                'capability'          => 'fluentform_forms_manager',
                // Not reversible in any meaningful sense: silently repointing a
                // recipient exfiltrates every future submission of the form,
                // and nothing in the UI signals that it happened.
                'annotations' => ['destructive' => true],
            ],
        ];
    }

    public static function listNotifications($params = [])
    {
        $form = FormAccess::resolveForm($params);
        if (is_wp_error($form)) {
            return $form;
        }
        $formId = (int) $form->id;

        $rows = self::rowsFor($formId);

        $out = [];
        foreach ($rows as $row) {
            $n = json_decode($row->value, true);
            if (!is_array($n)) {
                continue;
            }
            $out[] = [
                'id'      => (int) $row->id,
                'name'    => Arr::get($n, 'name'),
                'enabled' => !empty($n['enabled']),
                'sendTo'  => Arr::get($n, 'sendTo'),
                'subject' => Arr::get($n, 'subject'),
                'message_preview' => MCPHelper::preview(MCPHelper::htmlToText((string) Arr::get($n, 'message', '')), 120),
            ];
        }

        return MCPHelper::envelope(
            sprintf(
                /* translators: %d: number of email notifications */
                _n('%d email notification configured.', '%d email notifications configured.', count($out), 'fluentform'),
                count($out)
            ),
            ['form_id' => $formId, 'notifications' => $out]
        );
    }

    public static function upsertNotification($params = [])
    {
        $form = FormAccess::resolveForm($params);
        if (is_wp_error($form)) {
            return $form;
        }
        $formId = (int) $form->id;

        $metaId = isset($params['notification_id']) ? (int) $params['notification_id'] : 0;

        // Advisory read, for the dry-run preview and the confirm fingerprint
        // only. The authoritative read happens under FOR UPDATE inside the
        // mutation below, so the two layers cover different windows: the
        // fingerprint catches "changed since you previewed it", the row lock
        // catches "changed while we were writing".
        $current = [];
        if ($metaId) {
            // IDOR: the row must belong to THIS form, else refuse — never let a
            // foreign id silently fork a new notification onto this form.
            $match = null;
            foreach (self::rowsFor($formId) as $r) {
                if ((int) $r->id === $metaId) {
                    $match = $r;
                    break;
                }
            }
            if (!$match) {
                return MCPHelper::error(ErrorCodes::NOT_FOUND, __('No email notification with that id exists on this form.', 'fluentform'), ['fields' => ['notification_id']]);
            }
            $decoded = json_decode($match->value, true);
            $current = is_array($decoded) ? $decoded : [];
        }

        $notification = self::merge($current, $params, 0 === $metaId);

        return Mutation::runGuarded(
            'fluentform/upsert-email-notification',
            $params,
            // The resulting notification is part of the key, so a token minted
            // for an innocuous subject edit cannot be replayed to swap the
            // recipient on the same row.
            'notification:' . $formId . ':' . ($metaId ? $metaId : 'new') . ':' . md5((string) wp_json_encode($notification)),
            'notification:' . ($metaId ? md5((string) wp_json_encode($current)) : 'new'),
            function () use ($formId, $metaId, $current, $notification) {
                return [
                    'form_id'         => $formId,
                    'notification_id' => $metaId ? $metaId : null,
                    'action'          => $metaId ? 'update' : 'create',
                    'recipient'       => [
                        'from' => $metaId ? self::describeRecipient(Arr::get($current, 'sendTo', [])) : null,
                        'to'   => self::describeRecipient(Arr::get($notification, 'sendTo', [])),
                    ],
                    'subject' => [
                        'from' => $metaId ? Arr::get($current, 'subject') : null,
                        'to'   => Arr::get($notification, 'subject'),
                    ],
                    'enabled' => !empty($notification['enabled']),
                    // Only a genuine redirect warrants the warning. On create
                    // there is no previous recipient, so comparing against an
                    // empty one would cry wolf on every new notification.
                    'warning' => self::redirectWarning($metaId, $current, $notification),
                ];
            },
            function () use ($params, $formId, $metaId) {
                // Update is a read-modify-MERGE-write (merge() preserves keys the
                // agent didn't send). Lock + re-read the row inside one transaction
                // so a concurrent admin edit to subject/sendTo/conditionals isn't
                // lost. Create is a plain insert — no read-modify-write.
                if ($metaId) {
                    global $wpdb;
                    $wpdb->query('START TRANSACTION');
                    try {
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table from $wpdb->prefix; id/form_id %d-prepared, meta_key %s-prepared
                        $wpdb->query($wpdb->prepare("SELECT id FROM {$wpdb->prefix}fluentform_form_meta WHERE id = %d AND form_id = %d AND meta_key = %s FOR UPDATE", $metaId, $formId, self::META_KEY));

                        // IDOR + freshness: the row must still belong to this form.
                        $row = FormMeta::where('form_id', $formId)
                            ->where('meta_key', self::META_KEY)
                            ->where('id', $metaId)
                            ->first();
                        if (!$row) {
                            $wpdb->query('ROLLBACK');
                            return MCPHelper::error(ErrorCodes::NOT_FOUND, __('No email notification with that id exists on this form.', 'fluentform'), ['fields' => ['notification_id']]);
                        }

                        $decoded      = json_decode($row->value, true);
                        $notification = self::merge(is_array($decoded) ? $decoded : [], $params, false);

                        try {
                            list($savedId, $saved) = (new SettingsService())->store([
                                'form_id'  => $formId,
                                'meta_key' => self::META_KEY,
                                'value'    => wp_json_encode($notification),
                                'meta_id'  => $metaId,
                            ]);
                        } catch (\FluentForm\Framework\Validator\ValidationException $e) {
                            $wpdb->query('ROLLBACK');
                            return MCPHelper::error(ErrorCodes::INVALID_PARAM, self::flattenErrors($e), ['fields' => ['subject', 'message', 'send_to_email']]);
                        }

                        $wpdb->query('COMMIT');

                        return MCPHelper::envelope(
                            __('Email notification updated.', 'fluentform'),
                            ['form_id' => $formId, 'notification_id' => (int) $savedId, 'enabled' => !empty($saved['enabled'])]
                        );
                    } catch (\Throwable $e) {
                        $wpdb->query('ROLLBACK');
                        throw $e;
                    }
                }

                $notification = self::merge([], $params, true);
                try {
                    list($savedId, $saved) = (new SettingsService())->store([
                        'form_id'  => $formId,
                        'meta_key' => self::META_KEY,
                        'value'    => wp_json_encode($notification),
                    ]);
                } catch (\FluentForm\Framework\Validator\ValidationException $e) {
                    return MCPHelper::error(ErrorCodes::INVALID_PARAM, self::flattenErrors($e), ['fields' => ['subject', 'message', 'send_to_email']]);
                }

                return MCPHelper::envelope(
                    __('Email notification created.', 'fluentform'),
                    ['form_id' => $formId, 'notification_id' => (int) $savedId, 'enabled' => !empty($saved['enabled'])]
                );
            },
            ['form_id' => $formId]
        );
    }

    /**
     * The preview's headline: is this call about to redirect an existing form's
     * mail somewhere else? That is the one thing an operator must not miss.
     */
    private static function redirectWarning($metaId, array $current, array $notification)
    {
        if (!$metaId) {
            return __('New notification; nothing existing is being redirected.', 'fluentform');
        }

        $from = self::describeRecipient(Arr::get($current, 'sendTo', []));
        $to   = self::describeRecipient(Arr::get($notification, 'sendTo', []));

        if ($from === $to) {
            return __('The recipient is unchanged.', 'fluentform');
        }

        return __('This changes where submissions of this form are emailed. Every future submission will go to the new recipient. Confirm the address with the site owner before executing.', 'fluentform');
    }

    /**
     * One-line recipient description for the dry-run preview, so an operator can
     * eyeball where mail will go without decoding the sendTo structure.
     */
    private static function describeRecipient($sendTo)
    {
        if (!is_array($sendTo)) {
            return null;
        }

        if ('field' === Arr::get($sendTo, 'type')) {
            return 'field:' . (string) Arr::get($sendTo, 'field', '');
        }

        return 'email:' . (string) Arr::get($sendTo, 'email', '');
    }

    /**
     * Build the notification array to persist: start from the existing one (empty
     * for create, seeded with defaults), then overlay only the params supplied.
     * Sanitization/validation are left to SettingsService::store (the shared path).
     */
    private static function merge(array $current, array $params, $isCreate)
    {
        $n = $current;

        if ($isCreate) {
            $n = array_merge([
                'name'         => __('Email Notification', 'fluentform'),
                'sendTo'       => ['type' => 'email', 'email' => '{wp.admin_email}', 'field' => '', 'routing' => []],
                'fromName'     => '',
                'fromEmail'    => '',
                'replyTo'      => '',
                'bcc'          => '',
                'subject'      => '',
                'message'      => '',
                'conditionals' => ['status' => false, 'type' => 'all', 'conditions' => [['field' => '', 'operator' => '=', 'value' => '']]],
                'enabled'      => true,
                'email_template' => '',
            ], $n);
        }

        foreach (['name' => 'name', 'subject' => 'subject', 'message' => 'message', 'from_name' => 'fromName', 'from_email' => 'fromEmail', 'reply_to' => 'replyTo'] as $in => $key) {
            if (array_key_exists($in, $params)) {
                $n[$key] = (string) $params[$in];
            }
        }

        if (array_key_exists('enabled', $params)) {
            $n['enabled'] = (bool) $params['enabled'];
        }

        $sendTo = isset($n['sendTo']) && is_array($n['sendTo']) ? $n['sendTo'] : ['type' => 'email', 'email' => '', 'field' => '', 'routing' => []];
        if (array_key_exists('send_to_type', $params)) {
            $sendTo['type'] = in_array($params['send_to_type'], ['email', 'field'], true) ? $params['send_to_type'] : 'email';
        }
        if (array_key_exists('send_to_email', $params)) {
            $sendTo['email'] = (string) $params['send_to_email'];
        }
        if (array_key_exists('send_to_field', $params)) {
            $sendTo['field'] = (string) $params['send_to_field'];
        }
        $n['sendTo'] = $sendTo;

        return $n;
    }

    /**
     * The stored notification rows for a form (one row per notification).
     *
     * @return \FluentForm\Framework\Support\Collection
     */
    private static function rowsFor($formId)
    {
        return FormMeta::where('form_id', $formId)
            ->where('meta_key', self::META_KEY)
            ->get();
    }

    private static function flattenErrors(\FluentForm\Framework\Validator\ValidationException $e)
    {
        $errors = $e->errors();
        $flat   = [];
        if (is_array($errors)) {
            array_walk_recursive($errors, function ($m) use (&$flat) {
                if (is_string($m) && '' !== $m) {
                    $flat[] = $m;
                }
            });
        }

        return $flat ? implode(' ', array_unique($flat)) : $e->getMessage();
    }
}
