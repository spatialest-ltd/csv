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
class ResourceReader implements Reader
{
    /**
     * @var resource
     */
    private $resource;

    public static function fromFile(string $filename): ResourceReader
    {
        if (!is_file($filename) || !is_readable($filename)) {
            throw new \InvalidArgumentException(sprintf('File %s must be a readable file', $filename));
        }
        $resource = fopen($filename, 'rb');
        if ($resource === false) {
            throw new \InvalidArgumentException('Could not open resource');
        }

        return new self($resource);
    }

    public static function fromUrl(string $url): ResourceReader
    {
        $resource = @fopen($url, 'rb');
        if (!$resource) {
            throw new \InvalidArgumentException('Could not fetch url');
        }

        return new self($resource);
    }

    public static function stdin(): ResourceReader
    {
        return new self(STDIN);
    }

    /**
     * ResourceReader constructor.
     *
     * @param resource $resource
     */
    public function __construct($resource)
    {
        if (!is_resource($resource)) {
            throw new \InvalidArgumentException(sprintf('Argument 1 of method %s must be a resource, %s given.', __METHOD__, gettype($resource)));
        }
        $this->resource = $resource;
    }

    public function read(int $bytes = self::DEFAULT_BYTES): ?string
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
