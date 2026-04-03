<?php

namespace FluentForm\App\Http\Controllers;

use Exception;
use FluentForm\App\Services\Submission\SubmissionService;

class SubmissionNoteController extends Controller
{
    public function get(SubmissionService $submissionService, $submissionId)
    {
        try {
            $attributes = $this->request->all();
            
            $sanitizeMap = [
                'page'     => 'intval',
                'per_page' => 'intval',
            ];
            $attributes = fluentform_backend_sanitizer($attributes, $sanitizeMap);
            
            return $this->sendSuccess(
                $submissionService->getNotes($submissionId, $attributes)
            );
        } catch (Exception $exception) {
            return $this->sendError([
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function store(SubmissionService $submissionService, $submissionId)
    {
        try {
            $attributes = $this->request->all();

            if (isset($attributes['note']['content'])) {
                $attributes['note']['content'] = wp_kses_post($attributes['note']['content']);
            }
            if (isset($attributes['note']['status'])) {
                $attributes['note']['status'] = sanitize_text_field($attributes['note']['status']);
            }

            return $this->sendSuccess(
                $submissionService->storeNote($submissionId, $attributes)
            );
        } catch (Exception $exception) {
            return $this->sendError([
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
