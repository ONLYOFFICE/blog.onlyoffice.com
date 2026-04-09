<?php

namespace FluentForm\App\Databases\Migrations;

defined('ABSPATH') or die;

/**
 * @deprecated since 6.2.0. Use FluentForm\Database\Migrations\SubmissionDetails instead.
 * @todo Remove in 7.0
 */
class SubmissionDetails extends \FluentForm\Database\Migrations\SubmissionDetails
{
    public function __construct()
    {
        _deprecated_function(__CLASS__, '6.2.0', 'FluentForm\Database\Migrations\SubmissionDetails');
    }
}
