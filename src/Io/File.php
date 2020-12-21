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
 * Class File.
 */
class File extends ResourceReader
{
    public static function open(string $filename): File
    {
        if (!is_file($filename) || !is_readable($filename)) {
            throw new \InvalidArgumentException(sprintf('File %s must be a readable file', $filename));
        }
        $resource = fopen('file://'.$filename, 'rb');
        if ($resource === false) {
            throw new \InvalidArgumentException('Could not open resource');
        }

        return new self($resource);
    }
}
