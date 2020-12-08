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

namespace Spatialest\Csv\Io;

/**
 * Interface Reader.
 */
interface Reader
{
    public const DEFAULT_BYTES = 4096;

    /**
     * Reads some bytes from a source.
     *
     * @return string|null on EOF
     */
    public function read(int $bytes = self::DEFAULT_BYTES): ?string;
}
