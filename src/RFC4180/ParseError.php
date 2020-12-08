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
 * The ParseError represents an error occurred in parsing.
 */
class ParseError extends ReaderError
{
    private int $fileLine;
    private int $recordNumber;
    private ?int $col;

    /**
     * ParseError constructor.
     */
    public function __construct(string $message, int $fileLine, int $recordNumber, int $col = null)
    {
        $message = sprintf('Parse error: %s in record %s', $message, $recordNumber);
        if ($fileLine !== $recordNumber) {
            $message .= '; at file line '.$fileLine;
        }
        if ($col !== null) {
            $message .= '; at column '.$col;
        }
        parent::__construct($message);
        $this->fileLine = $fileLine;
        $this->recordNumber = $recordNumber;
        $this->col = $col;
    }

    public function getCol(): ?int
    {
        return $this->col;
    }

    public function getFileLine(): int
    {
        return $this->fileLine;
    }

    public function getRecordNumber(): int
    {
        return $this->recordNumber;
    }
}
