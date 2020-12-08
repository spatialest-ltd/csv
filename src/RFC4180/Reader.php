<?php


namespace Spatialest\Csv\RFC4180;

use Generator;
use Spatialest\Csv\BuffIo\Reader as BufIoReader;
use Spatialest\Csv\Io\Reader as IoReader;
use function Spatialest\Csv\strIndex;

/**
 * The Reader reads CSV files according to the RFC4180
 *
 * A valid csv file contains zero or more records of one or more fields per record.
 * Each record is separated by the newline character "\n". The final record MAY
 * optionally be followed by a newline character.
 *
 * Whitespace IS considered part of a field.
 *
 * Carriage returns before newline characters are silently removed.
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
 * @link  https://tools.ietf.org/html/rfc4180
 *
 * @package Spatialest\Csv\RFC4180
 */
class Reader
{
    private const QUOTE = '"';

    /**
     * @var BufIoReader
     */
    private BufIoReader $reader;
    private int $lineNum;
    private string $recordBuffer;
    private array $fieldIndexes;

    /**
     * The comma character is the character that delimits fields. Sometimes is
     * the tab ("\t") character.
     *
     * @var string
     */
    public string $comma;
    /**
     * The expected number of fields for each record.
     * When zero the number of expected fields for each record is set based on
     * the number of field of the first record.
     *
     * @var int
     */
    public int $expectedFields;
    /**
     * The comment character.
     * When a line starts with this character is completely skipped.
     *
     * @var string|null
     */
    public ?string $comment;
    /**
     * Whether to trim the leading space before the quote character in a field.
     * By default is true.
     *
     * @var bool
     */
    public bool $trimLeadingSpace;
    public bool $lazyQuotes;

    /**
     * @param IoReader $reader
     * @return Reader
     */
    public static function fromReader(IoReader $reader): Reader
    {
        return new self(new BufIoReader($reader));
    }

    /**
     * Reader constructor.
     * @param BufIoReader $reader
     */
    public function __construct(BufIoReader $reader, string $comma = ',')
    {
        $this->reader = $reader;
        $this->lineNum = 0;
        $this->comma = $comma;
        $this->comment = null;
        $this->trimLeadingSpace = true;
        $this->lazyQuotes = false;
        $this->recordBuffer = '';
        $this->fieldIndexes = [];
        $this->expectedFields = 0;
    }

    /**
     * Returns a generator that iterates over all the records
     *
     * @return Generator<array<string>>
     * @throws ParseError
     */
    public function readAll(): Generator
    {
        while (($record = $this->readRecord()) !== null) {
            yield $record;
        }
    }

    /**
     * @return array|null
     * @throws ParseError
     */
    public function readRecord(): ?array
    {
        $this->guard();
        $fullLine = '';
        while (($line = $this->readLine()) !== null) {
            if ($this->comment !== null && $line[0] === $this->comment) {
                $line = null;
                continue; // Skip comment lines
            }
            if (strlen($line) === $this->lengthNL($line)) {
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
        $quoteLen = strlen(self::QUOTE);
        $commaLen = strlen($this->comma);
        $recordLine = $this->lineNum;
        $this->recordBuffer = '';
        $this->fieldIndexes = [];

        while (true) {
            if ($this->trimLeadingSpace) {
                $line = ltrim($line, ' ');
            }
            if ($line === '' || $line[0] !== self::QUOTE) {
                // Non quoted string field
                $i = strIndex($line, $this->comma);
                $field = $line;
                if ($i >= 0) {
                    $field = substr($field, 0, $i);
                } else {
                    $field = substr($field, 0, strlen($field) - $this->lengthNL($field));
                }
                // Check to make sure a quote does not appear in the field
                if (!$this->lazyQuotes && ($j = strIndex($field, self::QUOTE)) >= 0) {
                    $col = strlen(substr($fullLine, 0, strlen($fullLine) - strlen(substr($line, $j))));
                    throw ParseError::create($recordLine, $this->lineNum, $col, new \Exception('bare " in non-quoted field'));
                }
                $this->recordBuffer .= $field;
                $this->fieldIndexes[] = strlen($this->recordBuffer);
                if ($i >= 0) {
                    $line = substr($line, $i+$commaLen);
                    continue;
                }
                break;
            } else {
                // Quoted string field
                $line = substr($line, $quoteLen);
                while (true) {
                    $i = strIndex($line, self::QUOTE);
                    if ($i >= 0) {
                        // Hit next quote
                        $this->recordBuffer .= substr($line, 0, $i);
                        $line = substr($line, $i+$quoteLen);
                        $rn = $line[0];
                        switch (true) {
                            case $rn === self::QUOTE :
                                // "" sequence. We append the quote.
                                $this->recordBuffer .= self::QUOTE;
                                $line = substr($line, $quoteLen);
                                break;
                            case $rn === $this->comma :
                                // ", sequence. End of field.
                                $line = substr($line, $commaLen);
                                $this->fieldIndexes[] = strlen($this->recordBuffer);
                                continue 3;
                            case $this->lengthNL($line) === strlen($line) :
                                // "\n sequence. End of line.
                                $this->fieldIndexes[] = strlen($this->recordBuffer);
                                break 3;
                            case $this->lazyQuotes :
                                // " sequence. Bare quote.
                                $this->recordBuffer .= self::QUOTE;
                                break;
                            default :
                                // "* sequence. Invalid non-escaped quote.
                                $col = mb_strlen(substr($fullLine, 0, strlen($fullLine) - strlen($line) - strlen($quoteLen)));
                                throw ParseError::create($recordLine, $this->lineNum, $col, new \Exception('extraneous or missing \" in quoted-field'));
                        }
                    } elseif ($line !== '') {
                        // Hit end of line. Copy all data so far.
                        $this->recordBuffer .= $line;
                        $line = $this->readLine();
                        if ($line === null) {
                            $col = 4;
                            throw ParseError::create($recordLine, $this->lineNum, $col, new \Exception('Unexpected end of file'));
                        }
                        $fullLine = $line;
                    } else {
                        // Abrupt end of file.
                        if (!$this->lazyQuotes) {
                            $col = mb_strlen($fullLine);
                            throw ParseError::create($recordLine, $this->lineNum, $col, new \Exception('extraneous or missing \" in quoted-field'));
                        }
                        $this->fieldIndexes .= strlen($this->recordBuffer);
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
        if (count($record) !== $this->expectedFields) {
            throw ParseError::create($recordLine, $recordLine, null, new \Exception('wrong number of fields'));
        }

        return $record;
    }

    /**
     * Reads the next line (with the trailing end line)
     * @return string
     */
    private function readLine(): ?string
    {
        $line = $this->reader->readString("\n");
        if ($line === null) {
            return null;
        }
        $this->lineNum++;
        $length = strlen($line);
        // Normalize line endings at the end of the string
        if ($line[$length-2] === "\r" && $line[$length-1] === "\n") {
            $line = substr($line, 0, -2)."\n";
        }
        return $line;
    }

    private function guard(): void
    {
        if (
            $this->comma === $this->comment ||
            !$this->validDelimiter($this->comma) ||
            ($this->comment !== null && $this->validDelimiter($this->comment))
        ) {
            throw new \InvalidArgumentException('Invalid field or comment delimiter');
        }
    }

    /**
     * @param string|null $delimiter
     * @return bool
     */
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
        if ($string !== '' && $string[strlen($string)-1] === "\n") {
            return 1;
        }
        return 0;
    }
}