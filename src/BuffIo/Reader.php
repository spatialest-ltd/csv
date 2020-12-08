<?php


namespace Spatialest\Csv\BuffIo;

use Spatialest\Csv\Io\Reader as IoReader;

/**
 * The Reader composes an IoReader with useful buffered io operations
 *
 * @package Spatialest\Csv\BuffIo
 */
class Reader
{
    /**
     * @var IoReader
     */
    private IoReader $reader;
    private string $buffer;
    private bool $eof;

    /**
     * Reader constructor.
     * @param IoReader $reader
     */
    public function __construct(IoReader $reader)
    {
        $this->reader = $reader;
        $this->eof = false;
        $this->buffer = '';
    }

    /**
     * Reads until the first occurrence of a string
     * @param string $delimiter
     * @return string|null
     */
    public function readString(string $delimiter): ?string
    {
        if ($this->eof === true && $this->buffer === '') {
            return null;
        }
        // Read to the buffer until the delimiter is found.
        while (($pos = strpos($this->buffer, $delimiter)) === false) {
            $chunk = $this->reader->read(IoReader::DEFAULT_BYTES);
            if ($chunk === null || $chunk === '') {
                $this->eof = true;
                $string = $this->buffer === '' ? null : $this->buffer;
                $this->buffer = '';
                return $string;
            }
            $this->buffer .= $chunk;
        }
        $pos++;
        $line = substr($this->buffer, 0, $pos);
        $this->buffer = substr($this->buffer, $pos);
        return $line;
    }
}