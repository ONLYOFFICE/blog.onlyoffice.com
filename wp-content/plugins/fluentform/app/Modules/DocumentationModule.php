<?php

namespace FluentForm\App\Modules;

class DocumentationModule
{
    public function render()
    {
        wp_enqueue_script('fluentform-docs');
        wpFluentForm('view')->render('admin.docs.index', [
            'public_url' => fluentformMix(),
            'icon_path_url' => fluentformMix(''),
            'user_guides'   => $this->getUserGuides(),
        ]);
    }

    private function getUserGuides()
    {
        $guides = [
            [
                'title' => __('Getting started with Fluent Forms', 'fluentform'),
                'link'  => 'https://fluentforms.com/docs/getting-started-with-fluent-forms/',
            ],
            [
                'title' => __('Setting up form submission confirmation', 'fluentform'),
                'link'  => 'https://fluentforms.com/docs/setup-form-submission-confirmation-message-in-fluent-forms/',
            ],
            [
                'title' => __('Form layout settings', 'fluentform'),
                'link'  => 'https://fluentforms.com/docs/form-layout-settings-in-fluent-forms/',
            ],
            [
                'title' => __('Conditional logics', 'fluentform'),
                'link'  => 'https://fluentforms.com/docs/set-up-forms-with-conditional-logic-in-fluent-forms/',
            ],
            [
                'title' => __('Managing form submissions', 'fluentform'),
                'link'  => 'https://fluentforms.com/docs/managing-entries-in-fluent-forms/',
            ],
            [
                'title' => __('Setting up email notifications', 'fluentform'),
                'link'  => 'https://fluentforms.com/docs/how-to-setup-admin-user-email-notifications/',
            ],
            [
                'title' => __('Mailchimp integration', 'fluentform'),
                'link'  => 'https://fluentforms.com/docs/how-to-integrate-mailchimp-with-fluent-forms/',
            ],
            [
                'title' => __('Slack integration', 'fluentform'),
                'link'  => 'https://fluentforms.com/docs/how-to-integrate-slack-with-fluent-forms/',
            ],
            [
                'title' => __('Form restrictions', 'fluentform'),
                'link'  => 'https://fluentforms.com/docs/form-restrictions-feature-in-fluent-forms/',
            ],
            [
                'title' => __('Setup conditional confirmation messages', 'fluentform'),
                'link'  => 'https://fluentforms.com/docs/conditional-confirmation-message-in-fluent-forms/',
            ],
            [
                'title' => __('Predefined form fields', 'fluentform'),
                'link'  => 'https://fluentforms.com/docs/general-docs/field-types/',
            ],
        ];
    
        $guides = apply_filters_deprecated(
            'fluentform_user_guide_links',
            [
                $guides
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/user_guide_links',
            'Use fluentform/user_guide_links instead of fluentform_user_guide_links'
        );

        return apply_filters('fluentform/user_guide_links', $guides);
    }
}
