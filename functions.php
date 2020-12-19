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

namespace Spatialest\Csv\Str;

/**
 * Returns the position of the first occurrence of needle in haystack.
 *
 * Returns -1 if the needle is not found.
 */
function index(string $haystack, string $needle): int
{
    $result = \strpos($haystack, $needle);
    if (is_int($result)) {
        return $result;
    }

    return -1;
}

function slice(string $string, int $offset, int $length = 0): string
{
    return substr($string, $offset, $length);
}

/**
 * Returns the length of a string.
 */
function len(string $string): int
{
    return \strlen($string);
}

namespace Spatialest\Csv\Utf8;

/**
 * Returns the rune count for multibyte strings.
 */
function runeCount(string $string): int
{
    return \mb_strlen($string);
}
