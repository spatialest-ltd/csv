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
 * Class ResourceReader.
 */
abstract class ResourceReader implements Reader
{
    /**
     * @var resource
     */
    private $resource;

    /**
     * File constructor.
     *
     * @param resource $resource
     */
    protected function __construct($resource)
    {
        if (!is_resource($resource)) {
            throw new \InvalidArgumentException(sprintf('Argument 1 of method %s must be a resource, %s given.', __METHOD__, gettype($resource)));
        }
        $this->resource = $resource;
    }

    public function read(int $bytes = Reader::DEFAULT_BYTES): ?string
    {
        if (feof($this->resource)) {
            return null;
        }
        $string = fread($this->resource, $bytes);
        if ($string === '') {
            return null;
        }

        return $string;
    }
}
