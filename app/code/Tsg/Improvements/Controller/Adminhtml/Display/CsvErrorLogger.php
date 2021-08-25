<?php

declare(strict_types = 1);

namespace Tsg\Improvements\Controller\Adminhtml\Display;

use \Magento\Framework\Filesystem\Driver\File;
use \Psr\Log\LoggerInterface;

class CsvErrorLogger
{
    private $file;
    private $csvValidator;
    private $logger;

    public function __construct(
        File $file,
        CsvValidator $csvValidator,
        LoggerInterface $logger
    )
    {
        $this->file = $file;
        $this->csvValidator = $csvValidator;
        $this->logger = $logger;
    }

    public function getExpectedColumnsNames()
    {
      return implode(" | ", $this->csvValidator::EXPECTED_CSV_COLUMNS);
    }

    public function rowErrorLogger($row)
    {
        $rowLoggerMessages = [];

        foreach ($this->csvValidator::VALIDATED_CSV_COLUMNS as $columnName) {
            $rowLoggerMessages[] = !$this->csvValidator->isRowValueCorrect($row, $columnName)
                ? "<b>$columnName value</b> in this row is not valid. " : "";
        }
        return $rowLoggerMessages;
    }

    public function csvErrorLogger($filePath)
    {
        $csvLoggerMessages = [];
        $expectedColumnNames = $this->getExpectedColumnsNames();

        try {
            if($this->file->isExists($filePath)) {
                if (!$this->csvValidator->isCsvHeaderCorrect($filePath)) {
                    $csvLoggerMessages [] = "The header of the imported file $filePath is incorrect.</br>
                    | $expectedColumnNames | are expected;";
                }
                else {
                    $linesQty = count($this->csvValidator->getRawCsvData($filePath));

                    for ($i = 0; $i < $linesQty; $i++) {
                        $row = $this->csvValidator->getRawCsvData($filePath)[$i];
                        $rowlineNumber = $i + 2;
                        if (!$this->csvValidator->isRowCorrect($row)) {
                            $csvLoggerMessages[] = "The row <b>#$rowlineNumber</b> of your file was skipped. The reason(s): ";
                            $csvLoggerMessages = array_merge($csvLoggerMessages, $this->rowErrorLogger($row));
                            $csvLoggerMessages[] = "<br>";
                        }
                    }
                }
            }
        }catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }

        return implode(" ", $csvLoggerMessages);
    }
}
