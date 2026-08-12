<?php

namespace FluentForm\App\Modules\MCP\Tools;

defined('ABSPATH') || exit;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Modules\MCP\Support\ErrorCodes;
use FluentForm\App\Modules\MCP\Support\FormAccess;
use FluentForm\App\Modules\MCP\Support\MCPHelper;
use FluentForm\App\Modules\MCP\Support\Mutation;
use FluentForm\App\Modules\MCP\Support\WriteGuard;
use FluentForm\App\Services\Settings\Customizer;
use FluentForm\Framework\Support\Arr;

/**
 * Form styling tools.
 *
 * Read/write a form's theme preset and custom CSS. Theme presets are always
 * writable; custom CSS writes only when the user holds unfiltered_html — the same
 * gate the admin styler enforces. Custom JS is intentionally not writable here
 * (that surface stays in the form styler UI).
 */
class StylingTools
{
    const STYLE_META = ['_ff_selected_style', '_ff_form_styles', '_custom_form_css', '_custom_form_js'];

    public static function definitions()
    {
        return [
            'fluentform/get-form-styling' => [
                'label'       => __('Get Form Styling', 'fluentform'),
                'group'       => __('Design', 'fluentform'),
                'description' => __('Read a form\'s styling: theme preset (styler_theme), structured styles (styler_styles), and any custom CSS. Requires form_id.', 'fluentform'),
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'form_id' => ['type' => 'integer', 'description' => 'Required. The form to read styling for.'],
                    ],
                    'required' => ['form_id'],
                ],
                'execute_callback'    => [self::class, 'getStyling'],
                'capability'          => 'fluentform_forms_manager',
                'annotations' => ['readonly' => true],
            ],

