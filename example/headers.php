<?php

declare(strict_types=1);

/**
 * @project Spatialest CSV
 * @link https://github.com/spatialest-ltd/csv
 * @package spatialest/csv
 * @author Matias Navarro-Carter matias.navarro@spatialest.com
 * @license MIT
 * @copyright Spatialest Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Spatialest\Csv\Io\ResourceReader;
use Spatialest\Csv\RFC4180\HeaderIterator;
use Spatialest\Csv\RFC4180\Reader;
use Spatialest\Csv\RFC4180\RecordIterator;

require_once __DIR__.'/../vendor/autoload.php';

$reader = Reader::fromReader(ResourceReader::fromUrl('https://data.wprdc.org/datastore/dump/5bbe6c55-bce6-4edb-9d04-68edeb6bf7b1'));

$records = new RecordIterator($reader);
$hashes = new HeaderIterator($records);

foreach ($hashes as $record) {
    fwrite(STDOUT, json_encode($record, JSON_THROW_ON_ERROR) .PHP_EOL);
}

if ($records->hasErrors()) {
    echo 'DANGER: There are '.count($records->getErrors()).' errors.';
}