<?php

namespace FluentForm\App\Modules\Form;

use FluentForm\App\Services\Form\Fields;
use FluentForm\Framework\Foundation\Application;

class Inputs
{
    /**
     * Request object
     *
     * @var \FluentForm\Framework\Request\Request
     */
    private $request;

    /**
     * Build the class instance
     *
     * @throws \Exception
     */
    public function __construct(Application $application)
    {
        $this->request = $application->request;
    }

    /**
     * Get all the flatten form inputs for shortcode generation.
     */
    public function index()
    {
        $formId = $this->request->get('formId');

        wp_send_json((new Fields())->get($formId), 200);
    }
}
