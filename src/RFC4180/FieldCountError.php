<?php


namespace Spatialest\Csv\RFC4180;


use Exception;
use Throwable;

/**
 * Class FieldCountError
 * @package Spatialest\Csv\RFC4180
 */
class FieldCountError extends Exception
{
    public function __construct()
    {
        parent::__construct('wrong number of fields');
    }
}