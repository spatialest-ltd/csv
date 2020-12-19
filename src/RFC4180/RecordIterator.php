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

/**
 * Class CsvColumnIterator.
 */
class RecordIterator implements \IteratorAggregate
{
    private Reader $reader;
    /**
     * @var WrongFieldsNumberError[]
     */
    private array $errors;

    /**
     * CsvColumnIterator constructor.
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
        $this->errors = [];
    }

    /**
     * @throws ReaderError
     */
    public function getIterator(): Generator
    {
        while (true) {
            try {
                $record = $this->reader->readRecord();
            } catch (WrongFieldsNumberError $error) {
                $this->errors[] = $error;
                continue;
            }
            if ($record === null) {
                break;
            }
            yield $record;
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }
}
