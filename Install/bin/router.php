<?php

[$path] = explode('?', $_SERVER['REQUEST_URI']);
$basePath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;
chdir($basePath);
$path = $basePath . $path;
if (is_file($path)) {
    $contentType = mime_content_type($path);
    if (str_ends_with($path, 'css')) {
        $contentType = 'text/css';
    } elseif (str_ends_with($path, 'jpg') || str_ends_with($path, 'jpeg')) {
        $contentType = 'image/jpeg';
    } elseif (str_ends_with($path, 'png')) {
        $contentType = 'image/png';
    } elseif (str_ends_with($path, 'svg')) {
        $contentType = 'image/svg+xml';
    } elseif (str_ends_with($path, 'webp')) {
        $contentType = 'image/webp';
    } elseif (str_ends_with($path, 'gif')) {
        $contentType = 'image/gif';
    } elseif (str_ends_with($path, 'json')) {
        $contentType = 'application/json';
    } elseif (str_ends_with($path, 'js')) {
        $contentType = 'text/javascript';
    }

    header('Content-type:' . $contentType);
    echo file_get_contents($path);
} else {
    include($basePath . 'index.php');
}
