[Back to Index](index.md)

# Working with Controllers

The Controller classes are the backbone of FEAST. All requests [route](routing.md) to a controller/action and it is from
there that your business logic starts.

## HttpController

The HttpController is the base class for all web request controllers. It has many helper methods to simplify your
programming.

### Passing input to HttpController Actions

Any arguments to the controller method are passed in via dependency injection or by HTTP Request analysis. The query
string is analyzed and values are formatted to be passed in. Additionally, form input is also analyzed for requests that
pass in a body.

### init

The `init` method can do any initialization needed for your controller and returns either `true` if the request should
be allowed to continue, or `false` if the request should be denied for any reason. If false is returned when the
controller is initialized, a ServerFailureException is thrown, and the error page (`/views/Error/server-failure.phtml`)
is included.

### forward

The `forward` method will forward a request to a different action (or optionally different controller/module/route). It
takes the following parameters and will use the current values for any that are passed in null. Forwarding a request
will not rerun any `preDispatch` on plugins. The final forwarded destination will be used for the `postDispatch` on
plugins.

Arguments to forward:

1. action - the action to forward to.
2. controller - the controller to forward to.
3. arguments - An array of arguments to pass to the next controller.
4. queryString - An array of values to be interpreted as part of the query string.
5. module - (optional) The name of the module to forward to.
6. route - A named route to forward to, or empty string if none.

### alwaysJson

This method returns if the selected action should always return a JSON response. It is used internally by the framework,
but can be extended by the user in their controllers to provide true/false. An example implementation is below.

```php
public function alwaysJson(string $actionName): bool
    {
        return match ($actionName) {
            'graph' => true,
            default => false
        };
    }
``` 

### redirect

The `redirect` method will redirect a request to a different action (or optionally different controller/module/route).
It takes the following parameters and will use the current values for any that are passed in null. Redirecting a request
will run postDispatch plugins on the current request before the redirect occurs. PreDispatch and postDispatch plugins
will run on the new url as well.

Arguments to redirect:

1. action - the action to forward to.
2. controller - the controller to forward to.
3. arguments - An array of arguments to pass to the next controller.
4. queryString - An array of values to be interpreted as part of the query string.
5. module - (optional) The name of the module to forward to.
6. route - A named route to forward to, or empty string if none.
7. code - The response code to send on the redirect. Defaults to 302.
    1. Note: For forwards compatibility with the next version of Feast, pass in the Feast\Enums\ResponseCode constant
       for the request.

### externalRedirect

The `externalRedirect` method will redirect a request to an external url. As with redirect, all plugins will still run.

Arguments to redirect:

1. url - The URL to redirect to.
2. code - The response code to send on the redirect. Defaults to 302.
    1. Note: For forwards compatibility with the next version of Feast, pass in the Feast\Enums\ResponseCode constant
       for the request.

### allowJsonForAction

This method takes an action name as an argument and marks it as being allowed to return a json object instead of
rendering a view. It is used in conjunction with `format=json`
on the request URL.

### jsonAllowed

This method returns true if the current request can be a json response and false otherwise.

### sendJsonResponse

This method takes an object as its only argument. It marks the response as a json response, and sets the passed in
object as the object to be serialized as the response. This object will run through the [JSON Marshaller](json.md).

[Back to Top](#working-with-controllers)

## CliController

The CliController is the base class for all CLI controllers. It has an init helper methods and a Terminal object to
simplify your programming.

The `init` method can do any initialization needed for your controller and returns either `true` if the request should
be allowed to continue, or `false` if the request should be denied for any reason. If false is returned when the
controller is initialized, a ServerFailureException is thrown.

### Passing in Arguments
The CliController actions can have two types of arguments passed in. Standard and flag.

Flag arguments are passed in the format of `--name=value`. The designated name must match the parameter name.

Standard arguments are passed in the same order they appear in the command line after the flag arguments. All cli methods
should show their arguments with `php famine help`.

### The Terminal Object
The Terminal object (at `$this->terminal`) contains 3 methods to print text to the terminal (with color formatting)
and 3 to fetch the text without printing. In addition there are three methods for collecting user input (besides via
arguments).

#### Terminal Printing

These functions each take a string $text to print, and a boolean $newLine of whether to print a NewLine character (
PHP_EOL) at the end of the line

1. `message` - Message prints the text as is with no color formatting applied.
2. `command` - Command prints the text as yellow text.
3. `error` - Error prints the text as white text on a red background.

#### Terminal Formatting

These functions each take a string $text to format

1. `messageText` - MessageText returns the text as is with no color formatting applied.
2. `commandText` - CommandText returns the text as yellow text.
3. `errorText` - ErrorText returns the text as white text on a red background.

#### Collecting Terminal Input

The following functions are used to collect user input.

1. `getInputFromPrompt` - This method takes a question and an optional default value. If a default is passed, it will be
   shown in square brackets.
2. `getArrayFromPromptWithSentinel` - This method takes a question, and a sentinel value. In addition, a boolean flag
   for `allowBlank` is passed in. If allowBlank is false, blank inputs will be skipped from the returned array. The
   sentinel value is never included in the returned array. When the sentinel value is detected, the array is returned.
3. `getArrayFromPromptWithCount` - This method takes a question, and a count. In addition, an optional
   subsequentQuestion can be passed in. If the subsequent question is passed, then the initial question is used only
   once. Additionally, this method has an `allowBlank` boolean flag. If this flag is passed in and the user does not
   enter any input, the count does not increase, ensuring that the passed in count is the number of responses.

[Back to Top](#working-with-controllers)

