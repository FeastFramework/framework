[Back to Index](index.md)

# The Logger

FEAST comes with a [PSR-3](https://www.php-fig.org/psr/psr-3/) compatible logger built in. This logger is lightweight,
and can be replaced by any other PSR-3 compatible logger by extending that logger and implementing the FEAST Logger
interface.

The logger is available in the [Service Container](service-container.md) and can be retrieved at any time with the `di`
function call. In addition, as with all items in the container, it can be automatically injected anywhere FEAST supports
dependency injection.

The following logging levels are available. Each level builds on the previous level.

1. `debug` - This level should be used for extremely verbose logging. Currently, FEAST logs Database Queries at this
   level.
2. `info` - Less verbose messages, useful for logging events with less detail than debug.
3. `notice` - Useful for significant event logging.
4. `warning` - Logging of possibly undesirable behavior but not necessarily invalid.
5. `error` - Runtime errors that should be investigated and fixed.
6. `critical` - Errors that should be dealt with sooner rather than later.
7. `alert` - Immediate action should be taken.
8. `emergency` - System unusable.

The logging level is set in the [config](config.md) file and can be different for each environment.

Each logging level has a corresponding method in the logger that will log the message if, and only if, the level set in
the config is at least as low as the level of the method. For example, if your config file is set to `notice`, then
a `debug` message will not be logged, but an `error` will be.

While other methods exist in the Logger class, the level-specific ones should be used at all times.