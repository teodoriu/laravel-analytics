<?php

namespace Teodoriu\Analytics\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Teodoriu\Analytics\Analytics
 */
class Analytics extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-analytics';
    }
}
