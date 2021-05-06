[Back to Index](index.md)

# Routing

[Automatic default routing](#automatic-default-routing)

[Dynamic routing](#dynamic-routing)

FEAST contains three different methods for controlling routing. One is static, the other two are dynamic.

## Automatic default routing

When a new Controller/Action pair is made with the `php famine feast:create:action`
command, a default route is made. In addition, unless `noview true` is passed in, a View file is created as well.

The Controller and action by default become the URL. For example,
`AboutController->privacyGet` will have a default url of `/about/privacy` for get requests. No user action is required
to create this routing.

A special case exists for the `index` actions. If `indexGet` exists in
`AboutController`, then `/about` will route to the `indexGet`
(or post etc based on request type) method.

[Back to Top](#routing)

## Dynamic routing

There are two methods of dynamic routing, both of which use similar path strings for urls. The url path can contain
substituted variables by adding in `:variableName`. Variables are considered dynamic properties. The final property can
be marked as optional by preceding the `:` with a `?`. This allows a route that can optionally omit the last section.

Examples:
`/user/:username/:password` would be matched by `/user/jeremy/feast` and would NOT be matched by `/user/jeremy`

`/user/:username/password/:password` would be matched by `/user/jeremy/password/feast` and would NOT be matched
by `/user/jeremy/password`

`/user/:username/?:password` would be matched by both `/user/jeremy/feast` and `/user/jeremy`

[Back to Top](#routing)

### Dynamic Routing via Attributes - Recommended

FEAST makes use of the [Attributes](https://www.php.net/manual/en/language.attributes.overview.php)
feature of PHP 8.0 for custom route generation. Similar to annotations in docblocks in older versions of PHP, Attributes
allow for a "comment" that can be evaluated by reflection. See `\Feast\Attributes\Path` for the attribute and below for
an example

```php
<?php
class RegisterController 
{

#[Path(path:'register/:key/:registrationType', name:'freereg',method: Path::METHOD_GET|Path::METHOD_POST)]
    public function indexGet(string $key, string $registrationType): void
```

This creates a route at /register/$key/$registrationType for GET and POST requests to use the indexGet method of the
RegisterController.

[Back to Top](#routing)

### Dynamic routing via method call - Not recommended

While using the default routing or the Attribute based routing is recommended, you can also call
`Feast\Router\Router::addRoute` directly in `bootstrap.php` to create a route. This allows for easily finding your
configured routes, but is a more verbose syntax. The parameters to `addRoute` are as follows (with a sample call below)

```php
 public function addRoute(
        string $path,               // The url path for the route 
        string $controller,         // The name of the controller class to call (as string not class string)
        string $action,             // The name of the action method to call 
        ?string $routeName = null,  // An optional route name for easy use with 
                                    // redirect and forward (documented elsewhere).
        array $defaults = null,     // an array of default values for the method call if any
        string $httpMethod = 'GET', // The HTTP verb (can only specify one)
        string $module = 'Default'  // The Module to use. Modules documented elsewhere.
    ): void;
    
    $router->addRoute('/im-a-teapot/:user/:pass', 'Teapot', 'shortAndStout', null, ['test' => 'testing']);
    // The above will create a dynamic url at /im-a-teapot/{user}/{password} that will call TeapotController::shortAndStout
    // with the expected parameters of $user, $pass, and $test 
```

[Back to Top](#routing)

## Caching routes

To speed up your requests, you may cache your router configuration by running `php famine feast:cache:router-generate`.
To clear the cache, run `php famine feast:cache:router-clear`.

If you cache your routes, changes will NOT take effect unless you clear or regenerate.

[Back to Top](#routing)