            'fluentform/update-form-styling' => [
                'label'       => __('Update Form Styling', 'fluentform'),
                'group'       => __('Design', 'fluentform'),
                'description' => __('Update a form\'s styling. styler_theme (preset id) is always writable. Custom css is written only if you hold the unfiltered_html capability; otherwise the call returns unfiltered_html_required and changes nothing. Pass only the keys you want to change. Call once with dry_run:true to preview the before/after and get a confirm_token, then call again with the same values plus confirm_token to execute. Requires form_id. (Structured styler_styles are read-only via get-form-styling; edit them in the form styler UI. Custom JS is not editable through MCP.)', 'fluentform'),
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => array_merge([
                        'form_id'      => ['type' => 'integer', 'description' => 'Required. The form to restyle.'],
                        'styler_theme' => ['type' => 'string', 'description' => 'Theme preset id (e.g. ffs_default).'],
                        'css'          => ['type' => 'string', 'description' => 'Custom CSS (requires unfiltered_html).'],
                    ], WriteGuard::schemaProps()),
                    'required' => ['form_id'],
                    // Unknown keys (e.g. styler_styles, js) must fail loudly, not be
                    // silently dropped — agents need the signal.
                    'additionalProperties' => false,
                ],
                'execute_callback'    => [self::class, 'updateStyling'],
                'capability'          => 'fluentform_forms_manager',
            ],
        ];
    }

    /**
     * Every preset id this install can apply, from the Pro styler when available
     * else the free fallbacks — the same source DefaultStyleApplicator uses.
     *
     * @return array<string, array>
     */
    public static function presets()
    {
        if (class_exists('\FluentFormPro\classes\FormStyler')) {
            $presets = (new \FluentFormPro\classes\FormStyler())->getPresets();

            return is_array($presets) ? $presets : [];
        }

        return [
            'ffs_default'       => ['style' => '[]'],
            'ffs_inherit_theme' => ['style' => '{}'],
        ];
    }

    /** The preset ids valid on this install, for validation + error hints. */
    public static function presetIds()
    {
        return array_keys(self::presets());
    }

    /**
     * The structured styles a preset id maps to. Null when the preset is unknown
     * or carries unparseable styles.
     */
    private static function presetStyles($theme)
    {
        $presets = self::presets();

        if (!isset($presets[$theme]['style'])) {
            return null;
        }

        $styles = json_decode($presets[$theme]['style'], true);

        return is_array($styles) ? $styles : null;
    }

    /**
     * The form's live style state, read fresh from the DB. The forced read busts
     * Helper's per-request meta cache, so a re-read inside the write transaction
     * reflects a concurrent styler save rather than the value read on entry.
     */
    private static function freshStyleState($formId)
    {
        Helper::getFormMeta($formId, '_ff_selected_style', '', true);

        return (new Customizer())->get($formId, self::STYLE_META);
    }

    /**
     * Fingerprint of the style state the confirm_token is bound to. Both sides of
     * every comparison must read through freshStyleState so identical data always
     * yields the same string (Customizer::get drops empty metas; a raw read does not).
     */
    private static function styleFingerprint($theme, $css)
    {
        return 'styling:' . md5(wp_json_encode([$theme, $css]));
    }

    public static function getStyling($params = [])
    {
        $form = FormAccess::resolveForm($params);
        if (is_wp_error($form)) {
            return $form;
        }
        $formId = (int) $form->id;

        $styling = (new Customizer())->get($formId, self::STYLE_META);

        return MCPHelper::envelope(
            sprintf(
                /* translators: %s: form title */
                __('Styling for "%s" loaded.', 'fluentform'),
                $form->title
            ),
            [
                'form_id'       => $formId,
                'styler_theme'  => Arr::get($styling, 'styler_theme'),
                'styler_styles' => Arr::get($styling, 'styler_styles'),
                'css'           => Arr::get($styling, 'css'),
            ]
        );
    }

    public static function updateStyling($params = [])
    {
        $form = FormAccess::resolveForm($params);
        if (is_wp_error($form)) {
            return $form;
        }
        $formId = (int) $form->id;

        $hasTheme = array_key_exists('styler_theme', $params);
        $hasCss   = array_key_exists('css', $params);

        if (!$hasTheme && !$hasCss) {
            return MCPHelper::error(ErrorCodes::MISSING_PARAM, __('Provide at least one of: styler_theme, css.', 'fluentform'), ['fields' => ['styler_theme', 'css']]);
        }

        // An unknown preset id used to be stored verbatim and reported as a
        // success, leaving the form pointed at a theme that does not exist.
        // Which ids are valid depends on whether Pro is active, so the error
        // carries the list this install actually accepts.
        if ($hasTheme) {
            $requestedTheme = sanitize_text_field($params['styler_theme']);
            if (null === self::presetStyles($requestedTheme)) {
                return MCPHelper::error(
                    ErrorCodes::INVALID_PARAM,
                    sprintf(
                        /* translators: %s: comma-separated list of valid theme preset ids */
                        __('Unknown styler_theme. Valid preset ids on this site: %s.', 'fluentform'),
                        implode(', ', self::presetIds())
                    ),
                    ['fields' => ['styler_theme'], 'valid_values' => self::presetIds()]
                );
            }
        }

        // CSS writes require unfiltered_html — the WP-standard gate the styler
        // enforces. Pre-check so the agent gets a structured error instead of the
        // raw exception Customizer::store() throws, and nothing is persisted.
        if ($hasCss && !fluentformCanUnfilteredHTML()) {
            return MCPHelper::error(ErrorCodes::UNFILTERED_HTML_REQUIRED, __('Saving custom CSS requires the unfiltered_html capability.', 'fluentform'), ['fields' => ['css']]);
        }

        // Sanitize the CSS even for unfiltered_html users — an agent is a more
        // easily-manipulated input channel than a human at the styler, so it gets
        // a stricter bar. fluentformSanitizeCSS blanks the whole value if it holds
        // any tag (a </style><script> breakout); reject loudly instead of silently
        // storing empty. Same sanitizer create-form runs on custom CSS.
        $safeCss = '';
        if ($hasCss) {
            $safeCss = fluentformSanitizeCSS((string) $params['css']);
            if ('' === $safeCss && '' !== trim((string) $params['css'])) {
                return MCPHelper::error(ErrorCodes::INVALID_PARAM, __('The css contains markup (an HTML tag) and was rejected. Provide plain CSS only — no <style>, <script>, or other tags.', 'fluentform'), ['fields' => ['css']]);
            }
        }

        $before      = self::freshStyleState($formId);
        $fingerprint = self::styleFingerprint(Arr::get($before, 'styler_theme'), Arr::get($before, 'css'));

        return Mutation::runGuarded(
            'fluentform/update-form-styling',
            $params,
            // The requested values are part of the key, so a token minted for a
            // harmless theme swap cannot be replayed to inject a CSS payload.
            'styling:' . $formId . ':' . md5(($hasTheme ? (string) $params['styler_theme'] : '~') . '|' . $safeCss),
            $fingerprint,
            function () use ($formId, $before, $hasTheme, $hasCss, $params, $safeCss) {
                $preview = ['form_id' => $formId, 'changes' => []];
                if ($hasTheme) {
                    $preview['changes']['styler_theme'] = [
                        'from' => Arr::get($before, 'styler_theme'),
                        'to'   => sanitize_text_field($params['styler_theme']),
                    ];
                }
                if ($hasCss) {
                    $preview['changes']['css'] = [
                        'from' => MCPHelper::preview((string) Arr::get($before, 'css', '')),
                        'to'   => MCPHelper::preview($safeCss),
                    ];
                }

                return $preview;
            },
            function () use ($formId, $form, $hasTheme, $hasCss, $params, $safeCss, $fingerprint) {
                global $wpdb;

                $changed = [];

                // Persist the related style meta in one transaction with each write
                // verified (setFormMeta returns null on a failed persist), so a
                // half-write can't leave _ff_selected_style pointing at a theme whose
                // _ff_form_styles is stale. START/COMMIT go through raw $wpdb, so
                // their return values are checked too; roll back on any failure.
                if (false === $wpdb->query('START TRANSACTION')) {
                    return MCPHelper::error(ErrorCodes::TOOL_FAILED, __('Could not start a database transaction for the styling update.', 'fluentform'), ['retryable' => true]);
                }
                try {
                    // Serialize against a concurrent styler save (admin UI, another
                    // agent) and close the window between the on-entry read the
                    // confirm_token was validated against and this write: lock the
                    // form row, then re-read the live style state and re-check it
                    // against what was previewed. A change that landed in that window
                    // would otherwise be silently clobbered (lost update); refuse and
                    // force a fresh dry_run instead.
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from $wpdb->prefix, id is %d-prepared
                    $wpdb->query($wpdb->prepare("SELECT id FROM {$wpdb->prefix}fluentform_forms WHERE id = %d FOR UPDATE", $formId));

                    $current = self::freshStyleState($formId);
                    if (self::styleFingerprint(Arr::get($current, 'styler_theme'), Arr::get($current, 'css')) !== $fingerprint) {
                        $wpdb->query('ROLLBACK');
                        return MCPHelper::error(ErrorCodes::STATE_CHANGED, __('The form styling changed while this update was in flight. Run a fresh dry_run to re-preview, then execute.', 'fluentform'), ['next_step' => 'set dry_run:true']);
                    }

                    if ($hasTheme) {
                        $theme        = sanitize_text_field($params['styler_theme']);
                        $presetStyles = self::presetStyles($theme);
                        // Mirror DefaultStyleApplicator: the selected style and its
                        // structured styles move together (preset validated non-null
                        // up front), so a reader never sees one theme's id with
                        // another theme's styles.
                        if (
                            null === Helper::setFormMeta($formId, '_ff_selected_style', $theme)
                            || null === Helper::setFormMeta($formId, '_ff_form_styles', $presetStyles)
                        ) {
                            throw new \Exception(__('The theme styles could not be saved.', 'fluentform'));
                        }
                        $changed[] = 'styler_theme';
                    }

                    if ($hasCss) {
                        // Write only the css meta directly — no js read-and-rewrite — so a
                        // concurrent js edit in the form styler UI is never clobbered. css is
                        // already sanitized (fluentformSanitizeCSS) and unfiltered_html-gated
                        // above, matching what Customizer::store would apply.
                        if (null === Helper::setFormMeta($formId, '_custom_form_css', $safeCss)) {
                            throw new \Exception(__('The custom CSS could not be saved.', 'fluentform'));
                        }
                        $changed[] = 'css';
                    }
                } catch (\Throwable $e) {
                    $wpdb->query('ROLLBACK');

                    return MCPHelper::error(ErrorCodes::TOOL_FAILED, $e->getMessage(), ['retryable' => true]);
                }

                if (false === $wpdb->query('COMMIT')) {
                    $wpdb->query('ROLLBACK');

                    return MCPHelper::error(ErrorCodes::TOOL_FAILED, __('The styling update could not be committed; no changes were saved.', 'fluentform'), ['retryable' => true]);
                }

                return MCPHelper::envelope(
                    sprintf(
                        /* translators: %s: form title */
                        __('Styling for "%s" updated.', 'fluentform'),
                        $form->title
                    ),
                    ['form_id' => $formId, 'updated' => $changed]
                );
            },
            ['form_id' => $formId]
        );
    }
}
