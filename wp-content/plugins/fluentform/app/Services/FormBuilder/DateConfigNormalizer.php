<?php

namespace FluentForm\App\Services\FormBuilder;

/**
 * Normalises the Date/Time field's Advanced Date Configuration into
 * data-only JSON and regenerates the emitted flatpickr JS from it.
 *
 * Security invariant (see docs/SECURITY-REGRESSION-CHECKLIST.md): user
 * input never reaches the script sink. Recognised dynamic snippets are
 * converted into data tokens (__ff_fp_incr, __ff_date, __ff_disable_expr,
 * legacy __ff_disable_days); toJs() rebuilds code exclusively from
 * whitelisted enums, int casts, and wp_json_encode()d values. Anything
 * unrecognised fails json_decode and rejects the whole config.
 */
class DateConfigNormalizer
{
    /**
     * Encode flags for every user-influenced string/key that reaches the inline
     * <script> sink. JSON_HEX_TAG/AMP/APOS/QUOT turn <, >, &, ', " into \uXXXX so
     * no raw angle bracket can trigger the HTML5 script-data-double-escaped state
     * (which fluentform_kses_js() alone does not fully close). Slashes stay escaped
     * because the unescaped-slashes flag is never added here. Legit date/locale
     * values contain none of these characters, so their emitted output is unchanged.
     */
    const JS_STRING_FLAGS = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

    public static function sanitize($value)
    {
        if (!is_string($value) || '' === trim($value)) {
            return '';
        }

        $value = trim($value);

        $decoded = json_decode($value, true);

        if (JSON_ERROR_NONE !== json_last_error() || !is_array($decoded)) {
            $decoded = json_decode(static::normalizeJsObject($value), true);
        }

        if (JSON_ERROR_NONE !== json_last_error() || !is_array($decoded)) {
            return '';
        }

        if ([] === $decoded) {
            return '{}';
        }

        // date_config must be an object; reject a top-level JSON array. Nested
        // arrays (e.g. flatpickr `disable: [...]`) are preserved by not forcing
        // JSON_FORCE_OBJECT recursively.
        if (array_keys($decoded) === range(0, count($decoded) - 1)) {
            return '';
        }

        return wp_json_encode($decoded);
    }

    public static function normalizeJsObject($value)
    {
        $value = static::stripComments($value);

        $value = preg_replace_callback(
            '/new\s+Date\s*\(\s*(["\'])([^"\']{1,40})\1\s*\)/',
            function ($m) {
                if (!preg_match('/^[0-9A-Za-z ,:.\\/\\-]+$/', $m[2])) {
                    return $m[0];
                }
                return '{"__ff_date": ' . wp_json_encode($m[2]) . '}';
            },
            $value
        );

        $value = preg_replace_callback(
            '/new\s+Date\s*\(\s*\)\s*(?:\.\s*fp_incr\s*\(\s*(-?\d+)\s*\)\s*)?/',
            function ($m) {
                $days = isset($m[1]) && '' !== $m[1] ? (int) $m[1] : 0;
                return '{"__ff_fp_incr": ' . $days . '}';
            },
            $value
        );

        $value = preg_replace_callback(
            '/function\s*\(\s*([A-Za-z_$][A-Za-z0-9_$]*)\s*\)\s*\{\s*return\b\s*(.*?)\s*;?\s*\}/s',
            function ($m) {
                $expr = static::parsePredicate($m[2], $m[1]);
                return $expr ? wp_json_encode(['__ff_disable_expr' => $expr]) : $m[0];
            },
            $value
        );

        // Single-quoted strings -> double-quoted (respecting escapes).
        $value = preg_replace_callback(
            "/'((?:\\\\.|[^'\\\\])*)'/s",
            function ($m) {
                return '"' . str_replace(['\\\'', '"'], ['\'', '\\"'], $m[1]) . '"';
            },
            $value
        );

        // Quote unquoted object keys: `{ key:` / `, key:` -> `{ "key":`.
        $value = preg_replace('/([{,]\s*)([A-Za-z_$][A-Za-z0-9_$]*)(\s*:)/', '$1"$2"$3', $value);

        // Drop trailing commas before a closing brace/bracket.
        $value = preg_replace('/,\s*([}\]])/', '$1', $value);

        return $value;
    }

    public static function stripComments($value)
    {
        $result = '';
        $length = strlen($value);
        $inString = '';

        for ($i = 0; $i < $length; $i++) {
            $char = $value[$i];

            if ($inString) {
                $result .= $char;
                if ('\\' === $char) {
                    if ($i + 1 < $length) {
                        $result .= $value[++$i];
                    }
                } elseif ($char === $inString) {
                    $inString = '';
                }
                continue;
            }

            if ('"' === $char || "'" === $char) {
                $inString = $char;
                $result .= $char;
                continue;
            }

            if ('/' === $char && $i + 1 < $length) {
                if ('/' === $value[$i + 1]) {
                    while ($i < $length && "\n" !== $value[$i]) {
                        $i++;
                    }
                    $result .= "\n";
                    continue;
                }
                if ('*' === $value[$i + 1]) {
                    $i += 2;
                    while ($i + 1 < $length && !('*' === $value[$i] && '/' === $value[$i + 1])) {
                        $i++;
                    }
                    $i++;
                    continue;
                }
            }

            $result .= $char;
        }

        return $result;
    }

