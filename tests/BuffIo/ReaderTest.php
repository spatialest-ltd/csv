<?php

namespace Spatialest\Csv\BuffIo;

use PHPUnit\Framework\TestCase;
use Spatialest\Csv\Io\ResourceReader;

/**
 * Class ReaderTest
 * @package Spatialest\Csv\BuffIo
 */
class ReaderTest extends TestCase
{
    public function testReadStringWithNormalBytes(): void
    {
        $readerMock = $this->createMock(\Spatialest\Csv\Io\Reader::class);

        $readerMock->expects(self::exactly(2))
            ->method('read')
            ->willReturnOnConsecutiveCalls(
                "This is line one\nThis is line two\nThis is line three\nThis is line four",
                null
            );

        $reader = new Reader($readerMock);
        self::assertSame("This is line one\n", $reader->readString("\n"));
        self::assertSame("This is line two\n", $reader->readString("\n"));
        self::assertSame("This is line three\n", $reader->readString("\n"));
        self::assertSame("This is line four", $reader->readString("\n"));
        self::assertNull($reader->readString("\n"));
    }

    public function testReadLinesWithConsecutiveDelimiters(): void
    {
        $readerMock = $this->createMock(\Spatialest\Csv\Io\Reader::class);

        $readerMock->expects(self::exactly(2))
            ->method('read')
            ->willReturnOnConsecutiveCalls(
                "This is line one\n\nThis is line three\nThis is line four",
                null
            );

        $reader = new Reader($readerMock);
        self::assertSame("This is line one\n", $reader->readString("\n"));
        self::assertSame("\n", $reader->readString("\n"));
        self::assertSame("This is line three\n", $reader->readString("\n"));
        self::assertSame("This is line four", $reader->readString("\n"));
        self::assertNull($reader->readString("\n"));
    }

    public function testReadLinesWithConsecutiveDelimitersAtTheEnd(): void
    {
        $readerMock = $this->createMock(\Spatialest\Csv\Io\Reader::class);

        $readerMock->expects(self::exactly(2))
            ->method('read')
            ->willReturnOnConsecutiveCalls(
                "This is line one\nThis is line two\nThis is line three\nThis is line four\n\n",
                null
            );

        $reader = new Reader($readerMock);
        self::assertSame("This is line one\n", $reader->readString("\n"));
        self::assertSame("This is line two\n", $reader->readString("\n"));
        self::assertSame("This is line three\n", $reader->readString("\n"));
        self::assertSame("This is line four\n", $reader->readString("\n"));
        self::assertSame("\n", $reader->readString("\n"));
        self::assertNull($reader->readString("\n"));
    }

    public function testReadLinesWithLowBytes(): void
    {
        $readerMock = $this->createMock(\Spatialest\Csv\Io\Reader::class);

        $readerMock->expects(self::exactly(5))
            ->method('read')
            ->willReturnOnConsecutiveCalls(
                "This is line ",
                "one\nThis is line ",
                "two\nThis is line ",
                "three\nThis is line four",
                null
            );

        $reader = new Reader($readerMock);
        self::assertSame("This is line one\n", $reader->readString("\n"));
        self::assertSame("This is line two\n", $reader->readString("\n"));
        self::assertSame("This is line three\n", $reader->readString("\n"));
        self::assertSame("This is line four", $reader->readString("\n"));
        self::assertNull($reader->readString("\n"));
    }
}
