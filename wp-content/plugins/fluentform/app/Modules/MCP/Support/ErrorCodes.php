<?php

namespace FluentForm\App\Modules\MCP\Support;

defined('ABSPATH') || exit;

/**
 * The closed set of machine-readable error codes the MCP tools return.
 *
 * Every MCPHelper::error() call uses one of these constants so the agent-facing
 * error contract lives in one place — auditable, typo-proof, and stable for a
 * client to branch on. Add a code here before using it.
 */
class ErrorCodes
{
    const MISSING_IDENTIFIER    = 'missing_identifier';
    const MISSING_PARAM         = 'missing_param';
    const INVALID_PARAM         = 'invalid_param';
    const FORBIDDEN             = 'forbidden';
    const NOT_FOUND             = 'not_found';
    const CREATE_FAILED         = 'create_failed';
    const TOOL_FAILED           = 'tool_failed';
    const CONFIRMATION_REQUIRED = 'confirmation_required';
    const CONFIRMATION_EXPIRED  = 'confirmation_expired';
    const CONFIRMATION_INVALID  = 'confirmation_invalid';
    const STATE_CHANGED         = 'state_changed';
    const LIMIT_EXCEEDED        = 'limit_exceeded';
    const UNFILTERED_HTML_REQUIRED = 'unfiltered_html_required';
    const FEATURE_DISABLED      = 'feature_disabled';
    const CONCURRENT_REQUEST    = 'concurrent_request';
    const EXECUTION_UNKNOWN     = 'execution_unknown';

    /** Every declared code — used by tests to guard against undeclared codes. */
    public static function all()
    {
        return [
            self::MISSING_IDENTIFIER,
            self::MISSING_PARAM,
            self::INVALID_PARAM,
            self::FORBIDDEN,
            self::NOT_FOUND,
            self::CREATE_FAILED,
            self::TOOL_FAILED,
            self::CONFIRMATION_REQUIRED,
            self::CONFIRMATION_EXPIRED,
            self::CONFIRMATION_INVALID,
            self::STATE_CHANGED,
            self::LIMIT_EXCEEDED,
            self::UNFILTERED_HTML_REQUIRED,
            self::FEATURE_DISABLED,
            self::CONCURRENT_REQUEST,
            self::EXECUTION_UNKNOWN,
        ];
    }
}
