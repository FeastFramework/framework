[FEAST Framework Documentation](index.md)

This documentation is for the `feast/json` project found
at [https://github.com/FeastFramework/json](https://github.com/FeastFramework/json). If you are looking for the version
built into FEAST, click [here](json.md).

# Working with JSON and Objects

PHP includes a simple, built-in JSON serializer with json_encode and json_decode. However, these functions create either
a stdClass or array, and have no flexibility beyond that. Sometimes, you just want a little more power and flexibility.
For those cases, the authors of FEAST created a dynamic JSON marshaller.

## The Components

### The marshaller

`\Feast\Json\Json` is a class containing two static methods; one for marshalling and one for unmarshalling. The marshal
method takes two parameters.

1. `object` - the object to be marshalled into a json string.
2. `propertyTypesFlag` - int or null.
   See https://www.php.net/manual/en/class.reflectionproperty.php#reflectionproperty.constants.modifiers for values.

The unmarshal method takes three parameters.

1. `data` - a json string
2. `objectOrClass` - either a class name or a pre-instantiated object.
3. `skipConstructor` - skip constructor call and instantiate object through reflection. This will also apply to any
   nested objects.

### The Attribute

`\Feast\Json\Attributes\JsonItem` is a PHP8 attribute that is used to decorate properties in your class to specify
transformations on JSON data. It has four optional properties.

1. `name` - specifies an alternate name to be used when serializing to JSON as well as the name of the key for this
   property when reading from the JSON string. If not supplied, the class property name will be used as the name.
2. `arrayOrCollectionType` - used as a decorator on arrays to specify what the type contained inside an array is. This
   can be used to mark a property as being made up of an array of another type.
3. `dateFormat` - Specifies the format to serialize into for objects of PHP's built in `DateTime` class. Defaults to
   ISO-8601.
4. `included` - Defaults to true. If set to false, Json strings created with the `Json::marshal` function will not
   include the property.
5. `omitEmpty` - Defaults to false. If set to true, Json strings created with the `Json::marshal` function will not
   include the property if the value is null or empty string (`''`).

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
    public DateTime $timestamp;
    #[JsonItem(included: false)]
    public string $notIncluded;
    #[JsonItem(omitEmpty: true)]
    public string $emptyNotIncluded = '';
```

This class has six properties. The first, `$firstName` is a string, and is pulled from the `first_name` key. The second
property is `$lastName` and behaves the same as `$firstName`. The third property is an array. This array contains other
items of the same class. These items will marshal or unmarshal through all layers. The fourth property is an instance
of `DateTime`. The fifth property, `$notIncluded` is a string that is pulled from `notIncluded` in the JSON, but will
NOT be marshalled back into JSON. The sixth property, `$emptyNotIncluded` will not be marshalled back into JSON if no
value was set.

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
  "timestamp": "20210405",
  "notIncluded": "Feast",
  "emptyNotIncluded": "test"
}
```

Assuming the json string was assigned to `$string`, unmarshalling would be performed on the string as follows.

```php
Json::unmarshal($string,TestJsonItem::class);
```

In addition, calling either of the following would return the JSON string again (in minified format)  with `notIncluded`
not contained in the string, but `emptyNotIncluded` will be included since it has a non-empty string value.

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
$object->timestamp = DateTime::createFromFormat('Ymd','20210405');
$object->notIncluded = 'Feast';
$object->emptyNotIncluded = 'test';

$secondaryObject = new TestJsonItem();
$secondaryObject->firstName = 'Jeremy';
$secondaryObject->lastName = 'Presutti';

$object->items = [$secondaryObject];
```

Note that if the above object is marshalled it will NOT contain the `notIncluded` property.

[Back to Top](#working-with-json-and-objects)
