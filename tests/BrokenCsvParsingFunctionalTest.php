<?php

namespace Spatialest\Csv;


use PHPUnit\Framework\TestCase;
use Spatialest\Csv\Io\ResourceReader;
use Spatialest\Csv\RFC4180\Reader;

/**
 * Class BrokenCsvParsingFunctionalTest
 * @package Spatialest\Csv
 */
class BrokenCsvParsingFunctionalTest extends TestCase
{
    public function testItParsesBrokenOne(): void
    {
        $countErrors = [];
        $reader = Reader::fromReader(ResourceReader::fromFile(__DIR__ . '/broken1.csv'));
        while (true) {
            try {
                $record = $reader->readRecord();
            } catch (RFC4180\WrongFieldsNumberError $e) {
                $countErrors[] = $e;
                continue;
            }
            if ($record === null) {
                break;
            }
        }
        self::assertCount(9, $countErrors);
    }
}