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

use Iterator;

/**
 * Class CsvColumnIterator.
 */
final class CsvIterator implements Iterator
{
    private Reader $reader;
    private ?array $current;
    private int $key;
    private array $headers = [];
    private array $errors;

    /**
     * @throws ReaderError
     */
    public static function withHeaders(Reader $reader): CsvIterator
    {
        $iterator = new self($reader);
        $iterator->headers = $iterator->current ?? [];
        $iterator->current = $iterator->nextValidRecord($reader);

        return $iterator;
    }

    /**
     * CsvColumnIterator constructor.
     *
     * @throws ReaderError
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
        $this->current = $this->nextValidRecord($reader);
        $this->key = 0;
        $this->errors = [];
    }

    public function current(): ?array
    {
        if ($this->headers !== []) {
            return array_combine($this->headers, $this->current);
        }

        return $this->current;
    }

    /**
     * @throws ReaderError
     */
    public function next(): void
    {
        $this->current = $this->nextValidRecord($this->reader);
        ++$this->key;
    }

    public function key()
    {
        return $this->key;
    }

    public function valid(): bool
    {
        return $this->current !== null;
    }

    public function rewind(): void
    {
        // Rewind does nothing
    }

    /**
     * @throws ReaderError
     */
    private function nextValidRecord(Reader $reader): ?array
    {
        try {
            return $reader->readRecord();
        } catch (FieldMismatchError $e) {
            $this->errors[] = $e;

            return $this->nextValidRecord($reader);
        }
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
