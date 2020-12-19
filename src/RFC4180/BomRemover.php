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

use Spatialest\Csv\Io\Reader;
use Spatialest\Csv\Str;

/**
 * The BomRemover composes a Reader that removes known byte order marks.
 */
final class BomRemover implements Reader
{
    private static array $bomList = [
        "\xEF\xBB\xBF",     // UTF-8 little endian
        "\xFE\xFF",         // UTF-16 big endian
        "\xFF\xFE",         // UTF-16 little endian
        "\x00\x00\xFE\xFF", // UTF-32 big endian
        "\xFF\xFE\x00\x00",  // UTF-32 little endian
    ];

    private Reader $reader;
    private bool $read;

    /**
     * BomRemover constructor.
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
        $this->read = false;
    }

    public function read(int $bytes = self::DEFAULT_BYTES): ?string
    {
        $chunk = $this->reader->read($bytes);
        if ($this->read === true) {
            return $chunk;
        }
        if (!is_string($chunk)) {
            return null;
        }
        foreach (self::$bomList as $bom) {
            if (Str\index($chunk, $bom) === 0) {
                $bomLen = strlen($bom);
                $chunk = substr($chunk, $bomLen);
                break;
            }
        }
        $this->read = true;

        return $chunk;
    }
}
