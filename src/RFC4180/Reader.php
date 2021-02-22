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

use Generator;
use Spatialest\Csv\BuffIo;
use Spatialest\Csv\Io;
use Spatialest\Csv\Io\File;
use Spatialest\Csv\Str;
use Spatialest\Csv\Utf8;

/**
 * The Reader reads CSV files according to the RFC4180.
 *
 * A valid csv file contains zero or more records of one or more fields per record.
 * Each record is separated by the newline character "\n". The final record MAY
 * optionally be followed by a newline character.
 *
 * Whitespace IS considered part of a field.
 *
 * Carriage returns ("\r") before newline characters are silently removed.
 *
 * Blank lines are ignored. A line with only whitespace characters (excluding
 * the ending newline character) IS NOT considered a blank line.
 *
 * Fields which start and stop with the quote character " are called quoted-fields.
 * The beginning and ending quote ARE NOT part of the field.
 *
 * Within a quoted-field a quote character followed by a second quote
 * character is considered a single quote.
 *
 * Newlines and commas MAY be included in a quoted-field.
 *
 * @see  https://tools.ietf.org/html/rfc4180
 * @note This is a direct port of Golang's CSV reader.
 */
class Reader implements \IteratorAggregate
{
    private const QUOTE = '"';

    private BuffIo\Reader $reader;
    private int $lineNum;
    private string $recordBuffer;
    private array $fieldIndexes;

    /**
     * The comma character is the character that delimits fields. Sometimes is
     * the tab ("\t") character.
     */
    public string $comma;
    /**
     * The quote character is the character that is used to enclose and escape strings.
     */
    public string $quote;
    /**
     * The expected number of fields for each record.
     * When zero the number of expected fields for each record is set based on
     * the number of field of the first record.
     */
    public int $expectedFields;
    /**
     * The comment character.
     * When a line starts with this character is completely skipped.
     */
    public ?string $comment;
    /**
     * Whether to trim the leading space before the quote character in a field.
     * By default is true.
     */
    public bool $trimLeadingSpace;
    public bool $lazyQuotes;

    public static function fromFile(string $filename, bool $removeBom = true): Reader
    {
        $reader = File::open($filename);

        return self::fromReader($reader, $removeBom);
    }

    public static function fromReader(Io\Reader $reader, bool $removeBom = true): Reader
    {
        if ($removeBom === true) {
            $reader = new BomRemover($reader);
        }

        return new self(new BuffIo\Reader($reader));
    }

    /**
     * Reader constructor.
     */
    public function __construct(BuffIo\Reader $reader, string $comma = ',', string $quote = '"')
    {
        $this->reader = $reader;
        $this->lineNum = 0;
        $this->comma = $comma;
        $this->quote = $quote;
        $this->comment = null;
        $this->trimLeadingSpace = true;
        $this->lazyQuotes = false;
        $this->recordBuffer = '';
        $this->fieldIndexes = [];
        $this->expectedFields = 0;
    }

    public function getIterator(): CsvIterator
    {
        return CsvIterator::withHeaders($this);
    }

    /**
     * Returns a generator that iterates over all the records.
     *
     * @return Generator<array<string>>
     *
     * @throws ReaderError
     */
    public function readAll(): Generator
    {
        while (($record = $this->readRecord()) !== null) {
            yield $record;
        }
    }

