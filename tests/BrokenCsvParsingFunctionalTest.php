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
use Spatialest\Csv\RFC4180\Reader;

/**
 * Class BrokenCsvParsingFunctionalTest.
 */
class BrokenCsvParsingFunctionalTest extends TestCase
{
    public function testItParsesBrokenOne(): void
    {
        $reader = Reader::fromFile(__DIR__.'/broken1.csv');
        $iterator = $reader->getIterator();
        foreach ($iterator as $record) {
            // Empty on purpose.
        }
        self::assertCount(9, $iterator->getErrors());
    }
}
