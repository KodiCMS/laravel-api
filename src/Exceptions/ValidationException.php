<?php

namespace KodiCMS\API\Exceptions;

use Exception;
use KodiCMS\API\Http\Response;

class ValidationException extends Exception
{

    /**
     * The validator instance.
     *
     * @var \Illuminate\Validation\Validator
     */
    protected $validator;

    /**
     * ValidationException constructor.
     *
     * @param \Illuminate\Validation\Validator $validator
     */
    public function __construct(\Illuminate\Validation\Validator $validator)
    {
        parent::__construct('The given data failed to pass validation.');

        $this->validator = $validator;
    }

    /**
     * @var int
     */
    protected $code = Response::ERROR_VALIDATION;

    /**
     * @return array
     */
    public function getFailedRules()
    {
        return $this->validator->failed();
    }

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->validator->errors()->getMessages();
    }

    /**
     * @return array
     */
    public function responseArray()
    {
        $data                 = parent::responseArray();
        $data['failed_rules'] = $this->getFailedRules();
        $data['errors']       = $this->getErrorMessages();

        return $data;
    }
}
