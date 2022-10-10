<?php
# This is the main configuration file for Feast and should be in your repository
# Sections can inherit from another section by being prefixed with the section
# name and a colon. Settings in the chosen environment will be overridden by
# the config.local.php file
# See config.md for more details.

use Feast\Database\MySQLQuery;
use Feast\Enums\DatabaseType;
use Feast\HttpRequest\Curl;

$environments = [];
$environments['production'] = [
    'siteurl' => 'http://feast-framework.com',
    'database.default.host' => 'localhost',
    'database.default.user' => 'prod_user',
    'database.default.pass' => 'prodPassword',
    'database.default.connectionType' => DatabaseType::MYSQL,
    //'database.default.connectionType' => \Feast\Enums\DatabaseType::SQLITE,
    'database.default.queryClass' => MySQLQuery::class,
    //'database.default.queryClass' => \Feast\Database\SQLiteQuery::class,
    'database.default.name' => 'feasty',
    // 'plugin.throttle' => \Plugins\Throttle::class,
    'buildroutes' => true,
    'profiler' => false,
    'showerrors' => false,
    'title' => 'My Feast Site',
    'session.name' => 'session',
    'session.timeout' => 0,
    'session.strictIp' => true,
    'error.http404.url' => 'error/fourohfour',
    'cron.spawn' => true,
    'service.class' => Curl::class,
    #'service.class' => \Feast\HttpRequest\Simple::class,
    'email.sendmailpath' => '/usr/sbin/sendmail -oi -t -f ',
    'log.path' => APPLICATION_ROOT . 'storage/logs', // Path where the log file is stored.
    'log.permissions.path' => 0755,
    'log.permissions.file' => 0666,
    'log.syslog.enabled' => false, // Use syslog instead of file based logging.
    'log.syslog.prefix' => false, // String Prefix to use for syslog prefix (or false for no prefix)
    'log.syslog.facility' => LOG_USER,
    'log.syslog.flags' => LOG_ODELAY,
    'storage.path' => APPLICATION_ROOT . 'storage', // Path for local file storage.
];

$environments['production : development'] = [
    'profiler' => true,
    'showerrors' => true,
    'log.level' => 'debug',
    'ttycolor' => true
];

$environments['development : test'] = [
    'database.default.connectionType' => DatabaseType::SQLITE,
    'database.default.name' => ':memory:'
];
return $environments;
