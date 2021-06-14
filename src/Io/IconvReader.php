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

/**
 * Class IconvReader.
 */
final class IconvReader implements Reader
{
    private Reader $reader;
    private ?string $source;
    private bool $ignore;

    /**
     * Windows1252Reader constructor.
     */
    public function __construct(Reader $reader, string $source = null, bool $ignore = false)
    {
        $this->reader = $reader;
        $this->source = $source;
        $this->ignore = $ignore;
    }

    /**
     * {@inheritDoc}
     */
    public function read(int $length, string &$bytes): int
    {
        $this->reader->read($length, $bytes);
        if ($this->source = null) {
            $this->source = mb_detect_encoding($bytes);
        }
        $target = sprintf('UTF-8//%s', $this->ignore ? 'IGNORE' : 'TRANSLIT');
        $bytes = iconv($this->source, $target, $bytes);

        return strlen($bytes);
    }
}
