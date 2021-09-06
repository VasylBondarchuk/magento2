<?php

declare(strict_types = 1);

namespace Tsg\Improvements;

use \Psr\Log\LoggerInterface;

class CsvReader
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function csvReaderGenerator(string $filePath, string $delimeter = ',')
    {
        $delimeter = ',';
        $batchSize = 100;
        $batch = [];
        $csvHeader = [];
        $batchCounter = 0;
        $rowCounter = 0;

        try {
            $handle = fopen($filePath, "r");
            while (($row = fgetcsv($handle, $delimeter)) !== false) {
                if (0 == $rowCounter) {
                    $csvHeader = $row;
                } else {
                    $batch = array_combine($csvHeader, $row);
                }
                $rowCounter++;
                // get a batch
                if (++$batchCounter == $batchSize) {
                    yield $batch;
                    $batch = [];
                    $batchCounter = 0;
                }
                // return a residue of csv file data if any
                if (count($batch) > 0) {
                    yield $batch;
                }
            }
            fclose($handle);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }
}