    public static function parsePredicate($body, $param)
    {
        $tokens = [];
        $pos = 0;
        $length = strlen($body);
        $leaf = '/\G' . preg_quote($param, '/') . '\s*\.\s*(getDay|getMonth|getDate|getFullYear)\s*\(\s*\)\s*(===|==|!==|!=|>=|<=|>|<)\s*(\d{1,4})/';

        while ($pos < $length) {
            $char = $body[$pos];
            if (ctype_space($char)) {
                $pos++;
                continue;
            }
            if ('(' === $char || ')' === $char) {
                $tokens[] = $char;
                $pos++;
                continue;
            }
            if ('|' === $char || '&' === $char) {
                if ($pos + 1 >= $length || $body[$pos + 1] !== $char) {
                    return null;
                }
                $tokens[] = '|' === $char ? '||' : '&&';
                $pos += 2;
                continue;
            }
            if (preg_match($leaf, $body, $m, 0, $pos)) {
                $tokens[] = ['fn' => $m[1], 'cmp' => $m[2], 'val' => (int) $m[3]];
                $pos += strlen($m[0]);
                continue;
            }
            return null;
        }

        if ([] === $tokens) {
            return null;
        }

        $index = 0;
        $ast = self::parseOr($tokens, $index);

        return null !== $ast && count($tokens) === $index ? $ast : null;
    }

    private static function parseOr(array $tokens, &$index)
    {
        $parts = [];
        do {
            $node = self::parseAnd($tokens, $index);
            if (null === $node) {
                return null;
            }
            $parts[] = $node;
            $more = isset($tokens[$index]) && '||' === $tokens[$index];
            if ($more) {
                ++$index;
            }
        } while ($more);
        return 1 === count($parts) ? $parts[0] : ['or' => $parts];
    }

    private static function parseAnd(array $tokens, &$index)
    {
        $parts = [];
        do {
            $node = self::parseUnit($tokens, $index);
            if (null === $node) {
                return null;
            }
            $parts[] = $node;
            $more = isset($tokens[$index]) && '&&' === $tokens[$index];
            if ($more) {
                ++$index;
            }
        } while ($more);
        return 1 === count($parts) ? $parts[0] : ['and' => $parts];
    }

    private static function parseUnit(array $tokens, &$index)
    {
        if (!isset($tokens[$index])) {
            return null;
        }
        if ('(' === $tokens[$index]) {
            ++$index;
            $node = self::parseOr($tokens, $index);
            if (null === $node || !isset($tokens[$index]) || ')' !== $tokens[$index]) {
                return null;
            }
            ++$index;
            return $node;
        }
        if (is_array($tokens[$index])) {
            return $tokens[$index++];
        }
        return null;
    }

    public static function exprToJs($node)
    {
        static $fns = ['getDay' => 1, 'getMonth' => 1, 'getDate' => 1, 'getFullYear' => 1];
        static $cmps = ['==' => 1, '===' => 1, '!=' => 1, '!==' => 1, '>=' => 1, '<=' => 1, '>' => 1, '<' => 1];

        if (!is_array($node)) {
            return null;
        }

        foreach (['or' => ' || ', 'and' => ' && '] as $op => $glue) {
            if (isset($node[$op])) {
                if (1 !== count($node) || !is_array($node[$op]) || [] === $node[$op]) {
                    return null;
                }
                $parts = [];
                foreach ($node[$op] as $child) {
                    $js = static::exprToJs($child);
                    if (null === $js) {
                        return null;
                    }
                    $parts[] = $js;
                }
                return '(' . implode($glue, $parts) . ')';
            }
        }

        if (3 !== count($node) || !isset($node['fn'], $node['cmp'], $node['val'])) {
            return null;
        }
        if (!is_string($node['fn']) || !is_string($node['cmp']) || !isset($fns[$node['fn']], $cmps[$node['cmp']])) {
            return null;
        }

        return 'date.' . $node['fn'] . '() ' . $node['cmp'] . ' ' . (int) $node['val'];
    }

    public static function toJs($json)
    {
        if (!is_string($json) || '' === $json) {
            return '';
        }

        $decoded = json_decode($json, true);

        if (JSON_ERROR_NONE !== json_last_error() || !is_array($decoded)) {
            return '';
        }

        return static::nodeToJs($decoded);
    }

    public static function nodeToJs($node)
    {
        if (!is_array($node)) {
            return wp_json_encode($node, static::JS_STRING_FLAGS);
        }

        if ([] === $node) {
            return '{}';
        }

        if (isset($node['__ff_fp_incr']) && 1 === count($node)) {
            $days = (int) $node['__ff_fp_incr'];
            return $days ? "new Date().fp_incr({$days})" : 'new Date()';
        }

        if (isset($node['__ff_disable_days']) && 1 === count($node) && is_array($node['__ff_disable_days'])) {
            $days = implode(', ', array_map('intval', $node['__ff_disable_days']));
            return "function(date) { return [{$days}].indexOf(date.getDay()) !== -1; }";
        }

        if (isset($node['__ff_date']) && 1 === count($node)) {
            $date = is_string($node['__ff_date']) ? $node['__ff_date'] : '';
            if (!preg_match('/^[0-9A-Za-z ,:.\\/\\-]{1,40}$/', $date)) {
                return 'null';
            }
            return 'new Date(' . wp_json_encode($date, static::JS_STRING_FLAGS) . ')';
        }

        if (isset($node['__ff_disable_expr']) && 1 === count($node)) {
            $expr = static::exprToJs($node['__ff_disable_expr']);
            return null === $expr ? 'null' : "function(date) { return {$expr}; }";
        }

        $isList = array_keys($node) === range(0, count($node) - 1);

        $parts = [];
        foreach ($node as $key => $value) {
            $encoded = static::nodeToJs($value);
            $parts[] = $isList ? $encoded : wp_json_encode((string) $key, static::JS_STRING_FLAGS) . ': ' . $encoded;
        }

        return $isList ? '[' . implode(', ', $parts) . ']' : '{' . implode(', ', $parts) . '}';
    }
}
