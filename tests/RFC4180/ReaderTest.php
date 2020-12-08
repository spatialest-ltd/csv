<?php

namespace Spatialest\Csv\RFC4180;

use PHPUnit\Framework\TestCase;
use Spatialest\Csv\Io\Reader as IoReader;

/**
 * Class ReaderTest
 * @package Spatialest\Csv\RFC4180
 */
class ReaderTest extends TestCase
{
    public function testItReadsRecordUnquoted(): void
    {
        $readerMock = $this->createMock(IoReader::class);
        $readerMock->expects(self::once())
            ->method('read')
            ->willReturn("xxx,y y,zzz\n");
        $reader = Reader::fromReader($readerMock);

        $record = $reader->readRecord();

        self::assertSame(['xxx', 'y y', 'zzz'], $record);
    }

    public function testItReadsWithoutFinalNewLine(): void
    {
        $readerMock = $this->createMock(IoReader::class);
        $readerMock->expects(self::exactly(2))
            ->method('read')
            ->willReturnOnConsecutiveCalls("xxx,y y,zzz", null);
        $reader = Reader::fromReader($readerMock);

        $record = $reader->readRecord();

        self::assertSame(['xxx', 'y y', 'zzz'], $record);
    }

    public function testItThrowsBareQuoteError(): void
    {
        $readerMock = $this->createMock(IoReader::class);
        $readerMock->expects(self::once())
            ->method('read')
            ->willReturn('xxx,y"y,zzz'."\n");
        $reader = Reader::fromReader($readerMock);

        $this->expectException(ParseError::class);
        $reader->readRecord();
    }

    public function testItParsesRecordQuoted(): void
    {
        $readerMock = $this->createMock(IoReader::class);
        $readerMock->expects(self::once())
            ->method('read')
            ->willReturn('"xxx","y y","zzz"'."\r\n");
        $reader = Reader::fromReader($readerMock);

        $record = $reader->readRecord();

        self::assertSame(['xxx', 'y y','zzz'], $record);
    }

    public function testItDetectsWrongNumberOfFields(): void
    {
        $readerMock = $this->createMock(IoReader::class);
        $readerMock->expects(self::exactly(3))
            ->method('read')
            ->willReturnOnConsecutiveCalls(
                '"xxx","yyy","zzz"'."\r\n",
                '"xxx","yyy","zzz"'."\r\n",
                '"xxx","zzz"'."\r\n",
            );
        $reader = Reader::fromReader($readerMock);
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
        $this->expectException(ParseError::class);
        $reader->readRecord();
    }

    public function testItCanContinueFromAWrongFieldsException(): void
    {
        $readerMock = $this->createMock(IoReader::class);
        $readerMock->expects(self::exactly(4))
            ->method('read')
            ->willReturnOnConsecutiveCalls(
                '"xxx","yyy","zzz"'."\r\n",
                '"xxx","yyy","zzz"'."\r\n",
                '"xxx","zzz"'."\r\n",
                '"xxx","yyy","zzz"'."\r\n",
            );
        $reader = Reader::fromReader($readerMock);
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
        try {
            $reader->readRecord();
        } catch (ParseError $error) {

        }
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
    }

    public function testItSkipsEmptyLine(): void
    {
        $readerMock = $this->createMock(IoReader::class);
        $readerMock->expects(self::exactly(4))
            ->method('read')
            ->willReturnOnConsecutiveCalls(
                '"xxx","yyy","zzz"'."\r\n",
                '"xxx","yyy","zzz"'."\r\n",
                ''."\r\n",
                '"xxx","yyy","zzz"'."\r\n",
            );
        $reader = Reader::fromReader($readerMock);
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
    }

    public function testItParsesNewLineInsideQuotedField(): void
    {
        $readerMock = $this->createMock(IoReader::class);
        $readerMock->expects(self::exactly(3))
            ->method('read')
            ->willReturnOnConsecutiveCalls(
                '"xxx","yyy","zzz"'."\r\n",
                '"xxx","y'."\n".'yy","zzz"'."\r\n",
                '"xxx","yyy","zzz"'."\r\n",
            );
        $reader = Reader::fromReader($readerMock);
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
        self::assertSame(['xxx', "y\nyy", 'zzz'], $reader->readRecord());
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
    }

    public function testItParsesEscapedQuotesCorrectly(): void
    {
        $readerMock = $this->createMock(IoReader::class);
        $readerMock->expects(self::once())
            ->method('read')
            ->willReturn('"xxx","y""yy","zzz"'."\r\n");
        $reader = Reader::fromReader($readerMock);
        self::assertSame(['xxx', 'y"yy', 'zzz'], $reader->readRecord());
    }

    public function testItFailsOnUnescapedQuote(): void
    {
        $readerMock = $this->createMock(IoReader::class);
        $readerMock->expects(self::once())
            ->method('read')
            ->willReturn('"xxx","y"yy","zzz"'."\r\n");
        $reader = Reader::fromReader($readerMock);
        $this->expectException(ParseError::class);
        $reader->readRecord();
    }

    public function testWhitespaceLineFails(): void
    {
        $readerMock = $this->createMock(IoReader::class);
        $readerMock->expects(self::exactly(2))
            ->method('read')
            ->willReturnOnConsecutiveCalls(
                '"xxx","yyy","zzz"'."\r\n",
                '     '."\r\n",
            );
        $reader = Reader::fromReader($readerMock);
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
        $this->expectException(ParseError::class);
        $reader->readRecord();
    }

    public function testItThrowsErrorOnInvalidCommaCharacter(): void
    {
        $readerStub = $this->createStub(IoReader::class);
        $reader = Reader::fromReader($readerStub);
        $reader->comma = "\n";
        $this->expectException(\InvalidArgumentException::class);
        $reader->readRecord();
    }
}
