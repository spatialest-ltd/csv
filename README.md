Spatialest CSV
==============

CSV parsing at its best, made by Spatialest.

## Installation

```bash
composer require spatialest/csv
```

## What does it do?

This library implements a CSV parser that complies with RFC 4180. The parser
is directly ported from Golang's Standard Library `csv.Reader` struct.

It can read csv from any data source that implements the 
`Spatialest\Csv\Io\Reader` interface.

## Features

- Removes UTF Byte Order Marks
- Normalizes lines by removing carriage returns
- Handles new lines in quoted fields correctly (`fgetcsv` does not)
- Provides errors with line, record and column numbers
- Provides distinction between parsing errors and column mismatch errors
- Provides useful iteration primitives

## Basic Usage

You can just create a `Spatialest\Csv\RFC4180\Reader` from a file 
and then iterate over all the records.

```php
use Spatialest\Csv\Io\ResourceReader;
use Spatialest\Csv\RFC4180\HeaderIterator;
use Spatialest\Csv\RFC4180\Reader;
use Spatialest\Csv\RFC4180\RecordIterator;

$reader = Reader::fromReader(ResourceReader::fromUrl('https://data.wprdc.org/datastore/dump/5bbe6c55-bce6-4edb-9d04-68edeb6bf7b1'));

$records = new RecordIterator($reader);
$hashes = new HeaderIterator($records);

// This will emit the csv records as a line delimited json in the output
foreach ($hashes as $record) {
    fwrite(STDOUT, json_encode($record, JSON_THROW_ON_ERROR) .PHP_EOL);
}

if ($records->hasErrors()) {
    echo 'DANGER: There are '.count($records->getErrors()).' errors.';
}
```