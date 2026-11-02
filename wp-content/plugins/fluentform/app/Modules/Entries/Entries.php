<?php

namespace FluentForm\App\Modules\Entries;

use FluentForm\App\Modules\Form\FormDataParser;
use FluentForm\App\Modules\Form\FormFieldsParser;

/**
 * @deprecated Use SubmissionService or direct wpFluent() queries instead.
 * Backward-compatible stub kept for third-party plugins (Ninja Tables, etc.)
 * that resolve this class via wpFluentForm() container.
 * @todo Remove in next major version.
 */
class Entries extends EntryQuery
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @deprecated Use direct queries or REST API instead.
     */
    public function _getEntries(
        $formId,
        $currentPage,
        $perPage,
        $sortBy,
        $entryType,
        $search,
        $wheres = []
    ) {
        $this->formId = $formId;
        $this->per_page = $perPage;
        $this->sort_by = $sortBy;
        $this->page_number = $currentPage;
        $this->search = $search;
        $this->wheres = $wheres;

        if ('favorite' == $entryType) {
            $this->is_favourite = true;
        } elseif ('all' != $entryType && $entryType) {
            $this->status = $entryType;
        }

        $dateRange = $this->request->get('date_range');
        if ($dateRange) {
            $this->startDate = $dateRange[0];
            $this->endDate = $dateRange[1];
        }

        $form = $this->formModel->find($formId);
        $formMeta = $this->getFormInputsAndLabels($form);
        $formLabels = $formMeta['labels'];

        $formLabels = apply_filters('fluentform/entry_lists_labels', $formLabels, $form);
        $submissions = $this->getResponses();
        $submissions['data'] = FormDataParser::parseFormEntries($submissions['data'], $form);

        return compact('submissions', 'formLabels');
    }

    public function getFormInputsAndLabels($form, $with = ['admin_label', 'raw'])
    {
        $formInputs = FormFieldsParser::getEntryInputs($form, $with);
        $inputLabels = FormFieldsParser::getAdminLabels($form, $formInputs);
        return [
            'inputs' => $formInputs,
            'labels' => $inputLabels,
        ];
    }
}
