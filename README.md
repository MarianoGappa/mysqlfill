# mysqlfill
Handy CLI tool to quickly fill up a MySQL table for test purposes.

## Status
Highly experimental for now. I will update when ready for general usage. I take contributions :)

## Usage
```
./mysqlfill %database_name% %table_name%
```
If you are gonna be working on the same database most of the time, then consider adding the `database_name` configuration on `mysqlfill.conf` with the database name. You can still override it on the command line, but then you can just go:
```
./mysqlfill %table_name%
```
You can use the typical mysql connection parameters:
```
./mysqlfill -h localhost -u root -p 1234 %database_name% %table_name%
```
(note, however, that you can't omit spaces between modifier and parameter e.g. -uroot)

## Test
```
cd test
phpunit MySqlFillTest.php
phpunit ConfigTest.php
```

## Dependencies (for testing)

PHPUnit - https://phpunit.de/

## Default configuration
(even if you delete the config file)

```
[
    // Where is the main configuration file?
    "config_path" => "mysqlfill.conf",

    // (i.e. mysql -h ????)
    "hostname" => "localhost",

    // (i.e. mysql -u ????)
    "username" => "root",

    // (i.e. mysql -p ???? )
    "password" => "",

    // How many rows should mysqlfill create by default?
    "rows_to_fill" => 5,

    // if the table to fill is not empty, what should mysqlfill do? ["abort", "truncate", "append"]
    "on_table_not_empty" => "abort",

    // On string-based column types, should I try non-latin characters?
    "utf8" => false,

    // Should mysqlfill try to guess what the column contains and put data it thinks it'd fit on it?
    "predictive" => true
]
```
