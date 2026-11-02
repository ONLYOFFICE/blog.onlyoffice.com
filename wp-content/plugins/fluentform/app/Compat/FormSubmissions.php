<?php

namespace FluentForm\App\Databases\Migrations;

defined('ABSPATH') or die;

/**
 * @deprecated since 6.2.0. Use FluentForm\Database\Migrations\Submissions instead.
 * @todo Remove in 7.0
 */
class FormSubmissions extends \FluentForm\Database\Migrations\Submissions
{
    public function __construct()
    {
        _deprecated_function(__CLASS__, '6.2.0', 'FluentForm\Database\Migrations\Submissions');
    }
}
