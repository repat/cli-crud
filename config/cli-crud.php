<?php

return [
    'resources' => [
        'path' => app_path('CliCrud/Resources'),
        'namespace' => 'App\\CliCrud\\Resources',
    ],
    'actions' => [
        'path' => app_path('CliCrud/Actions'),
        'namespace' => 'App\\CliCrud\\Actions',
    ],
    'pagination' => [
        'per_page' => 15,
        'relation_per_page' => 10,
    ],
    'authorization' => [
        // See Repat\CliCrud\Authorization\Authorizer for the default-allow semantics.
        'enabled' => false,
    ],
    'display' => [
        'date_format' => 'Y-m-d H:i:s',
    ],
];
