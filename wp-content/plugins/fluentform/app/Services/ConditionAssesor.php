<?php

namespace FluentForm\App\Services;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Helpers\Str;
use FluentForm\App\Services\FormBuilder\EditorShortcodeParser;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;

class ConditionAssesor
{
    public static function evaluate(&$field, &$inputs, $form = null)
    {
        $status = Arr::get($field, 'conditionals.status');
        if (!$status) {
            return true;
        }

        $type = Arr::get($field, 'conditionals.type', 'any');

        // Handle group conditions
        if ($type === 'group' && $conditionGroups = Arr::get($field, 'conditionals.condition_groups')) {
            return self::evaluateGroupConditions($conditionGroups, $inputs, $form);
        }

        // Handle 'any', 'all' conditions
        if ($type !== 'group' && $conditions = Arr::get($field, 'conditionals.conditions')) {
            return self::evaluateConditions($conditions, $inputs, $type, $form);
        }
        return true;
    }

    private static function evaluateGroupConditions($conditionGroups, &$inputs, $form = null)
    {
        $hasGroupConditionsMet = true;
        foreach ($conditionGroups as $group) {
            if ($conditions = Arr::get($group, 'rules')) {
                $hasGroupConditionsMet = self::evaluateConditions($conditions, $inputs, 'all', $form);
                if ($hasGroupConditionsMet) {
                    return true;
                }
            }
        }
        return $hasGroupConditionsMet;
    }

    private static function evaluateConditions($conditions, &$inputs, $type, $form = null)
    {
        $hasConditionMet = true;

        foreach ($conditions as $condition) {
            if (!Arr::get($condition, 'field') || !Arr::get($condition, 'operator')) {
                continue;
            }

            $hasConditionMet = static::assess($condition, $inputs, $form);

            if ($hasConditionMet && $type == 'any') {
                return true;
            }

            if ($type === 'all' && !$hasConditionMet) {
                return false;
            }
        }

        return $hasConditionMet;
    }

    public static function assess(&$conditional, &$inputs, $form = null)
    {
        if ($conditional['field']) {
            $accessor = rtrim(str_replace(['[', ']', '*'], ['.'], $conditional['field']), '.');

            if (!Arr::has($inputs, $accessor)) {
                return false;
            }
            $inputValue = Arr::get($inputs, $accessor);

            if ($numericFormatter = Arr::get($conditional, 'numeric_formatter')) {
                $inputValue = Helper::getNumericValue($inputValue, $numericFormatter);
            }
            $conditionValue = Arr::get($conditional, 'value');
            $isArrayAcceptable = in_array($conditional['operator'], ['=', '!=']);
            if (!empty($conditionValue) && is_string($conditionValue)) {
                $conditionValue = self::processSmartCodesInValue($conditionValue, $inputs, $form, $isArrayAcceptable);
            }

            $conditionValue = is_null($conditionValue) ? '' : $conditionValue;
            
            switch ($conditional['operator']) {
                case '=':
                    if (is_array($inputValue) && is_array($conditionValue)) {
                        $flatInput = Arr::flatten($inputValue);
                        $flatCondition = Arr::flatten($conditionValue);
                        sort($flatInput);
                        sort($flatCondition);
                        return $flatInput == $flatCondition;
                    }
                    
                    if (is_array($conditionValue)) {
                        return in_array($inputValue, Arr::flatten($conditionValue));
                    }
                    if (is_array($inputValue)) {
                        return in_array($conditionValue, Arr::flatten($inputValue));
                    }
                    return $inputValue == $conditionValue;
                case '!=':
                    if (is_array($inputValue) && is_array($conditionValue)) {
                        return count(array_intersect(Arr::flatten($inputValue), Arr::flatten($conditionValue))) == 0;
                    }
                    if (is_array($conditionValue)) {
                        return !in_array($inputValue, Arr::flatten($conditionValue));
                    }
                    if (is_array($inputValue)) {
                        return !in_array($conditionValue, Arr::flatten($inputValue));
                    }
                    return $inputValue != $conditionValue;
                case '>':
                    return $inputValue > $conditionValue;
                case '<':
                    return $inputValue < $conditionValue;
                case '>=':
                    return $inputValue >= $conditionValue;
                case '<=':
                    return $inputValue <= $conditionValue;
                case 'startsWith':
                    return Str::startsWith($inputValue, $conditionValue);
                case 'endsWith':
                    return Str::endsWith($inputValue, $conditionValue);
                case 'contains':
                    return Str::contains($inputValue, $conditionValue);
                case 'doNotContains':
                    return !Str::contains($inputValue, $conditionValue);
                case 'length_equal':
                    if (is_array($inputValue)) {
                        return count($inputValue) == $conditionValue;
                    }
                    $inputValue = (string)$inputValue;
                    return strlen($inputValue) == $conditionValue;
                case 'length_less_than':
                    if (is_array($inputValue)) {
                        return count($inputValue) < $conditionValue;
                    }
                    $inputValue = (string)$inputValue;
                    return strlen($inputValue) < $conditionValue;
                case 'length_greater_than':
                    if (is_array($inputValue)) {
                        return count($inputValue) > $conditionValue;
                    }
                    $inputValue = (string)$inputValue;
                    return strlen($inputValue) > $conditionValue;
                case 'test_regex':
                    if (is_array($inputValue)) {
                        $inputValue = implode(' ', $inputValue);
                    }
                    $pattern = '/' . $conditionValue . '/';
                    $result = @preg_match($pattern, $inputValue);
                    if ($result === false) {
                        // Invalid regex pattern, handle gracefully
                        return false;
                    }
                    return (bool) $result;
            }
        }

        return false;
    }

    private static function processSmartCodesInValue($value, &$inputs, $form = null, $isArrayAcceptable = true)
    {
        if (strpos($value, '{') === false) {
            return $value;
        }

        if (preg_match('/^{inputs\.([^}]+)}$/', $value, $inputMatches)) {
            $fieldName = $inputMatches[1];
            $fieldKey = str_replace(['[', ']'], ['.', ''], $fieldName);
            
            $resolvedValue = Arr::get($inputs, $fieldKey);
            
            if ($resolvedValue === null && $fieldKey !== $fieldName) {
                $resolvedValue = Arr::get($inputs, $fieldName);
            }

            // Return array if it's an array
            if (is_array($resolvedValue) && $isArrayAcceptable) {
                return $resolvedValue;
            }
        }

        try {
            $processedValue = preg_replace_callback('/{+(.*?)}/', function ($matches) use ($inputs, $form) {
                $smartCode = $matches[1];

                if (false !== strpos($smartCode, 'inputs.')) {
                    $fieldName = substr($smartCode, strlen('inputs.'));
                    
                    $fieldKey = str_replace(['[', ']'], ['.', ''], $fieldName);
                    
                    $value = Arr::get($inputs, $fieldKey, '');
                    
                    if ($value === '' && $fieldKey !== $fieldName) {
                        $value = Arr::get($inputs, $fieldName, '');
                    }
                    
                    if (is_array($value)) {
                        $value = fluentImplodeRecursive(', ', $value);
                    }
                    
                    return $value !== null && $value !== '' ? $value : '';
                }

                // @todo Support general shortcodes in future

                // Always return the original value if we don't have a match
                return $matches[0];
            }, $value);

            return $processedValue;
        } catch (\Exception $e) {
            return $value;
        }
    }
}
