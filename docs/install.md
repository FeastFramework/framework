[Back to Index](index.md)

# The Five-Minute Quick Start

[Installation](#installation)

[Configuration](#configuration)

[Running on Apache2](#running-on-apache2)

[Running on nginx](#running-on-nginx)

[Running with PHP's built in server](#running-with-phps-built-in-server)

## Installation

There are three ways to install FEAST. They are presented in order from easiest (and preferred) to most difficult.

### Create Project with Composer

The easiest way to install FEAST is with [Composer](https://getcomposer.org/download/). Once you have installed
Composer, simply navigate in your Terminal to the parent folder of your new project, and
run `composer create-project feast/feast folderName`. You're all set!

### Manual Composer install

The next way to install FEAST with [Composer](https://getcomposer.org/download/)
is to first create an empty project folder and navigate to it in your Terminal. Run `composer require feast/framework`
to download all dependencies.

After the install finishes, run `cd vendor/feast/framework/Install && php composer-install.php` to bootstrap the
project.

### Manual install - no Composer

Navigate to the FEAST repository on Github [here](https://github.com/FeastFramework/framework). Click the `Code` button
and copy the URL for the repository. Navigate to your project in your Terminal and run `git clone [repo url] Feast`.

After the project is checked out, run `cd Feast/Install && php install.php`.

Note: If you manually install in a different folder, FEAST will not automatically find the correct namespacing.

[Back to Top](#the-five-minute-quick-start)

# Configuration

FEAST uses two Config files that have similar but slightly different syntax. The first file is `configs/config.php`.
This file returns an array of multiple environments. The second file is `configs/config.local.php`.

The settings in `configs/config.local.php` will override all prior settings. This file is where any secrets should be
placed and should NEVER be under version control.

Learn more about configuring FEAST
[here](config.md).

[Back to Top](#the-five-minute-quick-start)

# Running the Site
For all configurations, the server root should be the `public` folder of your project.
## Running on Apache2

To run on Apache2, you must install [mod rewrite](https://httpd.apache.org/docs/current/mod/mod_rewrite.html). In
addition, your site configuration must allow overrides for the .htaccess file to be read. If you do not wish to use the
.htaccess file, add the following to your Directory section in your site config.

```
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
```

[Back to Top](#the-five-minute-quick-start)

## Running on nginx

To run FEAST on nginx, add the following to your server configuration
```
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

[Back to Top](#the-five-minute-quick-start)

## Running with PHP's built in server

To run PHP's built-in web server (for dev purposes only, not production)
navigate to your project directory in your terminal and run
`php famine feast:serve:serve`. See more info [here](cli.md#feastserveserve)

The file `bin/router.php` has some common mime types built in, but you may edit it to add more.


[Back to Top](#the-five-minute-quick-start)