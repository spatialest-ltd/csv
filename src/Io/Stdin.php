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
 * Class Stdin.
 */
final class Stdin extends ResourceReader
{
    private static ?Stdin $instance = null;

    public static function instance(): Stdin
    {
        if (self::$instance === null) {
            self::$instance = new self(STDIN);
        }

        return self::$instance;
    }
}
