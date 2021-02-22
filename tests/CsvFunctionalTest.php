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

namespace Spatialest\Csv;

use PHPUnit\Framework\TestCase;
use Spatialest\Csv\RFC4180\ParseError;
use Spatialest\Csv\RFC4180\Reader;

/**
 * Class CsvFunctionalTest.
 */
class CsvFunctionalTest extends TestCase
{
    public function testItParsesBomFile(): void
    {
        $reader = Reader::fromFile(__DIR__.'/bom.csv');
        $reader->comma = ';';
        self::assertSame(['sku', 'name'], $reader->readRecord());
    }

    public function testItParsesBrokenOne(): void
    {
        $reader = Reader::fromFile(__DIR__.'/broken1.csv');
        $iterator = $reader->getIterator();
        foreach ($iterator as $record) {
            // Empty on purpose.
        }
        self::assertCount(9, $iterator->getErrors());
    }

    public function testItParsesPipeCarrot(): void
    {
        $reader = Reader::fromFile(__DIR__.'/pipe-carrot.csv');
        $reader->quote = '^';
        $reader->comma = '|';
        $iterator = $reader->getIterator();
        $i = 0;
        foreach ($iterator as $record) {

        }
        self::assertCount(0, $iterator->getErrors());
    }

    public function testItParsesPipeQuote(): void
    {
        $reader = Reader::fromFile(__DIR__.'/pipe-quote.csv');
        $reader->comma = '|';
        $iterator = $reader->getIterator();
        $this->expectException(ParseError::class);
        foreach ($iterator as $record) {
            // Empty on purpose.
        }
    }
}
