<?php

return [
    /*
    |--------------------------------------------------------------------------
    | EAV Table Names
    |--------------------------------------------------------------------------
    |
    | Here you can customize the table names used by the EAV package.
    |
    */
    'tables' => [
        'attributes' => 'attributes',
        'attribute_groups' => 'attribute_groups',
        'attribute_group_attributes' => 'attribute_group_attributes',
        'attributable_attributes' => 'attributable_attributes',
        'attributable_attribute_groups' => 'attributable_attribute_groups',
    ],

    /*
    |--------------------------------------------------------------------------
    | EAV Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching for EAV attributes and groups.
    |
    */
    'cache' => [
        'enabled' => env('EAV_CACHE_ENABLED', true),
        'prefix' => 'eav',
        'ttl' => 3600, // 1 hour
    ],
];


