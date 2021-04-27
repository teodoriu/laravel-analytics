<?php

return [

    /*
     * The property id of which you want to display data.
     */
    'property_id'               => env('ANALYTICS_PROPERTY_ID', null),

    /*
     * The amount of minutes the Google API responses will be cached.
     * If you set this to zero, the responses won't be cached at all.
     */
    'cache_lifetime_in_minutes' => 60 * 24,
];
