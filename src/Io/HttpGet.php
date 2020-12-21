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
 * Class HttpFile.
 */
final class HttpGet extends ResourceReader
{
    public static function request(string $url): HttpGet
    {
        $resource = fopen($url, 'rb');
        if ($resource === false) {
            throw new \RuntimeException(sprintf('Could not open url %s', $url));
        }

        return new self($resource);
    }
}
