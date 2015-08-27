<?php

namespace KodiCMS\API;

use KodiCMS\API\Http\RouteAPI;
use Illuminate\Support\Facades\Facade;

class RouteApiFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return RouteAPI::class;
    }
}