[Back to Index](index.md)

# Working with JSON and Objects

PHP includes a simple, built-in JSON serializer with json_encode and json_decode. However, these functions create either
a stdClass or array, and have no flexibility beyond that. Sometimes, you just want a little more power and flexibility.
For those cases, FEAST includes a dynamic JSON marshaller.

## The Components

### The marshaller

`\Feast\Json` is a class containing two static methods; one for marshalling and one for unmarshalling. The marshal
method takes an object. The unmarshal method takes two parameters.

1. `data` - a json string
2. `objectOrClass` - either a class name or a pre-instantiated object.

### Limitations

Any object being serialized to must either allow an empty constructor call, or must be passed in to the unmarshal call
as an object. This includes any nested objects.

### The Attribute

`\Feast\Attributes\JsonItem` is a PHP8 attribute that is used to decorate properties in your class to specify
transformations on JSON data. It has three optional properties.

1. `name` - specifies an alternate name to be used when serializing to JSON as well as the name of the key for this
   property when reading from the JSON string. If not supplied, the class property name will be used as the name.
2. `arrayOrCollectionType` - used as a decorator on arrays or `\Feast\Collection\Collection` and its descendents to
   specify what the type contained inside a collection is. This can be used to mark a property as being a collection of
   another type.
3. `dateFormat` - Specifies the format to serialize into for objects of the `\Feast\Date` class. Defaults to ISO 8601.

[Back to Top](#working-with-json-and-objects)

## Tying it together

Below is a sample class that can be used with the JSON marshaller.

```php
class TestJsonItem
{
    #[JsonItem(name: 'first_name')]
    public string $firstName;
    #[JsonItem(name: 'last_name')]
    public string $lastName;
    #[JsonItem(arrayOrCollectionType: TestJsonItem::class)]
    public array $items;
    #[JsonItem(dateFormat: 'Ymd')]
    public Date $timestamp;
```

This class has four properties. The first, `$firstName` is a string, and is pulled from the `first_name` key. The second
property is `$lastName` and behaves the same as `$firstName`. The third property is an array. This array contains other
items of the same class. These items will marshal or unmarshal through all layers. The fourth property is an instance
of `\Feast\Date`

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
  ],
  "timestamp": "20210405"
}
```

Assuming the json string was assigned to `$string`, unmarshalling would be performed on the string as follows.

```php
Json::unmarshal($string,TestJsonItem::class);
```

In addition, calling either of the following would return the JSON string again (in minified format).

```php
$object = Json::unmarshal($string,TestJsonItem::class);
Json::marshal($object);
```

```php
$object = Json::unmarshal($string,null, new TestJsonItem());
Json::marshal($object);
```

This string will unmarshal into a class as if the below code had been called manually.

```php
$object = new TestJsonItem();
$object->firstName = 'FEAST';
$object->lastName = 'Framework';
$object->timestamp = Date::createFromString('20210405');

$secondaryObject = new TestJsonItem();
$secondaryObject->firstName = 'Jeremy';
$secondaryObject->lastName = 'Presutti';

$object->items = [$secondaryObject];
```

[Back to Top](#working-with-json-and-objects)
