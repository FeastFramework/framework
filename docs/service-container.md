[Back to Index](index.md)

# The Service Container

FEAST contains a robust, [PSR-11](https://www.php-fig.org/psr/psr-11/) compliant service container that doubles as a
Dependency Injection system.

## How it works

The service container is made up of several files working together.

1. `container.php` - This file is created when you first install FEAST and contains all the mappings for the built-in
   FEAST classes. You can add your own items to the container here as well.
2. `Feast\DependencyInjector.php` - This file contains the `di` function. This function takes a class or interface name
   and any optional arguments and returns the matching item from the container.
3. `Feast\ServiceContainer.php` - This class holds the items for the container.

## Storing and retrieving from the Container

The container is retrieved by calling `$container = di()` with no arguments. You can then add items to the container by
calling `$container->add(className,object,...arguments)`. Items can be then retrieved from the container by
calling `$container->get(className,...arguments)`. The arguments are optional and do NOT have to match the arguments
called on the object's constructor.

Items can also be retrieved by calling `di(className,...arguments)` instead of first retrieving the container.

Calling `add` on the container with matching arguments that already exists will throw a
`Feast\ServiceContainer\ContainerException`. If you wish to replace an item in the container, instead use `replace`

## Dependency Injection

FEAST uses the Service Container to automatically inject certain classes into the following places.

1. Plugin Constructors.
2. Controller Constructors.
3. Plugin Pre/Post Dispatch methods.
4. Controller Actions.

Note that the container does not intercept manual calls to those functions.

In addition to items in the container, Mappers may be injected as long as they extend from the `\Feast\BaseMapper`
class, and Models may be injected by having the primary key appear in the URL in the same name as the matching parameter
as long as they extend the `\Feast\BaseModel` class.

Note that the call to `findByPrimaryKey` will pass validate => true for models on dependency injection. You can write
your own custom rules for the validation to ensure that users do not just change urls to change what they are fetching.