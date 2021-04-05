[Back to Index](index.md)

# Forms
FEAST has classes to make working with html forms simpler and more convenient. While the usage of these classes is optional,
it allows you to validate user input dynamically instead of manually checking the values.

## The Form class
To create a new form, simply create a new class extending `\Feast\Form\Form`. In your constructor, call the parent constructor
and pass in the form name, the url for the action (or null to default to same url as the current request), the method, and any html attributes
as an array in key => value format.

The form name will become the ID of the form.

Example:
```php
class register extends \Feast\Form\Form
{
    public function __construct()
    {
        parent::__construct('register','/user/register','GET',['id' => 'registerForm']);
    }
}
```

### Field/Value methods on Form class
1. `addField` - This method takes a [\Feast\Form\Field](#the-field-class) as its only argument. The field is added to the list of
fields on the form.
2. `setValue` - This method takes a field name, and a value to set. In addition, an optional false 
   to not overwrite existing values. This is useful on checkboxes or multi-selects.
3. `setAllValues` - This method takes an array of key => values to set on a form. If the value is passed in as an array, 
   it will not overwrite pre-existing values. The most common usage of this method would be `setAllValues($_POST)` or `setAllValues($_GET)`.
4. `setAllFiles` - This method will set all files from `$_FILES` onto the form's `files` property.
5. `getFile` - This method takes a key matching to the field name and returns either an array (see [POST method upload](https://www.php.net/manual/en/features.file-upload.post-method.php) in the PHP manual for the properties)
   or `null` if the file is not found.
6. `filter` - This method takes an array of `field name` => `values` and runs the [Filter](#filtering-form-input) rules
   on each, and sets the value on the Field property of the Form.
7. `setAction` - This method takes a string parameter and overwrites the default action for the form.
8. `getErrors` - This method will get all errors from the validation (see next section).
### Validation
FEAST contains a rich, extendable validation engine for the Forms engine. It includes many validation classes by default, and you may write your own.
See [Validating Forms](#validating-forms) below for more details on using or creating validators.

The form class contains two methods for validation. They behave identically with one distinct difference. The first method, 
`isValid` will check both validation rules and any required fields. The second method, `isValidPartial` will only validate
based on the validation rules.

### Displaying Forms
The form class contains the following methods for building form HTML
1. `openForm` - This method will assemble the `<form>` tag with all the attributes you pass in.
2. `closeForm` - This method will close the `<form>` tag.
3. `displayField` - This method takes the field name, a boolean flag for showing the label, and (for radio and checkboxes) 
   the string value of the item to show.

[Back to Top](#forms)
## The Field class
FEAST contains multiple Field classes that extend from `Feast\Form\Field`
1. `\Feast\Form\Field\Checkbox`
2. `\Feast\Form\Field\Radio`
3. `\Feast\Form\Field\Select`
4. `\Feast\Form\Field\Text`
5. `\Feast\Form\Field\TextArea`

Each of these except Text have the same constructor signature of `name`, `formName`, and `id`. The latter two parameters are optional.
Text also takes a `type` parameter to allow for more dynamic field types such as password.

### Form Field Methods
1. `setId` - This method allows overriding the default or chosen field id.
2. `setFormNameAndGenerateId` - This method allows generating a dynamic id for the field from the passed in form name.
3. `clearSelected` - This method will unset any selected values on the field.
4. `addClass` - This method will add the specified class to the field.
5. `removeClass` - This method will remove the specified class from the field.
6. `setClass` - This method will set the exact class string to be used for the field.
7. `setDefault` - This method allows choosing a default value for a Text or Textarea field.
8. `setLabel` - This method takes a `\Feast\Form\Label` as a parameter and sets the label for the field.
9. `setPlaceholder` - This method allows setting the placeholder for the field.
10. `setRequired` - This method marks the field as required (or not required, if `false` is passed as a parameter)
11. `setStyle` - This method adds a style attribute to the field.
12. `addMeta` - This method takes a key and value as parameters. These values will be output as `key="value"` on the field
    output string.
13. `setValue` - This method sets the chosen value for the field. It takes an optional parameter to not overwrite the already
    selected values on Checkboxes and Select fields.
14. `addFilter` - This method takes a [Filter](#filtering-form-input) class string as the argument. Any values on this field
    will be processed by the filter when `filter` is called on the form.
15. `addValidator` - This method takes a [Validator](#validating-forms) class string as an argument. The validator will be
    processed when `isValid` or `isValidPartial` is called on the containing form.
16. `toString` - This method is called by `displayField` on the form to output the HTML for the field.
17. `addValue` - This method takes a string name and a `Value` or `SelectValue` argument to be added to the field. This method 
    will throw an exception except on radio, checkboxes, and select fields.

[Back to Top](#forms)
## Filtering Form input
FEAST contains many default implementations of the `\Feast\Form\Filter\Filter` interface. These can be found in `Feast\Form\Filter` folder.
Each of these filters contains a `filter` method that takes the current value of the form, and processes it to return the new value. 
The `filter` method on the Form will call each of these automatically.

[Back to Top](#forms)
## Validating Forms
FEAST contains many default implementations of the `\Feast\Form\Validator\Validator` interface. These can be found in `Feast\Form\Validator` folder.
Each of these validators contain a `validate` method that will verify the form rule and return either true if the field is valid,
or will add to the errors array and return false if the field is not valid.

[Back to Top](#forms)
