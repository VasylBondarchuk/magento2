<?php

declare(strict_types = 1);

namespace Tsg\Improvements\Controller\Adminhtml\Display;

use \Magento\Framework\Filesystem\Driver\File;
use \Magento\Framework\File\Csv;
use \Magento\InventoryApi\Api\SourceRepositoryInterface;
use \Psr\Log\LoggerInterface;

class CsvValidator
{
    const EXPECTED_CSV_COLUMNS = ["Sku", "Qty", "Source"];
    const VALIDATED_CSV_COLUMNS = ["Qty", "Source"];

    private $file;
    private $csv;
    private $csvReader;
    private $logger;
    private $sourceRepository;

    public function __construct(
        File $file,
        Csv $csv,
        SourceRepositoryInterface $sourceRepository,
        CsvReader $csvReader,
        LoggerInterface $logger
    )
    {
        $this->file = $file;
        $this->csv = $csv;
        $this->sourceRepository = $sourceRepository;
        $this->csvReader = $csvReader;
        $this->logger = $logger;
    }

    public function getCsvDirectRawData(string $filePath): array
    {
        $csvDirectRawData = [];
        try {
            if($this->file->isExists($filePath)){
                $csvDirectRawData = $this->csv->getData($filePath);
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
        return $csvDirectRawData;
    }

    public function getCsvColumnNames(string $filePath): array
    {
        $csvColumnNames = [];
        try {
            if($this->file->isExists($filePath)){
                $csvDirectData = $this->getCsvDirectRawData($filePath);
                $csvColumnNames = array_filter($csvDirectData[0]);
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
        return $csvColumnNames;
    }

    public function isCsvHeaderCorrect($filePath): bool
    {
        try {
            if($this->file->isExists($filePath)) {
                return ($this->getCsvColumnNames($filePath) === self::EXPECTED_CSV_COLUMNS);
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
        return false;
    }

    public function filterExcessiveRowData(array $row)
    {
        return array_filter($row, function($a) {return $a !== "";},ARRAY_FILTER_USE_KEY);
    }

    public function getRawCsvData(string $filePath): array
    {
        $rawCsvData = [];

        try {
            if (!$this->isCsvHeaderCorrect($filePath)) {
                return $rawCsvData;
            }
            foreach ($this->csvReader->csvReaderGenerator($filePath) as $row) {
                // if the row has excessive data
                $rawCsvData[] = $this->filterExcessiveRowData($row);
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
        return $rawCsvData;
    }

    public function getRowParam(array $row, string $columnName): string
    {
        return $row[$columnName];
    }

    public function getSourcesCodes(): array
    {
        $source_codes = [];
        $sourceData = $this->sourceRepository->getList()->getItems();
        foreach ($sourceData as $source) {
            $source_codes[] = $source['source_code'];
        }
        return $source_codes;
    }

    public function isRowValueCorrect(array $row, string $columnName): bool
    {
        if ($columnName == "Qty") return is_numeric($this->getRowParam($row, "Qty"));
        if ($columnName == "Source") return in_array($this->getRowParam($row, "Source"), $this->getSourcesCodes());
        return true;
    }

    public function isRowCorrect(array $row): bool
    {
        foreach (self::VALIDATED_CSV_COLUMNS as $columnName) {
            if (!$this->isRowValueCorrect($row, $columnName)) return false;
        }
        return true;
    }

    public function getValidatedCsvData(string $filePath): array
    {
        $validatedCsvData = [];

        try {
            if($this->file->isExists($filePath)) {
                $validatedCsvData = array_filter($this->getRawCsvData($filePath), function ($row) {
                    if ($this->isRowCorrect($row)) {
                        return $row;
                    }
                });
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
        return $validatedCsvData;
    }
}
