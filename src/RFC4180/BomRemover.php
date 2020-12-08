<?php

namespace Spatialest\Csv\RFC4180;

use Spatialest\Csv\Io\Reader;

/**
 * The BomRemover composes a Reader that removes known byte order marks
 *
 * @package Spatialest\Csv\RFC4180
 */
final class BomRemover implements Reader
{
    private static array $bomList = [
        "\xEF\xBB\xBF",     // UTF-8 little endian
        "\xFE\xFF",         // UTF-16 big endian
        "\xFF\xFE",         // UTF-16 little endian
        "\x00\x00\xFE\xFF", // UTF-32 big endian
        "\xFF\xFE\x00\x00"  // UTF-32 little endian
    ];

    private Reader $reader;
    private bool $read;

    /**
     * BomRemover constructor.
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
        $this->read = false;
    }

    /**
     * @param int $bytes
     * @return string|null
     */
    public function read(int $bytes = self::DEFAULT_BYTES): ?string
    {
        $chunk = $this->reader->read($bytes);
        if ($this->read === true) {
            return $chunk;
        }
        if (!is_string($chunk)) {
            return null;
        }
        $chunk = str_replace(self::$bomList, '', $chunk);
        $this->read = true;
        return $chunk;
    }
}