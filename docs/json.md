[Back to Index](index.md)

# Working with JSON and Objects

PHP includes a simple, built-in JSON serializer with json_encode and json_decode. However, these functions create either
a stdClass or array, and have no flexibility beyond that. Sometimes, you just want a little more power and flexibility.
For those cases, FEAST includes a dynamic JSON marshaller.

## The Components

### The marshaller

`\Feast\Json` is a class containing two static methods; one for marshalling and one for unmarshalling. The marshal
method takes an object that implements the `\Feast\Interfaces\JsonSerializableInterface`.

### The interface

`\Feast\Interfaces\JsonSerializableInterface` is an interface that contains no method signatures, except the
constructor. Classes implementing the interface must have a constructor with no required parameters. Optional parameters
with defaults will function correctly, however.

### The Attribute

`\Feast\Attributes\JsonItem` is a PHP8 attribute that is used to decorate properties in your class to specify
transformations on JSON data. It has two properties. The first, `name` specifies an alternate name to be used when
serializing to JSON as well as the name of the key for this property when reading from the JSON string. The
second, `arrayOrCollectionType` is used as a decorator on arrays or `\Feast\Collection\Collection` and its descendents
to specify what the type contained inside a collection are. This can be used to mark a property as being a collection of
another type (also implementing `JsonSerializableInterface`).

[Back to Top](#working-with-json-and-objects)

## Tying it together

Below is a sample class that can be used with the JSON marshaller.

```php
class TestJsonItem implements JsonSerializableInterface
{
    #[JsonItem(name: 'first_name')]
    public string $firstName;
    #[JsonItem(name: 'last_name')]
    public string $lastName;
    #[JsonItem(arrayOrCollectionType: TestJsonItem::class)]
    public array $items;
```

This class has three properties. The first, `$firstName` is a string, and is pulled from the `first_name` key. The
second property is `$lastName` and behaves the same as `$firstName`. The third property is an array. This array contains
other items of the same class. These items will marshal or unmarshal through all layers.

Sample string below:

```json
{
  "first_name": "FEAST",
  "last_name": "Framework",
  "items": [
    {
      "first_name": "Jeremy",
      "last_name": "Presutti"
    }
  ]
}
```

Assuming the json string was assigned to `$string`, unmarshalling would be performed on the string as follows.

```php
Json::unmarshal($string,TestJsonItem::class);
```

In addition, calling the following would return the JSON string again (in minified format).

```php
$object = Json::unmarshal($string,TestJsonItem::class);
Json::marshal($object);
```

This string will unmarshal into a class as if the below code had been called manually.

```php
$object = new TestJsonItem();
$object->firstName = 'FEAST';
$object->lastName = 'Framework';

$secondaryObject = new TestJsonItem();
$secondaryObject->firstName = 'Jeremy';
$secondaryObject->lastName = 'Presutti';

$object->items = [$secondaryObject];
```

[Back to Top](#working-with-json-and-objects)