    /**
     * @throws ReaderError
     * @psalm-ignore PossiblyUndefinedVariable
     */
    public function readRecord(): ?array
    {
        $this->guard();
        $fullLine = '';
        while (($line =
                $this->readLine()) !== null) {
            if ($this->comment !== null && $line[0] === $this->comment) {
                $line = null;
                continue; // Skip comment lines
            }
            if (Str\len($line) === $this->lengthNL($line)) {
                $line = null;
                continue; // Skip empty lines
            }
            $fullLine = $line;
            break;
        }
        if ($line === null) {
            return null;
        }

        // We parse each field in the record
        $quoteLen = Str\len($this->quote);
        $commaLen = Str\len($this->comma);
        $recordLine = $this->lineNum;
        $this->recordBuffer = '';
        $this->fieldIndexes = [];

        while (true) {
            if ($this->trimLeadingSpace) {
                $line = ltrim($line, ' ');
            }
            if ($line === '' || $line[0] !== $this->quote) {
                // Non quoted string field
                $i = Str\index($line, $this->comma);
                $field = $line;
                if ($i >= 0) {
                    $field = substr($field, 0, $i);
                } else {
                    $field = substr($field, 0, Str\len($field) - $this->lengthNL($field));
                }
                // Check to make sure a quote does not appear in the field
                if (!$this->lazyQuotes && ($j = Str\index($field, $this->quote)) >= 0) {
                    $col = Utf8\runeCount(substr($fullLine, 0, Utf8\runeCount($fullLine) - Utf8\runeCount(substr($line, $j))));
                    throw new ParseError('bare quote in non-quoted field', $this->lineNum, $recordLine, $col);
                }
                $this->recordBuffer .= $field;
                $this->fieldIndexes[] = Str\len($this->recordBuffer);
                if ($i >= 0) {
                    $line = substr($line, $i + $commaLen);
                    continue;
                }
                break;
            } else {
                // Quoted string field
                $line = substr($line, $quoteLen);
                while (true) {
                    $i = Str\index($line, $this->quote);
                    if ($i >= 0) {
                        // Hit next quote
                        $this->recordBuffer .= substr($line, 0, $i);
                        $line = substr($line, $i + $quoteLen);
                        $rn = $line[0];
                        switch (true) {
                            case $rn === $this->quote:
                                // "" sequence. We append the quote.
                                $this->recordBuffer .= $this->quote;
                                $line = substr($line, $quoteLen);
                                break;
                            case $rn === $this->comma:
                                // ", sequence. End of field.
                                $line = substr($line, $commaLen);
                                $this->fieldIndexes[] = Str\len($this->recordBuffer);
                                continue 3;
                            case $this->lengthNL($line) === Str\len($line):
                                // "\n sequence. End of line.
                                $this->fieldIndexes[] = Str\len($this->recordBuffer);
                                break 3;
                            case $this->lazyQuotes:
                                // " sequence. Bare quote.
                                $this->recordBuffer .= $this->quote;
                                break;
                            default:
                                // "* sequence. Invalid non-escaped quote.
                                $col = Utf8\runeCount(substr($fullLine, 0, Str\len($fullLine) - Str\len($line) - $quoteLen));
                                throw new ParseError('extraneous or missing quote in quoted-field', $this->lineNum, $recordLine, $col);
                        }
                    } elseif ($line !== '') {
                        // Hit end of line. Copy all data so far.
                        $this->recordBuffer .= $line;
                        $line = $this->readLine();
                        if ($line === null) {
                            $col = Str\len($fullLine);
                            throw new ParseError('unexpected end of file', $this->lineNum, $recordLine, $col);
                        }
                        $fullLine = $line;
                    } else {
                        // Abrupt end of file.
                        if (!$this->lazyQuotes) {
                            $col = Utf8\runeCount($fullLine);
                            throw new ParseError('extraneous or missing quote in quoted-field', $this->lineNum, $recordLine, $col);
                        }
                        $this->fieldIndexes[] = Str\len($this->recordBuffer);
                        break 2;
                    }
                }
            }
        }
        // Process the buffered records
        $record = [];
        $preIdx = 0;
        foreach ($this->fieldIndexes as $i) {
            $record[] = substr($this->recordBuffer, $preIdx, $i - $preIdx);
            $preIdx = $i;
        }

        // Check or update the expected fields per record
        if ($this->expectedFields === 0) {
            $this->expectedFields = count($record);
        }
        if ($this->expectedFields !== count($record)) {
            throw new FieldMismatchError($record, $recordLine, $recordLine);
        }

        return $record;
    }

    /**
     * Reads the next line (with the trailing end line).
     *
     * @return string
     */
    private function readLine(): ?string
    {
        $line = $this->reader->readString("\n");
        if ($line === null) {
            return null;
        }
        ++$this->lineNum;
        $length = Str\len($line);
        // Normalize line endings at the end of the string
        if ($line[$length - 2] === "\r" && $line[$length - 1] === "\n") {
            $line = substr($line, 0, -2)."\n";
        }

        return $line;
    }

    /**
     * @throws ReaderError
     */
    private function guard(): void
    {
        if (
            $this->comma === $this->comment ||
            !$this->validDelimiter($this->comma) ||
            ($this->comment !== null && $this->validDelimiter($this->comment))
        ) {
            throw new ReaderError('Invalid field or comment delimiter');
        }
    }

    private function validDelimiter(string $delimiter = null): bool
    {
        return $delimiter !== null
            && $delimiter !== '"'
            && $delimiter !== "\r"
            && $delimiter !== "\n"
            && mb_check_encoding($delimiter, 'UTF-8');
    }

    private function lengthNL(string $string): int
    {
        if ($string !== '' && $string[Str\len($string) - 1] === "\n") {
            return 1;
        }

        return 0;
    }
}
