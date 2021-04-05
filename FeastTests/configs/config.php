<?php

$environments = [];
$environments['production'] = [
    'test' => 1,
    'database.default' => [
        'name' => 'feast',
        'user' => 'feast_user',
        'password' => 'dont_put_passwords_in_the_config_file',

    ]
];
$environments['production:development'] = [
    'test' => 2,
    'database' => [
        'default' => [
            'name' => 'feast',
            'user' => 'feast_user'
        ]
    ]
];

return $environments;
