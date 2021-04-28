[Back to Index](index.md)

# Working with CSV files

## Reading CSVs

FEAST has a built-in CSV reader class class (`\Feast\Csv\CsvReader`). This class allows working with CSV data as named
Key arrays instead of numeric keys, reducing errors.

The CSV Reader class constructor takes the same parameters as `fopen`. You can find more
info [here](https://www.php.net/manual/en/function.fopen.php).

The CSV Reader has several useful methods for working with data.

1. `setHeaderRow` - This method takes a row number (starting at 0) and an optional additional headers parameter. The
   additional headers parameter is useful if the CSV has a header row and then some additional data that is not actually
   part of the dataset.
2. `getHeader` - This method returns the header row info in numeric keyed array format.
3. `setCsvOptions` - This method takes a separator, enclosure, and escape character. All parameters are defaulted, so
   named parameters can be used.
4. `getFileHandler` - This method retrieves the underlying `SplFileObject`.
5. `getIterator` - This method yields the next row and can be used in a foreach loop to save memory.
6. `getAll` - This method returns all the rows from the CSV. This method will use large amounts of memory on large
   files.
7. `getNextLine` - This method returns the next row from the CSV.
8. `rewind` - This method rewinds the CSV to the beginning of the CSV (or after all headers, if headers are set).

## Writing to CSVs

FEAST contains a CSV writer class (`\Feast\Csv\CsvWriter`) that operates similarly to the reader class.

The CSV Writer class constructor takes the same parameters as `fopen`. You can find more
info [here](https://www.php.net/manual/en/function.fopen.php).

The CSV Reader has several useful methods for working with data.

1. `setHeaderRow` - This method takes a row number (starting at 0) and an optional additional headers parameter. The
   additional headers parameter is useful if the CSV has a header row and then some additional data that is not actually
   part of the dataset.
2. `setHeader` - This method takes an array of column headers to be used.
3. `setCsvOptions` - This method takes a separator, enclosure, and escape character. All parameters are defaulted, so
   named parameters can be used.
4. `getFileHandler` - This method retrieves the underlying `SplFileObject`.
5. `writeHeader` - This method writes the header to the file. If not called before `writeLine`, it will be called
   automatically.
6. `writeLine` - This method writes a line to the CSV. Named keys are matched up to the header. If header is not set,
   then the array is written as is.