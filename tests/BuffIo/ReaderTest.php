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

namespace Spatialest\Csv\BuffIo;

use Castor\Io\TestReader;
use PHPUnit\Framework\TestCase;

/**
 * Class ReaderTest.
 */
class ReaderTest extends TestCase
{
    public function testReadStringWithNormalBytes(): void
    {
        $reader = TestReader::fromString("This is line one\nThis is line two\nThis is line three\nThis is line four");

        $reader = new Reader($reader);
        self::assertSame("This is line one\n", $reader->readString("\n"));
        self::assertSame("This is line two\n", $reader->readString("\n"));
        self::assertSame("This is line three\n", $reader->readString("\n"));
        self::assertSame('This is line four', $reader->readString("\n"));
        self::assertNull($reader->readString("\n"));
    }

    public function testReadLinesWithConsecutiveDelimiters(): void
    {
        $reader = TestReader::fromString("This is line one\n\nThis is line three\nThis is line four");
        $reader = new Reader($reader);
        self::assertSame("This is line one\n", $reader->readString("\n"));
        self::assertSame("\n", $reader->readString("\n"));
        self::assertSame("This is line three\n", $reader->readString("\n"));
        self::assertSame('This is line four', $reader->readString("\n"));
        self::assertNull($reader->readString("\n"));
    }

    public function testReadLinesWithConsecutiveDelimitersAtTheEnd(): void
    {
        $reader = TestReader::fromString("This is line one\nThis is line two\nThis is line three\nThis is line four\n\n");
        $reader = new Reader($reader);

        self::assertSame("This is line one\n", $reader->readString("\n"));
        self::assertSame("This is line two\n", $reader->readString("\n"));
        self::assertSame("This is line three\n", $reader->readString("\n"));
        self::assertSame("This is line four\n", $reader->readString("\n"));
        self::assertSame("\n", $reader->readString("\n"));
        self::assertNull($reader->readString("\n"));
    }
}
