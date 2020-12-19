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
use IteratorAggregate;
use Traversable;

/**
 * Class HeaderIterator.
 */
final class HeaderIterator implements IteratorAggregate
{
    private Traversable $iterator;
    private array $headers;

    /**
     * HeaderIterator constructor.
     */
    public function __construct(Traversable $iterator, array $headers = [])
    {
        $this->iterator = $iterator;
        $this->headers = $headers;
    }

    public function getIterator(): Generator
    {
        foreach ($this->iterator as $record) {
            if ($this->headers === []) {
                $this->headers = $record;
                continue;
            }
            yield array_combine($this->headers, $record);
        }
    }
}
