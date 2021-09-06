<?php

declare(strict_types = 1);

namespace Tsg\Improvements\Ui\Component\DataProvider;

use \Magento\Ui\DataProvider\AbstractDataProvider;
use Tsg\Improvements\CsvValidator;
use Tsg\Improvements\Model\ImportCsvConfigs;

class CsvImport extends AbstractDataProvider
{
    private $csvValidator;
    private $configs;

    public function __construct(
        CsvValidator $csvValidator,
        ImportCsvConfigs $configs,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->csvValidator = $csvValidator;
        $this->configs = $configs;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getCsvFileDataArray():array
    {
        $csvFileDataArray = [];

        if ($this->csvValidator->getValidatedCsvData($this->configs->getLocalImportPath())) {
            foreach ($this->csvValidator->getValidatedCsvData($this->configs->getLocalImportPath()) as $row) {
                $csvFileDataArray[] = [
                    'sku' => $row['Sku'],
                    'qty' => $row['Qty'],
                    'source' => $row['Source']
                ];
            }
        }
        return $csvFileDataArray;
    }

    public function getData()
    {
        $result = [
            'items' => $this->getCsvFileDataArray(),
            'totalRecords' => count($this->getCsvFileDataArray())
        ];
        return $result;
    }
}
