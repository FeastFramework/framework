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
2. `implode` - This method will return a string calling PHP's native implode on the underlying array with the specified
   separator. If the underlying array is an object, pass in the key you wish to implode on as the second parameter. Note
   that implode does not operate on array collections,
3. `sort` - This method sorts the collection based on a `\Feast\Enum\CollectionSort` sort flag. It returns an array and
   takes the following parameters.
    1. `sortType` - a `CollectionSort` enum value (int backed, use the constant to ensure forwards compatibility with
       the next version of FEAST).
    2. `modifyOriginal` - a boolean flag for whether to modify the stored collection values or only return the new
       sorted array.
    3. `sortOptions` - a bitwise int of `SORT_` flags
4. `objectSort` - This method allows sorting a collection of a named class by one or more keys. It returns an array and
   takes the following parameters.
    1. `key` - a string or array of strings to sort on. If it is an array, it is sorted on them in order.
    2. `sortType` - a `CollectionSort` enum value (int backed, use the constant to ensure forwards compatibility with
       the next version of FEAST).
    3. `modifyOriginal` - a boolean flag for whether to modify the stored collection values or only return the new
       sorted array.
5. `shuffle` - This method shuffles the values of the correction. It takes a boolean flag for whether to modify the
   original as its only parameter and returns an array.
6. `toArray` - This method gets the underlying array from the collection.
7. `isEmpty` - Returns whether the collection is empty.
8. `clear` - Empties the collection
9. `contains` - This method takes a value to check against, and a boolean flag for using strict comparison. Returns true
   if the value is found.
10. `containsAll` - This method takes an array of values to check against, and a boolean flag for using strict
    comparison. Returns true if all the values are found.
11. `indexOf` - This method takes a value to check against, and a boolean flag for using strict comparison. Returns the
    first occurrence if found.
12. `lastIndexOf` - This method takes a value to check against, and a boolean flag for using strict comparison. Returns
    the last occurrence if found.
13. `size` - Returns the size of the collection (count of the underlying array).
14. `remove` - This method takes a value to check against, and a boolean flag for using strict comparison. Removes all
    matching items.
15. `removeAll` - This method takes an array of values to check against, and a boolean flag for using strict comparison.
    Removes all matching items.
16. `pop` - This method pops the last element off the underlying collection and returns it.
17. `shift` - This method shifts the first element off the underlying collection and returns it.
18. `first` - This method returns the first item from the collection.
19. `getType` - This method returns what the valid value type is for the collection. Can be any scalar type, array,
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