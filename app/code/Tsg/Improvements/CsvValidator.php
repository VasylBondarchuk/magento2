<?php

declare(strict_types = 1);

namespace Tsg\Improvements;

use \Magento\Framework\File\Csv;
use \Magento\InventoryApi\Api\SourceRepositoryInterface;
use \Psr\Log\LoggerInterface;

/**
 This class validates and filters csv rows
 */
class CsvValidator
{

    const EXPECTED_CSV_COLUMNS = ["Sku", "Qty", "Source"];

    const VALIDATED_CSV_COLUMNS = ["Qty", "Source"];

    /**
     * @var Csv
     */
    private $csv;
    /**
     * @var CsvReader
     */
    private $csvReader;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    private $validatedCsvData = [];

    /**
     * @param Csv $csv
     * @param SourceRepositoryInterface $sourceRepository
     * @param CsvReader $csvReader
     * @param LoggerInterface $logger
     */
    public function __construct(
        Csv $csv,
        SourceRepositoryInterface $sourceRepository,
        CsvReader $csvReader,
        LoggerInterface $logger
    ) {
        $this->csv = $csv;
        $this->sourceRepository = $sourceRepository;
        $this->csvReader = $csvReader;
        $this->logger = $logger;
    }

    /**
     * @param string $filePath
     * @return array
     */
    public function getCsvDirectRawData(string $filePath): array
    {
        $csvDirectRawData = [];

        try {
            $csvDirectRawData = $this->csv->getData($filePath);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
        return $csvDirectRawData;
    }

    /**
     * @param string $filePath
     * @return array
     */
    public function getCsvColumnNames(string $filePath): array
    {
        $csvColumnNames = [];
        try {
            $csvDirectData = $this->getCsvDirectRawData($filePath);
            $csvColumnNames = array_filter($csvDirectData[0]);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
        return $csvColumnNames;
    }

    /**
     * @param $filePath
     * @return bool
     */
    public function isCsvHeaderCorrect($filePath): bool
    {
        try {
            return ($this->getCsvColumnNames($filePath) === self::EXPECTED_CSV_COLUMNS);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
        return false;
    }

    /**
     * @param array $row
     * @return array
     */
    public function filterExcessiveRowData(array $row)
    {
        return array_filter($row, function ($a) {
            return $a !== "";
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param string $filePath
     * @return array
     */
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

    /**
     * @param array $row
     * @param string $columnName
     * @return string
     */
    public function getRowParam(array $row, string $columnName): string
    {
        return $row[$columnName];
    }

    /**
     * @return array
     */
    public function getSourcesCodes(): array
    {
        $source_codes = [];
        $sourceData = $this->sourceRepository->getList()->getItems();
        foreach ($sourceData as $source) {
            $source_codes[] = $source['source_code'];
        }
        return $source_codes;
    }

    /**
     * @param array $row
     * @param string $columnName
     * @return bool
     */
    public function isRowValueCorrect(array $row, string $columnName): bool
    {
        if ($columnName === "Qty") {
            return is_numeric($this->getRowParam($row, "Qty"));
        }
        if ($columnName === "Source") {
            return in_array($this->getRowParam($row, "Source"), $this->getSourcesCodes());
        }
        return true;
    }

    /**
     * @param array $row
     * @return bool
     */
    public function isRowCorrect(array $row): bool
    {
        foreach (self::VALIDATED_CSV_COLUMNS as $columnName) {
            if (!$this->isRowValueCorrect($row, $columnName)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $filePath
     * @return array
     */
    public function getValidatedCsvData(string $filePath): array
    {
        try {
            $this->validatedCsvData =
                array_filter($this->getRawCsvData($filePath), function ($row) {
                    if ($this->isRowCorrect($row)) {
                        return $row;
                    }
                });
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
        return $this->validatedCsvData;
    }
}
