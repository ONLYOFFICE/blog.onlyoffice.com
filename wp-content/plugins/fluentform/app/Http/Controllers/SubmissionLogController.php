<?php

namespace FluentForm\App\Http\Controllers;

use Exception;
use FluentForm\App\Services\Logger\Logger;

class SubmissionLogController extends Controller
{
    public function get(Logger $logger, $submissionId)
    {
        try {
            $attributes = $this->request->all();
            
            $sanitizeMap = [
                'page' => 'intval',
                'per_page' => 'intval',
            ];
            $attributes = fluentform_backend_sanitizer($attributes, $sanitizeMap);
            
            return $this->sendSuccess(
                $logger->getSubmissionLogs($submissionId, $attributes)
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function remove(Logger $logger)
    {
        try {
            $attributes = $this->request->all();
            
            $sanitizeMap = [
                'log_id' => 'intval',
                'submission_id' => 'intval',
            ];
            $attributes = fluentform_backend_sanitizer($attributes, $sanitizeMap);
            
            return $this->sendSuccess(
                $logger->remove($attributes)
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
}
