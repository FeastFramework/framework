<?php

/**
 * Copyright 2021 Jeremy Presutti <Jeremy@Presutti.us>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types=1);

chdir(__DIR__);
if (!defined('APPLICATION_ROOT')) {
    define('APPLICATION_ROOT', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..');
}
$paths = [
    'cache',
    'bin' . DIRECTORY_SEPARATOR . 'templates',
    'configs',
    'Controllers',
    'Form',
    'Form' . DIRECTORY_SEPARATOR . 'Filter',
    'Form' . DIRECTORY_SEPARATOR . 'Validator',
    'Handlers',
    'Helper',
    'Jobs' . DIRECTORY_SEPARATOR . 'Cron',
    'Jobs' . DIRECTORY_SEPARATOR . 'Queueable',
    'Mapper',
    'Migrations',
    'Model' . DIRECTORY_SEPARATOR . 'Generated',
    'Modules',
    'Modules' . DIRECTORY_SEPARATOR . 'CLI' . DIRECTORY_SEPARATOR . 'Controllers',
    'Plugins',
    'storage' . DIRECTORY_SEPARATOR . 'logs',
    'storage' . DIRECTORY_SEPARATOR . 'ratelimit',
    'storage' . DIRECTORY_SEPARATOR . 'cronrunning',
    'Services',
    'Tests',
    'Views' . DIRECTORY_SEPARATOR . 'Error',
    'Views' . DIRECTORY_SEPARATOR . 'Index',
    'public',
    'public' . DIRECTORY_SEPARATOR . 'css',
    'public' . DIRECTORY_SEPARATOR . 'graphics',
    'public' . DIRECTORY_SEPARATOR . 'js',
    'public' . DIRECTORY_SEPARATOR . 'icons',
];

foreach ($paths as $path) {
    if (is_dir(APPLICATION_ROOT . DIRECTORY_SEPARATOR . $path)) {
        continue;
    }
    mkdir(APPLICATION_ROOT . DIRECTORY_SEPARATOR . $path, 0755, true);
    echo 'Making Directory: ' . APPLICATION_ROOT . DIRECTORY_SEPARATOR . $path . "\n";
}

$directories = [
    'bin' . DIRECTORY_SEPARATOR . 'templates',
    'bin',
    'configs',
    'Controllers',
    'public',
    'public' . DIRECTORY_SEPARATOR . 'icons',
    'Migrations',
    'Views' . DIRECTORY_SEPARATOR . 'Index',
    'Views' . DIRECTORY_SEPARATOR . 'Error',
    'Model',
    'Mapper'
];

foreach ($directories as $directory) {
    $files = scandir(__DIR__ . DIRECTORY_SEPARATOR . $directory);

    foreach ($files as $file) {
        $newFile = str_contains($directory, 'templates') ? $file : str_replace('.txt', '', $file);
        if ($file === '.' || $file === '..' || is_dir(
                __DIR__ . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . $file
            ) || file_exists(APPLICATION_ROOT . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . $newFile)) {
            continue;
        }
        echo 'Creating file: ' . APPLICATION_ROOT . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . $newFile . "\n";
        copy(
            __DIR__ . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . $file,
            APPLICATION_ROOT . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . $newFile
        );
    }
}

$rootFiles = [
    '.appenv',
    'bootstrap.php',
    'container.php',
    'famine',
    'scheduled_jobs.php'
];
foreach ($rootFiles as $file) {
    if ($file === '.' || $file === '..' || file_exists(APPLICATION_ROOT . DIRECTORY_SEPARATOR . $file)) {
        continue;
    }
    echo 'Creating file: ' . APPLICATION_ROOT . DIRECTORY_SEPARATOR . $file . "\n";
    copy(__DIR__ . DIRECTORY_SEPARATOR . $file, APPLICATION_ROOT . DIRECTORY_SEPARATOR . $file);
    if ($file === 'famine') {
        chmod(APPLICATION_ROOT . DIRECTORY_SEPARATOR . $file, 0744);
    }
}

if (!file_exists(APPLICATION_ROOT . DIRECTORY_SEPARATOR . '.gitignore')) {
    echo 'Creating gitignore file' . "\n";
    copy(__DIR__ . DIRECTORY_SEPARATOR . 'gitignore.template', APPLICATION_ROOT . DIRECTORY_SEPARATOR . '.gitignore');
}

if (!file_exists(APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'layout.phtml')) {
    echo 'Creating layout file' . "\n";
    file_put_contents(
        APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'layout.phtml',
        file_get_contents(
            __DIR__ . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'layout.phtml'
        )
    );
}

echo "\n\n" . 'Installation completed. Check the docs folder for help on getting started.' . "\n";
