<?php

namespace Spatialest\Csv;

/**
 * Returns the position of the first occurrence of needle in haystack.
 *
 * Returns -1 if the needle is not found.
 *
 * @param string $haystack
 * @param string $needle
 * @return int
 */
function strIndex(string $haystack, string $needle): int
{
    $result = \strpos($haystack, $needle);
    if (is_int($result)) {
        return $result;
    }
    return -1;
}