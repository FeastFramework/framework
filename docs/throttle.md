[Back to Index](index.md)

# Throttle Plugin

FEAST ships with an optional plugin for throttling requests from the same IP. This plugin is not enabled by default, but
can be easily configured by adding a few lines to your `configs/config.php` file.

To enable, simply add `'plugin.throttle' => \Plugins\Throttle::class,` to the appropriate environment.

The folder `storage/throttle` MUST be writeable by the web server.

The Throttle plugin has the following options.

1. `throttle.maxrequests` - defaults to 200
2. `throttle.maxrequesttime` - defaults to 20.
3. `error.throttle.url` - defaults to `/error/rate-limit`
   These options means that if a user exceeds 200 requests in a 20 second window, they should be forwarded
   to `/error/rate-limit`. Note that plugins will all still run on the rate-limit url, as it will not be throttled.