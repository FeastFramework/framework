[Back to Index](index.md)

# Working with Sessions

FEAST includes some out of the box session management capabilities to make working with user sessions easier.

## Session config options

FEAST contains the following config parameters for sessions:

1. `session.name` - The name of the session. Defaults to `Feast_Session`.
2. `session.timeout` - The time in seconds for the session. Defaults to 0 (when browser closes).

## Retrieving the session

The session is placed into the service container and can be retrieved in one of two ways. First, it can be passed by
direct injection
into methods
through [Dependency Injection](service-container.md#dependency-injection). The parameter must be type hinted
as `\Feast\Session\Session`. Secondly, it can be retrieved by calling the global `di` function
with `\Feast\Session\Session::class` as the only argument.

## Session namespacing

FEAST makes use of namespaces for sessions. The following session namespaces are used by the framework and are not
recommended
for user interaction:

1. `Feast`
2. `FlashMessage`

Any other namespace can be retrieved by calling `getNamespace` on an instance of the `Session` class. This method
returns a stdClass, and will create it if it is empty.

The method `destroyNamespace` will unset an entire namespace, removing all items in that namespace.

## Flash Messages

Flash messages are one time use messages that are wiped automatically upon retrieval. This allows easily showing an
error message or success message on a redirect, for example.

The Flash Message namespace is not interfaced with through the session class. Rather, two static methods are provided.

## Storing a message

`FlashMessage::setMessage` takes two string parameters. A name and a value to be stored.

## Retrieving and erasing a message

`FlashMessage::getMessage` takes a name parameter, checks if the message exists, and returns it if so, after erasing it
from the session.

If no message is found, `null` is returned.