<?php

$environments = [];
$environments['production'] = [
    'test' => 1,
    'database.default' => [
        'name' => 'feast',
        'user' => 'feast_user',
        'password' => 'dont_put_passwords_in_the_config_file',

    ],
    'featureflags' => [
        'test' => new \Feast\Config\FeatureFlag(true),
        'otherTest' => new \Feast\Config\FeatureFlag(false),
        'trueTest' => new \Feast\Config\FeatureFlag(true),
        'falseTest' => new \Feast\Config\FeatureFlag(false),
    ]
];
$environments['production:development'] = [
    'test' => 2,
    'database' => [
        'default' => [
            'name' => 'feast',
            'user' => 'feast_user'
        ]
    ],
    'featureflags' => ''
];

$environments['production:features'] = [
    'featureflags' => [
        'trueTest' => new \Feast\Config\FeatureFlag(false),
        'falseTest' => new \Feast\Config\FeatureFlag(true),
    ]
];

return $environments;
