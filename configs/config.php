<?php

/**
 * Copyright 2021 Jeremy Presutti <Jeremy@Presutti.us>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use Feast\Enums\DatabaseType;
use Feast\HttpRequest\Curl;

$environments = [];

$environments['production'] = [

    'buildroutes' => true,
    'profiler' => false,
    'showerrors' => false,

    'error' => [
        'http404' => [
            'url' => 'error/fourohfour'
        ]
    ],
    'service' => [
        'class' => Curl::class,
        //'class' => \Feast\HttpRequest\Simple::class,
        // Switch the above two lines if simple http requests (without curl) are desired.
    ]
];

$environments['production : development'] = [
    'log' => [
        'level' => 'debug'
    ],
    'ttycolor' => true
];

$environments['development : test'] = [
    'database' => [
        'default' => [
            'connectionType' => DatabaseType::SQLITE,
            'name' => ':memory:'
        ]
    ]
];
return $environments;
