[Back to Index](index.md)

# Configuring FEAST

FEAST contains an `.appenv` file that contains the name of your environment. This value will
match to one of the environments in the `configs/config.php` file. Note that if using
inherited environments, only the child name is included. Example: `production : dev` would be
recorded as just `dev`. Inheritance is covered below.

[Basic FEAST Configuration](#basic-feast-configuration)

[Built-in Configuration Directives](#built-in-configuration-directives)

[Feature Flags](#feature-flags)

[Caching the Config](#caching-the-config)

## Basic FEAST configuration

FEAST uses two different files for configuration. These files are similar but serve slightly different purposes. The
first is `configs/config.php`. This file contains all your shared configuration, and the top level keys are the
different
environments.

You may make any config key/values your application needs.

Config keys may be separated by `.` rather than by making new levels to the array. The following
three code examples are identical in result.

```php
<?php
$environments['production'] = [
    'database.default.host' => '127.0.0.1',
    'database.default.name' => 'feasty',
    'database.default.connectionType' => \Feast\Enums\DatabaseType::MYSQL,
    'showerrors' => false
];

$environments['production'] = [
    'database.default' => [ 
        'host' => '127.0.0.1',
        'name' => 'feasty'
    ],
    'showerrors' => false
];

$environments['production'] = [
    'database' => [
        'default' => [ 
            'host' => '127.0.0.1',
            'name' => 'feasty'
        ]
    ],
    'showerrors' => false
];
```

In addition, environments can inherit from other environments through
`:` separation on the name. The following code blocks show a config file and the result

```php
<?php
$environments = [];
$environments['production'] = [
    'database.default.host' => '127.0.0.1',
    'database.default.name' => 'feasty',
    'database.default.connectionType' => \Feast\Enums\DatabaseType::MYSQL,
    'showerrors' => false
];

$environments['production : dev'] = [
    'database.default.user' => 'codebox',
    'database.default.name' => 'localfeast'
    'showerrors' => true
];

$environments['dev : feast'] = [
    'database.default.host' => '192.168.0.2'
    'showerrors' => false
];

$environments['production:staging'] = [
    'database.default.name' => 'staging'
];

return $environments;

# The final config ends up processed as if you had entered the following.
$environments['production'] = [
    'database.default.host' => '127.0.0.1',
    'database.default.name' => 'feasty',
    'database.default.connectionType' => \Feast\Enums\DatabaseType::MYSQL,
    'showerrors' => false
];
$environments['dev'] = [
    'database.default.host' => '127.0.0.1',
    'database.default.user' => 'codebox',
    'database.default.name' => 'localfeast',
    'database.default.connectionType' => \Feast\Enums\DatabaseType::MYSQL,
    'showerrors' => true
];

$environments['feast'] = [
    'database.default.host' => '192.168.0.1',
    'database.default.user' => 'codebox',
    'database.default.name' => 'localfeast',
    'database.default.connectionType' => \Feast\Enums\DatabaseType::MYSQL,
    'showerrors' => false
];

$environments['staging'] = [
    'database.default.host' => '127.0.0.1',
    'database.default.name' => 'staging',
    'database.default.connectionType' => \Feast\Enums\DatabaseType::MYSQL,
    'showerrors' => false
];
```

The second configuration file is `configs/config.local.php`. Any secret
information such as passwords or tokens should be stored here. This file
should NOT be under version control.

The `configs/config.local.php` file differs in syntax in that
an array of configuration options is returned, not an array of configured environments.
The settings in this file will override all others. See the below example.

```php
return [
    'database.default.password' => 'testPass'
];

# The final config would be a combination of the chosen environment and this local config. 
# Examples for each of the prior ones are below.
$environments['production'] = [
    'database.default.host' => '127.0.0.1',
    'database.default.name' => 'feasty',
    'database.default.password' => 'testPass',
    'database.default.connectionType' => \Feast\Enums\DatabaseType::MYSQL,
    'showerrors' => false
];
$environments['dev'] = [
    'database.default.host' => '127.0.0.1',
    'database.default.user' => 'codebox',
    'database.default.name' => 'localfeast',
    'database.default.password' => 'testPass',
    'database.default.connectionType' => \Feast\Enums\DatabaseType::MYSQL,
    'showerrors' => true
];

$environments['feast'] = [
    'database.default.host' => '192.168.0.1',
    'database.default.user' => 'codebox',
    'database.default.name' => 'localfeast',
    'database.default.password' => 'testPass',
    'database.default.connectionType' => \Feast\Enums\DatabaseType::MYSQL,
    'showerrors' => false
];

$environments['staging'] = [
    'database.default.host' => '127.0.0.1',
    'database.default.name' => 'staging',
    'database.default.password' => 'testPass',
    'database.default.connectionType' => \Feast\Enums\DatabaseType::MYSQL,
    'showerrors' => false
];
```

Note that the final configuration stored is ONLY the one matching your
current environment.

[Back to Top](#configuring-feast)

## Built-in Configuration Directives

FEAST has several built in configuration directives that change the behavior
of the framework.

```php
<?php
$config = [
    'siteurl' => 'https://www.feast-framework.com',
                             // The website url
    'html.doctype' => Feast\Enums\DocTypes::HTML_5,
                             // The doctype to use by default. See \Feast\Enums\DocTypes for list.    
    'showerrors' => true,    // Show errors in the browser w/ stack trace.
    'showerrors' => false,   // Do not show errors in the browser.
    'ttycolor' => true,      // Force color output on CLI.
    'ttycolor' => false,     // Disable color output on CLI.
    'ttycolor' => null,      // Allow terminal to decide whether to use color.
    'title' => 'Page Title', // The default page title for the project.
                             // The title can be overriden on a per action basis.
    'buildroutes' => true,   // Build routes dynamically from the
                             // Path attribute (see /Feast/Attributes/Path).
    'buildroutes' => false,  // Do not dynamically build routes. Only set this if you 
                             // Are not using the dynamic routing.
    'plugin' => [
        'throttle' =>
        \Feast\Plugin\Throttle::class
    ],                       // An array of named plugins in key => className format.
                             // A sample is included with the pre-installed Throttle class.
    'error.http404.url' =>
        'error/fourohfour',  // URL for 404 error redirects
    'error.throttle.url' =>
        'error/fourohfour',  // URL for throttle errors. The default controller is created on install
                             // And should use no database connection to prevent resource burnup.
    'service.class' =>
        \Feast\HTTPRequest\Curl::class,
                             // Use Curl for Service requests
    'service.class' =>
        \Feast\HTTPRequest\Simple::class,
                             // Use Simple (file_get_contents based) for Service requests
    'cron.spawn' => true     // Allow cron jobs to be ran in their own process
                             // when combined with CronJob::runInBackground().
    'database.default'   => [
        'host' => 'hostname',
        'name' => 'databaseName'
        'user' => 'username'
        'password' => 'password',
        'url' => 'mysql:host=%s;port=%s;dbname=%s' // OPTIONAL: A manually specified connection string. If blank, the framework
                                                   // will build from the other parameters
        'connectionType' => \Feast\Enums\DatabaseType::MYSQL, // For MySQL/MariaDB
        // 'connectionType' => \Feast\Enums\DatabaseType::SQLITE, // For SQLite DB
        'options' => []       # PDO Options list in array format.
    ],                        // Database connection info. The config database.default means 
                              // the details belong to the default connection.
    'pdoClass' => 
        \PDO::class,          // Optional class name for use by database rather than PDO. 
                              // This class MUST extend \PDO.
    'email.sendmailpath' => 
        '/usr/sbin/sendmail -oi -t -f ',
                              // The path for Sendmail on the server. SMTP support coming soon.
    'log' => [
        'level' => \Feast\Enums\LogLevel::Error,
                              // One of the loglevel constants. This specifies the default loglevel.
        'permissions.file' => 0666,
                              // The permissions for creating a log file if it doesn't exist. This MUST be octal.
        'permissions.path' => 0755,
                              // The permissions for creating the log directory if it doesn't exist. This MUST be octal.
    ],
    'session' => [
        'enabled' => true // Enable sessions. This is the default value.
        'enabled' => false // Disable sessions. Calls to Session::getNamespace and Session::destroyNamespace will throw a SessionNotStarted exception.
        'name' => 'Feast_Session',
                              // The name of the session cookie
        'timeout' => 0,       // Session timeout (0 for window close)
        'strictIp' => true,   // Destroy session if the session IP does not match the current IP
        'strictIp' => false,  // Do not destroy session if the session IP does not match the current IP
    ],
];
```

[Back to Top](#configuring-feast)

## Feature Flags

The config item `featureflags` is a special item. This config takes an array of `key` => `value` mappings of feature
flags.

The base class can be used if simple `on` or `off` functionality is needed, or you may extend this class. There is a cli
action for creating these helper classes. See the docs [here](cli.md#feastcreatefeature-flag).

### Sample configuration

```php
<?php
$environments = [];

$environments['production'] =
    [
        'featureflags' => [
            'test' => new \Feast\Config\FeatureFlag(true),
            'otherTest' => new \Feast\Config\FeatureFlag(false)
        ]
    ]
];

$environments['production : dev'] =
    [
        'featureflags' => [
            'test' => new \Feast\Config\FeatureFlag(false),
            'otherTest' => new \Feast\Config\FeatureFlag(true)
        ]
    ]
];

return $environments;

```

You can set the same configuration item at different levels to change the value.

### Retrieving feature flag configurations

You can retrieve a [`CollectionList`](collections.md#collection-list) of all FeatureFlags by calling the
method `getFeatureFlags` or can retrieve a single item by calling the method `getFeatureFlag` and passing in the name of
the flag you wish to retrieve.

If an item matching the chosen flag does not exist, a default one will be returned with the value for enabled
being the second parameter to `getFeatureFlag` (defaults to `false`).

### Checking if enabled

The method `isEnabled` on `FeatureFlag` objects will return true if enabled, or false if disabled and can be overridden
in the child class.

[Back to Top](#configuring-feast)

## Caching the Config

To speed up your requests, you may cache your configuration
by running `php famine feast:cache:config-generate`. To clear
the cache, run `php famine feast:cache:config-clear`.

If you cache your configuration, changes will NOT take effect unless you clear or regenerate.

[Back to Top](#configuring-feast)