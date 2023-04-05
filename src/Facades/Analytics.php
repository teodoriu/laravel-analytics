<?php

namespace ErlanCarreira\Analytics\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ErlanCarreira\Analytics\Analytics
 */
class Analytics extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-analyticsV1';
    }
}
