<?php

declare(strict_types = 1);

namespace Tsg\Improvements;

use \Magento\Framework\Filesystem\Driver\File;
use \Psr\Log\LoggerInterface;

/**
 *
 */
class CsvErrorLogger
{
    /**
     * @var CsvValidator
     */
    private $csvValidator;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $csvLoggerMessage = [];

    /**
     * @param File $file
     * @param CsvValidator $csvValidator
     * @param LoggerInterface $logger
     */
    public function __construct(
        CsvValidator $csvValidator,
        LoggerInterface $logger
    )
    {
        $this->csvValidator = $csvValidator;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function getExpectedColumnsNames() : string
    {
        return implode(" | ", $this->csvValidator::EXPECTED_CSV_COLUMNS);
    }

    /**
     * @param $row
     * @return array
     */
    public function getRowErrors(array $row): string
    {
        $rowErrorMessage = [];

        foreach ($this->csvValidator::VALIDATED_CSV_COLUMNS as $columnName) {
            $rowErrorMessage[] =
                !$this->csvValidator->isRowValueCorrect($row, $columnName)
                    ? "$columnName value in this row is not valid. "
                    : "";
        }
        return implode(" ",$rowErrorMessage);
    }

    /**
     * @param array $row
     * @param string $filePath
     * @return int
     */
    public function getRowIndex(array $row, string $filePath): int
    {
        return array_search($row, $this->csvValidator->getRawCsvData($filePath)) + 2;
    }

    /**
     * @param string $filePath
     * @param string $newLineSymbol
     */
    public function getAllRowsErrors(string $filePath, string $newLineSymbol)
    {
        foreach($this->csvValidator->getRawCsvData($filePath) as $row)
        {
            $this->csvLoggerMessage[] =
                !$this->csvValidator->isRowCorrect($row)
                    ? "The row #{$this->getRowIndex($row, $filePath)} of imported file $filePath was skipped. The reason(s): ".
                    $this->getRowErrors($row).$newLineSymbol
                    : "";
        }
    }

    /**
     * @param string $filePath
     * @param string $newLineSymbol
     */
    public function getHeaderError(string $filePath, string $newLineSymbol)
    {
        $this->csvLoggerMessage[] =
            !$this->csvValidator->isCsvHeaderCorrect($filePath)
                ? "The header of the imported file $filePath is incorrect.
            | {$this->getExpectedColumnsNames()} | are expected;"
                : "";
    }

    /**
     * @param $filePath
     * @return string
     */
    public function csvErrorLogger(string $filePath, string $newLineSymbol) : string
    {
        try {
            $this->getHeaderError($filePath, $newLineSymbol);
            $this->getAllRowsErrors($filePath, $newLineSymbol);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
        return implode(" ", $this->csvLoggerMessage);
    }
}
