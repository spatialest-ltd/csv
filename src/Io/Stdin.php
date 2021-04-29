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

use Castor\Io\Reader;
use Castor\Io\TestReader;

/**
 * Class Stdin.
 */
final class Stdin
{
    private static ?Reader $instance = null;

    public static function instance(): Reader
    {
        if (self::$instance === null) {
            self::$instance = new TestReader(STDIN);
        }

        return self::$instance;
    }
}
