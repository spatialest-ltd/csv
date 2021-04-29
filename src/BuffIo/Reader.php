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

namespace Spatialest\Csv\BuffIo;

use Castor\Io;
use Spatialest\Csv\Str;

/**
 * The Reader composes an Io\Reader with useful buffered io operations.
 */
class Reader
{
    private Io\Reader $reader;
    private string $buffer;
    private bool $eof;

    /**
     * Reader constructor.
     */
    public function __construct(Io\Reader $reader)
    {
        $this->reader = $reader;
        $this->buffer = '';
        $this->eof = false;
    }

    /**
     * Reads until the first occurrence of a string.
     *
     * @psalm-ignore PossiblyUndefinedVariable
     *
     * @throws Io\Error
     */
    public function readString(string $delimiter): ?string
    {
        if ($this->eof === true && $this->buffer === '') {
            return null;
        }
        $chunk = '';
        // Read to the buffer until the delimiter is found.
        while (($pos = Str\index($this->buffer, $delimiter)) === -1) {
            try {
                $this->reader->read(4096, $chunk);
            } catch (Io\Eof $e) {
                $this->eof = true;
                $string = $this->buffer === '' ? null : $this->buffer;
                $this->buffer = '';

                return $string;
            }
            $this->buffer .= $chunk;
        }
        ++$pos;
        $line = substr($this->buffer, 0, $pos);
        $this->buffer = substr($this->buffer, $pos);

        return $line;
    }
}
