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

/**
 * The FieldMismatchError is thrown when a record has an invalid count.
 */
class FieldMismatchError extends ParseError
{
    private array $record;

    /**
     * FieldMismatchError constructor.
     */
    public function __construct(array $record, int $fileLine, int $recordNumber)
    {
        parent::__construct('wrong number of fields', $fileLine, $recordNumber);
        $this->record = $record;
    }

    /**
     * Returns the wrong record.
     */
    public function getRecord(): array
    {
        return $this->record;
    }
}
