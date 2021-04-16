[Back to Index](index.md)

# Working with Dates

FEAST contains a powerful Date class `\Feast\Date` that is designed to make working with Dates and Times simple. While
it overlaps some behavior of the built-in `DateTime` class, it does not extend it.

[Creating Date objects](#creating-date-objects)

[Setting Properties and Manipulating Dates](#setting-properties-and-manipulating-dates)

[Getting Textual Representation](#getting-textual-representation)

[Getting Other Information](#getting-other-information)

[Getting a DateTime object](#getting-a-datetime-object)

[Comparing Dates](#comparing-dates)
## Creating Date objects

There are multiple methods for creating a `\Feast\Date` object.

### Creating from Unix Timestamp

`createFromTimestamp` takes two parameters - the Unix Timestamp (or number of seconds since Midnight on Jan 1 1970) and
an optional timezone string. If no timezone string is passed in, it will default to the current server timezone.

### Create from the current time

`createFromNow` creates a `\Feast\Date` object from the current unix timestamp. It can optionally take a string
representing the timezone. If no timezone string is passed in, it will default to the current server timezone.

### Create from String

`createFromString` will create a `\Feast\Date` object using the strtotime call. It takes in a string representing the
date to create, and an optional timezone parameter.

### Create from a pre-determined format string

`createFromFormat` will create a `\Feast\Date` object by first creating an intermediary `DateTime` object. The method
takes a format string, a string representing the date, and an optional timezone representation.

### Create via time parts

If you wish to create a `\Feast\Date` object from a set of date parts, you can instead call `new \Feast\Date()`
The constructor has the following parameters (and default values, if noted)

1. `Year`
2. `Month`
3. `Day`
4. `Hour` - Defaults to 0 (or midnight)
5. `Minute` - Defaults to 0
6. `Second` - Defaults to 0
7. `Timezone` - Defaults to null, thus defaulting to the server timezone.

## Setting Properties and Manipulating Dates

After instantiation, you can set various properties. See the method list below

1. `setTimezone` - Takes a string representing the timezone.
2. `setYear`
3. `setMonth`
4. `setDay`
5. `setHour`
6. `setMinute`
7. `setSecond`

In addition, you can modify the current `\Feast\Date` object by calling `modify` with a string representing the change
to make. For example `5 days` will increase the current object by 5 days. `-4 hours` will decrease it by 4 hours. Under
the hood, this uses the PHP built-in strtotime function and all the limitations thereof.

## Getting Textual Representation

The `\Feast\Date` class contains many methods for retrieving information about the Date, including some shorthands.

1. `getTimestamp` - Returns the Unix Timestamp of the object as an int.
2. `getYear` - Returns the 4-digit year as a string.
3. `getMonth` - Returns the 2-digit month as a string.
4. `getDay` - Returns the 2-digit day as a string.
5. `getHour` - Returns the hour in 24-hour format as a string. Eg: 6pm is 18.
6. `getMinute` - Returns the minute as a string.
7. `getSecond` - Returns the seconds as a string.
8. `getDayOfWeek` - Returns the day of the week in numeric format as a string. 0 is Sunday, 6 is Saturday.
9. `getDayOfYear` - Returns the day of the year (0-365 inclusive) as a string. Jan 1st is 0, Dec 31 on a non leap year
   is 364.
10. `getFormattedDate` - Returns the date in the chosen format. See the
    PHP [manual](https://www.php.net/manual/en/datetime.format.php) for formats. Some class constants exist on
    the `\Feast\Date` class to make working with format strings easier.
11. `__toString` - Returns the date in `Y-m-D H:i:s` format. Casting the object to string will call this method.

## Getting Other Information

The `\Feast\Date` class contains methods for checking if it is Daylight savings time or a leap year.

1. `isDaylightSavingsTime`
2. `isLeapYear`

## Getting a DateTime object

If you wish to operate on PHP's built-in `DateTime` object, the method `getAsDateTime` will return an instance of
the `DateTime` class.

## Comparing Dates

The `\Feast\Date` class contains 4 methods for comparing with other `\Feast\Date` objects. These methods all take
another `\Feast\Date` as their only argument.

1. `greaterThan`
2. `greaterThanEqual`
3. `lessThan`
4. `lessThanEqual`