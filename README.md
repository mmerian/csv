csv
===

[![Build Status](https://travis-ci.org/mmerian/csv.svg?branch=master)](https://travis-ci.org/mmerian/csv)

This library is designed to help you read and write csv files.

Usage
---

### Reading CSV files

Say you have a CSV file that looks like this :
```
first_name;last_name;gender;birthdate
John;Smith;M;1972-05-12
Susan;Chatman;F;1972-05-12
```

Instanciating the reader is really easy :

```php

use Csv\Reader;

$reader = new Reader($file, array(
    'hasHeader' => true,
    'inputEncoding' => 'ISO-8859-15',
    'outputEncoding' => 'UTF-8',
    'delimiter' => ';'
));

foreach ($reader as $line) {
    /*
     * $line is an array.
     * If the CSV file has an header,
     * the array keys are the header fields
     */
    echo $line['first_name'];
}

// Or

while ($line = $reader->fetch()) {
    // Process line
}
```

In this example, `$file` can be the path to an existing file, or a pointer to an already open file.
Available options are :

- `'hasHeader'` : A boolean determining if the first line of the CSV file is a head with fields names
- `'header'` : If the CSV file doesn't have a header, you can provide one here.
  If both `'header'` and `'hasHeader'` are provided, the `'header'` option takes precedence
- `'inputEncoding'` : The encoding of the CSV file. Defaults to UTF-8
- `'outputEncoding'` : The encoding of the data that will be returned when reading the file.
  If `'inputEncoding'` and `'outputEncoding'` are different, the reader automatically uses mbstring to convert
- `'delimiter'` : The CSV delimiter
- `'enclosure'` : The CSV enclosure
- `'ignoreEmptyLines'`: If set to true (the default), the reader will silently ignore empty lines. Otherwise, an exception will be raised if an empty line is encountered

### Automatic field processing
The reader is also able to apply formatting functions to your CSV fields.

```php

use Csv\Reader;

$reader = new Reader($file, array(
    'hasHeader' => true,
    'inputEncoding' => 'ISO-8859-15',
    'outputEncoding' => 'UTF-8',
    'delimiter' => ';'
));
$reader->registerFormatter('birthdate', function($date) {
    return preg_replace('/^([0-9]+)-([0-9]+)-([0-9]+)$/', '$3/$2/$1', $date);
});

foreach ($reader as $line) {
    echo $line['birthdate']; // will echo 12/05/1972 for the first line
}
```


