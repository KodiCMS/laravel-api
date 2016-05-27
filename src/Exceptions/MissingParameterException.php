<?php

namespace KodiCMS\API\Exceptions;

use KodiCMS\API\Http\Response;

class MissingParameterException extends Exception
{
    /**
     * @var string
     */
    protected $code = Response::ERROR_MISSING_PAPAM;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * MissingParameterException constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
        $this->message = trans('api::core.messages.missing_params', [
            'field' => implode(', ', array_keys($parameters)),
        ]);
    }

    /**
     * @return array
     */
    public function getFailedRules()
    {
        return $this->parameters;
    }

    /**
     * @return array
     */
    public function responseArray()
    {
        $data = parent::responseArray();
        $data['failed_rules'] = $this->getFailedRules();

        return $data;
    }
}
