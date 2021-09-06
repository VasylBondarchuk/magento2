<?php

declare(strict_types = 1);

namespace Tsg\Improvements\Block\Adminhtml;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Tsg\Improvements\CsvValidator;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Tsg\Improvements\Model\ImportCsvConfigs;
use Tsg\Improvements\CsvErrorLogger;

class Import extends Template
{
    private $csvValidator;
    private $sourceRepository;
    private $configs;
    private $csvErrorLogger;

    public function __construct(
        Context $context,
        CsvValidator $csvValidator,
        SourceRepositoryInterface $sourceRepository,
        ImportCsvConfigs $configs,
        CsvErrorLogger $csvErrorLogger,
        array $data = []
    ) {
        $this->csvValidator = $csvValidator;
        $this->sourceRepository = $sourceRepository;
        $this->configs = $configs;
        $this->csvErrorLogger = $csvErrorLogger;
        parent::__construct($context, $data);
    }

    public function getValidatedCsvData()
    {
        return $this->csvValidator->getValidatedCsvData($this->configs->getLocalImportPath());
    }

    public function getRawCsvData()
    {
        return $this->csvValidator->getRawCsvData($this->configs->getLocalImportPath());
    }

    public function getErrorMessage($filePath)
    {
        return $this->csvErrorLogger->csvErrorLogger($filePath,"<br>");
    }

    public function getSourceList()
    {
        $sourceData = $this->sourceRepository->getList();
        return $sourceData->getItems();
    }

    public function getCsvColumnNames()
    {
        return $this->csvValidator->getCsvColumnNames($this->configs->getLocalImportPath());
    }

    public function getCsvDirectRawData()
    {
        return $this->csvValidator->getCsvDirectRawData($this->configs->getLocalImportPath());
    }
}
