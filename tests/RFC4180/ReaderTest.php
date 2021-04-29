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

namespace Spatialest\Csv\RFC4180;

use Castor\Io;
use Castor\Io\TestReader;
use PHPUnit\Framework\TestCase;

/**
 * Class ReaderTest.
 */
class ReaderTest extends TestCase
{
    public function testItReadsRecordUnquoted(): void
    {
        $reader = TestReader::fromString("xxx,yyy,zzz\n");
        $reader = Reader::fromReader($reader);

        $record = $reader->readRecord();

        self::assertSame(['xxx', 'yyy', 'zzz'], $record);
    }

    public function testItReadsRecordWithTabSeparator(): void
    {
        $reader = TestReader::fromString("xxx\tyyy\tzzz\n");
        $reader = Reader::fromReader($reader);
        $reader->comma = "\t";
        $record = $reader->readRecord();

        self::assertSame(['xxx', 'yyy', 'zzz'], $record);
    }

    public function testItReadsRecordUnquotedWithNonAsciiChar(): void
    {
        $reader = TestReader::fromString("xxx,yúy,zzz\n");
        $reader = Reader::fromReader($reader);

        $record = $reader->readRecord();

        self::assertSame(['xxx', 'yúy', 'zzz'], $record);
    }

    public function testItReadsWithoutFinalNewLine(): void
    {
        $reader = TestReader::fromString('xxx,y y,zzz');
        $reader = Reader::fromReader($reader);

        $record = $reader->readRecord();

        self::assertSame(['xxx', 'y y', 'zzz'], $record);
    }

    public function testItThrowsBareQuoteError(): void
    {
        $reader = TestReader::fromString('xxx,y"y,zzz'."\n");
        $reader = Reader::fromReader($reader);

        $this->expectExceptionMessage('Parse error: bare quote in non-quoted field in record 1; at column 5');
        $this->expectException(ParseError::class);
        $reader->readRecord();
    }

    public function testItThrowsBareQuoteErrorWithRightEncoding(): void
    {
        $reader = TestReader::fromString('xxx,ú"y,zzz'."\n");
        $reader = Reader::fromReader($reader);

        $this->expectExceptionMessage('Parse error: bare quote in non-quoted field in record 1; at column 5');
        $this->expectException(ParseError::class);
        $reader->readRecord();
    }

    public function testItParsesRecordQuoted(): void
    {
        $reader = TestReader::fromString('"xxx","y y","zzz"'."\r\n");
        $reader = Reader::fromReader($reader);

        $record = $reader->readRecord();

        self::assertSame(['xxx', 'y y', 'zzz'], $record);
    }

    public function testItDetectsWrongNumberOfFields(): void
    {
        $reader = TestReader::fromString('"xxx","yyy","zzz"'."\r\n".'"xxx","yyy","zzz"'."\r\n".'"xxx","zzz"'."\r\n");
        $reader = Reader::fromReader($reader);
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
        $this->expectExceptionMessage('Parse error: wrong number of fields in record 3');
        $this->expectException(FieldMismatchError::class);
        $reader->readRecord();
    }

    public function testItCanContinueFromAWrongFieldsException(): void
    {
        $reader = TestReader::fromString(
            '"xxx","yyy","zzz"'."\r\n".
            '"xxx","yyy","zzz"'."\r\n".
            '"xxx","zzz"'."\r\n".
            '"xxx","yyy","zzz"'."\r\n"
        );
        $reader = Reader::fromReader($reader);
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
        try {
            $reader->readRecord();
        } catch (FieldMismatchError $error) {
            self::assertSame(['xxx', 'zzz'], $error->getRecord());
        }
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
    }

    public function testItSkipsEmptyLine(): void
    {
        $reader = TestReader::fromString(
            '"xxx","yyy","zzz"'."\r\n".
            '"xxx","yyy","zzz"'."\r\n".
            ''."\r\n".
            '"xxx","yyy","zzz"'."\r\n"
        );
        $reader = Reader::fromReader($reader);
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
    }

    public function testItParsesNewLineInsideQuotedField(): void
    {
        $reader = TestReader::fromString(
            '"xxx","yyy","zzz"'."\r\n".
            '"xxx","y'."\n".'yy","zzz"'."\r\n".
            '"xxx","yyy","zzz"'."\r\n"
        );
        $reader = Reader::fromReader($reader);
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
        self::assertSame(['xxx', "y\nyy", 'zzz'], $reader->readRecord());
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
    }

    public function testItParsesEscapedQuotesCorrectly(): void
    {
        $reader = TestReader::fromString('"xxx","y""yy","zzz"'."\r\n");
        $reader = Reader::fromReader($reader);
        self::assertSame(['xxx', 'y"yy', 'zzz'], $reader->readRecord());
    }

    public function testItFailsOnUnescapedQuote(): void
    {
        $reader = TestReader::fromString('"xxx","y"yy","zzz"'."\r\n");
        $reader = Reader::fromReader($reader);
        $this->expectException(ParseError::class);
        $reader->readRecord();
    }

    public function testWhitespaceLineFails(): void
    {
        $reader = TestReader::fromString('"xxx","yyy","zzz"'."\r\n".'     '."\r\n");
        $reader = Reader::fromReader($reader);
        self::assertSame(['xxx', 'yyy', 'zzz'], $reader->readRecord());
        $this->expectExceptionMessage('Parse error: wrong number of fields in record 2');
        $this->expectException(FieldMismatchError::class);
        $reader->readRecord();
    }

    public function testItThrowsErrorOnInvalidCommaCharacter(): void
    {
        $readerStub = $this->createStub(Io\Reader::class);
        $reader = Reader::fromReader($readerStub);
        $reader->comma = "\n";
        $this->expectException(ReaderError::class);
        $reader->readRecord();
    }
}
