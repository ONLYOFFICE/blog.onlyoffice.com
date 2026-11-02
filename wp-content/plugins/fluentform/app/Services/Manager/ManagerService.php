<?php

namespace FluentForm\App\Services\Manager;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Modules\Acl\Acl;
use FluentForm\Framework\Support\Arr;
use FluentForm\Framework\Validator\ValidationException;
use FluentForm\Framework\Validator\Validator;

class ManagerService
{
    public function getManagers($attributes = [])
    {
        $limit = Arr::get($attributes, 'per_page', 10);
        $page = Arr::get($attributes, 'page', 1);
        $offset = 1 == $page ? 0 : ($page - 1) * $limit;

        $query = new \WP_User_Query([
            'meta_key'     => '_fluent_forms_has_role',
            'meta_value'   => 1,
            'meta_compare' => '=',
            'number'       => $limit,
            'offset'       => $offset,
        ]);

        $managers = [];

        foreach ($query->get_results() as $user) {
            $allowedForms = FormManagerService::getUserAllowedFormsScope($user->ID);
            $hasSpecificFormsPermission = false !== $allowedForms;
            $managers[] = [
                'id'          => $user->ID,
                'first_name'  => $user->first_name,
                'last_name'   => $user->last_name,
                'email'       => $user->user_email,
                'permissions' => Acl::getUserPermissions($user),
                'forms'       => false !== $allowedForms ? array_values($allowedForms) : false,
                'roles'       => $this->getUserRoles($user->roles),
                'has_specific_forms_permission'=> $hasSpecificFormsPermission ? 'yes' : 'no',
            ];
        }

        $total = $query->get_total();

        $forms = Helper::getForms();
        if ($forms) {
            Arr::forget($forms, 0);
        }

        return ([
            'managers'  => $managers,
            'total' => $total,
            'permissions' => Acl::getReadablePermissions(),
            'forms' => $forms,
        ]);
    }

    public function addManager($attributes = [])
    {
        $manager = Arr::get($attributes, 'manager');

        $this->validate($manager);

        $permissions = Arr::get($manager, 'permissions', []);

        // Defense in depth: only a full WordPress administrator may grant the
        // crown-jewel `fluentform_full_access` permission. A holder of that ACL
        // permission is not necessarily a WP admin (managers can be delegated),
        // so without this a compromised full-access session — e.g. a script
        // running via XSS in a privileged session — could mint new full-access
        // managers and escalate. Lesser permission grants are unaffected.
        if (in_array('fluentform_full_access', (array) $permissions, true) && !current_user_can('manage_options')) {
            $permissions = array_values(array_filter((array) $permissions, function ($permission) {
                return 'fluentform_full_access' !== $permission;
            }));
        }

        $user = get_user_by('email', $manager['email']);

        if (!$user) {
            throw new ValidationException('', 0, null, ['message' => 'Please Provide Valid Email']);
        }

        Acl::attachPermissions($user, $permissions);

        update_user_meta($user->ID, '_fluent_forms_has_role', 1);

        $hasSpecificFormsPermission = 'yes' === Arr::get($manager, 'has_specific_forms_permission');
        $allowedForms = array_values(array_filter(array_map('intval', (array) Arr::get($manager, 'forms', []))));

        // Keep an empty selection unrestricted so the manager UI's
        // "leave blank for all forms" behavior matches the saved ACL state.
        if ($hasSpecificFormsPermission && $allowedForms) {
            FormManagerService::updateHasSpecificFormsPermission($user->ID, 'yes');
            FormManagerService::addUserAllowedForms($allowedForms, $user->ID);
        } else {
            FormManagerService::updateHasSpecificFormsPermission($user->ID, 'no');
            FormManagerService::deleteUserAllowedForms($user->ID);
        }

        $updatedUser = [
            'id'          => $user->ID,
            'first_name'  => $user->first_name,
            'last_name'   => $user->last_name,
            'email'       => $user->user_email,
            'permissions' => Acl::getUserPermissions($user),
        ];

        return ([
            'message' => __('Manager has been saved.', 'fluentform'),
            'manager' => $updatedUser,
        ]);
    }

    public function removeManager($attributes = [])
    {
        $userID = intval(Arr::get($attributes, 'id'));
        $user = get_user_by('ID', $userID);

        if (!$user) {
            return ([
                'message' => __('Associate user could not be found', 'fluentform'),
            ]);
        }

        Acl::attachPermissions($user, []);

        delete_user_meta($user->ID, '_fluent_forms_has_role');

        $deletedUser = [
            'id'          => $user->ID,
            'first_name'  => $user->first_name,
            'last_name'   => $user->last_name,
            'email'       => $user->user_email,
            'permissions' => Acl::getUserPermissions($user),
        ];

        return ([
            'message' => __('Manager has been removed.', 'fluentform'),
            'manager' => $deletedUser,
        ]);
    }

    private function validate($manager)
    {
        $rules = [
            'permissions' => 'required',
            'email'       => 'required|email',
        ];

        $validatorInstance = new Validator();
        $validator = $validatorInstance->make($manager, $rules);

        $errors = null;

        if ($validator->validate()->fails()) {
            $errors = $validator->errors();
        }

        if (!isset($errors['email'])) {
            $user = get_user_by('email', $manager['email']);

            if (!$user) {
                $errors['email'] = [
                    'no_user' => __('We could not found any user with this email.', 'fluentform'),
                ];
            }
        }

        if (!isset($errors['permissions'])) {
            $message = $this->dependencyValidate($manager['permissions']);

            if ($message) {
                $errors['permissions'] = [
                    'dependency' => $message,
                ];
            }
        }

        if ($errors) {
            // Escape all error messages before throwing
            $escapedErrors = array_map(function ($errorMessages) {
                if (is_array($errorMessages)) {
                    return array_map('esc_html', $errorMessages);
                }
                return esc_html($errorMessages);
            }, $errors);

            throw new ValidationException('', 0, null, [
                // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Error messages are escaped above
                'errors' => $escapedErrors,
            ]);
        }
    }

    private function dependencyValidate($permissions)
    {
        $allPermissions = Acl::getReadablePermissions();

        foreach ($permissions as $permission) {
            $depends = Arr::get($allPermissions, $permission . '.depends', []);

            if ($depends && $more = array_values(array_diff($depends, $permissions))) {
                $message = $allPermissions[$permission]['title'] . ' requires permission: ';

                foreach ($more as $i => $p) {
                    $joiner = $i ? ', ' : '';
                    $message = $message . $joiner . $allPermissions[$p]['title'];
                }

                return $message;
            }
        }
    }

    private function getUserRoles($roles)
    {
        $roleStr = '';
        if (count($roles) > 1) {
            foreach ($roles as $role) {
                $roleStr .= $role . ', ';
            }
        } else {
            $roleStr = Arr::get($roles, '0');
        }

        return $roleStr;
    }
}
