<?php


namespace Spatialest\Csv\RFC4180;


use Exception;
use Throwable;

/**
 * Class ParseError
 * @package Spatialest\Csv\RFC4180
 */
class ParseError extends Exception
{
    /**
     * @param int $startLine
     * @param int $line
     * @param int|null $column
     * @param Throwable|null $error
     * @return ParseError
     */
    public static function create(int $startLine, int $line, int $column = null, Throwable $error = null): ParseError
    {
        if ($column === null && $error !== null) {
            $message = sprintf('record on line %d: %s', $line, $error->getMessage());
            return new self($message, 0, $error);
        }
        if ($startLine !== $line) {
            $message = sprintf('record on line %d; parse error on line %d, column %d: %s', $startLine, $line, $column, $error->getMessage());
            return new self($message, 0, $error);
        }
        $message = sprintf('parse error on line %d, column %d: %s', $line, $column, $error->getMessage());
        return new self($message, 0, $error);
    }
}