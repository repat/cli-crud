<?php

return [
    'resources' => [
        'path' => app_path('CliCrud/Resources'),
        'namespace' => 'App\\CliCrud\\Resources',
    ],
    'pagination' => [
        'per_page' => 15,
        'relation_per_page' => 10,
    ],
    'authorization' => [
        'enabled' => false,
    ],
    'display' => [
        'date_format' => 'Y-m-d H:i:s',
    ],
];
