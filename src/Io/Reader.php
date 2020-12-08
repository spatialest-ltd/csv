<?php


namespace Spatialest\Csv\Io;

/**
 * Interface Reader
 * @package Spatialest\Csv\Io
 */
interface Reader
{
    public const DEFAULT_BYTES = 4096;

    /**
     * Reads some bytes from a source
     * @param int $bytes
     * @return string|null on EOF
     */
    public function read(int $bytes = self::DEFAULT_BYTES): ?string;
}