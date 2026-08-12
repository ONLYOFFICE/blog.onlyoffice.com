<?php

namespace FluentForm\App\Http\Controllers;

use Exception;
use FluentForm\App\Services\Logger\Logger;

class LogController extends Controller
{
    public function get(Logger $logger)
    {
        try {
            $attributes = $this->request->all();
            
            $sanitizeMap = [
                'form_id'  => 'intval',
                'page'     => 'intval',
                'per_page' => 'intval',
                'search'   => 'sanitize_text_field',
                'log_type' => 'sanitize_text_field',
            ];
            $attributes = fluentform_backend_sanitizer($attributes, $sanitizeMap);
            
            return $this->sendSuccess(
                $logger->get($attributes)
            );
        } catch (Exception $e) {
            $response = ['message' => __('Something went wrong, please try again!', 'fluentform')];
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $response['error'] = $e->getMessage();
            }
            return $this->sendError($response);
        }
    }

    public function getFilters(Logger $logger)
    {
        try {
            $attributes = $this->request->all();
            
            $sanitizeMap = [
                'form_id' => 'intval',
            ];
            $attributes = fluentform_backend_sanitizer($attributes, $sanitizeMap);
            
            return $this->sendSuccess(
                $logger->getFilters($attributes)
            );
        } catch (Exception $e) {
            $response = ['message' => __('Something went wrong, please try again!', 'fluentform')];
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $response['error'] = $e->getMessage();
            }
            return $this->sendError($response);
        }
    }

    public function remove(Logger $logger)
    {
        try {
            $attributes = $this->request->all();
            
            $sanitizeMap = [
                'log_id' => 'intval',
                'log_ids' => function ($value) {
                    if (is_array($value)) {
                        return array_map('intval', $value);
                    }

                    return [];
                },
                'type' => 'sanitize_text_field',
            ];
            $attributes = fluentform_backend_sanitizer($attributes, $sanitizeMap);
            
            return $this->sendSuccess(
                $logger->remove($attributes)
            );
        } catch (Exception $e) {
            $response = ['message' => __('Something went wrong, please try again!', 'fluentform')];
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $response['error'] = $e->getMessage();
            }
            return $this->sendError($response);
        }
    }
}
