[Back to Index](index.md)

# Collections

FEAST contains two collection classes for simulating generics. Each of these classes manage an underlying array. While
they both share some methods, they each also have distinct methods.

[Collection Trait](#collection-trait)

[Collection List](#collection-list)

[Collection Set](#set)

## Collection Trait

The `\Feast\Traits\Collection` trait contains methods that are usable on both collection classes.

Methods:

1. `getValues` - This gets the underlying array values from the collection.
2. `sort` - This method sorts the collection based on a `\Feast\Enum\CollectionSort` sort flag. It returns an array and
   takes the following parameters.
    1. `sortType` - a `CollectionSort` enum value (int backed, use the constant to ensure forwards compatibility with
       the next version of FEAST).
    2. `modifyOriginal` - a boolean flag for whether to modify the stored collection values or only return the new
       sorted array.
    3. `sortOptions` - a bitwise int of `SORT_` flags
3. `objectSort` - This method allows sorting a collection of a named class by one or more keys. It returns an array and
   takes the following parameters.
    1. `key` - a string or array of strings to sort on. If it is an array, it is sorted on them in order.
    2. `sortType` - a `CollectionSort` enum value (int backed, use the constant to ensure forwards compatibility with
       the next version of FEAST).
    3. `modifyOriginal` - a boolean flag for whether to modify the stored collection values or only return the new
       sorted array.
4. `shuffle` - This method shuffles the values of the correction. It takes a boolean flag for whether to modify the
   original as its only parameter and returns an array.
5. `toArray` - This method gets the underlying array from the collection.
6. `isEmpty` - Returns whether the collection is empty.
7. `clear` - Empties the collection
8. `contains` - This method takes a value to check against, and a boolean flag for using strict comparison. Returns true
   if the value is found.
9. `containsAll` - This method takes an array of values to check against, and a boolean flag for using strict
   comparison. Returns true if all the values are found.
10. `indexOf` - This method takes a value to check against, and a boolean flag for using strict comparison. Returns the
    first occurrence if found.
11. `lastIndexOf` - This method takes a value to check against, and a boolean flag for using strict comparison. Returns
    the last occurrence if found.
12. `size` - Returns the size of the collection (count of the underlying array).
13. `remove` - This method takes a value to check against, and a boolean flag for using strict comparison. Removes all
    matching items.
14. `removeAll` - This method takes an array of values to check against, and a boolean flag for using strict comparison.
    Removes all matching items.
15. `pop` - This method pops the last element off the underlying collection and returns it.
16. `shift` - This method shifts the first element off the underlying collection and returns it.
17. `first` - This method returns the first item from the collection.
18. `getType` - This method returns what the valid value type is for the collection. Can be any scalar type, array,
    object, any class, or mixed.

In addition, all methods in the PHP built in ArrayAccess interface are available.

[Back to Top](#collections)

## Collection List

The `\Feast\Collection\CollectionList` class is used to manage a collection by key => value mapping. This collection
type allows duplicates.

The constructor takes the type, an array of values, and a boolean flag for if the values are pre-validated

### Methods on CollectionList

1. `add` - This method takes a key and value to add to the collection.
2. `addAll` - This method takes an array of key=>value pairs to add to the collection.
3. `removeByKey` - This method removes an item from the collection by key.
4. `get` - This method retrieves an item from the collection by key.

[Back to Top](#collections)

## Set

The `\Feast\Collection\Set` class is used to manage a collection of unique values. Duplicates are ignored.

The constructor takes the type, an array of values, a boolean flag for whether to perform a strict match when adding
items to the collection, and a boolean flag for if the values are pre-validated.

### Methods on Set

1. `add` - This method takes a value to add to the collection.
2. `addAll` - This method takes an array and adds all the values to the collection.
3. `merge` - This method takes another `Set` as the parameter and merges all values in.

### Mathematical functions on Set

Set contains mathematical functions that behave in 2 different ways depending on if the collection is a named class
type, or an int/float set. All math functions on a named class type take a key as the only parameter and searches the
collection objects for that key and performs the operation on the corresponding values.

1. `min` - This method returns the minimum value from the collection.
2. `max`
3. `average`
4. `sum`

[Back to Top](#collections)